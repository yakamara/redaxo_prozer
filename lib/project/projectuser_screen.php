<?php

class pz_projectuser_screen
{
    /**
     * @var pz_projectuser
     */
    public $projectuser = null;

    public function __construct($projectuser)
    {
        $this->projectuser = $projectuser;
    }

    // ------------------------------------------------------------------ user/s

    public static function getUserlist($p, $projectusers, $project, $my_projectuser)
    {
        $list = '';

        $paginate_screen = new pz_paginate_screen($projectusers);
        $paginate = $paginate_screen->getPlainView($p);

        foreach ($paginate_screen->getCurrentElements() as $projectuser) {
            $ps = new pz_projectuser_screen($projectuser);
            $list .= $ps->getTableView($p, $project, $my_projectuser);
        }

        $content = $paginate.'
          <table class="projectuserss tbl1">
          <thead><tr>
              <th></th>
              ';

        $content .= '<th>'.pz_i18n::msg('username').'</th>';
        if ($project->hasEmails() == 1) {
            $content .= '<th>'.pz_i18n::msg('emails').'</th>';
        }
        if ($project->hasCalendar() == 1) {
            $content .= '<th>'.pz_i18n::msg('calendar_events').'</th>';
        }
        if ($project->hasCalendarJobs() == 1) {
            $content .= '<th>'.pz_i18n::msg('calendar_jobs').'</th>';
        }
        if ($project->hasFiles() == 1) {
            $content .= '<th>'.pz_i18n::msg('files').'</th>';
        }
        $content .= '<th>'.pz_i18n::msg('project_admin').'</th>';
        if ($my_projectuser->isAdmin()) {
            $content .= '<th>'.pz_i18n::msg('functions').'</th>';
        }

        $content .= '
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>';

        if (isset($p['info'])) {
            $content = $p['info'].$content;
        }

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);
        return '<div id="projectusers_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    /**
     * @method getTableView
     *
     * @param array                   $p
     * @param Object | pz_project     $project
     * @param Object | pz_projectuser $projectuser
     *
     * @return string
     */
    public function getTableView($p = [], $project, $projectuser)
    {
        $row_id = 'project-userperm-'.$this->projectuser->getProject()->getId().'-'.$this->projectuser->getUser()->getId();

        $td = [];
        $td[] = '<td class="img1"><img src="'.$this->projectuser->getUser()->getInlineImage().'" width="40" height="40" alt="" /></td>';
        $td[] = '<td><span class="title">'.$this->projectuser->getUser()->getName().'</span></td>';

        if ($this->projectuser->getProject()->hasEmails()) {
            $status = 2;
            if ($this->projectuser->getProject()->hasEmails() == 1) {
                $status = $this->projectuser->hasEmails() ? $status = 1 : $status = 0;
            }
            $td[] = $this->getPermTableCellView('emails', $status, $projectuser);
        }

        if ($this->projectuser->getProject()->hasCalendarEvents()) {
            $status = 2;
            if ($this->projectuser->getProject()->hasCalendar() == 1) {
                $status = $this->projectuser->hasCalendarEvents() ? $status = 1 : $status = 0;
            }
            $td[] = $this->getPermTableCellView('calendar_events', $status, $projectuser);
        }

        if ($this->projectuser->getProject()->hasCalendarJobs()) {
            $status = 2;
            if ($this->projectuser->getProject()->hasCalendarJobs() == 1) {
                $status = $this->projectuser->hasCalendarJobs() ? $status = 1 : $status = 0;
            }
            $td[] = $this->getPermTableCellView('calendar_jobs', $status, $projectuser);
        }

        /*
        $status = 2;
          if ($this->projectuser->getProject()->hasCalendar() == 1) { $status = $this->projectuser->hasCalDAVEvents() ? $status = 1 : $status = 0; }
          $td[] = $this->getPermTableCellView("caldav_events", $status, $projectuser);

        $status = 2;
        if ($this->projectuser->getProject()->hasCalendar() == 1) { $status = $this->projectuser->hasCalDAVJobs() ? $status = 1 : $status = 0; }
        $td[] = $this->getPermTableCellView("caldav_jobs", $status, $projectuser);
        */

        if ($this->projectuser->getProject()->hasFiles()) {
            $status = 2;
            if ($this->projectuser->getProject()->hasFiles() == 1) {
                $status = $this->projectuser->hasFiles() ? $status = 1 : $status = 0;
            }
            $td[] = $this->getPermTableCellView('files', $status, $projectuser);
        }

        $status = $this->projectuser->isAdmin() ? $status = 1 : $status = 0;
        $td[] = $this->getPermTableCellView('admin', $status, $projectuser);

        if ($projectuser->isAdmin()) {
            $del_link = pz::url('screen', 'project', 'user', ['project_id' => $this->projectuser->getProject()->getId(), 'projectuser_id' => $this->projectuser->getVar('id'), 'mode' => 'delete']);

            if ($projectuser->getId() != $this->projectuser->getId()) {
                $td[] = '<td><a class="bt2" href="javascript:void(0);" onclick="pz_loadPage(\'projectusers_list\',\''.$del_link.'\')"><span class="title">'.pz_i18n::msg('delete').'</span></a></td>';
            } else {
                $td[] = '<td><span class="title"></span></td>';
            }
        }

        $return = '<tr id="'.$row_id.'">'.implode('', $td).'</tr>';

        return $return;
    }

    /**
     * @method getPermTableCellView
     *
     * @param string                  $type
     * @param int                     $status
     * @param object | pz_projectuser $projectuser
     *
     * @return string
     */
    public function getPermTableCellView($type = '', $status = 2, $projectuser = null)
    {
        $classes = [];
        $classes[] = 'projectperm-status';
        $classes[] = 'project-perm-'.$type;
        $classes[] = 'project-id-'.$this->projectuser->getProject()->getId();
        $classes[] = 'user-id-'.$this->projectuser->getUser()->getId();

        $td_id = 'project-userperm-'.$this->projectuser->getProject()->getId().'-'.$this->projectuser->getUser()->getId().'-'.$type;

        $link_a = pz::url('screen', 'project', 'userperm', ['project_id' => $this->projectuser->getProject()->getId(), 'user_id' => $this->projectuser->getUser()->getId(), 'mode' => 'toggle_'.$type]);
        $link = "pz_loadPage('#".$td_id."','".$link_a."')";

        if ($status == 2) {
            $classes[] = 'inactive';
            return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><span class="status status-2">'.pz_i18n::msg('not_available').'</span></td>';
        } else {
            if (
                ($type == 'admin' && isset($projectuser) && $projectuser->isAdmin() && $this->projectuser->getUser()->getId() != pz::getUser()->getId()) ||

                ($type != 'admin' && pz::getUser()->isAdmin()) ||

                ($status != 2 &&
                    pz::getUser()->getId() == $this->projectuser->getUser()->getId() &&
                    (($type == 'caldav_events' && $this->projectuser->hasCalendarEvents())  || ($type == 'caldav_jobs' && $this->projectuser->hasCalendarJobs()))
                )

            ) {
                $link_admin_a = pz::url('screen', 'project', 'user', ['project_id' => $this->projectuser->getProject()->getId(), 'user_id' => $this->projectuser->getUser()->getId(), 'mode' => 'list']);
                $link_admin = "pz_loadPage('#".$td_id."','".$link_a."', function(){ pz_loadPage('#projectusers_list', '".$link_admin_a."') })";

                if ($status == 1) {
                    return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><a href="javascript:void(0);" onclick="'.$link_admin.'" ><span class="status status-changeable status-'.$status.'">'.pz_i18n::msg('yes').'</span></a></td>';
                } else {
                    return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><a href="javascript:void(0);" onclick="'.$link_admin.'"><span class="status status-changeable status-'.$status.'">'.pz_i18n::msg('no').'</span></a></td>';
                }
            } else {
                if ((pz::getUser()->isAdmin() || $this->projectuser->isAdmin()) || $this->projectuser->getUser()->getId() == pz::getUser()->getId()) {
                    $classes[] = 'inactive';
                    if ($status == 1) {
                        return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><span class="status status-1">'.pz_i18n::msg('yes').'</span></td>';
                    } else {
                        return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><span class="status status-0">'.pz_i18n::msg('no').'</span></td>';
                    }
                } else {
                    if ($status == 1) {
                        return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><a href="javascript:void(0);" onclick="'.$link.'" ><span class="status status-changeable status-'.$status.'">'.pz_i18n::msg('yes').'</span></a></td>';
                    } else {
                        return '<td id="'.$td_id.'" class="'.implode(' ', $classes).'"><a href="javascript:void(0);" onclick="'.$link.'"><span class="status status-changeable status-'.$status.'">'.pz_i18n::msg('no').'</span></a></td>';
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------- Forms

    public static function getAddForm($p = [], $project)
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('add_projectuser').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        // $xform->setDebug(TRUE);

        $xform->setObjectparams('real_field_names', true);
        $xform->setObjectparams('form_showformafterupdate', true);
        $xform->setObjectparams('main_table', 'pz_project_user');
        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('projectuser_form','projectuser_add_form','".pz::url('screen', 'project', 'user', ['mode' => 'add_form'])."')");
        $xform->setObjectparams('form_id', 'projectuser_add_form');
        $xform->setHiddenField('project_id', $project->getId());
        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);

        $xform->setValidateField('pz_projectuser', ['pu', $project]);
        $xform->setValueField('hidden', ['project_id', $project->getId()]);
        $xform->setValueField('pz_select_screen', ['user_id', pz_i18n::msg('user'), pz::getUsersAsArray(pz::getUser()->getUsers()), '', '', 0, pz_i18n::msg('please_choose')]);

        $xform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

        if ($project->hasEmails() == 1) {
            $xform->setValueField('checkbox', ['emails', pz_i18n::msg('emails'), '', '1']);
        } else {
            $xform->setValueField('hidden', ['emails', '0']);
        }

        if ($project->hasCalendar() == 1) {
            $xform->setValueField('checkbox', ['calendar', pz_i18n::msg('calendar_events'), '', '1']);
        } else {
            $xform->setValueField('hidden', ['calendar', '0']);
        }

        if ($project->hasCalendarJobs() == 1) {
            $xform->setValueField('checkbox', ['calendar_jobs', pz_i18n::msg('calendar_jobs'), '', '1']);
        } else {
            $xform->setValueField('hidden', ['calendar_jobs', '0']);
        }

        if ($project->hasFiles() == 1) {
            $xform->setValueField('checkbox', ['files', pz_i18n::msg('files'), '', '1']);
        } else {
            $xform->setValueField('hidden', ['files', '0']);
        }

        $xform->setValueField('checkbox', ['admin', pz_i18n::msg('admin'), '', '0']);

        $xform->setActionField('db', []);
        $return = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            // $project_user_id = $xform->getObjectparams("main_id");
            $user = pz_user::get(rex_request('user_id', 'int'));

            if (($projectuser = pz_projectuser::get($user, $project))) {
                $projectuser->create();
            }

            $return = $header.'<p class="xform-info">'.pz_i18n::msg('projectuser_added').'</p>'.$return;
            $return .= pz_screen::getJSUpdateLayer('projectusers_list', pz::url('screen', 'project', 'user', ['project_id' => $project->getId(), 'mode' => 'list']));
        } else {
            $return = $header.$return;
        }
        $return = '<div id="projectuser_add" class="design1col xform-add">'.$return.'</div>';

        return $return;
    }
}
