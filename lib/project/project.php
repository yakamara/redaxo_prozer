<?php

class pz_project extends pz_model
{
    public $vars = [];
    private $isProject = false;
    /** @var pz_label */
    private $label;
    public $customer = null;
    public $users = [];
    private $directory = null;
    private $subprojects = null;

    private static $projects = [];

    public function __construct($vars = [])
    {
        if (count($vars) > 5) {
            $this->setVars($vars);
            $this->isProject = true;
            $this->customer = pz_customer::get($this->getVar('customer_id'));
            return true;
        }
        return false;
    }

    /**
     * @return pz_project
     */
    public static function get($id, $refresh = false)
    {
        if (isset($projects[$id]) && !$refresh) {
            return $projects[$id];
        }

        $sql = pz_sql::factory();
        $sql->setQuery('SELECT * FROM pz_project WHERE id = ? LIMIT 2', [$id]);
        $project = null;
        if ($sql->getRows() == 1) {
            $projects = $sql->getArray();
            $project = new self($projects[0]);
        }
        return $projects[$id] = $project;
    }

    // -------------------------------------------------------------------------

    public function getId()
    {
        return (int) $this->vars['id'];
    }

    public function getName()
    {
        return $this->vars['name'];
    }

    public function getDescription()
    {
        return $this->vars['description'];
    }

    /**
     * @return pz_label
     */
    public function getLabel()
    {
        return $this->label ?: $this->label = pz_label::get($this->vars['label_id']);
    }

    public function getLabelId()
    {
        return $this->vars['label_id'];
    }

    public function getInlineImage()
    {
        if (is_object($this->customer)) {
            return $this->customer->getInlineImage();
        }
        return '/assets/addons/prozer/themes/blue_grey/ic_project.png';
    }

    // -----------------------------

    public function hasCustomer()
    {
        if ($this->customer) {
            return true;
        }
        return false;
    }

    public function getCustomer()
    {
        if ($this->hasCustomer()) {
            return $this->customer;
        } else {
            return false;
        }
    }

    public function getCustomerId()
    {
        if ($this->hasCustomer()) {
            return $this->customer->getId();
        } else {
            return 0;
        }
    }

    // -----------------------------

    public function hasEmails()
    {
        if ($this->vars['has_emails'] == 1) {
            return true;
        }
        return false;
    }

    public function hasCalendar()
    {
        return $this->hasCalendarEvents();
    }

    public function hasCalendarEvents()
    {
        if ($this->vars['has_calendar'] == 1) {
            return true;
        }
        return false;
    }

    public function hasCalendarJobs()
    {
        if ($this->vars['has_calendar_jobs'] == 1) {
            return true;
        }
        return false;
    }

    public function hasFiles()
    {
        if ($this->vars['has_files'] == 1) {
            return true;
        }
        return false;
    }

    public function hasWiki()
    {
        if ($this->vars['has_wiki'] == 1) {
            return true;
        }
        return false;
    }

    // -----------------------------

    public function getJobs(DateTime $from = null, DateTime $to = null, $fulltext = '', $users = null)
    {
        $jobs = pz_calendar_event::getAll([$this->getId()], $from, $to, true, $users, ['from' => 'desc'], $fulltext);
        return $jobs;
    }

    /*
    public function hasEvents(DateTime $from = null, DateTime $to = null)
    {
        $jobs = pz_calendar_event::getAllEvents(array($this->getId()), $from, $to);
        return $jobs;
    }
    */

    public function getCalendarEvents(DateTime $from = null, DateTime $to = null)
    {
        $stream = pz_calendar_event::getAll([$this->getId()], $from, $to, false, null, ['from' => 'desc']);
        return $stream;
    }

    public function getHistoryEntries($filter = [], $limit = 1000)
    {
        $filter[] = ['type' => '=', 'field' => 'project_id', 'value' => $this->getId()];
        $return = pz_history::get($filter, $limit);
        return $return;
    }



    // -----------------------------

    public function getProjectSubs()
    {
        if (isset($this->subprojects) && is_array($this->subprojects)) {
            return $this->subprojects;
        }

        $s = pz_sql::factory();
        $subprojects = $s->getArray('select * from pz_project_sub where project_id = ? ', [$this->getId()]);

        $this->subprojects = [];
        foreach ($subprojects as $s) {
            $this->subprojects[$s['id']] = new pz_project_sub($s);
        }
        return $this->subprojects;
    }

    public function hasProjectSubId($id)
    {
        if ($id == 0) {
            return true;
        }

        if (array_key_exists($id, $this->getProjectSubs())) {
            return true;
        }

        return false;
    }

    // -----------------------------

    public function getUsers()
    {
        if (count($this->users) > 0) {
            return $this->users;
        }

        $s = pz_sql::factory();
        $projectusers = $s->getArray('SELECT * FROM pz_project_user AS pu WHERE pu.project_id='.$this->getId().' ORDER BY pu.admin DESC, pu.user_id ASC');

        foreach ($projectusers as $projectuser) {
            $user = pz_user::get($projectuser['user_id']);
            $this->users[] = new pz_projectuser($projectuser, $user, $this);
        }
        return $this->users;
    }

    public function getProjectuserById($puser_id = 0)
    {
        $s = pz_sql::factory();
        $projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id='.$this->getId().' and pu.id='.$puser_id);

        if (count($projectusers) == 1) {
            $projectuser = current($projectusers);
            $user = pz_user::get($projectuser['user_id']);
            return new pz_projectuser($projectuser, $user, $this);
        }
        return false;
    }

    public function getProjectuserByUserId($user_id = 0)
    {
        $s = pz_sql::factory();
        $projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id='.$this->getId().' and pu.user_id='.$user_id);

        if (count($projectusers) == 1) {
            $projectuser = current($projectusers);
            $user = pz_user::get($projectuser['user_id']);
            return new pz_projectuser($projectuser, $user, $this);
        }
        return false;
    }

    public function getAdmins()
    {
        $admins = [];
        foreach ($this->getUsers() as $projectuser) {
            if ($projectuser->isAdmin()) {
                $admins[] = $projectuser->getUser();
            }
        }
        return $admins;
    }

    public function addUser($user_id, $admin = 0, $perm = [])
    {
        $a = pz_sql::factory();
        // $a->debugsql = 1;
        $a->setTable('pz_project_user');
        $a->setValue('user_id', $user_id);

        $a->setValue('project_id', $this->getId());

        $a->setRawValue('created', 'NOW()');
        $a->setRawValue('updated', 'NOW()');

        $a->setValue('admin', $admin);

        if (!isset($perm['calendar']) || $perm['calendar'] != 1) {
            $perm['calendar'] = 0;
        }
        $a->setValue('calendar', $perm['calendar']);

        if (!isset($perm['wiki']) || $perm['wiki'] != 1) {
            $perm['wiki'] = 0;
        }
        $a->setValue('wiki', $perm['wiki']);

        if (!isset($perm['files']) || $perm['files'] != 1) {
            $perm['files'] = 0;
        }
        $a->setValue('files', $perm['files']);

        if (!isset($perm['webdav']) || $perm['webdav'] != 1) {
            $perm['webdav'] = 0;
        }
        $a->setValue('webdav', $perm['webdav']);

        if (!isset($perm['caldav']) || $perm['caldav'] != 1) {
            $perm['caldav'] = 0;
        }
        $a->setValue('caldav', $perm['caldav']);

        if (!isset($perm['caldav_jobs']) || $perm['caldav_jobs'] != 1) {
            $perm['caldav_jobs'] = 0;
        }
        $a->setValue('caldav_jobs', $perm['caldav_jobs']);

        $a->insert();
    }

    public function deleteUser($projectuser_id)
    {
        if ($projectuser = $this->getProjectuserById($projectuser_id)) {
            if ($projectuser->delete()) {
                return true;
            }
        }
        return false;
    }

    // -----------------------------

    public function getFolder()
    {
        return rex_path::addonData('prozer', 'projects/'.$this->getId());
    }

    public function getFilesFolder()
    {
        return rex_path::addonData('prozer', 'projects/'.$this->getId().'/files');
    }

    public function getDirectory()
    {
        return $this->directory ?: $this->directory = new pz_project_root_directory($this);
    }

    // -----------------------------

    public function getEmails()
    {
        $projects = [];
        $projects[] = $this;
        $filter = [];
        return pz_email::getAll($projects, $filter);
    }

    // -----------------------------

    public function saveToHistory($mode = 'update')
    {
        $sql = pz_sql::factory();
        $sql->setTable('pz_history')
            ->setValue('control', 'project')
            ->setValue('data_id', $this->getId())
            ->setValue('project_id', $this->getId())
            ->setValue('user_id', pz::getUser()->getId())
            ->setRawValue('stamp', 'NOW()')
            ->setValue('mode', $mode);

        // if($mode != 'delete') {
        $data = $this->getVars();
        $data['users'] = [];
        foreach ($this->getUsers() as $u) {
            $data['users'][$u->getId()] = $u->getVars();
        }
        $sql->setValue('data', json_encode($data));
        // }

        $sql->insert();
    }

    public function update()
    {
        $this->saveToHistory('update');
        pz_sabre_caldav_backend::incrementCtag($this->vars['id']);
    }

    public function create()
    {
        rex_dir::create($this->getFilesFolder());
        $this->addUser(pz::getUser()->getId(), 1);
        $this->saveToHistory('create');
        pz_sabre_caldav_backend::incrementCtag($this->vars['id']);
        return $this->getFilesFolder();
    }

    public function delete()
    {
        $this->saveToHistory('delete');

        // TODO: Ordner löschen
        // Projektuser löschen
        // Projektdatensatz löschen
        // ...

        pz_sabre_caldav_backend::incrementCtag($this->vars['id']);
    }

    // -------------------------------------------------------------------

    public static function getProjectIds($projects)
    {
        $ids = [];
        foreach ($projects as $project) {
            $ids[] = $project->getId();
        }
        return $ids;
    }

    public static function getProjectsAsString($projects, $cutText = 100)
    {
        $return = [];
        foreach ($projects as $project) {
            $name = pz::cutText($project->getName(), $cutText).' ['.$project->getId().']';
            $name = str_replace(['=', ','], '', $name);
            $return[] = $name.'='.$project->getId();
        }
        return implode(',', $return);
    }

    public static function getProjectSubsAsString($projects, $cutText = 100)
    {
        $return = [];
        foreach ($projects as $project) {
            foreach ($project->getProjectSubs() as $project_sub) {
                $name = pz::cutText($project_sub->getName(), $cutText).' ['.$project_sub->getId().']';
                $name = str_replace(['=', ','], '', $name);
                $return[] = $name.'='.$project_sub->getId();
            }
        }
        return implode(',', $return);
    }
}
