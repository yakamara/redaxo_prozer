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
    
    	$edit_link = "pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_file")))."')";
    	$delete_link = "javascript:pz_loadPage('project_file_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_file")))."')";
    	$download_link = pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"download_file")));
    	$clipboard_link = "pz_exec_javascript('".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"file2clipboard")))."')";

		$create_date = DateTime::createFromFormat('U', $this->file->getLastModified());

		$user_name = "";
		if(($user = pz_user::get($this->file->getUserId())))
	    	$user_name = $user->getName();
    
    
    $class_figure = ' '.rex_file::extension($this->file->getName());
        
    	// '.$this->customer->getInlineImage().'
		$return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$download_link.'">
                <figure class="file40'.$class_figure.'"></figure>
                <hgroup>
                  <h3 class="hl7"><span class="title" title="'.htmlspecialchars($this->file->getName()).'">'.htmlspecialchars(pz::cutText($this->file->getName(),40)).'</span> ('.pz::readableFilesize($this->file->getSize()).', <span title="'.htmlspecialchars($user_name).'">'.htmlspecialchars(pz::cutText($user_name,10)).'</span>, '.$create_date->format(rex_i18n::msg("format_d_m_y_h_i")).')</h3>
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="javascript:void(0)" onclick="'.$clipboard_link.'">'.rex_i18n::msg("2clipboard").'</a>
              <a class="bt2" href="javascript:void(0)" onclick="'.$edit_link.'">'.rex_i18n::msg("project_file_edit").'</a>
            </footer>
          </article>
        ';
	
		return $return;
	}

	function getFolderListView($p = array())
	{
		$p["linkvars"]["file_id"] = $this->file->getId();	
    	$edit_link = "javascript:pz_loadPage('project_folder_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_folder")))."')";
    	$delete_link = "javascript:pz_loadPage('project_folder_form','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"edit_folder")))."')";

    	// '.$this->customer->getInlineImage().'
		$return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$this->getIntoFolderLink($this->file->getId(), $p).'">
                <figure class="folder40"></figure>
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

	function getIntoFolderLink($file_id, $p){
		$p["linkvars"]["file_id"] = $file_id;
		return "javascript:pz_loadPage('project_files_list','".pz::url("screen","project","files",array_merge($p["linkvars"],array("mode"=>"list")))."')";
	}

	function getPathView($category, $p = array()) 
	{
		$content = '';
		$first = ' first';
		$p["layer"] = 'project_files_list';
		
		// $paginate_screen = new pz_paginate_screen($files);
		// $paginate = $paginate_screen->getPlainView($p);
		// $paginate_screen->getCurrentElements()
		
		// path..
		$path = array();
		
		$current = $category;
		if($current->getId() != 0)
			$path[] = '<a class="bt7" href="'.pz_project_file_screen::getIntoFolderLink($current->getid(), $p).'">'.$current->getName().'</a>';
		
		while($current->getParentId() != 0)
		{
			$current = $current->getParent();
			$path[] = '<a class="bt7" href="'.pz_project_file_screen::getIntoFolderLink($current->getid(), $p).'">'.$current->getName().'</a>';
		}

		$path[] = '<a class="bt7" href="'.pz_project_file_screen::getIntoFolderLink(0, $p).'">'.rex_i18n::msg("project_folder_home").'</a>';

		if(count($path) > 0)
		{
			$content .= '<ul><li>'.implode("/</li><li>",array_reverse($path)).'</li></ul>';	
			$first = '';
		}
		
		
		$content = '<div class="grid1col project-folder-path"><dl><dt>'.rex_i18n::msg("project_file_path").':</dt><dd>'.$content.'</dd></dl></div>'; // $paginate.

		return $content;

	}

	function getFilesListView($category, $p = array()) 
	{
		$content = "";
		$first = " first";
		$p["layer"] = 'project_files_list';
		
		// $paginate_screen = new pz_paginate_screen($files);
		// $paginate = $paginate_screen->getPlainView($p);
		// $paginate_screen->getCurrentElements()
		
		foreach($category->getChildren() as $file) {
			if($cs = new pz_project_file_screen($file)) {
				if($file->isDirectory())
					$content .= '<li class="lev1 entry project-folder'.$first.'">'.$cs->getFolderListView($p).'</li>';
				else
					$content .= '<li class="lev1 entry project-file'.$first.'">'.$cs->getFileListView($p).'</li>';
				$first = "";
			}
		}
		
		$path = pz_project_file_screen::getPathView($category, $p);
		$content = $path.'<ul class="entries view-list">'.$content.'</ul>'; // $paginate.

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

	static function getAddFileLink($p) {
		
		$p["linkvars"]["mode"] = "add_file";
		$add_link = pz::url('screen','project','files',$p["linkvars"]);
		$return = '<div class="xform">
				<p><a class="bt1" onclick="pz_loadPage(\'project_file_form\',\''.$add_link.'\')" href="javascript:void(0);">+ '.rex_i18n::msg("project_file_add").'</a></p>
				</div>';
		return $return;
	}

	public function getDeleteFileLink($p) {
		
		$p["linkvars"]["mode"] = "delete_file";
		$p["linkvars"]["file_id"] = $this->file->getId();
		
		$delete_link = pz::url('screen','project','files',$p["linkvars"]);
		$return = '<div class="xform">
					<p><a class="bt17" onclick="check = confirm(\''.rex_i18n::msg("project_file_confirm_delete",htmlspecialchars($this->file->getName())).'\'); if (check == true) pz_loadPage(\'project_file_form\',\''.$delete_link.'\')" href="javascript:void(0);">- '.rex_i18n::msg("project_file_delete").'</a></p>
					</div>';
		return $return;
	}


	// ----------------------------- Fileform Views

	public function getDeleteFileForm($project, $p = array())
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("project_file_delete").'</h1>
	          </div>
	        </header>';
		$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_file_deleted", htmlspecialchars($this->file->getName())).'</p>';
		$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => $this->file->getParentId()))
				);
		
		$return .= pz_project_file_screen::getAddFileLink($p);

		$return = '<div id="project_file_form"><div id="project_file_delete" class="design1col xform-delete">'.$return.'</div></div>';
		return $return;
	}


	static function getAddFileForm($project, $p = array()) 
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
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_file_add','project_file_add_form','".pz::url('screen','project','files',$p["linkvars"])."')");
		$xform->setObjectparams("form_id", "project_file_add_form");
		$xform->setObjectparams("form_name", "projectfile");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("pz_attachment_screen",array("clip_ids",rex_i18n::msg("project_files")));
		$xform->setValidateField("empty",array("clip_ids", rex_i18n::msg("error_files_no_files_selected")));
		
		$node = new pz_project_root_directory($project);
		$paths = $node->getAllPaths();
		$xform->setValueField("select",	array("parent_id",rex_i18n::msg("project_file_path"),$paths,"","/",0,1));
		$xform->setValidateField("pz_project_folder",array("parent_id",0, $project->getId(),rex_i18n::msg("error_folder_name_parent"),rex_i18n::msg("error_folder_project_id")));
		$xform->setValueField("textarea",array("comment",rex_i18n::msg("project_file_comment"),"","0"));

		// $xform->setActionField("db",array());
		$return .= $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
			
			$parent_id = $xform->objparams["value_pool"]["sql"]["parent_id"];
			$comment = $xform->objparams["value_pool"]["sql"]["comment"];
			
			$node = pz_project_node::get($parent_id);
			if($parent_id == 0)
				$node = new pz_project_root_directory($project);

			$clip_ids = explode(",",$xform->objparams["value_pool"]["sql"]["clip_ids"]);
			$clips = array();
			foreach($clip_ids as $clip_id) {
				$clip_id = (int) $clip_id;
				if($clip_id > 0 && $clip = pz_clipboard::getClipById($clip_id,pz::getUser()->getId())) {

					$clip["path"] = pz_clipboard::getPath($clip["id"],pz::getUser()->getId());
					if(file_exists($clip["path"]))
					{
						$name = $node->getAvailableName($clip["filename"]);
						$data = file_get_contents($clip["path"]);
						$node->createFile($name, $data, $comment);

					}
				}
			}

			$return .= '<p class="xform-info">'.rex_i18n::msg("project_file_added").'</p>';
			$return .= pz_project_file_screen::getAddFileLink($p);
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => $parent_id))
				);
			
		}
		$return = '<div id="project_file_form"><div id="project_file_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}

	public function getEditFileForm($project, $p = array()) 
	{
		
		$p["linkvars"]["mode"] = "edit_file";
	
		$parent_id = $this->file->getParentId();
	
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

		$node = new pz_project_root_directory($project);
		$paths = $node->getAllPaths();
		$xform->setValueField("select",	array("parent_id",rex_i18n::msg("project_file_path"),$paths,"","/",0,1));
		$xform->setValidateField("pz_project_folder",array("parent_id",0, $project->getId()));
		$xform->setValidateField("unique",array("parent_id,name,project_id", rex_i18n::msg("error_file_not_unique"), 'pz_project_file'));

		$xform->setValueField("showvalue",array("name",rex_i18n::msg("project_file_name"),"","0"));
		$xform->setValueField("textarea",array("comment",rex_i18n::msg("project_file_comment"),"","0"));
		$xform->setValueField("hidden",array("project_id",$this->file->getProjectId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$this->file->setComment($xform->objparams["value_pool"]["sql"]["comment"]);
			
			$parent_id = $xform->objparams["value_pool"]["sql"]["parent_id"];
			if($this->file->getParentId() != $parent_id)
			{
				$node = pz_project_node::get($parent_id);
				if($parent_id == 0)
					$node = new pz_project_root_directory($project);
				
				if($node)
					$this->file->moveTo( $node );
				
			}

			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_file_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => rex_request('parent_id','int')))
				);
			
		}else
		{
			$return = $header.$return;	
		}

		$p["linkvars"]["mode"] = "add_file";
		$p["linkvars"]["parent_id"] = $parent_id;

		$return .= pz_project_file_screen::getAddFileLink($p);
		$return .= $this->getDeleteFileLink($p);

		$return = '<div id="project_file_form"><div id="project_file_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}








	// ----------------------------- Folder Form Views

	public function getDeleteFolderForm($project, $p = array())
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("project_folder_delete").'</h1>
	          </div>
	        </header>';
		$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_folder_deleted", htmlspecialchars($this->file->getName())).'</p>';
		$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => $this->file->getParentId()))
				);

		$p["linkvars"]["mode"] = "add_folder";
		$add_link = pz::url('screen','project','files',$p["linkvars"]);
		$return .= '<div class="xform">
			<p><a class="bt1" onclick="pz_loadPage(\'project_folder_form\',\''.$add_link.'\')" href="javascript:void(0);">+ '.rex_i18n::msg("project_folder_add").'</a></p>
			</div>';

		$return = '<div id="project_folder_form"><div id="project_folder_delete" class="design1col xform-delete">'.$return.'</div></div>';
		return $return;
	}

	static function getAddFolderForm($project, $p = array()) 
	{

		$p["linkvars"]["mode"] = "add_folder";
	
		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("project_folder_add").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_folder_add','project_folder_add_form','".pz::url('screen','project','files',$p["linkvars"])."')");
		$xform->setObjectparams("form_id", "project_folder_add_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$node = new pz_project_root_directory($project);
		$paths = $node->getAllPaths();
		$xform->setValueField("select",	array("parent_id",rex_i18n::msg("project_file_path"),$paths,"","/",0,1));
		$xform->setValidateField("pz_project_folder",array("parent_id",0, $project->getId()));
		$xform->setValidateField("unique",array("parent_id,name,project_id", rex_i18n::msg("error_folder_not_unique"), 'pz_project_file'));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("project_folder_name"),"","0"));
		$xform->setValidateField("empty",array("name", rex_i18n::msg("error_folder_name_empty")));
		$xform->setValidateField("preg_match",array("name", '/^([a-z0-9_][\\.\\-\\ ]?)*$/i', rex_i18n::msg("error_folder_name_chars")));
		// $xform->setValueField("textarea",array("comment",rex_i18n::msg("project_folder_comment"),"","0"));
		
		$xform->setValueField("hidden",array("project_id",$project->getId()));

		$return .= $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {

			$parent_id = $xform->objparams["value_pool"]["sql"]["parent_id"];
			$name = $xform->objparams["value_pool"]["sql"]["name"];
			
			$node = pz_project_node::get($parent_id);
			if($parent_id == 0)
				$node = new pz_project_root_directory($project);

			if($node->isDirectory())
				$node->createDirectory($name);

			$return .= '<p class="xform-info">'.rex_i18n::msg("project_folder_added").'</p>';
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => rex_request('parent_id','int')))
				);
				
			$p["linkvars"]["mode"] = "add_folder";
			$add_link = pz::url('screen','project','files',$p["linkvars"]);
			$return .= '<div class="xform">
				<p><a class="bt1" onclick="pz_loadPage(\'project_folder_form\',\''.$add_link.'\')" href="javascript:void(0);">+ '.rex_i18n::msg("project_folder_add").'</a></p>
				</div>';
		}

		$return = '<div id="project_folder_form"><div id="project_folder_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}

	public function getEditFolderForm($project, $p = array()) 
	{
		
		$p["linkvars"]["mode"] = "edit_folder";
	
    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("project_folder_edit").': '.$this->file->getName().'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("main_table",'pz_project_file');
		$xform->setObjectparams("main_id",$this->file->getId());
		$xform->setObjectparams("main_where",'id='.$this->file->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_folder_edit','project_folder_edit_form','".pz::url('screen','project','files',$p["linkvars"])."')");
		$xform->setObjectparams("form_id", "project_folder_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);
		$xform->setHiddenField("file_id",$this->file->getId());
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));

		$node = new pz_project_root_directory($project);
		$paths = $node->getAllPaths();
		$xform->setValueField("select",	array("parent_id",rex_i18n::msg("project_file_path"),$paths,"","/",0,1));
		$xform->setValidateField("pz_project_folder",array("parent_id", $this->file->getId(), $project->getId()));
		$xform->setValidateField("unique",array("parent_id,name,project_id", rex_i18n::msg("error_folder_not_unique"), 'pz_project_file'));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("project_folder_name"),"","0"));
		$xform->setValidateField("empty",array("name", rex_i18n::msg("error_folder_name_empty")));
		$xform->setValidateField("preg_match",array("name", '/^([a-z0-9_][\\.\\-\\ ]?)*$/i', rex_i18n::msg("error_folder_name_chars")));
		
		$xform->setValueField("hidden",array("project_id",$this->file->getProjectId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
			
			$parent_id = $xform->objparams["value_pool"]["sql"]["parent_id"];
			$name = $xform->objparams["value_pool"]["sql"]["name"];
			
			$node = pz_project_node::get($parent_id);
			if($parent_id == 0)
			{
				$node = new pz_project_root_directory($project);
			}

			if($node->isDirectory()) {
				$this->file->moveTo($node, $name);
			}
			
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_folder_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('project_files_list','project_files_search_form',
				pz::url('screen','project','files',array("mode"=>'list', 'project_id' => $project->getId(), 'file_id' => rex_request('parent_id','int')))
				);
		}else {
			$return = $header.$return;	
		}

		if(count($this->file->getChildren()) == 0)
		{
			$p["linkvars"]["mode"] = "delete_folder";
			$p["linkvars"]["file_id"] = $this->file->getId();
	
			$delete_link = pz::url('screen','project','files',$p["linkvars"]);
			$return .= '<div class="xform">
					<p><a class="bt17" onclick="check = confirm(\''.rex_i18n::msg("project_folder_confirm_delete",htmlspecialchars($this->file->getName())).'\'); if (check == true) pz_loadPage(\'project_folder_form\',\''.$delete_link.'\')" href="javascript:void(0);">- '.rex_i18n::msg("project_folder_delete").'</a></p>
					</div>';
		}

		$p["linkvars"]["mode"] = "add_folder";
		$add_link = pz::url('screen','project','files',$p["linkvars"]);
		$return .= '<div class="xform">
				<p><a class="bt1" onclick="pz_loadPage(\'project_folder_form\',\''.$add_link.'\')" href="javascript:void(0);">+ '.rex_i18n::msg("project_folder_add").'</a></p>
				</div>';

		$return = '<div id="project_folder_form"><div id="project_folder_edit" class="design1col xform-edit">'.$return.'</div></div>';


		return $return;	
		
	}
}