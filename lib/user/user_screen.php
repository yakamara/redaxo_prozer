<?php

class pz_user_screen {

	public $user;	
	
	function __construct($user) 
	{
		$this->user = $user;
	}
	
	
	public function getTableView($p = array())
	{
		$edit_link = pz::url("screen","tools","users",array("user_id"=>$this->user->getId(),"mode"=>"edit_user"));
		$del_link = pz::url("screen","tools","users",array("user_id"=>$this->user->getId(),"mode"=>"delete_user"));
		
		$return = '
              <tr>
                <td class="img1"><img src="'.$this->user->getInlineImage().'" width="40" height="40" alt="" /></td>';
                
		if(pz::getUser()->isAdmin()) {
        	$return .= '<td><a href="javascript:pz_loadPage(\'user_form\',\''.$edit_link.'\')"><span class="title">'.$this->user->getName().'</span></a></td>';
		}else
		{
        	$return .= '<td><span class="title">'.$this->user->getName().'</span></td>';
		}

		if($this->user->isAdmin())  
			$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';

		if($this->user->isActive())  
			$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';

		if(pz::getUser()->isAdmin())
		{
			if(pz::getUser()->getId() != $this->user->getId()) {
        		$return .= '<td><a class="bt2" href="javascript:pz_loadPage(\'users_list\',\''.$del_link.'\')"><span class="title">'.rex_i18n::msg("delete").'</span></a></td>';
			}else
			{
	        	$return .= '<td><span class="title"></span></td>';
			}
		}        
        
		return $return;
		
	}
	
	
	static function getTableListView($users, $p = array())
	{
		$list = "";
		
		$paginate_screen = new pz_paginate_screen($users);
		$paginate = $paginate_screen->getPlainView($p);
		
		foreach($paginate_screen->getCurrentElements() as $user) {
			$ps = new pz_user_screen($user);
			$list .= $ps->getTableView($p);
		}
		
		$content = $paginate.'
          <table class="users tbl1">
          <thead><tr>
              <th></th>
              <th>'.rex_i18n::msg("username").'</th>
              <th>'.rex_i18n::msg("admin").'</th>
              <th>'.rex_i18n::msg("active").'</th>
              ';
		
		if(pz::getUser()->isAdmin()) {
			$content .= '
              <th>'.rex_i18n::msg("functions").'</th>
				';
		}
		
        $content .= '
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>';
		
		if(isset($p["info"])) {
			$content = $p["info"].$content;
		}
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="users_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';

	}
	
	
	public function getProjectTableView($p, $projects)
	{
		$list = "";
		
		$p["linkvars"]["mode"] = "list_userperm";
		
		$paginate_screen = new pz_paginate_screen($projects);
		$paginate = $paginate_screen->getPlainView($p);
		
		foreach($paginate_screen->getCurrentElements() as $project) {
			$list .= $this->getProjectTableRowView($p, $project);
		}
		
		$content = $paginate.'
          <table class="userperm tbl1">
          <thead><tr>
              <th></th>
              ';
              
		$content .= '<th>'.rex_i18n::msg("project_name").'</th>';
		$content .= '<th>'.rex_i18n::msg("emails").'</th>';
		$content .= '<th>'.rex_i18n::msg("calendar").'</th>';
		$content .= '<th>'.rex_i18n::msg("calendar_caldav").'</th>';
		$content .= '<th>'.rex_i18n::msg("calendar_jobs_caldav").'</th>';
		$content .= '<th>'.rex_i18n::msg("files").'</th>';
		$content .= '<th>'.rex_i18n::msg("wiki").'</th>';
		
        $content .= '
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>';
		
		if(isset($p["info"])) {
			$content = $p["info"].$content;
		}
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="userperm_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
	
	}
	
	
	public function getProjectTableRowView($p = array(),$project)
	{
		
		if(!($projectuser = pz_projectuser::get($this->user,$project)))
			return "";
		
		$toggle_caldav_events_link = pz::url("screen",$p["controll"],$p["function"],array("project_id"=>$project->getId(),"mode"=>"toggle_caldav_events"));
		$toggle_caldav_jobs_link = pz::url("screen",$p["controll"],$p["function"],array("project_id"=>$project->getId(),"mode"=>"toggle_caldav_jobs"));
		$toggle_webdav_link = pz::url("screen",$p["controll"],$p["function"],array("project_id"=>$project->getId(),"mode"=>"toggle_webdav"));
		
		$toggle_caldav_events_link = "pz_exec_javascript('".$toggle_caldav_events_link."')";
		$toggle_caldav_jobs_link = "pz_exec_javascript('".$toggle_caldav_jobs_link."')";
		$toggle_webdav_link = "pz_exec_javascript('".$toggle_webdav_link."')";
		
		$return = '
              <tr>
                <td class="img1"><img src="'.$project->getInlineImage().'" width="40" height="40" alt="" /></td>';
                
		$return .= '<td><span class="title">'.$project->getName().'</span></td>';

		if($project->hasEmails() == 1) {
			if($projectuser->hasEmails())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status0">'.rex_i18n::msg("no").'</span></td>';
		}else {
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
		}
		
		if($project->hasCalendar() == 1) {
			if($projectuser->hasCalendar())
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status0">'.rex_i18n::msg("no").'</span></td>';

			if($projectuser->hasCalDAVEvents())  
				$return .= '<td><a href="javascript:void(0);" onclick="'.$toggle_caldav_events_link.'"><span class="status status-changeable status1 project-'.$project->getId().'-caldavevents">'.rex_i18n::msg("yes").'</span></a></td>';
			else 
				$return .= '<td><a href="javascript:void(0);" onclick="'.$toggle_caldav_events_link.'"><span class="status status-changeable status0 project-'.$project->getId().'-caldavevents">'.rex_i18n::msg("no").'</span></a></td>';
			
			if($projectuser->hasCalDAVJobs())  
				$return .= '<td><a href="javascript:void(0);" onclick="'.$toggle_caldav_jobs_link.'"><span class="status status-changeable status1 project-'.$project->getId().'-caldavjobs">'.rex_i18n::msg("yes").'</span></a></td>';
			else 
				$return .= '<td><a href="javascript:void(0);" onclick="'.$toggle_caldav_jobs_link.'"><span class="status status-changeable status0 project-'.$project->getId().'-caldavjobs">'.rex_i18n::msg("no").'</span></a></td>';

		}else {
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
		}
		
		if($project->hasFiles() == 1) {
			if($projectuser->hasFiles())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status0">'.rex_i18n::msg("no").'</span></td>';
		}else {
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
		}
		
		if($project->hasWiki() == 1) {
			if($projectuser->hasWiki())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status0">'.rex_i18n::msg("no").'</span></td>';
		}else {
			$return .= '<td><span class="status status2">'.rex_i18n::msg("not_available").'</span></td>';
		}
        
		return $return;
	}
	
	static function getSearchForm($p = array())
	{
		
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("search_for_users").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('users_list','users_search_form','".pz::url('screen','tools','users')."')");
		$xform->setObjectparams("form_id", "users_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("name")));
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));
		$searchform = $xform->getForm();
		
		$return = '<div id="users_search" class="design1col xform-search">'.$header.$searchform.'</div>';
		
		return $return;
		
	}
	
	
	
		
	public function getApiView($p) 
	{
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("api_key").'</h1>
          </div>
        </header>';
	
		$return = $header;
	
		$return .= '<div class="xform">'.rex_i18n::msg('api_info',$this->user->getAPIKey()).'</div>';
	
		$return = '<div id="api_form"><div id="api_view" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
	
	}
	
	
	// ---------------------------------------- FORM VIEWS
	
	static function getAddForm($p = array()) 
	{
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("user_add").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_add_form','".pz::url('screen','tools','users',array("mode"=>'add_user'))."')");
		$xform->setObjectparams("form_id", "user_add_form");

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("name",rex_i18n::msg("name")));
			$xform->setValidateField("empty",array("name",rex_i18n::msg("error_name_empty")));
		$xform->setValueField("text",array("login",rex_i18n::msg("login")));
			$xform->setValidateField("empty",array("login",rex_i18n::msg("error_login_empty")));
			$xform->setValidateField("unique",array("login",rex_i18n::msg("error_login_unique")));
		$xform->setValueField("text",array("password",rex_i18n::msg("password")));

		$xform->setValueField("text",array("email",rex_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",rex_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",rex_i18n::msg("error_email_unique")));

		$xform->setValueField("checkbox",array("status",rex_i18n::msg("active"),"1","0","0"));
		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin").' ('.rex_i18n::msg("admin_info").')',"1","0","0"));
		$xform->setValueField("stamp",array("created"," created","mysql_datetime","0","0","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));

		$xform->setValueField("checkbox",array("webdav",rex_i18n::msg("webdav").' ('.rex_i18n::msg("webdav_info").')',"1","0","no_db"));
		$xform->setValueField("checkbox",array("carddav",rex_i18n::msg("carddav").' ('.rex_i18n::msg("carddav_info").')',"1","0","no_db"));
		$xform->setValueField("checkbox",array("projectsadmin",rex_i18n::msg("projectsadmin").' ('.rex_i18n::msg("projectsadmin_info").')',"1","0","no_db"));

		$xform->setValueField("textarea",array("comment",rex_i18n::msg("user_comment")));

		$xform->setActionField("db",array('pz_user'));

		$return = $xform->getForm();


		if($xform->getObjectparams("actions_executed")) 
		{
			$user_id = $xform->getObjectparams("main_id");
			if($user = pz_user::get($user_id)) {
				
				$webdav = $xform->objparams["value_pool"]["email"]["webdav"];
				if($webdav == 1) $user->addPerm('webdav');
				else $user->removePerm('webdav');

				$carddav = $xform->objparams["value_pool"]["email"]["carddav"];
				if($carddav == 1) $user->addPerm('carddav');
				else $user->removePerm('carddav');

				$projectsadmin = $xform->objparams["value_pool"]["email"]["projectsadmin"];
				if($projectsadmin == 1) $user->addPerm('projectsadmin');
				else $user->removePerm('projectsadmin');
			
				$user->savePerm();
				
				$user->create();
			}
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("user_added").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url('screen','tools','users',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_add" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
	public function getEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("user_edit").': '.$this->user->getName().'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_edit_form','".pz::url('screen','tools','users',array("mode"=>'edit_user'))."')");
		$xform->setObjectparams("form_id", "user_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setHiddenField("user_id",$this->user->getId());

		$xform->setValueField("pz_digest",array("digest","login","password"));

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("name",rex_i18n::msg("name")));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_name_empty")));

		$xform->setValueField("text",array("login",rex_i18n::msg("login")));
		$xform->setValidateField("empty",array("login",rex_i18n::msg("error_login_empty")));
		$xform->setValidateField("unique",array("login",rex_i18n::msg("error_login_unique")));

		$xform->setValueField("text",array("password",rex_i18n::msg("password")));
		
		$xform->setValueField("text",array("email",rex_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",rex_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",rex_i18n::msg("error_email_unique")));

		if($this->user->getId() != pz::getUser()->getId())
		{
		}
		$xform->setValueField("checkbox",array("status",rex_i18n::msg("active"),"1","0","0"));
		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin").' ('.rex_i18n::msg("admin_info").')',"1","0","0"));
		
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));

		$xform->setValueField("checkbox",array("webdav",rex_i18n::msg("webdav").' ('.rex_i18n::msg("webdav_info").$this->user->hasPerm("webdav").')',"1",$this->user->hasPerm("webdav"),"no_db"));
		$xform->setValueField("checkbox",array("carddav",rex_i18n::msg("carddav").' ('.rex_i18n::msg("carddav_info").')',"1",$this->user->hasPerm("carddav"),"no_db"));
		$xform->setValueField("checkbox",array("projectsadmin",rex_i18n::msg("projectsadmin").' ('.rex_i18n::msg("projectsadmin_info").')',"1",$this->user->hasPerm("projectsadmin"),"no_db"));

		$xform->setValueField("textarea",array("comment",rex_i18n::msg("user_comment")));


		$xform->setActionField("db",array('pz_user','id='.$this->user->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			
			$webdav = $xform->objparams["value_pool"]["email"]["webdav"];
			if($webdav == 1) $this->user->addPerm('webdav');
			else $this->user->removePerm('webdav');

			$carddav = $xform->objparams["value_pool"]["email"]["carddav"];
			if($carddav == 1) $this->user->addPerm('carddav');
			else $this->user->removePerm('carddav');

			$projectsadmin = $xform->objparams["value_pool"]["email"]["projectsadmin"];
			if($projectsadmin == 1) $this->user->addPerm('projectsadmin');
			else $this->user->removePerm('projectsadmin');
		
			$this->user->savePerm();
			
			$this->user = pz_user::get($this->user->getId(),TRUE);
			$this->user->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("user_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url('screen','tools','users',array("mode"=>'list')));
			
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}

	
	
	public function getMyEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("profile_edit").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_edit_form','".pz::url('screen','tools','profile',array("mode"=>'edit_user'))."')");
		$xform->setObjectparams("form_id", "user_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("name",rex_i18n::msg("name")));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_name_empty")));

		$xform->setValueField("text",array("login",rex_i18n::msg("login")));
		$xform->setValidateField("empty",array("login",rex_i18n::msg("error_login_empty")));
		$xform->setValidateField("unique",array("login",rex_i18n::msg("error_login_unique")));

		$xform->setValueField("text",array("email",rex_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",rex_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",rex_i18n::msg("error_email_unique")));

		$xform->setValueField("select",array("account_id",rex_i18n::msg("default_email_account"),pz::getUser()->getEmailaccountsAsString(),"","",0,rex_i18n::msg("please_choose")));
		
		$startpages = array();
		$startpages[] = array('id'=>'projects','label'=>rex_i18n::msg("projects"));
		$startpages[] = array('id'=>'emails','label'=>rex_i18n::msg("emails"));
		$startpages[] = array('id'=>'calendars','label'=>rex_i18n::msg("calendars"));
		
		$xform->setValueField("select",array("startpage",rex_i18n::msg("default_startpage_account"),$startpages,"no_db",$this->user->getConfig('startpage'),0,rex_i18n::msg("please_choose")));
		
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
		$xform->setActionField("db",array('pz_user','id='.$this->user->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$this->user = pz_user::get($this->user->getId(),TRUE);
			$this->user->setConfig('startpage',$xform->objparams["value_pool"]["email"]["startpage"]);
			$this->user->saveConfig();
			$this->user->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("user_updated").'</p>'.$return;
			// $return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url('screen','tools','users',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
	public function getMyPasswordEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("profile_edit_password").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form_2','user_edit_password_form','".pz::url('screen','tools','profile',array("mode"=>'edit_password'))."')");
		$xform->setObjectparams("form_id", "user_edit_password_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));

		$xform->setValueField("password",array("password",rex_i18n::msg("password")));
		$xform->setValueField("password",array("password_2",rex_i18n::msg("password_reenter"),"","no_db"));

		$xform->setValidateField("empty",array("password",rex_i18n::msg("error_password_empty")));
		// TODO: compare passwords 

		$xform->setValidateField("compare",array("password","password_2",rex_i18n::msg("error_passwords_different")));

		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));

		$xform->setActionField("db",array('pz_user','id='.$this->user->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$this->user = pz_user::get($this->user->getId(),TRUE); // refresh data
			$this->user->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("user_password_updated").'</p>'.$return;
			// $return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url('screen','tools','users',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form_2"><div id="user_edit_password" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
}