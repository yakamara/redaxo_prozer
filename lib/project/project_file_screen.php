<?php

class pz_project_file_screen
{

	public $file;
	
	function __construct($file) 
	{
		$this->file = $file;
	}

	// --------------------------------------------------------------- Listviews

	function getFileListView($p = array())
	{
	
		$p["linkvars"]["file_id"] = $this->file->getId();
    
    	$edit_link = "javascript:pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_file")))."')";
    	$delete_link = "javascript:pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_file")))."')";

    	// '.$this->customer->getInlineImage().'
		$return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$edit_link.'">
                <figure><img src="" width="40" height="40" alt="" /></figure>
                <hgroup>
                  <h3 class="hl7"><span class="title">'.$this->file->getName().'</span></h3>
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="'.$edit_link.'">'.rex_i18n::msg("project_file_edit").'</a>
            </footer>
          </article>
        ';
	
		return $return;
	}

	function getFolderListView($p = array())
	{
	
		$p["linkvars"]["file_id"] = $this->file->getId();
    
    	$edit_link = "javascript:pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_folder")))."')";
    	$delete_link = "javascript:pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_folder")))."')";

    	// '.$this->customer->getInlineImage().'
		$return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$edit_link.'">
                <figure><img src="" width="40" height="40" alt="" /></figure>
                <hgroup>
                  <h3 class="hl7"><span class="title">'.$this->file->getName().'</span></h3>
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="'.$edit_link.'">'.rex_i18n::msg("project_folder_edit").'</a>
            </footer>
          </article>
        ';
	
		return $return;
	}



	function getFilesListView($files, $p = array()) 
	{
		$content = "";
		$p["layer"] = 'project_files_list';
		
		// $paginate_screen = new pz_paginate_screen($files);
		// $paginate = $paginate_screen->getPlainView($p);
		// $paginate_screen->getCurrentElements()
		
		$first = " first";
		foreach($files->getChildren() as $file) {
			if($cs = new pz_project_file_screen($file)) {
				if($file->isDirectory())
					$content .= '<li class="lev1 entry project_folder'.$first.'">'.$cs->getFolderListView($p).'</li>';
				else
					$content .= '<li class="lev1 entry project_file'.$first.'">'.$cs->getFileListView($p).'</li>';
				$first = "";
			}
		}
		
		$content = '<ul class="entries view-list">'.$content.'</ul>'; // $paginate.

		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', "", false);
	
		return '<div id="project_files_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';

	}


	// --------------------------------------------------------------- Pageviews

	function getSearchForm($p = array())
	{

		$searchform = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("project_files_search_for").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_files_list','project_files_search_form','".pz::url('screen','project',$this->function)."')");
		$xform->setObjectparams("form_id", "project_files_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("name")));
		$xform->setValueField("hidden",array("mode","list"));
		$xform->setValueField("hidden",array("project_id",$this->project->getId()));
		$searchform .= $xform->getForm();
		
		$searchform = '<div id="project_files_search" class="design1col xform-search">'.$searchform.'</div>';
		
		return $searchform;
		
	}


	// --------------------------------------------------------------- Formviews

	public function getEditForm($p = array()) 
	{
		
		$p["linkvars"]["mode"] = "edit_file";
	
    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("project_file_edit").': '.$this->file->getName().'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("main_table",'pz_project_file');
		$xform->setObjectparams("main_id",$this->file->getId());
		$xform->setObjectparams("main_where",'id='.$this->file->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_file_edit','project_file_edit_form','".pz::url('screen','project','files',$p["linkvars"])."')");
		$xform->setObjectparams("form_id", "project_file_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);
		$xform->setHiddenField("file_id",$this->file->getId());
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$xform->setValueField("pz_image_screen",array("image_inline",rex_i18n::msg("photo"),pz_customer::getDefaultImage()));

		$xform->setValueField("showvalue",array("path",rex_i18n::msg("project_file_path"),"","0"));
		$xform->setValueField("showvalue",array("name",rex_i18n::msg("project_file_name"),"","0"));
		
		$xform->setValueField("textarea",array("comment",rex_i18n::msg("project_file_comment"),"","0"));

		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
		$xform->setValueField("hidden",array("create_user_id",pz::getUser()->getId()));
		$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
		
			// $this->file->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("customer_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',pz::url('screen','projects','project',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="project_file_form"><div id="project_file_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}

	static function getAddForm($project, $p = array()) 
	{

		$p["linkvars"]["mode"] = "add_file";
	
		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("project_file_add").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("main_table",'pz_project_file');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_file_add','project_file_add_form','".pz::url('screen','project','files',$p["linkvars"])."')");
		$xform->setObjectparams("form_id", "project_file_add_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("pz_image_screen",array("image_inline",rex_i18n::msg("photo"),pz_customer::getDefaultImage()));
		
		$node = new pz_project_root_directory($project);
		$paths = $node->getAllPaths();

		// echo '<pre>'; var_dump($paths); echo '</pre>';
		
		$xform->setValueField("select",	array("path",rex_i18n::msg("project_file_path"),$paths,"","/",0,1));
		// TODO .. pfad muss gesetzt sein
		// TODO .. Datei/en müssen hochgeladen sein

		$xform->setValueField("textarea",array("comment",rex_i18n::msg("project_file_comment"),"","0"));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1"));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
		$xform->setValueField("hidden",array("create_user_id",pz::getUser()->getId()));
		$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));

		// $xform->setActionField("db",array());
		$return .= $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
			// $file_id = $xform->getObjectparams("main_id");
			if($file = pz_project_file::get($file_id)) {
				// $file->create();
			}
			$return .= '<p class="xform-info">'.rex_i18n::msg("project_file_added").'</p>';
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',pz::url('screen','projects','project',array("mode"=>'list')));
			
		}
		$return = '<div id="project_file_form"><div id="project_file_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}



}