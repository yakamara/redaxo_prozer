<?php

class pz_project_screen
{
    /**
     * @var pz_project $project
     */
    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

    // ---------------------------------------------------------------- PROJECTS VIEWS

    public static function getProjectsClipboardListView($p = [])
    {
        $return = '';
        foreach (pz::getUser()->getMyProjects() as $project) {
            $link = pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['project_id' => $project->getId()]));
            $link = "javascript:pz_loadPage('".$p['layer_list']."','".$link."')";

            $project_name = $project->getName();

            $return .= '
    	  <tr class="project-folder">
          <tbody>
            <tr class="folder">
              <td class="foldername" colspan="3">
                <a class="clearfix" href="'.$link.'">
                  <figure class="folder25"></figure>
                  <h3 class="hl7"><span class="title">'.htmlspecialchars($project_name).'</span></h3>
                </a>
              </td>
            </tr>
          </tbody>
        </tr>
        ';
        }
        $return = '<table class="clips tbl1">'.$return.'</table>';
        return '<div id="'.$p['layer_list'].'">'.$return.'</div>';
    }

    public static function getProjectsSearchForm($p = [], $ignore_fields = [])
    {
        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('search_for_projects').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        $xform->setObjectparams('real_field_names', true);
        $xform->setObjectparams('form_showformafterupdate', true);

        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('projects_list','project_search_form','".pz::url('screen', 'projects', $p['function'], ['mode' => 'list'])."')");
        $xform->setObjectparams('form_id', 'project_search_form');
        $xform->setObjectparams('form_name', 'project_search_form');

        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl', 'runtime']);
        $xform->setValueField('text', ['search_name', pz_i18n::msg('project_name')]);
        $xform->setValueField('pz_select_screen', ['search_label', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('pz_select_screen', ['search_customer', pz_i18n::msg('customer'), pz_customers::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('pz_select_screen', ['search_projectuser', pz_i18n::msg('project_admins_short'), pz::getActiveAdminUsersAsString(), '', '', 0, pz_i18n::msg('please_choose')]);

        if (!in_array('myprojects', $ignore_fields)) {
            $xform->setValueField('checkbox', ['search_myprojects', pz_i18n::msg('myprojects')]);
        }

        // $xform->setValueField('pz_date_screen',array('search_datetime', pz_i18n::msg('createdate')));
        $xform->setValueField('submit', ['submit', pz_i18n::msg('search'), '', 'search']);
        $return .= $xform->getForm();

        $return = '<div id="project_search" class="design1col xform-search">'.$return.'</div>';
        return $return;
    }

    // ---------------------------------------------------------------- PROJECT VIEWS

    public function getLabelView()
    {
        return '<span class="label-color-block '.pz_label_screen::getColorClass($this->project->getLabelId()).'"></span>'.htmlspecialchars($this->project->getName()); // pz::cutText(,35)
    }

    public function getClipboardLabelView()
    {
        return '<h2 class="hl2"><span class="label-color-block '.pz_label_screen::getColorClass($this->project->getLabelId()).'"></span>'.htmlspecialchars($this->project->getName()).'</h2>'; // pz::cutText(,35)
    }

    public function getBlockView($p = [])
    {
        $customer_name = pz_i18n::msg('no_customer');
        if ($this->project->hasCustomer()) {
            $customer_name = $this->project->customer->getName();
        }

        $return = '
		      <article class="project block image">
            <header>
              <figure><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></figure>
              <hgroup class="data">
                <h2 class="hl7 piped">
                  <span class="name">'.$customer_name.'</span>
                  <span class="info">'.$this->project->getVar('created', 'datetime').'</span>
                </h2>
                <h3 class="hl7"><a href="'.pz::url('screen', 'project', 'view', ['project_id' => $this->project->getId()]).'"><span class="title">'.$this->project->getVar('name').'</span></a></h3>
              </hgroup>
            </header>

            <section class="content">
			<!-- TODO: Streaminfo ? -->
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
              <span class="label '.pz_label_screen::getColorClass($this->project->getVar('label_id')).'">Label</span>
            </footer>
          </article>
        ';

        return $return;
    }

    public function getTableView($p = [])
    {
        $customer_name = pz_i18n::msg('no_customer');
        if ($this->project->hasCustomer()) {
            $customer_name = $this->project->customer->getName();
        }
        $admins = $this->project->getAdmins();
        $admin_text = [];
        foreach ($admins as $admin) {
            $admin_text[] = $admin->getName();
        }

        $return = '
              <tr>
                <td class="image img1"><img src="'.$this->project->getInlineImage().'" width="40" height="40" alt="" /></td>
                <td class="customer"><span class="name">'.$customer_name.'</span></td>
                <td class="name"><a href="'.pz::url('screen', 'project', 'view', ['project_id' => $this->project->getId()]).'"><span class="title">'.$this->project->getVar('name').'</span></a></td>
                <td class="date"><span class="info">'.$this->project->getVar('created', 'datetime').'</span></td>
                <td class="admin"><span class="info">'.implode('<br />', $admin_text).'</span></td>
                <td class="label '.pz_label_screen::getColorClass($this->project->getVar('label_id')).'"></td>
              </tr>
        ';

        return $return;
    }

    public static function getAddForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('add_project').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        // $xform->setDebug(TRUE);

        $xform->setObjectparams('main_table', 'pz_project');
        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_add','project_add_form','".pz::url('screen', 'projects', 'all', ['mode' => 'add_form'])."')");

        $xform->setObjectparams('form_id', 'project_add_form');

        $filter = [];
        $filter[] = ['field' => 'archived', 'value' => 0, 'type' => '='];
        $customer_string = pz::getUser()->getCustomersAsString($filter);

        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);
        $xform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0']);
        $xform->setValidateField('empty', ['name', pz_i18n::msg('error_project_enter_name')]);
        $xform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0']);
        $xform->setValueField('pz_select_screen', ['label_id', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', 0]);
        $xform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $xform->setValueField('pz_select_screen', ['customer_id', pz_i18n::msg('project_customer'), $customer_string, '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);
        $xform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
        $xform->setValueField('hidden', ['archived', 0]);
        $xform->setValueField('checkbox', ['has_emails', pz_i18n::msg('emails'), '', '1']);
        $xform->setValueField('checkbox', ['has_calendar', pz_i18n::msg('calendar_events'), '', '1']);
        $xform->setValueField('checkbox', ['has_calendar_jobs', pz_i18n::msg('calendar_jobs'), '', '1']);
        // $xform->setValueField("checkbox",array("has_wiki",pz_i18n::msg("wiki"),"","1"));
        $xform->setValueField('checkbox', ['has_files', pz_i18n::msg('files'), '', '1']);

        $xform->setActionField('db', []);
        $return = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $project_id = $xform->getObjectparams('main_id');
            if ($project = pz_project::get($project_id)) {
                $project->create();
            }
            $return = $header.'<p class="xform-info">'.pz_i18n::msg('project_added').'</p>'.$return;
        } else {
            $return = $header.$return;
        }
        $return = '<div id="project_add" class="design1col xform-add">'.$return.'</div>';

        return $return;
    }

    public function getEditForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('edit_project').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        // $xform->setDebug(TRUE);

        $xform->setObjectparams('real_field_names', true);
        $xform->setObjectparams('main_table', 'pz_project');
        $xform->setObjectparams('main_id', $this->project->getId());
        $xform->setObjectparams('main_where', 'id='.$this->project->getId());
        $xform->setObjectparams('getdata', true);
        $xform->setHiddenField('project_id', $this->project->getId());

        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_edit','project_edit_form','".pz::url('screen', 'project', 'view', ['mode' => 'edit_form'])."')");
        $xform->setObjectparams('form_id', 'project_edit_form');
        $xform->setObjectparams('form_showformafterupdate', 1);

        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);
        $xform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0', '', '', '', '', '']);
        $xform->setValidateField('empty', ['name', pz_i18n::msg('error_project_enter_name')]);
        $xform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0', '', '', '', '', '']);
        $xform->setValueField('pz_select_screen', ['label_id', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', '0']);
        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $xform->setValueField('pz_select_screen', ['customer_id', pz_i18n::msg('project_customer'), pz_customers::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
        $xform->setValueField('checkbox', ['has_emails', pz_i18n::msg('emails'), '', '1']);
        $xform->setValueField('checkbox', ['has_calendar', pz_i18n::msg('calendar_events'), '', '1']);
        $xform->setValueField('checkbox', ['has_calendar_jobs', pz_i18n::msg('calendar_jobs'), '', '1']);
        // $xform->setValueField("checkbox",array("has_wiki",pz_i18n::msg("wiki"),"","1"));
        $xform->setValueField('checkbox', ['has_files', pz_i18n::msg('files'), '', '1']);
        $xform->setValueField('checkbox', ['archived', pz_i18n::msg('archived'), '', '1']);

        $xform->setActionField('db', ['pz_project', 'id='.$this->project->getId()]);

        $return = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $this->project->update();
            $return = $header.'<p class="xform-info">'.pz_i18n::msg('project_updated').'</p>'.$return;
        } else {
            $return = $header.$return;
        }

        $return = '<div id="project_edit" class="design1col xform-edit">'.$return.'</div>';

        return $return;
    }

    public function getViewForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('view_project').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        $xform->setObjectparams('main_table', 'pz_project');
        $xform->setObjectparams('main_id', $this->project->getId());
        $xform->setObjectparams('main_where', 'id='.$this->project->getId());
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('form_id', 'project_view_form');
        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);
        $xform->setValueField('objparams', ['submit_btn_show', false]);

        $xform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0', 'disabled' => true]);
        $xform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0', 'disabled' => true]);

        /*
                // $xform->setValueField("pz_select_screen",array("label_id",pz_i18n::msg("project_label"),pz_labels::getAsString(), '', '', '0','disabled'=>TRUE));
                // $xform->setValueField("pz_select_screen",array("customer_id",pz_i18n::msg("project_customer"),pz_customers::getAsString(),"","",1,pz_i18n::msg("please_choose"),'disabled'=>TRUE));


                $xform->setValueField("checkbox",array("has_calendar",pz_i18n::msg("calendar"),"","1"));
                $xform->setValueField("checkbox",array("has_wiki",pz_i18n::msg("wiki"),"","1"));
                $xform->setValueField("checkbox",array("has_files",pz_i18n::msg("files"),"","1"));
                $xform->setValueField("checkbox",array("has_emails",pz_i18n::msg("emails"),"","1"));
                $xform->setValueField("checkbox",array("archived",pz_i18n::msg("archived"),"","1"));
        */

        $return = $header.$xform->getForm();

        $return = '<div id="project_view" class="design1col xform-view">'.$return.'</div>';

        return $return;
    }

    public function getMetaInfoView ()
    {
        $filter = [];
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('project_action_info').'</h1>
	          </div>
	        </header>';

        // Filter wird nicht benoetig, es wird einfach jede Aktion in der History beruecksichtigt.
        /* $filter = [ ['type' => '=', 'field' => 'control', 'value' => 'email'] ]; */

        $entries = $this->project->getHistoryEntries($filter, 1);
        $entity = $entries[0]->vars;
        $style = 'style="width: 35%; display: inline-block;"';
        $content = '
            <div class="xform">
                <span '.$style.'>'.pz_i18n::msg('project_name').' : </span><b>'.$this->project->getVar('name').'</b>
                <br><br>
                <ul>
                    <li><span '.$style.'>'.ucfirst(pz_i18n::msg('created_by_user')).' : </span><b>'.pz_user::get($this->project->getVar('create_user_id'))->getName().'</b></li>
                    <li><span '.$style.'>'.pz_i18n::msg('project_createdate').' : </span><b>'.$this->project->getVar('created').'</b></li>
                    <li><hr><li>
                    <li><span '.$style.'>'.pz_i18n::msg('project_last_user_action').' : </span><b>'.pz_user::get($entity['user_id'])->getName().'</b></li>
                    <li><span '.$style.'>'.pz_i18n::msg('project_last_update').' : </span><b>'.$entity['stamp'].'</b></li>
                </ul>
        	</div>';


        return $header.$content;
    }
}
