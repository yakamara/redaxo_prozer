<?php

class pz_project_controller_screen extends pz_project_controller
{

	var $name = "project";
	var $function = "";
	var $functions = array("view"=>"view", "user"=>"user", "jobs"=>"jobs", "files"=>"files", "emails"=>"emails", "userperm" => "userperm");
	var $navigation = array("view"=>"view", "user"=>"user", "jobs"=>"jobs", "files"=>"files", "emails"=>"emails");
	var $isVisible = FALSE;

	function controller($function) 
	{
		
		if(!$this->setProject(rex_request("project_id","int"))) 
		{
			return pz_i18n::msg("no_permission_contact_admin").' - PNPXS-'.rex_request("project_id","int");
		}

		if(!$this->project->hasEmails() || !$this->projectuser->hasEmails())
		{
			unset($this->functions["emails"]);
			unset($this->navigation["emails"]);
		}

		if(!$this->project->hasFiles() || !$this->projectuser->hasFiles())
		{
			unset($this->functions["files"]);
			unset($this->navigation["files"]);
		}

		if(!$this->project->hasCalendarJobs() || !$this->projectuser->hasCalendarJobs())
		{
			unset($this->functions["jobs"]);
			unset($this->navigation["jobs"]);
		}

		if($this->projectuser->isAdmin() && $this->project->hasCalendarEvents())
		{
			$this->functions[] = "project_sub";
			$this->navigation[] = "project_sub";
		}
	
		if(!in_array($function,$this->functions)) $function = current($this->functions);
		$this->function = $function;
		
		$p = array();
		$p["mediaview"] = "screen";
		$p["controll"] = "project";
		$p["function"] = $function;
		
		$p["linkvars"]["project_id"] = $this->project_id;

		$section_1 = "";

		switch($function) {
			// case("view"): return $this->getInfoPage($p); break;
			case("view"): return $this->getCalendarEventPage($p); break;
			case("user"): return $this->getUserPage($p); break;
			case("userperm"): return $this->getUserPerm($p); break;
			case("jobs"): return $this->getJobsPage($p); 	break;
			case("wiki"): return $this->getWikiPage($p); break;
			case("files"): return $this->getFilesPage($p); break;
			case("emails"): return $this->getEmailsPage($p); break;
			case("project_sub"): return $this->getProjectSubsPage($p); break;
			default: break;
		}

		return pz_i18n::msg("no_permission_contact_admin").' - PNPNF-'.rex_request("project_id","int");

	}


	// ---------------------------------------------------------------- VIEWS

	private function getNavigation() {
	
		$first = " first";
		$temp_k = "";
		$items = array();
		foreach($this->navigation as $k) {
			$active = "";
			if($this->function == $k) $active = " active";
			$items[$k] = array();
			$items[$k]["classes"] = $k.$first.$active;
			$items[$k]["name"] = pz_i18n::msg("page_".$this->name."_".$k);
			$items[$k]["url"] = pz::url('screen',$this->name, $k, array('project_id'=>$this->project_id));
			$first = "";
			$temp_k = $k;
		}
		if($temp_k != "") $items[$temp_k]["classes"] = $k." last";
		$f = new pz_fragment();
		$f->items = $items;
		$f->item_active = $this->function;
		
		
		$projects_screen = new pz_projects_screen(pz::getUser()->getMyProjects());
 		$f->flyout = $projects_screen->getProjectsFlyout(array(), $this->project);
		// $f->flyout = $this->getProjectsFlyout();

		return $f->parse('pz_screen_main_sub_navigation.tpl');
	
	}


  // -------------------------- Info
  
  public function getInfoPage($p = array()) 
	{
	
	  $p["layer"] = 'history_list';
		$p["title"] = pz_i18n::msg("project_history");
	
		$section_1 = '';
		$section_2 = '';
	
    $p["linkvars"]["mode"] = "list";

		// ----------------------- editform
		$ps = new pz_project_screen($this->project);
		if($this->projectuser->isAdmin()) {
			$edit_form = $ps->getEditForm($p);
		}else {
			$edit_form = $ps->getViewForm($p);
		}

		// ----------------------- liste
    
    $filter = array();
    if(!$this->projectuser->isAdmin())
    {
      
      $controls = array();
      if($this->projectuser->hasEmails()) {
        $controls[] = 'email';
      }

      if($this->projectuser->hasCalendarEvents() || $this->projectuser->hasCalendarJobs()) {
        $controls[] = 'calendar_event';
      }
      
      // if($this->projectuser->hasCalendarJobs())  

			if($this->projectuser->hasFiles()) {
        $controls[] = 'projectfile';
   		}

      if($this->projectuser->project->hasWiki() == 1) {
        $controls[] = 'wiki';
   		}

      $filter[] = array('type'=>'findinmyset', 'field' => 'control', 'value' => $controls );
      
    }
    
		$entries = $this->project->getHistoryEntries($filter);
    $entries_list = pz_history_screen::getListView($entries, $p);
		
		$mode = rex_request('mode', 'string');
		switch($mode)
		{
			case('edit_form'):
				return $edit_form;
				
			case('list'):
				return $entries_list;
				break;
			default:
				break;
		}
		
		$section_1 = $edit_form;
		$section_2 = $entries_list;
	
		$p = array();
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	
	}
	
	
	private function getCalendarEventPage($p = array())
	{

    $p["layer"] = 'ordered_list'; // history
		$p["title"] = pz_i18n::msg("page_project_view"); // project_history
	
		$section_1 = '';
		$section_2 = '';
	
    $p["linkvars"]["mode"] = "list";

		// ----------------------- editform
		$ps = new pz_project_screen($this->project);
		if($this->projectuser->isAdmin()) {
			$edit_form = $ps->getEditForm($p);
		}else {
			$edit_form = $ps->getViewForm($p);
		}

		// ----------------------- liste
/*    
    $filter = array();
    if (!$this->projectuser->isAdmin()) {
      $controls = array();
      if($this->projectuser->hasEmails()) {
        $controls[] = 'email';
      }
      if($this->projectuser->hasCalendarEvents() || $this->projectuser->hasCalendarJobs()) {
        $controls[] = 'calendar_event';
      }
      
      // if($this->projectuser->hasCalendarJobs())  
			if($this->projectuser->hasFiles()) {
        $controls[] = 'projectfile';
   		}
      if($this->projectuser->project->hasWiki() == 1) {
        $controls[] = 'wiki';
   		}
      $filter[] = array('type'=>'findinmyset', 'field' => 'control', 'value' => $controls );
    }
		$entries = $this->project->getHistoryEntries($filter);
    $entries_list = pz_history_screen::getListView($entries, $p);
*/

    // -------------------		
		$from = new Datetime;
	  $to = clone $from;   
	  $to->modify( '+3 month' );
		$events = $this->project->getCalendarEvents($from, $to);
		
		$entries_list = pz_calendar_event_screen::getOrderedListView($events,array_merge($p,array("linkvars"=>array("mode" => "list", "project_id" => $this->project->getId()))));
		
    // -------------------

		
		$mode = rex_request('mode', 'string');
		switch($mode)
		{
			case('edit_form'):
				return $edit_form;
				
			case('list'):
				return $entries_list;
				break;
			default:
				break;
		}
		
		$section_1 = $edit_form;
		$section_2 = $entries_list;
	
		$p = array();
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
  }


  
	
	
	// -------------------------- Users
	
	public function getUserPerm($p = array()) 
	{
		$return = "";
	
	  $user_id = rex_request("user_id","int");
	  if( !($user = pz_user::get($user_id)) ) {
	    return;
	  }
	  
	  if ( !($projectuser = pz_projectuser::get($user,$this->project)) ) {
	    return;
	  }
	
	  $mode = rex_request("mode","string");
		switch($mode) {

 			case("toggle_emails"):
 			
			  if (!$this->project->hasEmails()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin()
          ) {

          $status = $projectuser->hasEmails() ? $status = 0 : $status = 1;
          $status = $projectuser->setEmails($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("emails", $status, $this->projectuser);
        }
        return;

 			case("toggle_calendar_events"):
 			
			  if (!$this->project->hasCalendar()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin()
          ) {

          $status = $projectuser->hasCalendarEvents() ? $status = 0 : $status = 1;
          $status = $projectuser->setCalendarEvents($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("calendar_events", $status, $this->projectuser);
        }
        return;

 			case("toggle_calendar_jobs"):
 			
			  if (!$this->project->hasCalendarJobs()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin()
          ) {

          $status = $projectuser->hasCalendarJobs() ? $status = 0 : $status = 1;
          $status = $projectuser->setCalendarJobs($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("calendar_jobs", $status, $this->projectuser);

        }
        return;

			case("toggle_caldav_events"):

        if (!$this->project->hasCalendarEvents()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin() || 
              ($projectuser->user->getId() == pz::getUser()->getId() && $this->projectuser->hasCalendarEvents()) 
          ) {

          $status = $projectuser->hasCalDAVEvents() ? $status = 0 : $status = 1;
          $status = $projectuser->setCalDavEvents($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("caldav_events", $status, $this->projectuser);

        }
        return;
				
			case("toggle_caldav_jobs"):
			
			  if (!$this->project->hasCalendarJobs()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin() || 
              ($projectuser->user->getId() == pz::getUser()->getId() && $this->projectuser->hasCalendarJobs()) 
          ) {

          $status = $projectuser->hasCalDAVJobs() ? $status = 0 : $status = 1;
          $status = $projectuser->setCalDavJobs($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("caldav_jobs", $status, $this->projectuser);

        }
        return;
			
			case("toggle_files"):
 			
			  if (!$this->project->hasFiles()) {
          return;
        }

        if (  pz::getUser()->isAdmin() || 
              $this->projectuser->isAdmin()
          ) {

          $status = $projectuser->hasFiles() ? $status = 0 : $status = 1;
          $status = $projectuser->setFiles($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("files", $status, $this->projectuser);

        }
        
      case("toggle_admin"):
 			
        if (  ( pz::getUser()->isAdmin() || $this->projectuser->isAdmin() ) && pz::getUser()->getId() != $projectuser->getUser()->getId()
          ) {

          $status = $projectuser->isAdmin() ? $status = 0 : $status = 1;
          $status = $projectuser->setAdmin($status);
          
          $projectuser_screen = new pz_projectuser_screen($projectuser);
          return $projectuser_screen->getPermTableCellView("files", $status, $this->projectuser);

        }
        
        return;
			}
	
	}
	
	
	public function getUserPage($p = array()) 
	{
	
		$p["title"] = pz_i18n::msg("projectuserlist");
	
		$section_1 = "";
		$section_2 = "";
	
		$mode = rex_request("mode","string");
		$return = "";
		$ps = new pz_project_screen($this->project);
		switch($mode)
		{
			case("add_form"):
				if($this->projectuser->isAdmin()) 
				{
					return '<div id="projectuser_form">'.pz_projectuser_screen::getAddForm($p, $this->project).'</div>';
				}else 
				{
					return '<div id="project_form">'.$ps->getViewForm($p).'</div>';
				}
				break;
				
			case("delete"):
				if($this->project->deleteUser(rex_request("projectuser_id")))
				{
					$p["info"] = '<p class="xform-info">'.pz_i18n::msg("projectuser_deleted").'</p>';
				}else
				{
					$p["info"] = '<p class="xform-warning">'.pz_i18n::msg("projectuser_deleted_failed").'</p>';
				}
				
			case("list"):
				$projectusers = $this->project->getUsers();
				$p["layer"] = "projectusers_list";
				$p["linkvars"]["mode"] = "list";
				$return .= pz_projectuser_screen::getUserlist($p, $projectusers, $this->project, $this->projectuser);
				return $return;
				break;

			case(""):
				if($this->projectuser->isAdmin()) 
				{
					$section_1 .= '<div id="projectuser_form">'.pz_projectuser_screen::getAddForm($p, $this->project).'</div>';
				}else 
				{
					$section_1 .= '<div id="project_form">'.$ps->getViewForm($p).'</div>';
					
				}
				$projectusers = $this->project->getUsers();
				$p["layer"] = "projectusers_list";
				$p["linkvars"]["mode"] = "list";
				$section_2 .= pz_projectuser_screen::getUserlist($p, $projectusers, $this->project, $this->projectuser);
				break;

			default:
				break;

		}
	
		$p = array();
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false); // $pus->getAddForm()
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main.tpl');
	
	}
	
	
	
	
	
	
	// -------------------------- Wiki
	
	public function getWikiPage($p = array()) 
	{
	
		$p["title"] = pz_i18n::msg("project_wiki");
	
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');
		switch($mode)
		{
			case(''):
				$section_1 .= pz_project_wiki_screen::getArticlelist($p, $this->project);
				$section_1 .= pz_project_wiki_screen::getAddForm($p, $this->project);
				$section_2 .= pz_project_wiki_screen::getArticle($p, $this->project);
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
	

	
	// -------------------------- Job
		
		public function getJobsPage($p = array()) 
		{
		
			$p["title"] = pz_i18n::msg("project_jobs");
			$p["mediaview"] = "screen";
			$p["controll"] = "project";
			$p["function"] = "jobs";
			$p["layer"] = "projectjobs_list";
			$p["layer_list"] = "projectjobs_list";
			
			$section_1 = '';
			$section_2 = '';
		
			$mode = rex_request('mode', 'string');
			$search_title = rex_request('search_title','string');
			$search_date_from = null;
			$search_date_to = null;
			
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
			$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectjobs_list','projectjob_search_form','".pz::url('screen','project',$this->function)."')");
			$xform->setObjectparams("form_id", "projectjob_search_form");
			$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
			$xform->setValueField("text",array("search_title",pz_i18n::msg("title")));
			$xform->setValueField("pz_date_screen",array("search_date_from",pz_i18n::msg("search_date_from")));
			$xform->setValueField("pz_date_screen",array("search_date_to",pz_i18n::msg("search_date_to")));
			
			$xform->setValueField("submit",array('submit',pz_i18n::msg('search'), '', 'search'));
			$xform->setValueField("hidden",array("mode","list"));
			$xform->setValueField("hidden",array("project_id",$this->project->getId()));
			$searchform .= $xform->getForm();
			
			$searchform = '<div id="projectjob_search" class="design1col xform-search">'.$searchform.'</div>';
			
			// ----------------------- jobliste
			
			if($this->projectuser->isAdmin()) {
	  		$jobs = $this->project->getJobs($search_date_from, $search_date_to, $search_title);
	    }else {
	  		$jobs = $this->project->getJobs($search_date_from, $search_date_to, $search_title, array(pz::getUser()->getId()));
	    }
	
			$hours = 0;
			$minutes = 0;
			foreach($jobs as $j) { 
				$hours += $j->getDuration()->format("%h"); 
				$minutes += $j->getDuration()->format("%i");
			};
	
			$hfm = (int) ($minutes/60);
			$hours += $hfm;
			$minutes = $minutes - ($hfm*60);
	
			$p["list_links"] = array();
			$p["list_links"][] = pz_i18n::msg('jobtime_total').' '.$hours.'h '.$minutes.'m';
			$p["list_links"][] = '<a href="'.pz::url('screen','project',$this->function,array(
							"mode" =>"export_excel",
							"project_id" => $this->project->getId(),
							"search_title" => rex_request("search_title"),
							"search_date_from" => rex_request("search_date_from"),
							"search_date_to" => rex_request("search_date_to"),
						)).'">'.pz_i18n::msg('excel_export').'</a>';
	
	    $p["linkvars"]["mode"] = "list";
	    $p["linkvars"]["project_id"] = $this->project->getId();
	
			$jobs_list = pz_calendar_event_screen::getProjectJobsTableView($jobs,  $p);
			
			switch($mode) {
				case('export_excel'):
					return pz_calendar_event_screen::getExcelExport($jobs);
					
				case('list'):
					return $jobs_list;
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
		
	
	
	// ---------------------------------------------------------------- History
	
	

	// ---------------------------------------------------------------- EMails

	
	public function getEmailsPage($p = array()) 
	{
	
		$p["title"] = pz_i18n::msg("emails_inbox");
		$p["title_search"] = pz_i18n::msg("emails_projekt_search");
		
		$p["mediaview"] = "screen";
		$p["controll"] = "project";
		$p["function"] = "emails";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$p["linkvars"]["mode"] = "list";
		$p["linkvars"]["project_id"] = $this->project->getId();

		$ignore_search_fields = array("project_id","noprojects","intrash");

		$filter = array();
		// $filter[] = array("field" => "send", "value" => 0);
		$filter[] = array("field" => "trash", "value" => 0);
		$filter[] = array("field" => "draft", "value" => 0);
		$filter[] = array("field" => "spam", "value" => 0);
		$filter[] = array("field" => "status", "value" => 1);
		
		$result = pz_emails_controller_screen::getEmailListFilter($filter,$p["linkvars"], $ignore_search_fields);
		$filter = $result["filter"];
		$p["linkvars"] = $result["linkvars"];		
		
		
		$orders = array();
		$result = pz_emails_controller_screen::getEmailListOrders($orders, $p);		
		$orders = $result["orders"];
		$current_order = $result["current_order"];
		$p = $result["p"];
		
		$pager = new pz_pager();
    $pager_screen = new pz_pager_screen($pager, $p['layer_list']);
    
    $emails = pz_email::getAll($filter, array($this->project), array(), array($orders[$current_order]), $pager);
    
    $p['linkvars']['mode'] = 'list';
    $return = pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);

    $mode = rex_request('mode', 'string');
    
    /* switch ($mode) {
      case 'emails_search':
        return pz_email_screen::getEmailsSearchForm($p, array('intrash'));
    } */
    
    if ($mode == 'list') {
      return $return;
    }

    $section_1 = pz_email_screen::getEmailsSearchForm($p, $ignore_search_fields);
    $section_2 = $return;

    $f = new pz_fragment();
    $f->setVar('header', pz_screen::getHeader($p), false);
    $f->setVar('function', $this->getNavigation($p), false);
    $f->setVar('section_1', $section_1, false);
    $f->setVar('section_2', $section_2, false);

    return $f->parse('pz_screen_main.tpl');
	
	}

	
	// ---------------------------------------------------------------- Info
	
	
	
	
	// ---------------------------------------------------------------- Files
	
	/*
	private function getFilesTableView($files,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($files);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $file) {
			$content .= '<tr>';
			$content .= '<td>'.$file->getVar("dir").'</td>';
			$content .= '<td>'.$file->getVar("filename").'</td>';
			$content .= '<td>'.$file->getVar("filesize").'</td>';
			$content .= '<td>'.$file->getVar("comment").'</td>';
			$content .= '<td>'.$file->getVar("filectime").'</td>';
			$content .= '<td>'.$file->getVar("createuser").'</td>';
			$content .= '<td>'.$file->getVar("updateuser").'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectfiles tbl1">
          <thead><tr>
              <th></th>
              <th>'.pz_i18n::msg("filename").'</th>
              <th>'.pz_i18n::msg("filesize").'</th>
              <th>'.pz_i18n::msg("comment").'</th>
              <th>'.pz_i18n::msg("createdate").'</th>
              <th>'.pz_i18n::msg("createuser").'</th>
              <th>'.pz_i18n::msg("updateuser").'</th>
              <th class="label"></th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		
		$f = new pz_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projectfiles_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
		return $f->parse('pz_screen_list.tpl');
	}
	*/
	
	static function getFileListOrders($orders = array(), $p)
	{

		$orders['namedesc'] = array(
		  "orderby" => "name", 
		  "sort" => "desc", 
		  "name" => pz_i18n::msg("files_orderby_namedesc"),
			"link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			      pz::url(
			        $p["mediaview"],
			        $p["controll"],
			        $p["function"],
			        array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "namedesc"))
			      )."')"
		);
			
		$orders['nameasc'] = 
		  array(
		    "orderby" => "name", 
		    "sort" => "asc", 
		    "name" => pz_i18n::msg("files_orderby_nameasc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			      pz::url(
			        $p["mediaview"],
			        $p["controll"],
			        $p["function"],
			        array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "nameasc"))
			      )."')"
			  );

		$orders['createdesc'] = 
		  array(
		    "orderby" => "created", 
		    "sort" => "desc", 
		    "name" => pz_i18n::msg("files_orderby_createdesc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "createdesc"))
			    )."')"
			);

		$orders['createasc'] = 
		  array(
		    "orderby" => "created", 
		    "sort" => "asc", 
		    "name" => pz_i18n::msg("files_orderby_createasc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "createasc"))
			    )."')"
			);

		$orders['filesizedesc'] = 
		  array(
		    "orderby" => "filesize", 
		    "sort" => "desc", 
		    "name" => pz_i18n::msg("files_orderby_filesizedesc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "filesizedesc"))
			    )."')"
			);

		$orders['filesizeasc'] = 
		  array(
		    "orderby" => "filesize", 
		    "sort" => "asc", 
		    "name" => pz_i18n::msg("files_orderby_filesizeasc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "filesizeasc"))
			    )."')"
			);


		$orders['mimetypedesc'] = 
		  array(
		    "orderby" => "mimetype", 
		    "sort" => "desc", 
		    "name" => pz_i18n::msg("files_orderby_mimetypedesc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "mimetypedesc"))
			    )."')"
			);

		$orders['mimetypeasc'] = 
		  array(
		    "orderby" => "mimetype", 
		    "sort" => "asc", 
		    "name" => pz_i18n::msg("files_orderby_mimetypeasc"),
			  "link" => "javascript:pz_loadPage('".$p["layer_list"]."','".
			    pz::url(
			      $p["mediaview"],
			      $p["controll"],
			      $p["function"],
			      array_merge($p["linkvars"],array("mode" => "list", "search_orderby" => "mimetypeasc"))
			    )."')"
			);




		$current_order = 'createdesc';
		if(array_key_exists(rex_request("search_orderby"),$orders))
			$current_order = rex_request("search_orderby");

		$orders[$current_order]["active"] = true;
		
		$p["linkvars"]["search_orderby"] = $current_order;

		return array("orders" => $orders, "p" => $p, "current_order" => $current_order);
	}
	
	
	public function getFilesPage($p = array()) 
	{
		
		/*
			wenn edit, dann datei ersetzen ..
				- alte löschen, neue setzen
				
			dir...	createFile($name, $data = null)
		*/
		
		$p["title"] = pz_i18n::msg("files");
		$p["linkvars"]["project_id"] = $this->project->getId();
		$p["linkvars"]["search_name"] = rex_request("search_name","string");
		$p["layer_list"] = "project_files_list";

		$section_1 = "";
		$section_2 = "";
		$filter = array();
		
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("file2clipboard"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && $file = pz_project_file::get($file_id) ) 
				{
					if($file->getProjectId() == $this->project->getId())
					{
    				$file_type = pz::getMimeTypeByFilename($file->getName(), $file->getContent());
						$clip = pz_clip::createAsSource($file->getContent(), $file->getName(), $file->getSize(), $file_type, false);
						return '<script>pz_loadClipboard();</script>';
					}
				}else 
				{
					return '<p class="xform-warning">'.pz_i18n::msg("file_not_exists").'</p>';
				}
			
			case("clipboardfile2clipboard"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && $file = pz_project_file::get($file_id) ) 
				{
					if($file->getProjectId() == $this->project->getId())
					{
    				$file_type = pz::getMimeTypeByFilename($file->getName(), $file->getContent());
						$clip = pz_clip::createAsSource($file->getContent(), $file->getName(), $file->getSize(), $file_type, false);
						return '<script>
						  pz_clipboard_init();
						  pz_clipboard_msg("'.htmlspecialchars(pz_i18n::msg("copied_to_myclipboard",$file->getName())).'");
						  </script>';
					}
				}
				return '';
			
			case("clipboardfile2clipboard2select"):
			  $file_id = rex_request("file_id","int",0);
				if($file_id > 0 && $file = pz_project_file::get($file_id) ) 
				{
					if($file->getProjectId() == $this->project->getId())
					{
    				$file_type = pz::getMimeTypeByFilename($file->getName(), $file->getContent());
						$clip = pz_clip::createAsSource($file->getContent(), $file->getName(), $file->getSize(), $file_type, false);
						return '<script>
			        pz_clip_select('.$clip->getId().',"'.$clip->getFilename().'","'.pz::readableFilesize($clip->getContentLength()).'");
						  pz_clipboard_init();
						  </script>';
					}
				}
        return '';
			
			case("delete_folder"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && ($file = pz_project_file::get($file_id)) ) 
				{
					if($file->getProjectId() == $this->project->getId() && $file->isDirectory() && count($file->getChildren()) == 0){
						$cs = new pz_project_file_screen($file);
						$return = $cs->getDeleteFolderForm($this->project, $p);
						$file->delete();
						return $return;
					}
				}else {
					return '<p class="xform-warning">'.pz_i18n::msg("folder_not_exists").'</p>';
				}
				return '';
				
			case("add_folder"):
				return pz_project_file_screen::getAddFolderForm($this->project, $p);
				
			case("edit_folder"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && ($file = pz_project_file::get($file_id)) ) {
					if($file->getProjectId() == $this->project->getId()){
						$cs = new pz_project_file_screen($file);
						return $cs->getEditFolderForm($this->project, $p);
					}
				}else {
					return '<p class="xform-warning">'.pz_i18n::msg("folder_not_exists").'</p>';
				}
				return '';
			
			case("download_file"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && ($file = pz_project_file::get($file_id)) ) {
					if($file->getProjectId() == $this->project->getId()){
						pz::getDownloadHeader($file->getName(), $file->getContent());
					}
				}
				exit;
				break;
				
			case("add_file"):
				return pz_project_file_screen::getAddFileForm($this->project, $p);
				break;
			
			case("edit_file"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && $file = pz_project_file::get($file_id) ) {
					if($file->getProjectId() == $this->project->getId()){
						$cs = new pz_project_file_screen($file);
						return $cs->getEditFileForm($this->project, $p);
					}
				}else {
					return '<p class="xform-warning">'.pz_i18n::msg("file_not_exists").'</p>';
				}
				break;
			
			case("delete_file"):
				$file_id = rex_request("file_id","int",0);
				if($file_id > 0 && ($file = pz_project_file::get($file_id)) && !$file->isDirectory()) {
					if($file->getProjectId() == $this->project->getId()){
						$cs = new pz_project_file_screen($file);
						$return = $cs->getDeleteFileForm($this->project, $p);
						$file->delete();
						return $return;
					}
				}else 
				{
					return '<p class="xform-warning">'.pz_i18n::msg("folder_not_exists").'</p>';
				}
				return '';
				
			case("list"):
				$file_id = rex_request('file_id','int');
				$p["linkvars"]["file_id"] = $file_id;
				$p["linkvars"]["mode"] = "list";
				
				$orders = array();
    		$result = pz_project_controller_screen::getFileListOrders($orders, $p);		
    		$orders = $result["orders"];
    		$current_order = $result["current_order"];
    		$p = $result["p"];
				
				if(($category = pz_project_node::get($file_id)) && ($category->isDirectory()) )
				{
					$category = $category;
				}else
				{
					$category = $this->project->getDirectory();
				}
				return pz_project_file_screen::getFilesListView( $category, $category->getChildren(array($orders[$current_order])), $p, $orders, array($orders[$current_order])  );
				break;
				
			case(""):
				// $section_1 .= pz_project_file_screen::getSearchForm();
				$section_1 .= pz_project_file_screen::getAddFolderForm($this->project, $p);
				$section_1 .= pz_project_file_screen::getAddFileForm($this->project, $p);
				
				$orders = array();
    		$result = pz_project_controller_screen::getFileListOrders($orders, $p);		
    		$orders = $result["orders"];
    		$current_order = $result["current_order"];
    		$p = $result["p"];
				$p["linkvars"]["mode"] = "list";

				$category = $this->project->getDirectory();
				$section_2 = pz_project_file_screen::getFilesListView($category, $category->getChildren(array($orders[$current_order])), $p, $orders);
				break;
		}

		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $section_1, false);
		$f->setVar('section_2', $section_2, false);
		return $f->parse('pz_screen_main.tpl');
			
	}
	
	// ---------------------------------------------------------------- Files
	
	private function getProjectSubsPage($p = array())
	{
	
		$p["title"] = pz_i18n::msg("project_subs");
		$p["mediaview"] = "screen";
		$p["controll"] = "project";
		$p["function"] = "project_sub";

		$section_1 = "";
		$section_2 = "";

		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("delete_project_sub"):
				$project_sub_id = rex_request("project_sub_id","int");
				if( ($project_sub = pz_project_sub::get($project_sub_id)) && $project_sub->getProject()->getId() == $this->project->getid() ) 
				{
					$r = new pz_project_sub_screen($project_sub);
					$project_sub->delete();
					
					$p["project_sub_name"] = $project_sub->getName();
					return $r->getDeleteForm($p,$this->project);
				}
				return '';
				
			case("add_project_sub"):
				return pz_project_sub_screen::getAddForm($p, $this->project);
			
			case("list"):
				$project_subs = $this->project->getProjectSubs();
				$cs = new pz_project_sub_screen($project_subs);
				return $cs->getListView($p, $project_subs);
				
			case("edit_project_sub"):
				$project_sub_id = rex_request("project_sub_id","int",0);
				if($project_sub_id > 0 && ($project_sub = pz_project_sub::get($project_sub_id)) && $project_sub->getProject()->getId() == $this->project->getid()) 
				{
				  $p["show_delete"] = true;
					$cs = new pz_project_sub_screen($project_sub);
					return $cs->getEditForm($p);

				}
				return '<div id="project_sub_form"><p class="xform-warning">'.pz_i18n::msg("project_sub_not_found").'</p></div>';
				
			case("project_sub_info"):
				$project_sub_id = rex_request("project_sub_id","int",0);
				if($project_sub_id > 0 && $project_sub = pz_project_sub::get($project_sub_id)) {
					$cs = new pz_project_sub_screen($project_sub);
					$section_2 = $cs->getInfoPage($p);
				}
				return '<div id="project_sub_form"><p class="xform-warning">'.pz_i18n::msg("project_sub_not_found").'</p></div>';
			
			default:
				$project_subs = $this->project->getProjectSubs();
				$section_2 = pz_project_sub_screen::getListView($p,$project_subs);
				$section_1 .= pz_project_sub_screen::getAddForm($p, $this->project);
				break;
		}

		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $section_1, false);
		$f->setVar('section_2', $section_2, false);
		return $f->parse('pz_screen_main.tpl');

	}
	
	
	
	
}