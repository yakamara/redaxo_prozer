<?php

class pz_tools_controller_screen extends pz_tools_controller {

	var $name = "tools";
	var $function = "";
	var $functions = array("profile", "jobs", "tracker"); // "userrole", "clipboard", "sms", "shared",
	var $function_default = "profile";
	var $navigation = array("profile", "jobs"); // "userrole", "clipboard", "shared", "sms" 

	// Profile: persönliche einstellungen (daten, zeitzone, sprache, mailsettings mit signatur, passwort, 

	function controller($function) {
	
		if(pz::getUser()->isAdmin()) { 
			$this->functions[] = "users"; 
			$this->navigation[] = "users";
			$this->functions[] = "system"; 
			$this->navigation[] = "system"; 
		}

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = $this->function;

		switch($this->function)
		{
			case("shared"): return $this->getSharedPage($p);
			case("tracker"): return $this->getTracker($p);
			case("profile"): return $this->getProfilePage($p);
			case("users"): return $this->getUsersPage($p);
			case("jobs"): return $this->getJobsPage($p);
			case("system"): return $this->getSystemPage($p);
			default: return $this->getProfilePage($p);
		}
	}

	// -------------------------------------------------------

	function getNavigation($p = array())
	{
		return pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name);
	}

	// ------------------------------------------------------- page views

	function getTracker() 
	{
		$return = '<script language="Javascript">';
		$emails = pz::getUser()->countInboxEmails();
		if($emails == 0) {
			$return .= '$("ul#navi-main li.emails span").remove();';
		} else {
			$return .= '$("ul#navi-main li.emails span").remove().prepend(\'<span class="info1"><span class="inner">'.$emails.'</span></span>\');';
			$return .= '$("ul#navi-main li.emails:not(:has(span))").prepend(\'<span class="info1"><span class="inner">'.$emails.'</span></span>\');';
		}
		
		$attandees = pz::getUser()->countAttendeeEvents();
		if($attandees == 0) {
			$return .= '$("ul#navi-main li.calendars span").remove();';
		} else {
			$return .= '$("ul#navi-main li.calendars span").remove().prepend(\'<span class="info1"><span class="inner">'.$attandees.'</span></span>\');';
			$return .= '$("ul#navi-main li.calendars:not(:has(span))").prepend(\'<span class="info1"><span class="inner">'.$attandees.'</span></span>\');';
		}
		
		
		$return .= '</script>';
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
		$search_name = rex_request('search_name','string');
		
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
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("name")));
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));
		$searchform .= $xform->getForm();
		
		$searchform = '<div id="job_search" class="design1col xform-search">'.$searchform.'</div>';
		
		// ----------------------- jobliste
		
		$project_ids = array();
		$projects = pz::getUser()->getMyProjects();
		foreach($projects as $project) { $project_ids[] = $project->getId();} 

		$jobs = pz_calendar_event::getAll($project_ids, null, null, true, pz::getUser()->getId(), array('from'=>'desc'), $search_name);

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
						"search_name" => rex_request("search_name")
					)).'">'.rex_i18n::msg('excel_export').'</a>';

		$jobs_list = pz_calendar_event_screen::getUserJobsTableView(
					$jobs,
					array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
				);
		
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
		return $f->parse('pz_screen_main');
	}



	function getProfilePage($p = array()) 
	{

		$p["title"] = rex_i18n::msg("userperm_list");
		$p["layer"] = "userperm_list";

		$user = pz::getUser();
		$u_screen = new pz_user_screen($user);
		
		$mode = rex_request("mode","string");
		switch($mode) {
			case("toggle_caldav_events"):
				$return = "";
				$project_id = rex_request("project_id","int");
				if( ($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user,$project))) {
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
					$return.= '$(".project-'.$project->getId().'-caldavevents").removeClass("status'.$icon_status_inactive.'");';
					$return.= '$(".project-'.$project->getId().'-caldavevents").addClass("status'.$icon_status_active.'");';
					$return.= '</script>';
				}
				return $return;
				
			case("toggle_caldav_jobs"):
				$return = "";
				$project_id = rex_request("project_id","int");
				if( ($project = pz_project::get($project_id)) && $project->hasCalendar() && ($projectuser = pz_projectuser::get($user,$project))) {
					$status = 1;
					if($projectuser->hasCalDAVJobs())
						$status = 0;
					$status = $projectuser->setCalDavJobs($status);

					$icon_status_active = 0; // no
					$icon_status_inactive = 1; // yes
					if($status == 1) {
						$icon_status_active = 1; // yes
						$icon_status_inactive = 0; // no
					}
					$return.= '<script language="Javascript">';
					$return.= '$(".project-'.$project->getId().'-caldavjobs").removeClass("status'.$icon_status_inactive.'");';
					$return.= '$(".project-'.$project->getId().'-caldavjobs").addClass("status'.$icon_status_active.'");';
					$return.= '</script>';
				}
				return $return;
			
			case("list_userperm"):
				$projects = $user->getMyProjects();
				return $u_screen->getProjectTableView($p,$projects);
				
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
		$section_2 = $u_screen->getProjectTableView($p,$projects);
		
		$section_3 = ""; // Userrechte an andere geben";
		
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name) , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		$f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}	

	public function getUsersPage($p = array())
	{
		$p["title"] = rex_i18n::msg("users");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "users";
		$p["layer"] = "users_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$filter = array();
		$search_name = rex_request("search_name","string");
		if($search_name != "")
			$filter[] = array('type'=>'orlike', 'field' => 'name,email', 'value'=>$search_name);

		$mode = rex_request('mode', 'string');

		if(pz::getUser()->isAdmin()) {
			switch($mode) {
				
				case("add_user"):
					return pz_user_screen::getAddForm($p);;
			
				case("edit_user"):
					$user_id = rex_request("user_id","int");
					$u = pz_user::get($user_id);
					$u_screen = new pz_user_screen($u);
					return $u_screen->getEditForm($p);
				
			}
		}		
		$section_1 = pz_user_screen::getSearchForm($p);
		if(pz::getUser()->isAdmin())
			$section_1.= pz_user_screen::getAddForm($p);

		$users = pz::getUser()->getUsers($filter);
		$section_2 = pz_user_screen::getTableListView(
						$users,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
		
		switch($mode)
		{
			case('list'):
				return $section_2;
				break;
			default:
				break;
		}
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}

	public function getSystemPage($p = array())
	{
		$p["title"] = rex_i18n::msg("system");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "system";
		
		
		$section_1 = '			
			<div id="server_info">
				<div id="server_view" class="design1col xform-edit">
		        <header>
		          <div class="header">
		            <h1 class="hl1">Server - Info</h1>
		          </div>
        		</header>
        		<div class="xform">
        			Domain: <b>http://'.rex::getProperty('server').'</b>
        			<br /><br />REDAXO Version: <b>'.rex::getVersion().'</b>
        			<br />Prozer Version: <b></b>
					<br /><br />Disk Total Space: <b>'.pz::readableFilesize(disk_total_space("/")).'</b>
					<br />Disk Free Space: <b>'.pz::readableFilesize(disk_free_space("/")).'</b>
					<br />memory_get_usage: <b>'.pz::readableFilesize(memory_get_usage()).'</b>
					
					<br /><br />Sprache: <b>'.rex::getProperty('lang').'</b>
        		</div>
        		</div>
        	</div>
			';
			
		$section_2 = '';
		/*
						* Anfangsbild setzen können
				<br />	* Anfangstext setzen können
				<br />  * Firmenlogo setzen können
				<br />  *';
		*/

		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');

	}


	public function getSharedPage($p = array())
	{
		$p["title"] = rex_i18n::msg("users");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "shared";
		$p["layer"] = "shared_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		// ----------------------- searchform		
	
		switch($mode) {
			
			case("add_user"):
				return pz_user_screen::getAddForm($p);;
		
			case("edit_user"):
				$user_id = rex_request("user_id","int");
				$u = pz_user::get($user_id);
				$u_screen = new pz_user_screen($u);
				return $u_screen->getEditForm($p);
			
		}
		
		$section_1 = pz_user_screen::getSearchForm($p);
		$section_1.= pz_user_screen::getAddForm($p);

		// ----------------------- jobliste

		$users = pz::getUsers();
		$section_2 = pz_user_screen::getTableListView(
						$users,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
		
		
		switch($mode)
		{
			case('list'):
				return $section_2;
				break;
			default:
				break;
		}
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}

}