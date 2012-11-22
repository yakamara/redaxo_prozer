<?php

class pz_tools_controller_screen extends pz_tools_controller {

	var $name = "tools";
	var $function = "";
	var $functions = array("clean", "profile", "jobs", "tracker", "perms", "emailsetup");
	var $function_default = "profile";
	var $navigation = array("profile", "jobs", "perms", "emailsetup");

	// Profile: persönliche einstellungen (daten, zeitzone, sprache, mailsettings mit signatur, passwort, 

	function controller($function) {
	
		if(!in_array($function,$this->functions)) $function = $this->function_default;
		if(!pz::getUser()->isMe() && $function != "tracker") {
			$function = "clean";
			$this->navigation = array("no_page");
		}

		$this->function = $function;

		$p = array();
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = $this->function;

		switch($this->function)
		{
			case("clean"): return $this->getCleanPage($p);
			case("tracker"): return $this->getTracker($p);
			case("profile"): return $this->getProfilePage($p);
			case("perms"): return $this->getPermsPage($p);
			case("jobs"): return $this->getJobsPage($p);
			case("emailsetup"): return $this->getEmailSetupPage($p);
			default: return $this->getProfilePage($p);
		}
	}

	// -------------------------------------------------------

	function getNavigation($p = array())
	{
		return pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name);
	}

	// ------------------------------------------------------- page views

	private function getCleanPage($p = array())
	{
	
		$p = ".";
		$section_1 = "..";
		$section_2 = "...";

		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', "" , false); // $this->getNavigation()
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	
	}


	function getTracker() 
	{
		$emails = pz::getUser()->countInboxEmails();
		$attandees = pz::getUser()->countAttendeeEvents();
		$title = pz_screen::getPageTitle().' ['.$emails.']';
		$return = '<script>pz_updateInfocounter('.$emails.', '.$attandees.',"'.$title.'");</script>';
		return $return;
	}


	private function getJobsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("jobs");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "jobs";
		$p["layer"] = "jobs_list";
		$p["layer_list"] = "jobs_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');
		$search_title = rex_request('search_title','string');
		$search_date_from = null;
		$search_date_to = null;
		$search_project_id = "";
		
		if(rex_request("search_date_from","string") != "" && ($date_object = DateTime::createFromFormat("Y-m-d", rex_request("search_date_from","string")))) 
		{
			$search_date_from = $date_object;
			$p["linkvars"]["search_date_from"] = $date_object->format("Y-m-d");;
		}
				
		if(rex_request("search_date_to","string") != "" && ($date_object = DateTime::createFromFormat("Y-m-d", rex_request("search_date_to","string")))) 
		{
			$search_date_to = $date_object;
			$p["linkvars"]["search_date_to"] = $date_object->format("Y-m-d");;
		}
		
		$project_ids = array();
    // $projects = pz::getUser()->getProjects();
	  $user_ids = array(pz::getUser()->getId());
		$projects = pz::getUser()->getMyProjects();
		
		foreach($projects as $project) { $project_ids[] = $project->getId();}

    if(rex_request("search_project_id","int") != 0 && ($project = pz::getUser()->getProjectById(rex_request("search_project_id","int"))) ) {
    	$project_ids = array($project->getId());
    	$p["linkvars"]["search_project_id"] = $project->getId();
    }

		
		
		// ----------------------- searchform		
		$searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("search_for_jobs").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", 
			"javascript:pz_loadFormPage('jobs_list','job_search_form','".pz::url('screen','tools',$this->function)."')");
		$xform->setObjectparams("form_id", "job_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
		$xform->setValueField("text",array("search_title",rex_i18n::msg("title")));
		
		$xform->setValueField("pz_date_screen",array("search_date_from",rex_i18n::msg("search_date_from")));
		$xform->setValueField("pz_date_screen",array("search_date_to",rex_i18n::msg("search_date_to")));
		
		$projects = pz::getUser()->getCalendarProjects();
		$xform->setValueField("pz_select_screen",array("search_project_id",rex_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",0,rex_i18n::msg("please_choose")));
		
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));
		$searchform .= $xform->getForm();
		
		$searchform = '<div id="job_search" class="design1col xform-search">'.$searchform.'</div>';
		
		// ----------------------- jobliste
		
		$jobs = pz_calendar_event::getAll($project_ids, $search_date_from, $search_date_to, true, $user_ids, array('from'=>'desc'), $search_title);


		$hours = 0;
		$minutes = 0;
		foreach($jobs as $j) { 
			$hours += $j->getDuration()->format("%h"); 
			$minutes += $j->getDuration()->format("%i");
		};

		$hfm = (int) ($minutes/60);
		$hours += $hfm;
		$minutes = $minutes - ($hfm*60);

		if($hours == 0) $hours = '';
		else $hours .= 'h';

		if($minutes == 0) $minutes = '';
		else $minutes .= 'm';


		$p["list_links"] = array();
		$p["list_links"][] = rex_i18n::msg('jobtime_total').' '.$hours.' '.$minutes.'';
		$p["list_links"][] = '<a href="'.pz::url('screen','tools',$this->function,array(
						"mode" =>"export_excel",
						"search_title" => rex_request("search_title"),
						"search_date_from" => rex_request("search_date_from"),
						"search_date_to" => rex_request("search_date_to"),
						"search_project_id" => rex_request("search_project_id"),
					)).'">'.rex_i18n::msg('excel_export').'</a>';

    $p["linkvars"]["mode"] = "list";
		$jobs_list = pz_calendar_event_screen::getUserJobsTableView($jobs, $p);
		
		switch($mode)
		{
			case('export_excel'):
				return pz_calendar_event_screen::getExcelExport($jobs);
			case('list'):
				return $jobs_list;
				break;
			default:
				break;
		}
	
		$section_1 = $searchform;
		$section_2 = $jobs_list;
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}

  public function getEmailSetupPage($p = array()) 
  	{
  		$p["title"] = rex_i18n::msg("email_setup");
  		$p["mediaview"] = "screen";
  		$p["controll"] = "tools";
  		$p["function"] = "emailsetup";
  
  		$s1_content = "";
  		$s2_content = "";
  
  		$return = "";
  		$mode = rex_request("mode","string");
  		switch($mode)
  		{
  			case("add_email_account"):
  				return pz_email_account_screen::getAddForm($p);
  				break;
  				
  			case("delete_email_account"):
  				$email_account_id = rex_request("email_account_id","int",0);
  				if($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id,pz::getUser()->getId())) 
  				{
  					$email_account->delete();
  					$p["info"] = '<p class="xform-info">'.rex_i18n::msg("email_account_delete").'</p>';
  				}else {
  					$p["info"] = '<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
  				}
  				
  			case("list"):
  				$email_accounts = pz::getUser()->getEmailaccounts();
  				$return .= pz_email_account_screen::getAccountsListView(
  								$email_accounts,
  								array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
  							);
  				return $return;
  				break;
  			case("edit_email_account"):
  				$email_account_id = rex_request("email_account_id","int",0);
  				if($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id)) {
  					$cs = new pz_email_account_screen($email_account);
  					return $cs->getEditForm($p);
  				}else {
  					return '<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
  				}
  				break;
  			case(""):
  				$email_accounts = pz::getUser()->getEmailaccounts();
  				$s2_content = pz_email_account_screen::getAccountsListView(
  						$email_accounts,
  						array_merge( $p, array("linkvars" => array( "mode" =>"list" ) )
  					)
  				);
  				$s1_content .= pz_email_account_screen::getAddForm($p);
  				break;
  			default:
  				break;
  		}
  
  		$f = new rex_fragment();
  		$f->setVar('header', pz_screen::getHeader($p), false);
  		$f->setVar('function', $this->getNavigation($p), false);
  		$f->setVar('section_1', $s1_content, false);
  		$f->setVar('section_2', $s2_content, false);
  
  		return $f->parse('pz_screen_main.tpl');
  	}
  

	function getProfilePage($p = array()) 
	{

		$p["title"] = rex_i18n::msg("userperm_list");
		$p["layer"] = "userperm_list";
		$p["linkvars"] = array();
		$p["linkvars"]["mode"] = "list";

		$user = pz::getUser();
		$u_screen = new pz_user_screen($user);
		
		$mode = rex_request("mode","string");
		switch($mode) {
			case("toggle_caldav_events"):
				$return = "";
				$project_id = rex_request("project_id","int");
				if( ($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user,$project))) {
				
				  if(!$projectuser->hasCalendarEvents())
            return;
				
					$status = 1;
					if($projectuser->hasCalDAVEvents())
						$status = 0;
					$status = $projectuser->setCalDavEvents($status);

					$icon_status_active = 0; // no
					$icon_status_inactive = 1; // yes
					if($status == 1) {
						$icon_status_active = 1; // yes
						$icon_status_inactive = 0; // no
					}
					$return.= '<script language="Javascript">';
					$return.= '$(".project-'.$project->getId().'-caldavevents").removeClass("status-'.$icon_status_inactive.'");';
					$return.= '$(".project-'.$project->getId().'-caldavevents").addClass("status-'.$icon_status_active.'");';
					$return.= '</script>';
				}
				return $return;
				
			case("toggle_caldav_jobs"):
				$return = "";
				$project_id = rex_request("project_id","int");
				if( ($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user,$project))) {

          if(!$projectuser->hasCalendarJobs())
            return;
					
					$status = 1;
					if($projectuser->hasCalDAVJobs())
						$status = 0;
						
					pz::debug("caldavstatus",$status);
						
					$status = $projectuser->setCalDavJobs($status);

					$icon_status_active = 0; // no
					$icon_status_inactive = 1; // yes
					if($status == 1) {
						$icon_status_active = 1; // yes
						$icon_status_inactive = 0; // no
					}
					$return.= '<script language="Javascript">';
					$return.= '$(".project-'.$project->getId().'-caldavjobs").removeClass("status-'.$icon_status_inactive.'");';
					$return.= '$(".project-'.$project->getId().'-caldavjobs").addClass("status-'.$icon_status_active.'");';
					$return.= '</script>';
				}
				return $return;
			
			case("list"):
				$projects = $user->getMyProjects();
				return $u_screen->getProjectPermTableListView($p,$projects);
				
			case("edit_user"):
				return $u_screen->getMyEditForm($p);
				
			case("edit_password"):
				return $u_screen->getMyPasswordEditForm($p);
				
			default:		
		}
		
		$section_1 = $u_screen->getMyEditForm($p);
		$section_1.= $u_screen->getMyPasswordEditForm($p);
		
		if(pz::getUser()->isAdmin())
		{
			$section_1.= $u_screen->getApiView($p);
		}
		
		$projects = $user->getMyProjects();
		$section_2 = $u_screen->getProjectPermTableListView($p,$projects);
		
		$section_3 = ""; // Userrechte an andere geben";
		
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name) , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		$f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}	


	public function getPermsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("user_perms");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "perms";
		$p["layer"] = "user_perms_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');
		switch($mode) {

			case("add_user_perm"):
				return pz_user_perm_screen::getAddForm($p);;
		
			case("edit_user_perm"):
				$user_perms = pz::getUser()->getUserPerms();
				$user_perm_id = rex_request("user_perm_id","int");
				$u = pz_user_perm::get($user_perm_id);
				$u_screen = new pz_user_perm_screen($u);
				return $u_screen->getEditForm($p);
			
			case("list_user_perms"):
				$user_perms = pz::getUser()->getUserPerms();
				return pz_user_perm_screen::getTableListView(
						$user_perms,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);

			case("delete_user_perm"):
				$user_perm_id = rex_request("user_perm_id","int");
				$u = pz_user_perm::get($user_perm_id);
				$u->delete();
				$user_perms = pz::getUser()->getUserPerms();
				$return = pz_user_perm_screen::getTableListView(
						$user_perms,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
				return $return; 
			
		}

		$user_perms = pz::getUser()->getUserPerms();
		$section_1 .= pz_user_perm_screen::getAddForm($p);
		$section_2 .= pz_user_perm_screen::getTableListView(
						$user_perms,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
		
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}

}