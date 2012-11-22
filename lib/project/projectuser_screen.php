<?php

class pz_projectuser_screen{

	public
		$projectuser = NULL;

	function __construct($projectuser)
	{
		$this->projectuser = $projectuser;
	}


	// ------------------------------------------------------------------ user/s

	static function getUserlist($p, $projectusers, $project, $my_projectuser)
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
		if($project->hasCalendar() == 1) $content .= '<th>'.rex_i18n::msg("calendar_events").'</th>';
		if($project->hasCalendarJobs() == 1) $content .= '<th>'.rex_i18n::msg("calendar_jobs").'</th>';
		if($project->hasFiles() == 1) $content .= '<th>'.rex_i18n::msg("files").'</th>';
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
		return '<div id="projectusers_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
	
	}	

  public function getTableView($p = array(), $project, $projectuser)
	{

		$row_id = 'project-userperm-'.$this->projectuser->getProject()->getId().'-'.$this->projectuser->getUser()->getId();

    $td = array();
    $td[] = '<td class="img1"><img src="'.$this->projectuser->getUser()->getInlineImage().'" width="40" height="40" alt="" /></td>';
		$td[] = '<td><span class="title">'.$this->projectuser->getUser()->getName().'</span></td>';

    if($this->projectuser->getProject()->hasEmails()) {
      $status = 2;
  		if($this->projectuser->getProject()->hasEmails() == 1) { $status = $this->projectuser->hasEmails() ? $status = 1 : $status = 0; }
  	  $td[] = $this->getPermTableCellView("emails", $status, $projectuser);
    }

    if($this->projectuser->getProject()->hasCalendarEvents()) {
      $status = 2;
  	  if ($this->projectuser->getProject()->hasCalendar() == 1) { $status = $this->projectuser->hasCalendarEvents() ? $status = 1 : $status = 0; }
  	  $td[] = $this->getPermTableCellView("calendar_events", $status, $projectuser);
    }

    if($this->projectuser->getProject()->hasCalendarJobs()) {
      $status = 2;
      if ($this->projectuser->getProject()->hasCalendarJobs() == 1) { $status = $this->projectuser->hasCalendarJobs() ? $status = 1 : $status = 0; }
      $td[] = $this->getPermTableCellView("calendar_jobs", $status, $projectuser);
    }
    
    /*
    $status = 2;
	  if ($this->projectuser->getProject()->hasCalendar() == 1) { $status = $this->projectuser->hasCalDAVEvents() ? $status = 1 : $status = 0; }
	  $td[] = $this->getPermTableCellView("caldav_events", $status, $projectuser);

    $status = 2;
    if ($this->projectuser->getProject()->hasCalendar() == 1) { $status = $this->projectuser->hasCalDAVJobs() ? $status = 1 : $status = 0; }
    $td[] = $this->getPermTableCellView("caldav_jobs", $status, $projectuser);
    */  

    if($this->projectuser->getProject()->hasFiles()) {
      $status = 2;
      if ($this->projectuser->getProject()->hasFiles() == 1) { $status = $this->projectuser->hasFiles() ? $status = 1 : $status = 0; }
      $td[] = $this->getPermTableCellView("files", $status, $projectuser);
	  }
		
    $status = $this->projectuser->isAdmin() ? $status = 1 : $status = 0;
    $td[] = $this->getPermTableCellView("admin", $status, $projectuser);
		
		if($projectuser->isAdmin())
		{
      $del_link = pz::url("screen","project","user",array("project_id"=>$this->projectuser->getProject()->getId(),"projectuser_id"=>$this->projectuser->getVar("id"),"mode"=>"delete"));

			if($projectuser->getId() != $this->projectuser->getId()) {
        $td[] = '<td><a class="bt2" href="javascript:void(0);" onclick="pz_loadPage(\'projectusers_list\',\''.$del_link.'\')"><span class="title">'.rex_i18n::msg("delete").'</span></a></td>';
			} else {
	      $td[] = '<td><span class="title"></span></td>';
			}
		} 
		
    $return = '<tr id="'.$row_id.'">'.implode("",$td).'</tr>';
		
		return $return;
	}

  public function getPermTableCellView($type = "", $status = 2, $projectuser = NULL)
	{
	
	  $classes = array();
	  $classes[] = 'projectperm-status';
	  $classes[] = 'project-perm-'.$type;
	  $classes[] = 'project-id-'.$this->projectuser->getProject()->getId();
	  $classes[] = 'user-id-'.$this->projectuser->getUser()->getId();

	  $td_id = 'project-userperm-'.$this->projectuser->getProject()->getId().'-'.$this->projectuser->getUser()->getId().'-'.$type;

    $link_a = pz::url("screen","project","userperm",array("project_id"=>$this->projectuser->getProject()->getId(), "user_id" => $this->projectuser->getUser()->getId(),"mode"=>"toggle_".$type));
    $link = "pz_loadPage('#".$td_id."','".$link_a."')";

	  if($status == 2) {
	    $classes[] = "inactive";
	    return '<td id="'.$td_id.'" class="'.implode(" ",$classes).'"><span class="status status-2">'.rex_i18n::msg("not_available").'</span></td>';

	  } else {
	  
  	  if(
  	      ($type == "admin" && isset($projectuser) && $projectuser->isAdmin() && $this->projectuser->getUser()->getId() != pz::getUser()->getId()) || 
  	      
  	      ($type != "admin" && pz::getUser()->isAdmin()) || 
  	      
  	      ( $status != 2 && 
  	        pz::getUser()->getId() == $this->projectuser->getUser()->getId() && 
  	        ( ($type == "caldav_events" && $this->projectuser->hasCalendarEvents() )  || ($type == "caldav_jobs" && $this->projectuser->hasCalendarJobs()) ) 
  	      )
  	      
  	    ) {
  	    if($status == 1) {
  	      return '<td id="'.$td_id.'" class="'.implode(" ",$classes).'"><a href="javascript:void(0);" onclick="'.$link.'" ><span class="status status-changeable status-'.$status.'">'.rex_i18n::msg("yes").'</span></a></td>';
  	    } else {
          return '<td id="'.$td_id.'" class="'.implode(" ",$classes).'"><a href="javascript:void(0);" onclick="'.$link.'"><span class="status status-changeable status-'.$status.'">'.rex_i18n::msg("yes").'</span></a></td>';
  	    }
  	  } else {
	      $classes[] = "inactive";
  	    if ($status == 1) {
  	      return '<td id="'.$td_id.'" class="'.implode(" ",$classes).'"><span class="status status-1">'.rex_i18n::msg("yes").'</span></td>';
  	    } else {
  	      return '<td id="'.$td_id.'" class="'.implode(" ",$classes).'"><span class="status status-0">'.rex_i18n::msg("no").'</span></td>';
  	    }
  	  
  	  }
	  }  
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
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		
		$xform->setValidateField("pz_projectuser",array("pu",$project));
		$xform->setValueField("hidden",array("project_id",$project->getId()));
		$xform->setValueField('pz_select_screen',array('user_id', rex_i18n::msg('user'), pz::getUsersAsArray(pz::getUser()->getUsers()),"","",0,rex_i18n::msg("please_choose")));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		
		if($project->hasEmails() == 1) {
			$xform->setValueField("checkbox",array("emails",rex_i18n::msg("emails"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("emails","0"));
		}
		
    if($project->hasCalendar() == 1) {
    	$xform->setValueField("checkbox",array("calendar",rex_i18n::msg("calendar_events"),"1","1","0","","","",""));
    }else {
    	$xform->setValueField("hidden",array("calendar","0"));
    }
    
    if($project->hasCalendarJobs() == 1) {
    	$xform->setValueField("checkbox",array("calendar_jobs",rex_i18n::msg("calendar_jobs"),"1","1","0"));
    }else {
    	$xform->setValueField("hidden",array("calendar_jobs","0"));
    }

		if($project->hasFiles() == 1) {
			$xform->setValueField("checkbox",array("files",rex_i18n::msg("files"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("files","0"));
		}

		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin"),"1","0","0","","","",""));

		$xform->setActionField("db",array());
		$return = $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) 
		{
			// $project_user_id = $xform->getObjectparams("main_id");
			$user = pz_user::get(rex_request("user_id","int"));
			
			if( ($projectuser = pz_projectuser::get($user, $project)) ) 
			{
        $projectuser->create();
			}
			
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("projectuser_added").'</p>'.$return;
			$return .= pz_screen::getJSUpdateLayer('projectusers_list',pz::url('screen','project','user',array("project_id"=>$project->getId(),"mode"=>'list')));

		}else
		{
			$return = $header.$return;
		}
		$return = '<div id="projectuser_add" class="design1col xform-add">'.$return.'</div>';

		return $return;	
	
	}


}