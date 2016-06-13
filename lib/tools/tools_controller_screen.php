<?php

class pz_tools_controller_screen extends pz_tools_controller
{
    public $name = 'tools';
    public $function = '';
    public $functions = ['clean', 'profile', 'jobs', 'tracker', 'perms', 'emailsetup'];
    public $function_default = 'profile';
    public $navigation = ['profile', 'jobs', 'perms', 'emailsetup'];

    // Profile: persönliche einstellungen (daten, zeitzone, sprache, mailsettings mit signatur, passwort,

    public function controller($function)
    {
        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        if (!pz::getUser()->isMe() && $function != 'tracker') {
            $function = 'clean';
            $this->navigation = ['no_page'];
        }

        $this->function = $function;

        $p = [];
        $p['mediaview'] = 'screen';
        $p['controll'] = 'tools';
        $p['function'] = $this->function;

        switch ($this->function) {
            case('clean'): return $this->getCleanPage($p);
            case('tracker'): return $this->getTracker($p);
            case('profile'): return $this->getProfilePage($p);
            case('perms'): return $this->getPermsPage($p);
            case('jobs'): return $this->getJobsPage($p);
            case('emailsetup'): return $this->getEmailSetupPage($p);
            default: return $this->getProfilePage($p);
        }
    }

    // -------------------------------------------------------

    public function getNavigation($p = [])
    {
        return pz_screen::getNavigation($p, $this->navigation, $this->function, $this->name);
    }

    // ------------------------------------------------------- page views

    private function getCleanPage($p = [])
    {
        $p = '.';
        $section_1 = '..';
        $section_2 = '...';

        $p = [];
        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader(), false);
        $f->setVar('function', '', false); // $this->getNavigation()
        $f->setVar('section_1', $section_1, false);
        $f->setVar('section_2', $section_2, false);
        // $f->setVar('section_3', $section_3 , false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getTracker()
    {
        $emails = pz::getUser()->countInboxEmails();
        $attandees = pz::getUser()->countAttendeeEvents();

        $title = '['.$emails.'] '.pz_screen::getPageTitle();
        $return = '<script>
		pz_updateInfocounter('.$emails.', '.$attandees.',"'.$title.'");
		</script>';

        // Problem

        // Emails oder andere Dinge nachladen die ein neuerest Datum
        // als das aktuellste haben und diese an oberste Stelle der aktuellen
        // Liste stellen

        // Bsp. Inbox / Outbox / Müll / Projektemails / Kalendertermine / jobs / Adressen
        // immer in bestimmtem Kontext: Termine heute / 2 wochen ...

        // regelmaessige prüfung, nachladen und anzeigen

        // TODO: neue emails laden
        // - im tracker die page mit übergeben
        // -
        /*
        $filter = array();
        $filter[] = array('type' => 'plain', 'value' => '( (project_id>0 AND status=0) || (project_id=0))');
        $filter[] = array('type' => 'plain', 'value' => '( createdesc > )');
        $emails = pz::getUser()->getInboxEmails($filter, array(), array('createdesc'), $pager);
        */

        // - last trackingdate setzen
        // - Datum vom letzten Trackeraufruf mit übergeben und im pz_tracker mit übergeben
        // - prüfen ob auf email page
        // - email/s nachladen


        return $return;
    }

    private function getJobsPage($p = [])
    {
        $p['title'] = pz_i18n::msg('jobs');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'tools';
        $p['function'] = 'jobs';
        $p['layer'] = 'jobs_list';
        $p['layer_list'] = 'jobs_list';

        $section_1 = '';
        $section_2 = '';

        $mode = rex_request('mode', 'string');
        $search_title = rex_request('search_title', 'string');
        $search_date_from = null;
        $search_date_to = null;
        $search_project_id = '';

        if (rex_request('search_date_from', 'string') != '' && ($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_from', 'string')))) {
            $search_date_from = $date_object;
            $p['linkvars']['search_date_from'] = $date_object->format('Y-m-d');
        }

        if (rex_request('search_date_to', 'string') != '' && ($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_to', 'string')))) {
            $search_date_to = $date_object;
            $p['linkvars']['search_date_to'] = $date_object->format('Y-m-d');
        }

        $project_ids = [];
        $user_ids = [pz::getUser()->getId()];
        $projects = pz::getUser()->getMyProjects();

        foreach ($projects as $project) {
            $project_ids[] = $project->getId();
        }
        $customer_filter = [];
        if (rex_request('search_project_id', 'int') != 0 && ($project = pz::getUser()->getProjectById(rex_request('search_project_id', 'int')))) {
            $project_ids = [$project->getId()];
            $p['linkvars']['search_project_id'] = $project->getId();

            $customer_filter[] = ['field' => 'id', 'value'=>$project->getId()];
        }

        if (rex_request('search_customer_id', 'int') != 0 && ($customer_projects = pz::getUser()->getCustomerProjects(rex_request('search_customer_id', 'int'), $customer_filter))) {
            $p['linkvars']['search_customer_id'] = rex_request('search_customer_id', 'int');
            unset($project_ids);

            foreach ($customer_projects as $cp) {
                $project_ids[] = $cp->getId();
            }
        }
        // ----------------------- searchform
        $searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg('search_for_jobs').'</h1>
          </div>
        </header>';

        $yform = new rex_yform();
        $yform->setObjectparams('real_field_names', true);
        $yform->setObjectparams('form_showformafterupdate', true);
        $yform->setObjectparams('form_action',
            "javascript:pz_loadFormPage('jobs_list','job_search_form','".pz::url('screen', 'tools', $this->function)."')");
        $yform->setObjectparams('form_id', 'job_search_form');
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl', 'runtime']);
        $yform->setValueField('text', ['search_title', pz_i18n::msg('title')]);

        $yform->setValueField('pz_date_screen', ['search_date_from', pz_i18n::msg('search_date_from')]);
        $yform->setValueField('pz_date_screen', ['search_date_to', pz_i18n::msg('search_date_to')]);

        $projects = pz::getUser()->getCalendarProjects();
        $yform->setValueField('select', ['search_project_id', pz_i18n::msg('project'), pz_project::getProjectsAsString($projects), '', '', 0, pz_i18n::msg('please_choose')]);
        $yform->setValueField('select', ['search_customer_id', pz_i18n::msg('customer'), pz::getUser()->getCustomersAsString(), '', '', 0, pz_i18n::msg('please_choose')]);

        $yform->setValueField('submit', ['submit', pz_i18n::msg('search'), '', 'search']);
        $yform->setValueField('hidden', ['mode', 'list']);
        $searchform .= $yform->getForm();

        $searchform = '<div id="job_search" class="design1col yform-search">'.$searchform.'</div>';

        // ----------------------- jobliste

        $jobs = pz_calendar_event::getAll($project_ids, $search_date_from, $search_date_to, true, $user_ids, ['from' => 'desc'], $search_title);

        $hours = 0;
        $minutes = 0;
        foreach ($jobs as $j) {
            $hours += $j->getDuration()->format('%h');
            $minutes += $j->getDuration()->format('%i');
        };

        $hfm = (int) ($minutes / 60);
        $hours += $hfm;
        $minutes = $minutes - ($hfm * 60);

        if ($hours == 0) {
            $hours = '';
        } else {
            $hours .= 'h';
        }

        if ($minutes == 0) {
            $minutes = '';
        } else {
            $minutes .= 'm';
        }

        $p['list_links'] = [];
        $p['list_links'][] = pz_i18n::msg('jobtime_total').' '.$hours.' '.$minutes.'';
        $p['list_links'][] = '<a href="'.pz::url('screen', 'tools', $this->function, [
                'mode' => 'export_excel',
                'search_title' => rex_request('search_title'),
                'search_date_from' => rex_request('search_date_from'),
                'search_date_to' => rex_request('search_date_to'),
                'search_project_id' => rex_request('search_project_id'),
                'search_customer_id' => rex_request('search_customer_id'),
            ]).'">'.pz_i18n::msg('excel_export').'</a>';

        $p['linkvars']['mode'] = 'list';
        $jobs_list = pz_calendar_event_screen::getUserJobsTableView($jobs, $p);

        switch ($mode) {
            case('export_excel'):
                return pz_calendar_event_screen::getExcelExport($jobs);
            case('list'):
                return $jobs_list;
                break;
            default:
                break;
        }

        $section_1 = $searchform;
        $section_1 .='<div id="calendar_event_form" class="design1col"></div>';
        $section_2 = $jobs_list;

        $p = [];
        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader(), false);
        $f->setVar('function', $this->getNavigation(), false);
        $f->setVar('section_1', $section_1, false);
        $f->setVar('section_2', $section_2, false);
        // $f->setVar('section_3', $section_3 , false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getEmailSetupPage($p = [])
    {
        $p['title'] = pz_i18n::msg('email_setup');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'tools';
        $p['function'] = 'emailsetup';

        $s1_content = '';
        $s2_content = '';

        $return = '';
        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('add_email_account'):
                return pz_email_account_screen::getAddForm($p);
                break;
            case('delete_email_account'):
                $email_account_id = rex_request('email_account_id', 'int', 0);
                if ($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id, pz::getUser()->getId())) {
                    $email_account->delete();
                    if ($email_account_id > 0 && $email_account_id == pz::getUser()->getDefaultEmailAccountId()) {
                        pz::getUser()->saveDefaultUserEmailAccount();
                    }
                    $p['info'] = '<p class="yform-info">'.pz_i18n::msg('email_account_delete').'</p>';
                } else {
                    $p['info'] = '<p class="yform-warning">'.pz_i18n::msg('email_account_not_exists').'</p>';
                }
            case('default_user_email_account'):
                $default_account_id = rex_request('default_account_id', 'int', 0);
                if (empty($email_account_id) && ($default_account_id > 0 || $default_account_id != pz::getUser()->getDefaultEmailAccountId())) {
                    $p[ 'info' ] = '<p class="yform-info">' . pz_i18n::msg('default_email_account_not_changed') . '</p>';
                    if (pz::getUser()->saveDefaultUserEmailAccount($default_account_id)) {
                        $p[ 'info' ] = '<p class="yform-info">' . pz_i18n::msg('default_email_account_changed') . '</p>';
                    }
                }
            case('list'):
                $email_accounts = pz::getUser()->getEmailaccounts();
                $return .= pz_email_account_screen::getAccountsListView(
                    $email_accounts,
                    array_merge($p, ['linkvars' => ['mode' => 'list']])
                );
                return $return;
                break;
            case('edit_email_account'):
                $email_account_id = rex_request('email_account_id', 'int', 0);
                if ($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id)) {
                    $cs = new pz_email_account_screen($email_account);
                    return $cs->getEditForm($p);
                } else {
                    return '<p class="yform-warning">'.pz_i18n::msg('email_account_not_exists').'</p>';
                }
                break;
            case(''):
                $email_accounts = pz::getUser()->getEmailaccounts();
                $s2_content = pz_email_account_screen::getAccountsListView(
                    $email_accounts,
                    array_merge($p, ['linkvars' => ['mode' => 'list']]
                    )
                );
                $s1_content .= pz_email_account_screen::getAddForm($p);
                break;
            default:
                break;
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getProfilePage($p = [])
    {
        $p['title'] = pz_i18n::msg('userperm_list');
        $p['layer'] = 'userperm_list';
        $p['linkvars'] = [];
        $p['linkvars']['mode'] = 'list';

        $user = pz::getUser();
        $u_screen = new pz_user_screen($user);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('toggle_caldav_events'):
                $return = '';
                $project_id = rex_request('project_id', 'int');
                if (($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user, $project))) {
                    if (!$projectuser->hasCalendarEvents()) {
                        return;
                    }

                    $status = 1;
                    if ($projectuser->hasCalDAVEvents()) {
                        $status = 0;
                    }
                    $status = $projectuser->setCalDavEvents($status);

                    $icon_status_active = 0; // no
                    $icon_status_inactive = 1; // yes
                    if ($status == 1) {
                        $icon_status_active = 1; // yes
                        $icon_status_inactive = 0; // no
                    }
                    $return .= '<script language="Javascript">';
                    $return .= '$(".project-'.$project->getId().'-caldavevents").removeClass("status-'.$icon_status_inactive.'");';
                    $return .= '$(".project-'.$project->getId().'-caldavevents").addClass("status-'.$icon_status_active.'");';
                    $return .= '</script>';
                }
                return $return;

            case('toggle_caldav_jobs'):
                $return = '';
                $project_id = rex_request('project_id', 'int');
                if (($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user, $project))) {
                    if (!$projectuser->hasCalendarJobs()) {
                        return;
                    }

                    $status = 1;
                    if ($projectuser->hasCalDAVJobs()) {
                        $status = 0;
                    }

                    pz::debug('caldavstatus', $status);

                    $status = $projectuser->setCalDavJobs($status);

                    $icon_status_active = 0; // no
                    $icon_status_inactive = 1; // yes
                    if ($status == 1) {
                        $icon_status_active = 1; // yes
                        $icon_status_inactive = 0; // no
                    }
                    $return .= '<script language="Javascript">';
                    $return .= '$(".project-'.$project->getId().'-caldavjobs").removeClass("status-'.$icon_status_inactive.'");';
                    $return .= '$(".project-'.$project->getId().'-caldavjobs").addClass("status-'.$icon_status_active.'");';
                    $return .= '</script>';
                }
                return $return;

            case('list'):
                $projects = $user->getMyProjects();
                return $u_screen->getProjectPermTableListView($p, $projects);

            case('edit_user'):
                return $u_screen->getMyEditForm($p);

            case('edit_password'):
                return $u_screen->getMyPasswordEditForm($p);

            default:
        }

        $section_1 = $u_screen->getMyEditForm($p);
        $section_1 .= $u_screen->getMyPasswordEditForm($p);

        if (pz::getUser()->isAdmin()) {
        }

        $section_1 .= $u_screen->getApiView($p);

        $projects = $user->getMyProjects();
        $section_2 = $u_screen->getProjectPermTableListView($p, $projects);

        $section_3 = ''; // Userrechte an andere geben";

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader(), false);
        $f->setVar('function', pz_screen::getNavigation($p, $this->navigation, $this->function, $this->name), false);
        $f->setVar('section_1', $section_1, false);
        $f->setVar('section_2', $section_2, false);
        $f->setVar('section_3', $section_3, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getPermsPage($p = [])
    {
        $p['title'] = pz_i18n::msg('user_perms');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'tools';
        $p['function'] = 'perms';
        $p['layer'] = 'user_perms_list';

        $section_1 = '';
        $section_2 = '';

        $mode = rex_request('mode', 'string');
        switch ($mode) {

            case('add_user_perm'):
                return pz_user_perm_screen::getAddForm($p);

            case('edit_user_perm'):
                $user_perms = pz::getUser()->getUserPerms();
                $user_perm_id = rex_request('user_perm_id', 'int');
                $u = pz_user_perm::get($user_perm_id);
                $u_screen = new pz_user_perm_screen($u);
                return $u_screen->getEditForm($p);

            case('list_user_perms'):
                $user_perms = pz::getUser()->getUserPerms();
                return pz_user_perm_screen::getTableListView(
                    $user_perms,
                    array_merge($p, ['linkvars' => ['mode' => 'list']])
                );

            case('delete_user_perm'):
                $user_perm_id = rex_request('user_perm_id', 'int');
                $u = pz_user_perm::get($user_perm_id);
                $u->delete();
                $user_perms = pz::getUser()->getUserPerms();
                $return = pz_user_perm_screen::getTableListView(
                    $user_perms,
                    array_merge($p, ['linkvars' => ['mode' => 'list']])
                );
                return $return;

        }

        $user_perms = pz::getUser()->getUserPerms();
        $section_1 .= pz_user_perm_screen::getAddForm($p);
        $section_2 .= pz_user_perm_screen::getTableListView(
            $user_perms,
            array_merge($p, ['linkvars' => ['mode' => 'list']])
        );

        $p = [];
        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader(), false);
        $f->setVar('function', $this->getNavigation(), false);
        $f->setVar('section_1', $section_1, false);
        $f->setVar('section_2', $section_2, false);
        // $f->setVar('section_3', $section_3 , false);
        return $f->parse('pz_screen_main.tpl');
    }
}
