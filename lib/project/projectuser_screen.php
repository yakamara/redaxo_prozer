<?php

class pz_projectuser_screen{

	public
		$projectuser = NULL;

	function __construct($projectuser)
	{
		$this->projectuser = $projectuser;
	}


	// ------------------------------------------------------------------ user/s

	public function getTableView($p = array(), $project, $projectuser)
	{
		
		$edit_link = pz::url("screen","project","user",array("project_id"=>$this->projectuser->project->getId(),"projectuser_id"=>$this->projectuser->getVar("id"),"mode"=>"edit_form"));
		$del_link = pz::url("screen","project","user",array("project_id"=>$this->projectuser->project->getId(),"projectuser_id"=>$this->projectuser->getVar("id"),"mode"=>"delete"));
		
		$return = '
              <tr>
                <td class="img1"><img src="'.$this->projectuser->user->getInlineImage().'" width="40" height="40" alt="" /></td>';
                
		if($projectuser->isAdmin() && $projectuser->getId() != $this->projectuser->getId()) {
        	$return .= '<td><a href="javascript:pz_loadPage(\'projectuser_form\',\''.$edit_link.'\')"><span class="title">'.$this->projectuser->user->getName().'</span></a></td>';
		}else
		{
        	$return .= '<td><span class="title">'.$this->projectuser->user->getName().'</span></td>';
		}

		if($this->projectuser->project->hasEmails() == 1) {
			if($this->projectuser->hasEmails())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';
		}
		if($this->projectuser->project->hasCalendar() == 1) {
			if($this->projectuser->hasCalendar())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';
		}
		if($this->projectuser->project->hasFiles() == 1) {
			if($this->projectuser->hasFiles())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';
		}
		if($this->projectuser->project->hasWiki() == 1) {
			if($this->projectuser->hasWiki())  
				$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
			else 
				$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';
		}
		
		if($this->projectuser->isAdmin())  
			$return .= '<td><span class="status status1">'.rex_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status2">'.rex_i18n::msg("no").'</span></td>';


		if($projectuser->isAdmin())
		{
			if($projectuser->getId() != $this->projectuser->getId()) {
        	$return .= '<td><a class="bt2" href="javascript:pz_loadPage(\'projectusers_list\',\''.$del_link.'\')"><span class="title">'.rex_i18n::msg("delete").'</span></a></td>';
			}else
			{
	        	$return .= '<td><span class="title"></span></td>';
			}
		}        
        
		return $return;
	}



	static function getUserlist($p,$projectusers, $project, $my_projectuser)
	{
		$list = "";
		
		$paginate_screen = new pz_paginate_screen($projectusers);
		$paginate = $paginate_screen->getPlainView($p);
		
		foreach($paginate_screen->getCurrentElements() as $projectuser) {
			$ps = new pz_projectuser_screen($projectuser);
			$list .= $ps->getTableView($p, $project, $my_projectuser);
		}
		
		$content = $paginate.'
          <table class="projectuserss tbl1">
          <thead><tr>
              <th></th>
              ';
              
		$content .= '<th>'.rex_i18n::msg("username").'</th>';
		if($project->hasEmails() == 1) $content .= '<th>'.rex_i18n::msg("emails").'</th>';
		if($project->hasCalendar() == 1) $content .= '<th>'.rex_i18n::msg("calendar").'</th>';
		if($project->hasFiles() == 1) $content .= '<th>'.rex_i18n::msg("files").'</th>';
		if($project->hasWiki() == 1) $content .= '<th>'.rex_i18n::msg("wiki").'</th>';
		$content .= '<th>'.rex_i18n::msg("project_admin").'</th>';
		if($my_projectuser->isAdmin()) $content .= '<th>'.rex_i18n::msg("functions").'</th>';
		
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
		return '<div id="projectusers_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	
	}	


	// ------------------------------------------------------------------- Forms

	static function getAddForm($p = array(), $project)
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("add_projectuser").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		
		$xform->setObjectparams("main_table",'pz_project_user');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectuser_form','projectuser_add_form','".pz::url('screen','project','user',array("mode"=>'add_form'))."')");
		
		$xform->setObjectparams("form_id", "projectuser_add_form");
		
		$xform->setHiddenField("project_id",$project->getId());
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$xform->setValidateField("pz_projectuser",array("pu",$project));
		$xform->setValueField("hidden",array("project_id",$project->getId()));
		
		$xform->setValueField('pz_select_screen',array('user_id', rex_i18n::msg('user'), pz::getUsersAsString(),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		
		if($project->hasEmails() == 1) {
			$xform->setValueField("checkbox",array("emails",rex_i18n::msg("emails"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("emails","0"));
		}
		
		if($project->hasCalendar() == 1) {
			$xform->setValueField("checkbox",array("calendar",rex_i18n::msg("calendar"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("calendar","0"));
		}

		if($project->hasFiles() == 1) {
			$xform->setValueField("checkbox",array("files",rex_i18n::msg("files"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("files","0"));
		}

		if($project->hasWiki() == 1) {
			$xform->setValueField("checkbox",array("wiki",rex_i18n::msg("wiki"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("wiki","0"));
		}

		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin"),"1","1","0","","","",""));

		$xform->setActionField("db",array());
		$return = $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) {
			
			/*
			$project_id = $xform->getObjectparams("main_id");
			if($project = pz_project::get($project_id)) {
				$project->create();
			}
			*/
			
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("projectuser_added").'</p>'.$return;
			$return .= pz_screen::getJSUpdateLayer('projectusers_list',pz::url('screen','project','user',array("project_id"=>$project->getId(),"mode"=>'list')));
		}else
		{
			$return = $header.$return;
		}
		$return = '<div id="projectuser_add" class="design1col xform-add">'.$return.'</div>';

		return $return;	
	
	}



	public function getEditForm($p = array())
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("edit_projectuser").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		
		$xform->setObjectparams("main_table",'pz_project_user');
		$xform->setObjectparams("main_id",$this->projectuser->getId());
		$xform->setObjectparams("main_where",'id='.$this->projectuser->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setHiddenField("project_id",$this->projectuser->project->getId());
		$xform->setHiddenField("projectuser_id",$this->projectuser->getId());
		
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectuser_form','projectuser_edit_form','".pz::url('screen','project','user',array("mode"=>'edit_form'))."')");
		
		$xform->setObjectparams("form_id", "projectuser_edit_form");
		

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		// $xform->setValidateField("pz_projectuser",array("pu",$this->projectuser->project));
		
		$xform->setValueField("hidden",array("user_id",$this->projectuser->user->getId(),"","no_db"));
		$xform->setValueField("hidden",array("project_id",$this->projectuser->project->getId()));
		
		$xform->setValueField('pz_show_screen',array('user_id', rex_i18n::msg('user'), $this->projectuser->user->getName() ));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		
		if($this->projectuser->project->hasEmails() == 1) {
			$xform->setValueField("checkbox",array("emails",rex_i18n::msg("emails"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("emails","0"));
		}
		
		if($this->projectuser->project->hasCalendar() == 1) { 
			$xform->setValueField("checkbox",array("calendar",rex_i18n::msg("calendar"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("calendar","0"));
		}

		if($this->projectuser->project->hasFiles() == 1) {
			$xform->setValueField("checkbox",array("files",rex_i18n::msg("files"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("files","0"));
		}

		if($this->projectuser->project->hasWiki() == 1) {
			$xform->setValueField("checkbox",array("wiki",rex_i18n::msg("wiki"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("wiki","0"));
		}

		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin"),"1","1","0","","","",""));

		$xform->setActionField("db",array('pz_project_user','id='.$this->projectuser->getId()));
		$return = $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) {
			$this->projectuser->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("projectuser_updated").'</p>'.$return;
			$return .= pz_screen::getJSUpdateLayer('projectusers_list',pz::url('screen','project','user',array("project_id"=>$this->projectuser->project->getId(),"mode"=>'list')));

		}else
		{
			$return = $header.$return;
		}
		$return = '<div id="projectuser_edit" class="design1col xform-edit">'.$return.'</div>';

		return $return;	
	
	}


	public function getViewForm($p = array())
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("projectuser_info").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		$xform->setObjectparams("main_table",'pz_project_user');
		$xform->setObjectparams("main_id",$this->projectuser->getId());
		$xform->setObjectparams("main_where",'id='.$this->projectuser->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_action", "javascript:void(0)");
		$xform->setObjectparams("form_id", "projectuser_view_form");
		$xform->setValueField('objparams',array('submit_btn_show', FALSE));
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$xform->setValueField('pz_select_screen',array('user_id', rex_i18n::msg('user'), pz::getUsersAsString(),"","",1,rex_i18n::msg("please_choose"),'disabled'=>TRUE));
		
		if($this->projectuser->project->hasEmails() == 1) {
			$xform->setValueField("checkbox",array("emails",rex_i18n::msg("emails"),"1","1","0",'disabled'=>TRUE));
		}
		
		if($this->projectuser->project->hasCalendar() == 1) { 
			$xform->setValueField("checkbox",array("calendar",rex_i18n::msg("calendar"),"1","1","0",'disabled'=>TRUE));
		}

		if($this->projectuser->project->hasFiles() == 1) {
			$xform->setValueField("checkbox",array("files",rex_i18n::msg("files"),"1","1","0",'disabled'=>TRUE));
		}

		if($this->projectuser->project->hasWiki() == 1) {
			$xform->setValueField("checkbox",array("wiki",rex_i18n::msg("wiki"),"1","1","0",'disabled'=>TRUE));
		}

		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin"),"1","1","0",'disabled'=>TRUE));

		$return = $xform->getForm();
		$return = $header.$return;

		$return = '<div id="projectuser_view" class="design1col xform-view">'.$return.'</div>';

		return $return;	
	
	}






}