<?php

class pz_project_controller_screen extends pz_project_controller{

	var $name = "project";
	var $function = "";
	var $functions = array("view"=>"view", "user"=>"user", "jobs"=>"jobs", "wiki"=>"wiki", "files"=>"files", "emails"=>"emails", "history"=>"history");
	var $navigation = array("view"=>"view", "user"=>"user", "jobs"=>"jobs", "wiki"=>"wiki", "files"=>"files", "emails"=>"emails", "history"=>"history");
	var $isVisible = FALSE;

	function controller($function) {
		
		if(!$this->setProject(rex_request("project_id","int"))) 
		{
			return rex_i18n::msg("no_permission_contact_admin").' - PNPXS-'.rex_request("project_id","int");
		}
	
		if(!$this->project->hasWiki() || !$this->projectuser->hasWiki())  
		{
			unset($this->functions["wiki"]);
			unset($this->navigation["wiki"]);
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

		if(!$this->project->hasJobs() || !$this->projectuser->hasJobs())
		{
			unset($this->functions["jobs"]);
			unset($this->navigation["jobs"]);
		}
	
		if(!in_array($function,$this->functions)) $function = current($this->functions);
		$this->function = $function;
		
		$p["linkvars"]["project_id"] = $this->project_id;

		$section_1 = "";

		switch($function) {
			case("view"): return $this->getInfoPage($p); break;
			case("user"): return $this->getUserPage($p); break;
			case("jobs"): return $this->getJobsPage($p); 	break;
			case("wiki"): return $this->getWikiPage($p); break;
			case("files"): return $this->getFilesPage($p); break;
			case("emails"): return $this->getEmailsPage($p); break;
			case("history"): return $this->getStreamPage($p); break;
			default: break;
		}

		return rex_i18n::msg("no_permission_contact_admin").' - PNPNF-'.rex_request("project_id","int"); 

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
			$items[$k]["name"] = rex_i18n::msg("page_".$this->name."_".$k);
			$items[$k]["url"] = pz::url('screen',$this->name, $k, array('project_id'=>$this->project_id));
			$first = "";
			$temp_k = $k;
		}
		if($temp_k != "") $items[$temp_k]["classes"] = $k." last";
		$f = new rex_fragment();
		$f->items = $items;
		$f->item_active = $this->function;
		$f->flyout = $this->getProjectsFlyout();

		return $f->parse('pz_screen_main_sub_navigation');
	
	}

	static function getProjectsFlyout($p = array())
	{
		/*
		Alte Variante
		$entries = array();
		$project_id = rex_request("project_id","int");
		$i = -1;
		$title = '';
		foreach(pz::getUser()->getProjects() as $project)
		{
			$i++;
			$entries[$i]['url'] = pz::url('screen','project','view',array('project_id'=>$project->getId()));
		          $entries[$i]['title'] = pz::cutText($project->getName(),100); // 'Prozer the next generation';
			if($project->getId() == $project_id) $title .= pz::cutText($project->getName(),100);
		}
		if($title == '')
			$title = rex_i18n::msg("select_project");
		
		$f = new rex_fragment();
		$f->setVar('class_ul', 'w3', false);
		$f->setVar('class_selected', '', false);
		$f->setVar('selected', $title, false);
		$f->setVar('entries', $entries, false);
		$f->setVar('extra', '', false);
		return $f->parse('pz_screen_select_dropdown');
		*/
		
		$project_id = rex_request("project_id","int");
		$project_name = rex_i18n::msg("select_project");
		
		$projects = array();
		$first = " first";
		foreach(pz::getUser()->getMyProjects() as $project)
		{
			$links = array();
			if($project->hasCalendar()) 
				$links[] = '<a href="'.pz::url('screen','calendars', 'day', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("calendar").'</span></a>';
			if($project->hasJobs()) 
				$links[] = '<a href="'.pz::url('screen','project', 'jobs', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("jobs").'</span></a>';
			if($project->hasFiles()) 
				$links[] = '<a href="'.pz::url('screen','project', 'files', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("files").'</span></a>';
			if($project->hasEmails()) 
				$links[] = '<a href="'.pz::url('screen','project', 'emails', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("emails").'</span></a>';
			if($project->hasWiki()) 
				$links[] = '<a href="'.pz::url('screen','project', 'wiki', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("wiki").'</span></a>';
			$links[] = '<a href="'.pz::url('screen','project', 'history', array('project_id'=>$project->getId())).'">'.
					'<span class="title">'.rex_i18n::msg("history").'</span></a>';
			
			if($project_id == $project->getId())
				$project_name = $project->getName();
			
			$projects[] = '<li class="entry'.$first.'">
							<div class="wrapper">
								<div class="links">'.implode("",$links).'</div>
								<span class="name">'.$project->getName().'</span>
							</div>
						</li>';
			// <li class="entry"><a class="email" href="">
			// <span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
		}

		$return = '	<ul class="sl1 sl1b sl-r">
						<li class="selected"><span class="selected">'.$project_name.'</span>
							<div class="flyout">
								<div class="content"><ul class="entries">'.implode("",$projects).'</ul></div>
							</div>
						</li>
					</ul>
        ';
		return $return;
	}



	/*
	private function getProjectHeader ()
	{
    return '

      <header id="detail-header">
        <div class="wrapper clearfix">
          <h1 class="hl1">Projekte / Projektname</h1>
          
          <ul id="navi-lev3" class="clearfix">
            <li class="lev3 first"><a href="" class="lev3 bt1 first">Daten</a></li>
            <li class="lev3"><a href="" class="lev3 bt1">Nutzer</a></li>
            <li class="lev3"><a href="" class="lev3 bt1">Jobs</a></li>
            <li class="lev3"><a href="" class="lev3 bt1">Todos</a></li>
            <li class="lev3 active"><a href="" class="lev3 bt1 active">Dateien</a></li>
            <li class="lev3 last"><a href="" class="lev3 bt1 last">Mails</a></li>
          </ul>  
        </div>
      </header>';
	}
	*/
	
	
	// ---------------------------------------------------------------- Users
	
	public function getUserPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("projectuserlist");
	
		$s1_content = "";
		$s2_content = "";
	
		$mode = rex_request("mode","string");
		$return = "";
		switch($mode)
		{
			case("edit_form"):
			
				$projectuser_id = rex_request("projectuser_id","int");
				
				if(!$this->projectuser->isAdmin() && $projectuser_id != pz::getUser()->getId())
					return "";
				
				if($pu = $this->project->getProjectuserById($projectuser_id))
				{
					$pus = new pz_projectuser_screen($pu);
					return '<div id="projectuser_form">'.$pus->getEditForm($p).'</div>';
				}
			
			case("add_form"):
				if($this->projectuser->isAdmin()) {
					return '<div id="projectuser_form">'.pz_projectuser_screen::getAddForm($p, $this->project).'</div>';
				}else {
					return '<div id="projectuser_form">'.pz_projectuser_screen::getViewForm($p, $this->project).'</div>';
				}
				break;
				
			case("delete"):
				if($this->project->deleteUser(rex_request("projectuser_id")))
				{
					$p["info"] = '<p class="xform-info">'.rex_i18n::msg("projectuser_deleted").'</p>';					
				}else
				{
					$p["info"] = '<p class="xform-warning">'.rex_i18n::msg("projectuser_deleted_failed").'</p>';					
				}

				
			case("list"):
				$projectusers = $this->project->getUsers();
				$return .= pz_projectuser_screen::getUserlist($p, $projectusers, $this->project, $this->projectuser);
				return $return;
				break;
			case(""):
				if($this->projectuser->isAdmin()) {
					$s1_content .= '<div id="projectuser_form">'.pz_projectuser_screen::getAddForm($p, $this->project).'</div>';
				}else {
					$s1_content .= '<div id="projectuser_form">'.pz_projectuser_screen::getViewForm($p, $this->project).'</div>';
				}
				$projectusers = $this->project->getUsers();
				$s2_content .= pz_projectuser_screen::getUserlist($p, $projectusers, $this->project, $this->projectuser);
				break;
			default:
				break;
		}
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $s1_content , false); // $pus->getAddForm()
		$f->setVar('section_2', $s2_content , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	
	}
	
	
	
	
	
	
	// ---------------------------------------------------------------- Wiki
	
	public function getWikiPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("project_wiki");
	
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
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	
	}
	

	
	// ---------------------------------------------------------------- Job
	
	private function getJobsTableView($jobs,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($jobs);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $job) {
			
			$user = pz_user::get($job->getUserId());
			
			$content .= '<tr>';
			$content .= '<td class="img1"><img src="'.$user->getInlineImage().'" /></td>';
			$content .= '<td>'.$user->getName().'</td>';
			$content .= '<td>'.$job->getTitle().'</td>';
			$content .= '<td>'.$job->getDescription().'</td>';
			$content .= '<td>'.$job->getDuration()->format("%h.%I").'&nbsp;h</td>';
			$content .= '<td>'.$job->getFrom()->format(rex_i18n::msg("format_d_m_y"))."<br /><nowrap>".$job->getFrom()->format(rex_i18n::msg("format_h_i")).'h - '.$job->getTo()->format(rex_i18n::msg("format_h_i")).'</nowrap></td>';
			$content .= '<td>'.$job->getCreated()->format(rex_i18n::msg("format_d_m_y")).'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectjobs tbl1">
          <thead><tr>
              <th colspan="2">'.rex_i18n::msg("user").'</th>
              <th>'.rex_i18n::msg("title").'</th>
              <th>'.rex_i18n::msg("description").'</th>
              <th>'.rex_i18n::msg("hours").'</th>
              <th>'.rex_i18n::msg("duration").'</th>
              <th>'.rex_i18n::msg("createdate").'</th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		// $content = $this->getSearchPaginatePlainView().$content;
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projectjobs_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}
	
	public function getJobsPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("project_jobs");
		$p["mediaview"] = "screen";
		$p["controll"] = "project";
		$p["function"] = "jobs";
		$p["layer"] = "projectjobs_list";
		
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
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectjobs_list','projectjob_search_form','".pz::url('screen','project',$this->function)."')");
		$xform->setObjectparams("form_id", "projectjob_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("name")));
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));
		$xform->setValueField("hidden",array("project_id",$this->project->getId()));
		$searchform .= $xform->getForm();
		
		$searchform = '<div id="projectjob_search" class="design1col xform-search">'.$searchform.'</div>';

		// ----------------------- jobliste

		$jobs = $this->project->getJobs(null, null, $search_name);
		$jobs_list = $this->getJobsTableView(
					$jobs,
					array_merge( $p, array("linkvars" => array( "mode" =>"list", "project_id" => $this->project->getId() ) ) )
				);
		
		switch($mode)
		{
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
	
	
	
	// ---------------------------------------------------------------- History
	
	private function getStreamTableView($stream,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($stream);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $stream) {
			$content .= '<tr>';
			$content .= '<td>'.$stream.'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectstream tbl1">
          <thead><tr>
              <th></th>
              <th>'.rex_i18n::msg("customer").'</th>
              <th>'.rex_i18n::msg("project_name").'</th>
              <th>'.rex_i18n::msg("project_createdate").'</th>
              <th>'.rex_i18n::msg("project_admins").'</th>
              <th class="label"></th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projectstream_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}
	
	public function getStreamPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("project_stream");
	
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		// ----------------------- searchform		
		$searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("search_for_history").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectstream_list','projectstream_search_form','".pz::url('screen','project',$this->function)."')");
		$xform->setObjectparams("form_id", "projectjob_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("name")));
		$xform->setValueField("hidden",array("mode","list"));
		$xform->setValueField("hidden",array("project_id",$this->project->getId()));
		$searchform .= $xform->getForm();
		
		$searchform = '<div id="projectstream_search" class="design1col xform-search">'.$searchform.'</div>';

		$section_1 = $searchform;

		// ----------------------- infoliste

		$stream = $this->project->getHistoryStream();
		$section_2 = $this->getStreamTableView($stream,$p);
		
		
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

	// ---------------------------------------------------------------- EMails

	private function getEmailsTableView($emails,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($emails);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $email) {
			$content .= '<tr>';
			$content .= '<td>'.$email.'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectemail tbl1">
          <thead><tr>
              <th></th>
              <th>'.rex_i18n::msg("customer").'</th>
              <th>'.rex_i18n::msg("project_name").'</th>
              <th>'.rex_i18n::msg("project_createdate").'</th>
              <th>'.rex_i18n::msg("project_admins").'</th>
              <th class="label"></th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projectemail_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}
	
	// -------------------------------------------- Emails
	
	function getEmailsSearchForm ($p = array())
	{

		$link_refresh = pz::url("screen",$p["controll"], $p["function"], array_merge( 
								$p["linkvars"], array( "mode" => "emails_search", "project_ids" => "___value_ids___" ) 
							)
						);
		
	    $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("search_for_emails").'</h1>
	          </div>
	        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('emails_list','emails_search_form','".
			pz::url('screen',$p["controll"],$this->function,array("mode"=>'list','project_id'=>$this->project->getId()))."')");
		$xform->setObjectparams("form_id", "emails_search_form");
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("project_name")));

		$return .= $xform->getForm();
		
		$return = '<div id="emails_search" class="design1col xform-search" data-url="'.$link_refresh.'">'.$return.'</div>';
		return $return;

	}
	
	
	public function getEmailsPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("emails_inbox");
		$p["mediaview"] = "screen";
		$p["controll"] = "project";
		$p["function"] = "emails";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$filter = array();
		// $filter[] = array("field" => "send", "value" => 0);
		$filter[] = array("field" => "trash", "value" => 0);
		$filter[] = array("field" => "draft", "value" => 0);
		$filter[] = array("field" => "spam", "value" => 0);
		$filter[] = array("field" => "status", "value" => 1);
		
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		
		$emails = pz_email::getAll($filter, array($this->project));
		
		$list = pz_email_screen::getEmailsBlockView(
						$emails,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", "project_id" => $this->project->getId() ) ) )
					);
		
		$mode = rex_request("mode","string");
		switch($mode) {
			case("list"):
				return $list;
			default:
				break;
		}

		$s1_content = $this->getEmailsSearchForm($p);
		$s2_content = $list;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
	
	}

	
	// ---------------------------------------------------------------- Info
	
	public function getInfoPage($p = array()) 
	{
	
		$p["title"] = rex_i18n::msg("project_info");
	
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		// ----------------------- editform
		$ps = new pz_project_screen($this->project);
		
		if($this->projectuser->isAdmin()) {
			$edit_form = $ps->getEditForm($p);
		
		}else
		{
			$edit_form = $ps->getViewForm($p);
		
		}

		// ----------------------- liste

		$stream = $this->project->getInfoStream();
		$stream_list = $this->getStreamTableView($stream,$p);
		
		
		switch($mode)
		{
			case('edit_form'):
				return $edit_form;
			case('list'):
				return $stream_list;
				break;
			default:
				break;
		}
		
		$section_1 = $edit_form;
		$section_2 = $stream_list;
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	
	}
	
	
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
              <th>'.rex_i18n::msg("filename").'</th>
              <th>'.rex_i18n::msg("filesize").'</th>
              <th>'.rex_i18n::msg("comment").'</th>
              <th>'.rex_i18n::msg("createdate").'</th>
              <th>'.rex_i18n::msg("createuser").'</th>
              <th>'.rex_i18n::msg("updateuser").'</th>
              <th class="label"></th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projectfiles_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}
	*/
	
	public function getFilesPage($p = array()) 
	{
		
		/*
			wenn edit, dann datei ersetzen ..
				- alte löschen, neue setzen
				
			dir...	createFile($name, $data = null)
		*/
		
		$p["title"] = rex_i18n::msg("files");
		$p["linkvars"]["project_id"] = $this->project->getId();
		$p["linkvars"]["search_name"] = rex_request("search_name","string");

		$s1_content = "";
		$s2_content = "";
		$filter = array();
		
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_file"):
				return pz_project_file_screen::getAddForm($this->project, $p);
				break;
			case("list"):
				$category_id = rex_request('category_id','int');
				
				if($category = pz_project_node::get($category_id) )
					$files = $cat->getChildren();
				elseif($category_id == 0)
					$files = $this->project->getDirectory();
				return pz_project_file_screen::getFilesListView( $files, $p );
				break;
			case("edit_file"):
				$file_id = rex_request("file_id","int",0);
				
				if($file_id > 0 && $file = pz_project_file::get($file_id) ) {
					if($file->getProjectId() == $this->project->getId()){
						$cs = new pz_project_file_screen($file);
						return $cs->getEditForm($p);
					}
				}else {
					return '<p class="xform-warning">'.rex_i18n::msg("file_not_exists").'</p>';
				}
				break;
			case(""):
				$s1_content .= pz_project_file_screen::getSearchForm();
				$s1_content .= pz_project_file_screen::getAddForm($this->project, $p);
				$ffs = $this->project->getDirectory();
				$s2_content = pz_project_file_screen::getFilesListView($ffs, $p);
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', "NOCH NICHT BENUTZEN".$s1_content, false);
		$f->setVar('section_2', "NOCH NICHT BENUTZEN".$s2_content, false);
		return $f->parse('pz_screen_main');
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		$p["title"] = rex_i18n::msg("project_files");
	
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		$section_1 = pz_project_file_screen::getSearchForm($p);

		// ----------------------- liste

		$files = array(); // $this->project->getFiles();
		$section_2 = "TODO"; // $this->getFilesTableView($files,$p);
		
		$dir = $this->project->getDirectory();
		
		foreach($dir->getChildren() as $f)
		{
			$section_2 .= "<br />".$f->getName(); // ." [".pz::readableFilesize($f->getSize())."]";
		}
		
		
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