<?php

class pz_project_sub_screen{

	public $project_sub;

	function __construct($project_sub) 
	{
		$this->project_sub = $project_sub;
	}


	// --------------------------------------------------------------- Static returns


	// --------------------------------------------------------------- Listviews

	static function getListView($p = array(), $project_subs)
	{
    $content = "";
		$design = "";
		
		$lis = array();
    foreach($project_subs as $project_sub)
    {
    
      $edit_link = "javascript:pz_loadPage('project_sub_form','".pz::url("screen","project","project_sub",array_merge($p["linkvars"],array("mode"=>"edit_project_sub","project_sub_id"=>$project_sub->getId())))."')";
  
  		$lis[] = '<li class="lev1 entry">
  		   <article>
              <header>
                <a class="detail" href="'.$edit_link.'">
                  <hgroup>
                    <h3 class="hl7"><span class="title">'.$project_sub->getVar("name").'</span></h3>
                  </hgroup>
                  <!-- <span class="label labelc'.$project_sub->getVar('id').'">Label</span> -->
                </a>
              </header>
              <footer>
  	            <!-- <a class="bt2" href="'.$edit_link.'">'.rex_i18n::msg("label_edit").'</a> -->
              </footer>
            </article>
            </li>';
	
		  // <a class="bt2" href="'.pz::url("screen","projects","tools",array("mode"=>"delete","label_id"=>$this->label->getId())).'">'.rex_i18n::msg("label_delete").'</a>
	
	  }
	
		$content = '<ul class="entries view-list">'.implode("",$lis).'</ul>';

		// $content = $this->getSearchPaginatePlainView().$content;
		// $content = '<a href="'.pz::url('screen','tools','labels',array("mode"=>"add")).'">'.rex_i18n::msg("label_add").'</a>'.$content;

		$f = new rex_fragment();
		$f->setVar('design', $design, false);
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', '', false);
	
		return '<div id="project_subs_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';	

	}


	function getDeleteForm($p = array(), $project)
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("delete_project_sub").'</h1>
	          </div>
	        </header>';
		
		$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_sub_deleted", htmlspecialchars($p["project_sub_name"])).'</p>';
		$return .= pz_screen::getJSLoadFormPage('project_subs_list','labels_search_form',pz::url('screen','project',$p["function"],array("project_id"=>$project->getId(),"mode"=>'list')));
		$return = '<div id="project_sub_form"><div id="project_sub_delete" class="design1col xform-delete">'.$return.'</div></div>';

		return $return;
	}


	public function getEditForm($p = array()) 
	{
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("project_sub_edit").': '.$this->project_sub->getName().'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_project_sub');
		$xform->setObjectparams("main_id",$this->project_sub->getId());
		$xform->setObjectparams("main_where",'id='.$this->project_sub->getId()); // array("id"=>$this->label->getId())
		$xform->setObjectparams('getdata',true);
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_sub_form','project_sub_edit_form','".pz::url('screen','project','project_sub',array("mode"=>'edit_project_sub'))."')");
		$xform->setObjectparams("form_id", "project_sub_edit_form");
		$xform->setHiddenField("project_sub_id",$this->project_sub->getId());
		$xform->setHiddenField("project_id",$this->project_sub->getProject()->getId());
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField("text",array("name",rex_i18n::msg("project_sub_name")));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_project_sub_name_empty")));

		$xform->setActionField("db",array('pz_project_sub','id='.$this->project_sub->getId())); // array("id"=>$this->label->getId())

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_sub_updated").'</p>'.$return;
			$return .= pz_screen::getJSUpdateLayer('labels_list',pz::url('screen','project','project_sub',array("mode"=>'list')));

		}else
		{
			$return = $header.$return;
		}

		if($p["show_delete"])
		{
			$delete_link = pz::url("screen","project","project_sub",array("project_id" => $this->project_sub->getProject()->getid(), "project_sub_id"=>$this->project_sub->getId(),"mode"=>"delete_project_sub"));
			$return .= '<div class="xform">
				<p><a class="bt17" onclick="check = confirm(\''.
				rex_i18n::msg("project_sub_confirm_delete",htmlspecialchars($this->project_sub->getName())).
				'\'); if (check == true) pz_loadPage(\'project_sub_form\',\''.
				$delete_link.'\')" href="javascript:void(0);">- '.rex_i18n::msg("delete_project_sub").'</a></p>
				</div>';
		}

		$return = '<div id="project_sub_form"><div id="project_sub_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}


	static function getAddForm($p = array(), $project) 
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("add_project_sub").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_project_sub');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('project_sub_form','project_sub_add_form','".pz::url('screen','project','project_sub',array("mode"=>'add_project_sub','project_id'=>$project->getId()))."')");
		$xform->setObjectparams("form_id", "project_sub_add_form");
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		
		$xform->setValueField('hidden',array('project_id', $project->getId()));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("project_sub_name")));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_project_sub_name_empty")));
		$xform->setActionField("db",array()); // array("id"=>$label_id)
		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("project_sub_added").'</p>'.$return;
			$return .= pz_screen::getJSUpdateLayer('project_subs_list',pz::url('screen','project','project_sub',array("mode"=>'list','project_id'=>$project->getId())));
		}else
		{
			$return = $header.$return;
		}

		$return = '<div id="project_sub_form"><div id="project_sub_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}






}


?>