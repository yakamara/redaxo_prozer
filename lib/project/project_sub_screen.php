<?php

class pz_project_sub_screen
{
    public $project_sub;

    public function __construct($project_sub)
    {
        $this->project_sub = $project_sub;
    }

    // --------------------------------------------------------------- Static returns


    // --------------------------------------------------------------- Listviews

    public static function getListView($p = [], $project_subs)
    {
        $content = '';
        $design = '';

        $lis = [];
        foreach ($project_subs as $project_sub) {
            $edit_link = "javascript:pz_loadPage('project_sub_form','".pz::url('screen', 'project', 'project_sub', array_merge($p['linkvars'], ['mode' => 'edit_project_sub', 'project_sub_id' => $project_sub->getId()]))."')";

            $lis[] = '<li class="lev1 entry">
  		   <article>
              <header>
                <a class="detail" href="'.$edit_link.'">
                  <hgroup>
                    <h3 class="hl7"><span class="title">'.$project_sub->getVar('name').'</span></h3>
                  </hgroup>
                  <!-- <span class="label labelc'.$project_sub->getVar('id').'">Label</span> -->
                </a>
              </header>
              <footer>
  	            <!-- <a class="bt2" href="'.$edit_link.'">'.pz_i18n::msg('label_edit').'</a> -->
              </footer>
            </article>
            </li>';

            // <a class="bt2" href="'.pz::url("screen","projects","tools",array("mode"=>"delete","label_id"=>$this->label->getId())).'">'.pz_i18n::msg("label_delete").'</a>
        }

        $content = '<ul class="entries view-list">'.implode('', $lis).'</ul>';

        // $content = $this->getSearchPaginatePlainView().$content;
        // $content = '<a href="'.pz::url('screen','tools','labels',array("mode"=>"add")).'">'.pz_i18n::msg("label_add").'</a>'.$content;

        $f = new pz_fragment();
        $f->setVar('design', $design, false);
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);
        $f->setVar('paginate', '', false);

        return '<div id="project_subs_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    public function getDeleteForm($p = [], $project)
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('delete_project_sub').'</h1>
	          </div>
	        </header>';

        $return = $header.'<p class="yform-info">'.pz_i18n::msg('project_sub_deleted', htmlspecialchars($p['project_sub_name'])).'</p>';
        $return .= pz_screen::getJSLoadFormPage('project_subs_list', 'labels_search_form', pz::url('screen', 'project', $p['function'], ['project_id' => $project->getId(), 'mode' => 'list']));
        $return = '<div id="project_sub_form"><div id="project_sub_delete" class="design1col yform-delete">'.$return.'</div></div>';

        return $return;
    }

    public function getEditForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('project_sub_edit').': '.$this->project_sub->getName().'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('main_table', 'pz_project_sub');
        $yform->setObjectparams('main_id', $this->project_sub->getId());
        $yform->setObjectparams('main_where', 'id='.$this->project_sub->getId()); // array("id"=>$this->label->getId())
        $yform->setObjectparams('getdata', true);
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);

        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_sub_form','project_sub_edit_form','".pz::url('screen', 'project', 'project_sub', ['mode' => 'edit_project_sub'])."')");
        $yform->setObjectparams('form_id', 'project_sub_edit_form');
        $yform->setHiddenField('project_sub_id', $this->project_sub->getId());
        $yform->setHiddenField('project_id', $this->project_sub->getProject()->getId());
        $yform->setObjectparams('form_showformafterupdate', 1);

        $yform->setValueField('text', ['name', pz_i18n::msg('project_sub_name')]);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_project_sub_name_empty')]);

        $yform->setActionField('db', ['pz_project_sub', 'id='.$this->project_sub->getId()]); // array("id"=>$this->label->getId())

        $return = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $return = $header.'<p class="yform-info">'.pz_i18n::msg('project_sub_updated').'</p>'.$return;
            $return .= pz_screen::getJSUpdateLayer('labels_list', pz::url('screen', 'project', 'project_sub', ['mode' => 'list']));
        } else {
            $return = $header.$return;
        }

        if ($p['show_delete']) {
            $delete_link = pz::url('screen', 'project', 'project_sub', ['project_id' => $this->project_sub->getProject()->getid(), 'project_sub_id' => $this->project_sub->getId(), 'mode' => 'delete_project_sub']);
            $return .= '<div class="yform">
				<p><a class="bt17" onclick="check = confirm(\''.
                pz_i18n::msg('project_sub_confirm_delete', htmlspecialchars($this->project_sub->getName())).
                '\'); if (check == true) pz_loadPage(\'project_sub_form\',\''.
                $delete_link.'\')" href="javascript:void(0);">- '.pz_i18n::msg('delete_project_sub').'</a></p>
				</div>';
        }

        $return = '<div id="project_sub_form"><div id="project_sub_edit" class="design1col yform-edit">'.$return.'</div></div>';

        return $return;
    }

    public static function getAddForm($p = [], $project)
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('add_project_sub').'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('main_table', 'pz_project_sub');
        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_sub_form','project_sub_add_form','".pz::url('screen', 'project', 'project_sub', ['mode' => 'add_project_sub', 'project_id' => $project->getId()])."')");
        $yform->setObjectparams('form_id', 'project_sub_add_form');

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);

        $yform->setValueField('hidden', ['project_id', $project->getId()]);

        $yform->setValueField('text', ['name', pz_i18n::msg('project_sub_name')]);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_project_sub_name_empty')]);
        $yform->setActionField('db', []); // array("id"=>$label_id)
        $return = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $return = $header.'<p class="yform-info">'.pz_i18n::msg('project_sub_added').'</p>'.$return;
            $return .= pz_screen::getJSUpdateLayer('project_subs_list', pz::url('screen', 'project', 'project_sub', ['mode' => 'list', 'project_id' => $project->getId()]));
        } else {
            $return = $header.$return;
        }

        $return = '<div id="project_sub_form"><div id="project_sub_add" class="design1col yform-add">'.$return.'</div></div>';

        return $return;
    }
}
