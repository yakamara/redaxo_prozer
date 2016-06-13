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

        $yform = new rex_yform();
        $yform->setObjectparams('real_field_names', true);
        $yform->setObjectparams('form_showformafterupdate', true);

        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('projects_list','project_search_form','".pz::url('screen', 'projects', $p['function'], ['mode' => 'list'])."')");
        $yform->setObjectparams('form_id', 'project_search_form');
        $yform->setObjectparams('form_name', 'project_search_form');

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl', 'runtime']);
        $yform->setValueField('text', ['search_name', pz_i18n::msg('project_name')]);
        $yform->setValueField('select', ['search_label', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $yform->setValueField('select', ['search_customer', pz_i18n::msg('customer'), pz_customers::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $yform->setValueField('select', ['search_projectuser', pz_i18n::msg('project_admins_short'), pz::getActiveAdminUsersAsString(), '', '', 0, pz_i18n::msg('please_choose')]);

        if (!in_array('myprojects', $ignore_fields)) {
            $yform->setValueField('checkbox', ['search_myprojects', pz_i18n::msg('myprojects')]);
        }

        // $yform->setValueField('pz_date_screen',array('search_datetime', pz_i18n::msg('createdate')));
        $yform->setValueField('submit', ['submit', pz_i18n::msg('search'), '', 'search']);
        $return .= $yform->getForm();

        $return = '<div id="project_search" class="design1col yform-search">'.$return.'</div>';
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

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('main_table', 'pz_project');
        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_add','project_add_form','".pz::url('screen', 'projects', 'all', ['mode' => 'add_form'])."')");

        $yform->setObjectparams('form_id', 'project_add_form');

        $filter = [];
        $filter[] = ['field' => 'archived', 'value' => 0, 'type' => '='];
        $customer_string = pz::getUser()->getCustomersAsString($filter);

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        $yform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0']);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_project_enter_name')]);
        $yform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0']);
        $yform->setValueField('select', ['label_id', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', 0]);
        $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $yform->setValueField('select', ['customer_id', pz_i18n::msg('project_customer'), $customer_string, '', '', 0, pz_i18n::msg('please_choose')]);
        $yform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);
        $yform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
        $yform->setValueField('hidden', ['archived', 0]);
        $yform->setValueField('checkbox', ['has_emails', pz_i18n::msg('emails'), '', '1']);
        $yform->setValueField('checkbox', ['has_calendar', pz_i18n::msg('calendar_events'), '', '1']);
        $yform->setValueField('checkbox', ['has_calendar_jobs', pz_i18n::msg('calendar_jobs'), '', '1']);
        $yform->setValueField("checkbox", ['has_wiki', pz_i18n::msg('wiki'),'','1']);
        $yform->setValueField('checkbox', ['has_files', pz_i18n::msg('files'), '', '1']);

        $yform->setActionField('db', []);
        $return = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $project_id = $yform->getObjectparams('main_id');
            if ($project = pz_project::get($project_id)) {
                $project->create();
            }
            $return = $header.'<p class="yform-info">'.pz_i18n::msg('project_added').'</p>'.$return;
            //$return .= pz_screen::getJSUpdateLayer('projects_list', pz::url('projects', $p['controll'], $p['function'], ['mode' => 'list']));
            $return .= pz_screen::getJSUpdatePage(pz::url('screen', 'project', 'user', ['project_id' => $project_id]));
        } else {
            $return = $header.$return;
        }
        $return = '<div id="project_add" class="design1col yform-add">'.$return.'</div>';

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

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('real_field_names', true);
        $yform->setObjectparams('main_table', 'pz_project');
        $yform->setObjectparams('main_id', $this->project->getId());
        $yform->setObjectparams('main_where', 'id='.$this->project->getId());
        $yform->setObjectparams('getdata', true);
        $yform->setHiddenField('project_id', $this->project->getId());

        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_edit','project_edit_form','".pz::url('screen', 'project', 'view', ['mode' => 'edit_form'])."')");
        $yform->setObjectparams('form_id', 'project_edit_form');
        $yform->setObjectparams('form_showformafterupdate', 1);

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        $yform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0', '', '', '', '', '']);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_project_enter_name')]);
        $yform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0', '', '', '', '', '']);
        $yform->setValueField('select', ['label_id', pz_i18n::msg('project_label'), pz_labels::getAsString(), '', '', '0']);
        $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $yform->setValueField('select', ['customer_id', pz_i18n::msg('project_customer'), pz_customers::getAsString(), '', '', 0, pz_i18n::msg('please_choose')]);
        $yform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
        $yform->setValueField('checkbox', ['has_emails', pz_i18n::msg('emails'), '', '1']);
        $yform->setValueField('checkbox', ['has_calendar', pz_i18n::msg('calendar_events'), '', '1']);
        $yform->setValueField('checkbox', ['has_calendar_jobs', pz_i18n::msg('calendar_jobs'), '', '1']);
        $yform->setValueField("checkbox", ['has_wiki',pz_i18n::msg('wiki'),'','1']);
        $yform->setValueField('checkbox', ['has_files', pz_i18n::msg('files'), '', '1']);
        $yform->setValueField('checkbox', ['archived', pz_i18n::msg('archived'), '', '1']);

        $yform->setActionField('db', ['pz_project', 'id='.$this->project->getId()]);

        $return = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $this->project->update();
            $return  = $header.'<p class="yform-info">'.pz_i18n::msg('project_updated').'</p>'.$return;
        } else {
            $return = $header.$return;
        }

        $return = '<div id="project_edit" class="design1col yform-edit">'.$return.'</div>';

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

        $yform = new rex_yform();
        $yform->setObjectparams('main_table', 'pz_project');
        $yform->setObjectparams('main_id', $this->project->getId());
        $yform->setObjectparams('main_where', 'id='.$this->project->getId());
        $yform->setObjectparams('getdata', true);
        $yform->setObjectparams('form_id', 'project_view_form');
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        $yform->setValueField('objparams', ['submit_btn_show', false]);

        $yform->setValueField('text', ['name', pz_i18n::msg('project_name'), '', '0', 'disabled' => true]);
        $yform->setValueField('textarea', ['description', pz_i18n::msg('project_description'), '', '0', 'disabled' => true]);

        /*
                // $yform->setValueField("select",array("label_id",pz_i18n::msg("project_label"),pz_labels::getAsString(), '', '', '0','disabled'=>TRUE));
                // $yform->setValueField("select",array("customer_id",pz_i18n::msg("project_customer"),pz_customers::getAsString(),"","",1,pz_i18n::msg("please_choose"),'disabled'=>TRUE));


                $yform->setValueField("checkbox",array("has_calendar",pz_i18n::msg("calendar"),"","1"));
                $yform->setValueField("checkbox",array("has_wiki",pz_i18n::msg("wiki"),"","1"));
                $yform->setValueField("checkbox",array("has_files",pz_i18n::msg("files"),"","1"));
                $yform->setValueField("checkbox",array("has_emails",pz_i18n::msg("emails"),"","1"));
                $yform->setValueField("checkbox",array("archived",pz_i18n::msg("archived"),"","1"));
        */

        $return = $header.$yform->getForm();

        $return = '<div id="project_view" class="design1col yform-view">'.$return.'</div>';

        return $return;
    }

    public function getMetaInfoView ()
    {

        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('project_action_info').'</h1>
	          </div>
	        </header>';

        $li = [];
        $style = '';
        $li[] = '<li><span '.$style.'>'.pz_i18n::msg('project_name').' : </span><b>'.pz::cutText($this->project->getVar('name'),40, '...', 'center').'</b></li>';
        $li[] = '<li><span '.$style.'>'.ucfirst(pz_i18n::msg('created_by_user')).' : </span><b>'.pz_user::get($this->project->getVar('create_user_id'))->getName().'</b></li>';
        $li[] = '<li><span '.$style.'>'.pz_i18n::msg('project_createdate').' : </span><b>'.$this->project->getVar('created').'</b></li>';

        $filter = [];
        // Filter wird nicht benoetig, es wird einfach jede Aktion in der History beruecksichtigt.
        /* $filter = [ ['type' => '=', 'field' => 'control', 'value' => 'email'] ]; */
        $entries = $this->project->getHistoryEntries($filter, 1);
        if (count($entries) > 0) {
            $entity = $entries[0]->vars;
            $li[] = ' <li><hr><li>';
            $li[] = '<li><span '.$style.'>'.pz_i18n::msg('project_last_user_action').' : </span><b>'.pz_user::get($entity['user_id'])->getName().'</b></li>';
            $li[] = '<li><span '.$style.'>'.pz_i18n::msg('project_last_update').' : </span><b>'.$entity['stamp'].'</b></li>';
        }

        $style = 'style="width: 35%; display: inline-block;"';
        $content = '
            <div class="yform">
                <ul>
                    '.implode("",$li).'
                </ul>
        	</div>';


        return $header.$content;
    }
}
