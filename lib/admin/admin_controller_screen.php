<?php

class pz_admin_controller_screen extends pz_admin_controller {

	var $name = "admin";
	var $function = "";
	var $functions = array("clean", "profiles", "jobs", "users", "system", "history");
	var $function_default = "system";
	var $navigation = array("system", "profiles", "jobs", "users", "history"); 

	function controller($function) {
	
		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["mediaview"] = "screen";
		$p["controll"] = $this->name;
		$p["function"] = $this->function;

		switch($this->function)
		{
			case("profiles"): return $this->getProfilesPage($p);
			case("users"): return $this->getUsersPage($p);
			case("jobs"): return $this->getJobsPage($p);
			case("system"): return $this->getSystemPage($p);
			case("history"): return $this->getHistoryPage($p);
			default: return $this->getSystemPage($p);
		}
	}

	// -------------------------------------------------------

	function getNavigation($p = array())
	{
		return pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name);
	}

	// ------------------------------------------------------- page views

	private function getJobsPage($p = array())
	{
		$p["title"] = pz_i18n::msg("jobs");
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
	  $projects = pz::getUser()->getProjects();
		
		if ( rex_request("search_user_id","int") != 0) {
		  $user_ids = array(rex_request("search_user_id","int"));
	    $p["linkvars"]["search_user_id"] = current($user_ids);

		}else {
  		$user_ids = array();
		  
		}
  		
		
		foreach($projects as $project) { $project_ids[] = $project->getId();}

    if(rex_request("search_project_id","int") != 0 && ($project = pz::getUser()->getProjectById(rex_request("search_project_id","int"))) ) {
    	$project_ids = array($project->getId());
    	$p["linkvars"]["search_project_id"] = $project->getId();
    }

		
		
		// ----------------------- searchform		
		$searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("search_for_jobs").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", 
			"javascript:pz_loadFormPage('jobs_list','job_search_form','".pz::url($p["mediaview"],$p["controll"],$this->function)."')");
		$xform->setObjectparams("form_id", "job_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
		$xform->setValueField("text",array("search_title",pz_i18n::msg("title")));
		
		$xform->setValueField("pz_date_screen",array("search_date_from",pz_i18n::msg("search_date_from")));
		$xform->setValueField("pz_date_screen",array("search_date_to",pz_i18n::msg("search_date_to")));
		
		$projects = pz::getUser()->getCalendarProjects();
		$xform->setValueField("pz_select_screen",array("search_project_id",pz_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",0,pz_i18n::msg("please_choose")));
		
    if(pz::getUser()->isAdmin()) {
      $xform->setValueField('pz_select_screen',array('search_user_id', pz_i18n::msg('user'), pz::getUsersAsArray(pz::getUser()->getUsers()),"","",0,pz_i18n::msg("please_choose")));
    }
		
		$xform->setValueField("submit",array('submit',pz_i18n::msg('search'), '', 'search'));
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
		$p["list_links"][] = pz_i18n::msg('jobtime_total').' '.$hours.' '.$minutes.'';
		$p["list_links"][] = '<a href="'.pz::url($p["mediaview"],$p["controll"],$this->function,array(
						"mode" =>"export_excel",
						"search_title" => rex_request("search_title"),
						"search_date_from" => rex_request("search_date_from"),
						"search_date_to" => rex_request("search_date_to"),
						"search_project_id" => rex_request("search_project_id"),
						"search_user_id" => rex_request("search_user_id"),
					)).'">'.pz_i18n::msg('excel_export').'</a>';

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
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}

  
  

	private function getProfilesPage($p = array()) 
	{

		$p["title"] = pz_i18n::msg("userperm_list");
		$p["layer"] = "userperm_list";
		$p["linkvars"] = array();

    $user_id = rex_request("search_user_id","int");
    
    $user = NULL;
    if($user_id > 0 && ($user = pz_user::get($user_id)) ) {

    }else {
      $user = pz::getUser();

    }
 		$projects = $user->getMyProjects();
		$u_screen = new pz_user_screen($user);


		$p["linkvars"]["search_user_id"] = $user->getId();
		$p["linkvars"]["mode"] = "list";



    // ----------------------- searchform		
		$searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("search_for_userperms").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", 
			"javascript:pz_loadFormPage('userperm_list','userperm_search_form','".pz::url($p["mediaview"],$p["controll"],$this->function)."')");
		$xform->setObjectparams("form_id", "userperm_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
		
    $xform->setValueField('pz_select_screen',array('search_user_id', pz_i18n::msg('user'), pz::getUsersAsArray(pz::getUser()->getUsers()),"",$user->getId(),0));
		
		$xform->setValueField("submit",array('submit',pz_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));

		$searchform .= '<div id="userperm_search" class="design1col xform-search">'.$xform->getForm().'</form>';

    $section_1 = $searchform;
    $section_2 = '';

		
		$mode = rex_request("mode","string");
		switch($mode) {
			case("list"):
				return $u_screen->getProjectPermTableListView($p, $projects);
		}

		$section_2 = $u_screen->getProjectPermTableListView($p, $projects);

		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name) , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		return $f->parse('pz_screen_main.tpl');
	}	



  public function getUsersPage($p = array())
	{
		$p["title"] = pz_i18n::msg("users");
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
					return pz_user_screen::getAddForm($p);
			
				case("edit_user"):
					$user_id = rex_request("user_id","int");
					$u = pz_user::get($user_id);
					$u_screen = new pz_user_screen($u);
					return $u_screen->getEditForm($p);

        case("list"):
          // $users = pz::getUser()->getUsers($filter);
          $users = pz::getUsers($filter);
          return pz_user_screen::getTableListView(
						$users,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", "search_name" => $search_name) ) )
					);
				
			}
		}		
		$section_1 = pz_user_screen::getSearchForm($p);
		if(pz::getUser()->isAdmin())
			$section_1.= pz_user_screen::getAddForm($p);

		// $users = pz::getUser()->getUsers($filter);
    $users = pz::getUsers($filter);
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
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}

	public function getSystemPage($p = array())
	{
		$p["title"] = pz_i18n::msg("system");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "system";
		
		$mode = rex_request("mode","string");
		switch($mode) {
			case("show_phpinfo"):
				phpinfo();
				exit;
			case("edit_system"):
			  return $this->getSystemEditPage($p);
			  break;
		}
		
		ob_start();
    phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$webdav = "WebDAV works";
	  $dav_path = rex_path::addonData("prozer", "dav");
    rex_dir::create($dav_path);
		if(preg_match('/fcgi/', $phpinfo) || !preg_match('/mod_php/', $phpinfo)) {
		  $webdav = "WebDAV won`t work. Please deactivate FastCGI (fcgi) and use mod_php.";
		}

		
		$section_1 = '			
			<div id="server_info">
				<div id="server_view" class="design1col xform-edit">
		        <header>
		          <div class="header">
		            <h1 class="hl1">Server - Info</h1>
		          </div>
        		</header>
        		<div class="xform">
        			Domain: <b>'.pz::getServerUrl().'</b>
        			<br /><br />REDAXO Version: <b>'.pz::getProperty('redaxo_version').'</b>
        			<br />Prozer Version: <b>'.pz::getProperty('version').'</b>
					    <br /><br />Disk Total Space: <b>'.pz::readableFilesize(disk_total_space("/")).'</b>
					    <br />Disk Free Space: <b>'.pz::readableFilesize(disk_free_space("/")).'</b>
					    <br />memory_get_usage: <b>'.pz::readableFilesize(memory_get_usage()).'</b>
		    			<br /><br />Sprache: <b>'.pz::getProperty('lang').'</b>
			    		<br /><br /><a href="'.pz::url('screen','admin','system',array('mode'=>'show_phpinfo')).'" target="_blank">phpinfo</a>
					
    					<!-- check .. mbstring.func_overload -- must be 0 -->
    					<!-- fastcgi inactive und mod_php active - then web dav works well -->
		
		          <br /><br />'.$webdav.'
		
		          <br /><br />API: Download all emails (every 15 Minutes): 
		          <br />'.pz::getServerUrl().'/api/emails/download_all/?login=ADMINLOGIN&apitoken=APITOKEN		          

              <br /><br />API: Cleanup Sytem (Delete old trashed emails / older than 6 months / Delete Logentries older than 6 months) (once a day): 
              <br />'.pz::getServerUrl().'/api/admin/cleanup/?login=ADMINLOGIN&apitoken=APITOKEN		          
					
              <br /><br />API: Download Jobs as CSV              
              <br />'.pz::getServerUrl().'/api/calendar_event/jobs/?login=ADMINLOGIN&apitoken=APITOKEN&from=20111201&to=20111220&mode=all

              <br /><br />API: Download Users as CSV              
              <br />'.pz::getServerUrl().'/api/admin/users/?login=ADMINLOGIN&apitoken=APITOKEN&format=csv
              
              <br /><br />API: Refresh System (Update Label CSS, Update Address VT)              
              <br />'.pz::getServerUrl().'/api/admin/refresh/?login=ADMINLOGIN&apitoken=APITOKEN
					
              <br /><br />API: Address Export as CSV              
              <br />'.pz::getServerUrl().'/api/addresses/export/?login=ADMINLOGIN&apitoken=APITOKEN
					
        		</div>
        		</div>
        	</div>
			';
			
		$section_2 = $this->getSystemEditPage($p);
		
		$p = array();
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');

	}


	public function getSystemEditPage($p) {
  
		/* TODO:
		  - Anfangsbild setzen können
			- Anfangstext setzen können
			- Firmenlogo setzen können
		*/
		
    $header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("system_edit").'</h1>
          </div>
        </header>';
    
		$xform = new rex_xform;
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('system_edit','system_edit_form','".pz::url($p["mediaview"],"admin",$p["function"],array("mode"=>'edit_system'))."')");
		$xform->setObjectparams("form_id", "system_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		$xform->setValueField("text",array("system_page_title",pz_i18n::msg("page_title"),pz::getConfig("page_title")));
		
		$themes = array();
		foreach(pz_screen::getThemes() as $theme => $path) { $themes[] = $theme; }
		
		$xform->setValueField("pz_select_screen",array("system_page_theme",pz_i18n::msg("page_theme"),implode(",",$themes),"",pz_screen::getTheme(),0));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
		
		  pz::setConfig("page_title", $xform->objparams["value_pool"]["email"]["system_page_title"]);
		  pz::setConfig("page_theme", $xform->objparams["value_pool"]["email"]["system_page_theme"]);
		  
			$return = $header.'<p class="xform-info">'.pz_i18n::msg("system_info_updated").'</p>'.$return;

		}else {
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="system_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
	
  }



  public function getHistoryPage($p = array())
	{
		$p["title"] = pz_i18n::msg("history");
		$p["layer"] = "history_list";
		$p["layer_list"] = "history_list";
		
		$p["linkvars"] = array();
		
		$section_1 = '';
		$section_2 = '';
	
		$filter = array();

    if (rex_request('search_date_from', 'string') != '') {
      if (($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_from', 'string')))) {
        $filter[] = array('type' => '>=', 'field' => 'stamp', 'value' => $date_object->format('Y-m-d 00:00'));
        $p["linkvars"]['search_date_from'] = $date_object->format('Y-m-d');;
      }
    }

    if (rex_request('search_date_to', 'string') != '') {
      if (($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_to', 'string')))) {
        $filter[] = array('type' => '<=', 'field' => 'stamp', 'value' => $date_object->format('Y-m-d 23:59'));
        $p["linkvars"]['search_date_to'] = $date_object->format('Y-m-d');;
      }
    }

    if (rex_request('search_user_id', 'int') != 0
      && ($user = pz_user::get(rex_request('search_user_id', 'int')))
    ) {
      $filter[] = array('type' => '=', 'field' => 'user_id', 'value' => $user->getId());
      $p["linkvars"]['search_user_id'] = rex_request('search_user_id', 'string');
    }

    if (rex_request('search_modi', 'string') != "") {
      $filter[] = array('type' => '=', 'field' => 'mode', 'value' => rex_request('search_modi', 'string'));
      $p["linkvars"]['search_modi'] = rex_request('search_modi', 'string');
    }

    if (rex_request('search_control', 'string') != "") {
      $filter[] = array('type' => '=', 'field' => 'control', 'value' => rex_request('search_control', 'string'));
      $p["linkvars"]['search_control'] = rex_request('search_control', 'string');
    }

		$mode = rex_request('mode', 'string');
    $p["linkvars"]["mode"] = "list";

		$section_1 = pz_history_screen::getSearchForm($p);

		$history_entries = pz_history::get($filter);
		$section_2 = pz_history_screen::getListView(
						$history_entries,
						$p
					);
		
		switch($mode)
		{
		  
			case('list'):
				return $section_2;
				break;
		}
	
		$p = array();
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	}


  










}