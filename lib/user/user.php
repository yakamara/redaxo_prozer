<?php

class pz_user
{
    private static $users;
    private $perms,$config,$inline_image,$emails,$active_user = null,$cache = [];

    /**
     * SQL instance.
     *
     * @var pz_sql
     */
    protected $sql;

    /**
     * User role instance.
     *
     * @var rex_user_role_interface
     */
    protected $role;

    /**
     * Class name for user roles.
     *
     * @var string
     */
    protected static $roleClass;

    /**
     * Returns the value for the given key.
     *
     * @param string $key Key
     *
     * @return string value
     */
    public function getValue($key)
    {
        return $this->sql->getValue($key);
    }

    /**
     * Returns the name.
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->sql->getValue('name');
    }

    /**
     * Returns if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return (boolean) $this->sql->getValue('admin');
    }

    /**
     * Returns the language.
     *
     * @return string Language
     */
    public function getLanguage()
    {
        return $this->sql->getValue('language');
    }

    /**
     * Returns if the user has a role.
     *
     * @return bool
     */
    public function hasRole()
    {
        if (self::$roleClass && !is_object($this->role) && ($role = $this->sql->getValue('role'))) {
            $class = self::$roleClass;
            $this->role = $class::get($role);
        }
        return is_object($this->role);
    }

    /**
     * Returns the complex perm for the user.
     *
     * @param string $key Complex perm key
     *
     * @return rex_complex_perm Complex perm
     */
    public function getComplexPerm($key)
    {
        if ($this->hasRole()) {
            return $this->role->getComplexPerm($this, $key);
        }
        return rex_complex_perm::get($this, $key);
    }

    /**
     * Sets the role class.
     *
     * @param string $class Class name
     */
    public static function setRoleClass($class)
    {
        self::$roleClass = $class;
    }

    public function __construct(pz_sql $sql)
    {
        $this->sql = $sql;
        $this->setRoleClass('pz_user_role');

        $this->perms = @unserialize($this->getValue('perms'));
        $this->config = @unserialize($this->getValue('config'));

        if (!is_array($this->perms)) {
            $this->perms = [];
        }

        if (!is_array($this->config)) {
            $this->config = [];
        }

        $this->cache['email_projects'] = [];
    }

    public function getVars()
    {
        $v = ['id','email','status','name'];
        $vars = [];
        foreach ($v as $v) {
            $vars[$v] = $this->getValue($v);
        }
        return $vars;
    }

    public function getId()
    {
        return $this->getValue('id');
    }

    public function getLogin()
    {
        return $this->getValue('login');
    }

    public function getEmail()
    {
        return strtolower($this->getValue('email'));
    }

    public function getEmails()
    {
        // eigene Email nehmen - >addressbuch abfragen und zurück
        if ($this->getValue('email') == '') {
            return [];
        }

        if (isset($this->emails) && is_array($this->emails)) {
            return $this->emails;
        }

        $emails = [];
        if (($address = pz_address::getByEmail($this->getValue('email')))) {
            $emails_tmp = $address->getFieldsByType('EMAIL');
            foreach ($emails_tmp as $email) {
                $emails[] = strtolower($email);
            }
        } else {
            $emails[] = strtolower($this->getValue('email'));
        }

        $this->emails = $emails;

        return $this->emails;
    }

    public function isActive()
    {
        if ($this->getValue('status') == 1) {
            return true;
        }
        return false;
    }

    public function getAPIKey()
    {
        return $this->getValue('digest');
    }

    public function getFolder()
    {
        return rex_path::addonData('prozer', 'users/'.$this->getId());
    }

    public function getInlineImage()
    {
        if ($this->inline_image != '') {
            return $this->inline_image;
        } elseif ($this->getEmail() == '') {
            return pz_user::getDefaultImage();
        } elseif (($address = pz_address::getByEmail($this->getEmail()))) {
            $this->inline_image = $address->getInlineImage();
            return $this->inline_image;
        }

        return pz_user::getDefaultImage();
    }

    public function getComment()
    {
        return $this->getValue('comment');
    }

    // ----------------- static

    public static function getDefaultImage()
    {
        return '/assets/addons/prozer/css/user.png';
    }

    public static function get($id, $refresh = false)
    {
        if (isset(pz_user::$users[$id]) && !$refresh) {
            return pz_user::$users[$id];
        }

        $sql = pz_sql::factory();
        $sql->setQuery('SELECT * FROM pz_user WHERE id = ? LIMIT 2', [$id]);
        $user = null;
        if ($sql->getRows() == 1) {
            $user = new self($sql);
        }
        return pz_user::$users[$id] = $user;
    }

    // ----------------- Date

    public static function getDateTime($datetime = null)
    {
        // TODO: Userspecifix Timezone
        // current default: Europe/Berlin

        if (!$datetime) {
            $datetime = new DateTime();
        }

        $datetime->setTimezone(new DateTimeZone('Europe/Berlin'));
        return $datetime;
    }

    // -----------------

    public function saveToHistory($mode = 'update', $func = '')
    {
        $fields = ['id', 'name', 'status', 'login', 'login_tries', 'lasttrydate', 'last_login', 'session_id', 'cookiekey', 'admin', 'created', 'updated', 'address_id', 'email', 'account_id', 'config', 'perms', 'comment'];

        $sql = pz_sql::factory();
        $sql->setTable('pz_history')
            ->setValue('control', 'user')
            ->setValue('func', $func)
            ->setValue('data_id', $this->getId())
            ->setValue('user_id', pz::getUser()->getId())
            ->setRawValue('stamp', 'NOW()')
            ->setValue('mode', $mode);

        $data = [];
        $data['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
        $data['QUERY_STRING'] = $_SERVER['QUERY_STRING'];

        $data['SCRIPT_URI'] = '';
        if (isset($_SERVER['SCRIPT_URI'])) {
            $data['SCRIPT_URI'] = $_SERVER['SCRIPT_URI'];
        } elseif (isset($_SERVER['SCRIPT_URI'])) {
            $data['SCRIPT_URI'] = $_SERVER['REQUEST_URI'];
        }

        switch ($mode) {
            case('login'):
                break;
            case('update'):
            case('create'):
                $data['fields'] = [];
                foreach ($fields as $field) {
                    $data['fields'][$field] = $this->getValue($field);
                }
                break;
        }

        $sql->setValue('data', json_encode($data));
        $sql->insert();
    }

    public function update($successMessage = null)
    {
        $this->saveToHistory('update');
    }

    public function passwordHash($password)
    {
        $password = pz_login::passwordHash($password);
        $u = pz_sql::factory();
        // $u->debugsql = 1;
        $u->setTable('pz_user');
        $u->setWhere(['id' => $this->getId()]);
        $u->setValue('password', $password);
        $u->setValue('digest', sha1($password));
        $u->update();
    }

    public function create()
    {
        $this->saveToHistory('create');
    }

    public function delete($successMessage = null)
    {
        $this->saveToHistory('delete');
    }

    // ----------------- User Perm

    public function setUserPerm(pz_user_perm $user_perm)
    {
        $this->user_perm = $user_perm;
    }

    public function isMe()
    {
        if (isset($this->user_perm)) {
            return false;
        }
        return true;
    }

    public function getUserPerm()
    {
        return $this->user_perm;
    }

    public function getUserPerms()
    {
        return pz_user_perm::getUserPermsByUserId($this->getId());
    }

    public function getGivenUserPerms()
    {
        return pz_user_perm::getGivenUserPermsByUserId($this->getId());
    }

    // -----------------

    public function hasPerm($perm)
    {
        if (in_array($perm, $this->perms)) {
            return true;
        }
        return false;
    }

    public function addPerm($perm)
    {
        if (!in_array($perm, $this->perms) && is_string($perm)) {
            $this->perms[] = $perm;
        }
    }

    public function removePerm($perm)
    {
        if (in_array($perm, $this->perms)) {
            $perms = [];
            foreach ($this->perms as $p) {
                if ($perm != $p) {
                    $perms[] = $p;
                }
            }
            $this->perms = $perms;
        }
    }

    public function savePerm()
    {
        $perms = [];
        foreach ($this->perms as $p) {
            if (is_string($p)) {
                $perms[] = $p;
            }
        }

        $u = pz_sql::factory();
        // $u->debugsql = 1;
        $u->setTable('pz_user');
        $u->setWhere(['id' => $this->getId()]);
        $u->setValue('perms', serialize($perms));
        $u->update();
    }

    // -----------------

    public function getConfig($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return '';
    }

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function saveConfig()
    {
        $u = pz_sql::factory();
        // $u->debugsql = 1;
        $u->setTable('pz_user');
        $u->setWhere(['id' => $this->getId()]);
        $u->setValue('config', serialize($this->config));
        $u->update();
    }

    public function getStartpage()
    {
        $startpage = $this->getConfig('startpage');
        if ($startpage == '') {
            $startpage = 'projects';
        }
        return $startpage;
    }

    public function getTheme()
    {
        return pz_screen::getTheme();
    }

    // -------------------------------------------------------------------- Customers

    public function getActiveCustomers($filter = [])
    {
        return $this->getCustomers($filter, true);
    }

    public function getCustomers($filter = [], $onlyActiveProjects = false)
    {
        $params = [];
        $where = [];

        if ($onlyActiveProjects) {
            $where[] = 'id in (
  	    select
  	      p.customer_id
  	    from
  	      pz_project as p, pz_project_user as pu
  	    where
  	      pu.project_id=p.id and
  	      pu.user_id = '.pz::getUser()->getId().' and
  	      (pu.calendar = 1 OR pu.admin = 1) and
  	      p.archived=0 and p.archived IS NOT NULL

  	    )';
        }

        $nfilter = [];
        foreach ($filter as $f) {
            switch ($f['field']) {
                case('archived'):
                case('name'):
                    $nfilter[] = $f;
            }
        }

        $filter_return = pz::getFilter($nfilter, $where, $params);

        $sql = pz_sql::factory();
        // $sql->debugsql = 1;
        $sql->setQuery('SELECT c.* FROM pz_customer c '.$filter_return['where_sql'].' ORDER BY c.name', $filter_return['params']);

        $customers = [];
        foreach ($sql->getArray() as $row) {
            $customer = new pz_customer($row);
            $customers[$customer->getId()] = $customer;
        }
        return $customers;
    }

    public function getCustomersAsString($filter = [])
    {
        $return = [];
        foreach (self::getCustomers($filter) as $customer) {
            $v = $customer->getName();
            $v = str_replace('=', '', $v);
            $v = str_replace(',', '', $v);
            $return[] = $v.'='.$customer->getId();
        }
        return implode(',', $return);
    }

    /**
     * @param int $customer_id
     * @param array $filter
     * @return array|bool
     */
    public function getCustomerProjects ($customer_id, array $filter = [])
    {
        $filter[] = ['field' => 'customer_id', 'value' => $customer_id];
        $projects = $this->_getProjects('', true, $filter);

        if (count($projects) == 0) {
            return false;
        }

        return $projects;
    }

    // -------------------------------------------------------------------- Cal

    public function getAllEvents(array $projects, DateTime $from = null, DateTime $to = null)
    {
        // !! time matters
        $events = pz_calendar_event::getAll($projects, $from, $to);
        $jobs = $this->getJobs($projects, $from, $to);
        $attandee_events = pz_calendar_event::getAttendeeEvents($from, $to);
        $events = $events + $jobs + $attandee_events;

        return $events;
    }

    public function getEvents(array $projects, DateTime $from = null, DateTime $to = null)
    {
        // !! time matters
        return pz_calendar_event::getAll($projects, $from, $to, false, $this->getId());
    }

    public function getJobs(array $projects, DateTime $from = null, DateTime $to = null)
    {
        // !! time matters
        $events = pz_calendar_event::getAll($projects, $from, $to, true, $this->getId());
        return $events;
    }

    public function getJobTime(array $projects, DateTime $from = null, DateTime $to = null)
    {
        return pz_calendar_event::getJobTime($projects, $this->getId(), $from, $to);
    }

    public function getEventEditPerm(pz_calendar_event $event)
    {
        // solange bis es eingebaut ist.

        if ($event->isRuleEvent()) {
            return false;
        }

        if ($event->getUserId() == $this->getId()) {
            foreach ($event->getAttendees() as $attendee) {
                if (pz_calendar_attendee::ROLE_CHAIR == $attendee->getRole() && $attendee->getUserId() != $this->getId()) {
                    return false;
                }
            }

            return true;
        }

        // TODO: check if in project and/or projektadmin
        return false;
    }

    public function getEventDeletePerm(pz_calendar_event $event)
    {
        // solange bis es eingebaut ist.

        if ($event->isRuleEvent()) {
            return false;
        }

        if ($event->getUserId() == $this->getId()) {
            return true;
        }

        // TODO: check if in project and/or projektadmin
        return false;
    }

    public function getEventViewPerm(pz_calendar_event $event)
    {
        // TODO:
        // wenn man im projekt dieses events ist oder besitzer des events
        $filter = [];
        $filter[] = ['field' => 'id', 'value' => $event->getProject()->getId()];
        $projects = $this->getCalendarProjects();
        if (count($projects) > 0) {
            return true;
        }
        return false;
    }

    public function getAttandeeEvents(DateTime $from = null, DateTime $to = null, $ignore = [])
    {
        // $ignore = array(pz_calendar_attendee::NEEDSACTION, pz_calendar_attendee::ACCEPTED, pz_calendar_attendee::TENTATIVE, pz_calendar_attendee::DECLINED);
        $events = pz_calendar_event::getAttendeeEvents($from, $to, $this, $ignore);
        return $events;
    }

    public function countAttendeeEvents()
    {
        $ignore = [pz_calendar_attendee::STATUS_ACCEPTED, pz_calendar_attendee::STATUS_TENTATIVE, pz_calendar_attendee::STATUS_DECLINED];
        $events = pz_calendar_event::getAttendeeEvents(pz::getDateTime(), null, null, $ignore);
        return count($events);
    }

    // -------------------------------------------------------------------- E-Mail Account

    public function getEmailaccounts()
    {
        return pz_email_account::getAccounts($this->getId());
    }

    public function getEmailaccountsAsString()
    {
        $return = [];
        foreach (pz_email_account::getAccounts($this->getId()) as $email_account) {
            $v = $email_account->getName();
            $v = str_replace('=', '', $v);
            $v = str_replace(',', '', $v);
            $return[] = $v.'='.$email_account->getId();
        }
        return implode(',', $return);
    }

    public function getDefaultEmailaccountId()
    {
        if ($this->getValue('account_id') > 0) {
            return $this->getValue('account_id');
        }

        $accounts = $this->getEmailaccounts();

        if (is_array($accounts) && count($accounts) > 0) {
            $account = current($accounts);
            return $account->getId();
        }
        return false;
    }

    // -------------------------------------------------------------------- Users
    /**
     * @param array $filter
     * @return pz_user[] array
     */
    public static function getUsers($filter = [])
    {
        $filter[] = ['field' => 'status', 'type' => '=', 'value' => 1];
        return $users = pz::getUsers($filter);
    }

    // -------------------------------------------------------------------- Addresses

    public function getAddresses($fulltext = '')
    {
        $filter = [];
        if ($fulltext != '') {
            $filter[] = ['field' => 'vt','type' => 'like','value' => $fulltext];
        }
        $filter[] = ['field' => 'created_user_id','value' => $this->getId()];
        return pz_address::getAll($filter);
    }

    // -------------------------------------------------------------------- Projects

    public function getArchivedProjects($filter = [])
    {
        // Alle nicht archivierten (archived != 1) Projekte
        // + in den man eingtragen ist (table:project_user) ODER man hat in seinen Rolle den Projekt Admin

        $filter[] = ['field' => 'archived', 'value' => 1];

        if ($this->isAdmin()) {
            return $this->_getProjects('', false, $filter);
        }

        return $this->_getProjects('', true, $filter);
    }

    public function getAllProjects($filter = [])
    {
        if ($this->isAdmin()) {
            return $this->_getProjects('', false, $filter);
        }
        return $this->_getProjects('', true, $filter);
    }

    public function getProjects($filter = [], $orders = null)
    {
        // Alle nicht archivierten (archived != 1) Projekte
        // + in den man eingtragen ist (table:project_user)

        $filter[] = ['field' => 'archived', 'value' => 0];
        if ($this->isAdmin()) {
            $join = false;
            if (rex_request('search_projectuser', 'string') != '') {
                $join = true;
            }
            return $this->_getProjects('', $join, $filter, $orders);
        }

        return $this->_getProjects('', true, $filter, $orders);
    }

    public function getMyProjects($filter = [], $orders = null)
    {
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('', true, $filter, $orders);
    }

    public function getCalendarProjects($filter = [])
    {
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('((pu.calendar = 1 OR pu.calendar_jobs = 1 OR pu.admin = 1) and (p.has_calendar = 1 OR p.has_calendar_jobs = 1))', true, $filter);
    }

    public function getCalendarJobsProjects($filter = [])
    {
        $filter[] = ['field' => 'has_calendar_jobs', 'value' => 1];
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('(pu.calendar_jobs = 1 OR pu.admin = 1)', true, $filter);
    }

    public function getCalendarCalProjects($filter = [])
    {
        $filter[] = ['field' => 'has_calendar', 'value' => 1];
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('(pu.calendar = 1 OR pu.admin = 1)', true, $filter);
    }

    public function getCalDavProjects($filter = [])
    {
        $filter[] = ['field' => 'has_calendar', 'value' => 1];
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('(pu.caldav = 1 and (pu.calendar = 1 or pu.admin = 1) )', true, $filter);
    }

    public function getCalDavJobsProjects($filter = [])
    {
        $filter[] = ['field' => 'has_calendar_jobs', 'value' => 1];
        $filter[] = ['field' => 'archived', 'value' => 0];
        return $this->_getProjects('(pu.caldav_jobs = 1 and (pu.calendar_jobs = 1  or pu.admin = 1) )', true, $filter);
    }

    public function getWebDavProjects($filter = [])
    {
        if (pz::getUser()->hasPerm('webdav') || pz::getUser()->isAdmin()) {
            $filter[] = ['field' => 'has_files', 'value' => 1];
            $filter[] = ['field' => 'archived', 'value' => 0];
            return $this->_getProjects('(pu.files = 1 OR pu.admin = 1)', true, $filter);
        }
        return [];
    }

    public function getEmailProjects($filter = [], $refresh = true)
    {
        $serialized_filter = serialize($filter);
        if ($refresh && !isset($this->cache['email_projects'][$serialized_filter])) {
            $filter[] = ['field' => 'has_emails', 'value' => 1];
            $this->cache['email_projects'][$serialized_filter] = $this->_getProjects('(pu.emails = 1 OR pu.admin = 1)', true, $filter);
        }

        return $this->cache['email_projects'][$serialized_filter];
    }

    public function getProjectById($project_id)
    {
        $filter = [];
        $filter[] = ['field' => 'id', 'value' => $project_id];
        $projects = $this->_getProjects('', true, $filter);
        if (count($projects) != 1) {
            return false;
        }
        $project = current($projects);
        return $project;
    }

    /**
     * @param string $where_string
     * @param bool   $join
     * @param array  $filter
     * @param string $orderby
     * @return pz_project[]
     */
    private function _getProjects($where_string = '', $join = true, $filter = [], $orderby = 'p.name')
    {
        $where = [];
        if ($where_string != '') {
            $where[] = $where_string;
        }
        $orderby = (empty($orderby) && !is_array($orderby))? 'p.name' : $orderby;

        if(is_array($orderby))
        {
            $order_array = [];
            foreach ($orderby as $order) {
                $order_array[] = 'p.`'.$order['orderby'].'` '.$order['sort'];
            }

            $order_sql = '';
            if (count($order_array) > 0) {
                $order_sql = ''.implode(',', $order_array);
            }
            $orderby = $order_sql;
        }

        $params = [];
        if ($join) {
            $join     = ' INNER JOIN pz_project_user pu ON pu.project_id = p.id';
            $where[]  = 'pu.user_id = ?';
            $params[] = $this->getId();
        }

        // ----- Filter

        $nfilter = [];
        foreach ($filter as $f) {
            if (isset($f['field'])) {
                switch ($f['field']) {
                    case('id'):
                    case('name'):
                    case('archived'):
                    case('customer_id'):
                    case('has_calendar'):
                    case('has_calendar_jobs'):
                    case('has_files'):
                    case('has_emails'):
                    case('label_id'):
                    case('create_user_id'):
                        $f['field'] = 'p.'.$f['field'];
                        $nfilter[] = $f;
                        break;
                    case('user_id'):
                        $f['field'] = 'pu.'.$f['field'];
                        $nfilter[] = $f;
                        if ($this->isAdmin()) {
                            unset($where, $params);
                        }
                        break;
                }
            }
        }

        // ----- Filter

        $f = pz::getFilter($nfilter, $where, $params);
        $where = $f['where'];
        $params = $f['params'];
        $where_sql = $f['where_sql'];

        $sql = pz_sql::factory();
        $sql->setQuery('SELECT p.* FROM pz_project p'. $join .' '. $where_sql .' ORDER BY '.$orderby, $params);
        $projects = [];
        foreach ($sql->getArray() as $row) {
            $projects[] = new pz_project($row);
        }
        return $projects;
    }

    // -------------------------------------------------------------------- emails


    public function countInboxEmails()
    {
        $filter = [];
        $filter[] = ['field' => 'send', 'value' => 0];
        $filter[] = ['field' => 'trash', 'value' => 0];
        $filter[] = ['field' => 'draft', 'value' => 0];
        $filter[] = ['field' => 'spam', 'value' => 0];
        $filter[] = ['field' => 'status', 'value' => 0];
        $filter[] = ['field' => 'readed', 'value' => 0];
        $projects = pz::getUser()->getEmailProjects([['field' => 'archived', 'value' => 0]]);

        $pager = new pz_pager();
        pz_email::getAll($filter, $projects, [pz::getUser()], [], $pager);
        // echo "*****".$pager->getRowCount();
        return $pager->getRowCount();
    }

    public function getInboxEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        $filter[] = ['field' => 'send', 'value' => 0];
        $filter[] = ['field' => 'trash', 'value' => 0];
        $filter[] = ['field' => 'draft', 'value' => 0];
        $filter[] = ['field' => 'spam', 'value' => 0];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getOutboxEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        $filter[] = ['field' => 'send', 'value' => 1];
        $filter[] = ['field' => 'trash', 'value' => 0];
        $filter[] = ['field' => 'draft', 'value' => 0];
        $filter[] = ['field' => 'spam', 'value' => 0];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getSpamEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        $filter[] = ['field' => 'send', 'value' => 0];
        $filter[] = ['field' => 'trash', 'value' => 0];
        $filter[] = ['field' => 'draft', 'value' => 0];
        $filter[] = ['field' => 'spam', 'value' => 1];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getTrashEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        $filter[] = ['field' => 'trash', 'value' => 1];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getDraftsEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        $filter[] = ['field' => 'draft', 'value' => 1];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getAllEmails(array $filter = [], array $projects = [], $orders = [], $pager = '')
    {
        // $filter[] = array("field" => "trash", "value" => 0);
        // $filter[] = array("field" => "draft", "value" => 0);
        $filter[] = ['field' => 'spam', 'value' => 0];
        return pz_email::getAll($filter, $projects, [pz::getUser()], $orders, $pager);
    }

    public function getEmailById($email_id)
    {
        if ($this->isAdmin()) {
            if (($email = pz_email::get($email_id))) {
                return $email;
            }
            return false;
        }

        $filter = [];
        $filter[] = ['field' => 'id', 'value' => $email_id];

        $projects = [];
        $projects = $this->getEmailProjects();

        $emails = pz_email::getAll($filter, $projects, [pz::getUser()]);

        if (count($emails) != 1) {
            return false;
        }
        $email = current($emails);
        return $email;
    }
}
