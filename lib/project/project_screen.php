<?php

class pz_project_screen{

	function __construct($project) 
	{
		$this->project = $project;
	}

	// ---------------------------------------------------------------- VIEWS

	public function getAdmins()
	{
		return "TODO:ADMINS";
		
	}

	/*
	function getBlockView($p = array()) {
    
    	$customer_name = rex_i18n::msg("no_customer");
    	if($this->project->hasCustomer()){
    		$customer_name = $this->project->customer->getName();
    	}
    
		$return = '
		      <article>
            <header>
              <figure><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></figure>
              <hgroup>
                <h2 class="hl7"><span class="name">'.$customer_name.'</span><span class="info">'.$this->project->getVar("created", 'datetime').'</span></h2>
                <h3 class="hl7"><a href=""><span class="title">'.$this->project->getVar("name").'</span></a></h3>
              </hgroup>
            </header>
            
            <section class="content">
              <p>'.$this->project->getVar("description").'</p>
            </section>
            
            <footer>
              <ul class="sl2">
                <li class="selected option"><span class="selected option">Optionen</span>
                  <div class="flyout">
                    <div class="content">
                      <ul class="entries">
                        <li class="entry first"><a href=""><span class="title">Spam</span></a></li>
                        <li class="entry"><a href=""><span class="title">Ham</span></a></li>
                        <li class="entry"><a href=""><span class="title">Trash</span></a></li>
                      </ul>
                    </div>
                  </div>
                </li>
              </ul>
              <a class="label labelc'.$this->project->getVar('label_id').'" href="">Label</a>
            </footer>
          </article>
        ';
	
		return $return;
	}
	*/
	/*
	function getListView($p = array())
	{

    	$customer_name = rex_i18n::msg("no_customer");
    	if($this->hasCustomer()){
    		$customer_name = $this->project->customer->getName();
    	}
    
		$return = '
		      <article>
            <header>
              <figure><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></figure>
              <hgroup>
                <h2 class="hl7"><span class="name">'.$customer_name.'</span><span class="info">'.$this->project->getVar("created", 'datetime').'</span></h2>
                <h3 class="hl7"><a href="'.pz::url("screen","project","view",array("project_id"=>$this->project->getId())).'"><span class="title">'.$this->project->getVar("name").'</span></a></h3>
              </hgroup>
            </header>
            
            <footer>
              <ul class="sl2">
                <li class="selected option"><span class="selected option">Optionen</span>
                  <div class="flyout">
                    <div class="content">
                      <ul class="entries">
                        <li class="entry first"><a href=""><span class="title">Spam</span></a></li>
                        <li class="entry"><a href=""><span class="title">Ham</span></a></li>
                        <li class="entry"><a href=""><span class="title">Trash</span></a></li>
                      </ul>
                    </div>
                  </div>
                </li>
              </ul>
              <span class="status project-status status1">E-Mail wurde bearbeitet</span>
              <a class="label labelc'.$this->project->getVar('label_id').'" href="">Label</a>
            </footer>
          </article>
        ';
	
	
	
		return $return;
	}
	*/

	function getMatrixView($p = array()) 
	{
    
		$customer_name = rex_i18n::msg("no_customer");
    	if($this->project->hasCustomer()){
    		$customer_name = $this->project->customer->getName();
    	}
    
		$return = '
		      <article>
            <header>
              <figure><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></figure>
              <hgroup>
                <h2 class="hl7"><span class="name">'.$customer_name.'</span><span class="info">'.$this->project->getVar("created", 'datetime').'</span></h2>
                <h3 class="hl7"><a href="'.pz::url("screen","project","view",array("project_id"=>$this->project->getId())).'"><span class="title">'.$this->project->getVar("name").'</span></a></h3>
              </hgroup>
            </header>
            
            <section class="content">
			<!-- TODO: Meldungen etc reinsetzen ? -->
            </section>
            
            <footer>
            <!--
              <ul class="sl2">
                <li class="selected option"><span class="selected option">Optionen</span>
                  <div class="flyout">
                    <div class="content">
                      <ul class="entries">
                        <li class="entry first"><a href=""><span class="title">Spam</span></a></li>
                        <li class="entry"><a href=""><span class="title">Ham</span></a></li>
                        <li class="entry"><a href=""><span class="title">Trash</span></a></li>
                      </ul>
                    </div>
                  </div>
                </li>
              </ul>
              -->
              <span class="label labelc'.$this->project->getVar('label_id').'">Label</span>
            </footer>
          </article>
        ';
	
		return $return;
	}



	function getTableView($p = array())
	{
		$customer_name = rex_i18n::msg("no_customer");
    	if($this->project->hasCustomer()){
    		$customer_name = $this->project->customer->getName();
    	}
    	$admins = $this->project->getAdmins();
    	$admin_text = array();
    	foreach($admins as $admin) {
    		$admin_text[] = $admin->getName();
    	}
    	
    	
		$return = '
              <tr>
                <td class="img1"><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></td>
                <td><span class="name">'.$customer_name.'</span></td>
                <td><a href="'.pz::url("screen","project","view",array("project_id"=>$this->project->getId())).'"><span class="title">'.$this->project->getVar("name").'</span></a></td>
                <td><span class="info">'.$this->project->getVar("created", 'datetime').'</span></td>
                <td><span class="info">'.implode("<br />",$admin_text).'</span></td>
                <td class="label labelc'.$this->project->getVar('label_id').'"></td>
              </tr>            
        ';
	
		return $return;
	}


	static function getAddForm($p = array())
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("add_project").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		
		$xform->setObjectparams("main_table",'pz_project');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_add','project_add_form','".pz::url('screen','projects','all',array("mode"=>'add_form'))."')");
		
		$xform->setObjectparams("form_id", "project_add_form");
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("name",rex_i18n::msg("project_name"),"","0","","","","",""));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_project_enter_name")));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("project_description"),"","0","","","","",""));
		$xform->setValueField("pz_select_screen",array("label_id",rex_i18n::msg("project_label"),pz_labels::getAsString(), '', '', '0'));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		$xform->setValueField("pz_select_screen",array("customer_id",rex_i18n::msg("project_customer"),pz_customers::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("hidden",array("create_user_id",pz::getUser()->getId()));
		$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));
		$xform->setValueField("hidden",array("archived",0));
		$xform->setValueField("checkbox",array("has_calendar",rex_i18n::msg("calendar"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_wiki",rex_i18n::msg("wiki"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_files",rex_i18n::msg("files"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_emails",rex_i18n::msg("emails"),"1","1","0","","","",""));

		$xform->setActionField("db",array());
		$return = $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) {
			
			$project_id = $xform->getObjectparams("main_id");
			if($project = pz_project::get($project_id)) {
				$project->create();
			}
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_added").'</p>'.$return;
		}else
		{
			$return = $header.$return;
		}
		$return = '<div id="project_add" class="design1col xform-add">'.$return.'</div>';

		return $return;	
	
	}
	
	
	function getEditForm($p = array())
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("edit_project").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("main_table",'pz_project');
		$xform->setObjectparams("main_id",$this->project->getId());
		$xform->setObjectparams("main_where",'id='.$this->project->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setHiddenField("project_id",$this->project->getId());
		
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_edit','project_edit_form','".pz::url('screen','project','view',array("mode"=>'edit_form'))."')");
		$xform->setObjectparams("form_id", "project_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);
				
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("name",rex_i18n::msg("project_name"),"","0","","","","",""));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_project_enter_name")));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("project_description"),"","0","","","","",""));
		$xform->setValueField("pz_select_screen",array("label_id",rex_i18n::msg("project_label"),pz_labels::getAsString(), '', '', '0'));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		$xform->setValueField("pz_select_screen",array("customer_id",rex_i18n::msg("project_customer"),pz_customers::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));
		$xform->setValueField("checkbox",array("has_calendar",rex_i18n::msg("calendar"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_wiki",rex_i18n::msg("wiki"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_files",rex_i18n::msg("files"),"1","1","0","","","",""));
		$xform->setValueField("checkbox",array("has_emails",rex_i18n::msg("emails"),"1","1","0","","","",""));

		$xform->setValueField("checkbox",array("archived",rex_i18n::msg("archived"),"1","1","0","","","",""));

		$xform->setActionField("db",array('pz_project','id='.$this->project->getId()));

		$return = $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) {
			$this->project->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_updated").'</p>'.$return;
		}else
		{
			$return = $header.$return;
		}
		
		$return = '<div id="project_edit" class="design1col xform-edit">'.$return.'</div>';

		return $return;	
	
	}
	
	function getViewForm($p = array())
	{
		
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("view_project").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		$xform->setObjectparams("main_table",'pz_project');
		$xform->setObjectparams("main_id",$this->project->getId());
		$xform->setObjectparams("main_where",'id='.$this->project->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_id", "project_view_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField('objparams',array('submit_btn_show', FALSE));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("project_name"),"","0",'disabled'=>TRUE));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("project_description"),"","0",'disabled'=>TRUE));
		$xform->setValueField("pz_select_screen",array("label_id",rex_i18n::msg("project_label"),pz_labels::getAsString(), '', '', '0','disabled'=>TRUE));
		$xform->setValueField("pz_select_screen",array("customer_id",rex_i18n::msg("project_customer"),pz_customers::getAsString(),"","",1,rex_i18n::msg("please_choose"),'disabled'=>TRUE));

		$xform->setValueField("checkbox",array("has_calendar",rex_i18n::msg("calendar"),"1","1","0",'disabled'=>TRUE));
		$xform->setValueField("checkbox",array("has_wiki",rex_i18n::msg("wiki"),"1","1","0",'disabled'=>TRUE));
		$xform->setValueField("checkbox",array("has_files",rex_i18n::msg("files"),"1","1","0",'disabled'=>TRUE));
		$xform->setValueField("checkbox",array("has_emails",rex_i18n::msg("emails"),"1","1","0",'disabled'=>TRUE));
		$xform->setValueField("checkbox",array("archived",rex_i18n::msg("archived"),"1","1","0",'disabled'=>TRUE));

		$return = $header.$xform->getForm();
		
		$return = '<div id="project_view" class="design1col xform-view">'.$return.'</div>';

		return $return;	

		
		
	}
	


}