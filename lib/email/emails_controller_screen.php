<?php

class pz_emails_controller_screen extends pz_emails_controller
{
    public $name = 'emails';
    public $function = '';
    public $functions = [/*'inbox', 'outbox', 'trash', 'email', 'emails', 'create', 'search'*/]; // "history", "spam", "search",

    public $functions_read = ['inbox', 'outbox', 'trash', 'email', 'emails', 'search'];
    public $functions_write = ['create'];

    public $function_default = '';
    public $navigation = []; // "history", "spam", "search",

    private $emails_modes = ['unread_current_emails', 'read_current_emails', 'trash_current_emails', 'delete_current_emails', 'move_current_emails_to_project_id'];

    public function __construct()
    {
        if ($this->hasReadPerm()) {
            $this->functions = array_merge($this->functions, $this->functions_read);
            $this->navigation = array_merge($this->navigation, ['inbox', 'outbox', 'trash', 'search']);
            $this->function_default = 'inbox';
        }

        if ($this->hasWritePerm()) {
            $this->functions = array_merge($this->functions, $this->functions_write);
            $this->navigation = array_merge($this->navigation, ['create']);
            
            if (!$this->hasReadPerm()) {
                $this->function_default = 'create';
            }
        }
    }


    public function controller($function = '')
    {


        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];
        $p['linkvars'] = [];

        switch ($this->function) {
            case 'inbox':  return $this->getInboxPage($p);
            case 'outbox':  return $this->getOutboxPage($p);
            case 'spam':  return $this->getSpamPage($p);
            case 'trash':  return $this->getTrashPage($p);
            case 'history':return $this->getHistoryPage($p);
            case 'search':  return $this->getSearchPage($p);
            case 'create':  return $this->getEmailForm($p);
            case 'api':  return $this->controllerApi($p);
            case 'email':  return $this->getEmail($p);
            case 'emails': return $this->getEmails(rex_request('mode', 'string', ''), $p);
        }
        return '';
    }

    private function getProjects()
    {
        /*
        $projects = pz::getUser()->getEmailProjects();
        if(!isset($_REQUEST["email_project_ids"]))
        {
          $project_ids = rex_request::session("pz_email_project_ids","array");
          if(count($project_ids) == 0) {
            $project_ids = pz_project::getProjectIds($projects);
          }
        }else
        {
          $project_ids = explode(",",rex_request("email_project_ids","string"));
        }
        */

        $projects = pz::getUser()->getEmailProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $return_projects = [];
        $prooved_project_ids = [];
        foreach ($projects as $project) {
            if (in_array($project->getId(), $project_ids)) {
                $return_projects[] = $project;
                $prooved_project_ids[] = $project->getId();
            }
        }

        // rex_request::setSession("pz_email_project_ids",$prooved_project_ids);
        return $return_projects;
    }

    // -------------------------------------------------------------------

    private function getNavigation($p = [])
    {
        return pz_screen::getNavigation(
            $p,
            $this->navigation,
            $this->function,
            $this->name
        );
    }

    public static function getEmailListFilter($filter = [], $linkvars = [], $ignore_fields = [])
    {
        if (rex_request('search_fulltext', 'string') != '') {
            $filter[] = ['type' => 'orlike', 'field' => 'subject,body,to,cc', 'value' => rex_request('search_fulltext', 'string')];
            $linkvars['search_fulltext'] = rex_request('search_fulltext', 'string');
        }
        /*
        if(rex_request("search_account_id","int") != 0) {
          $filter[] = array("type"=>"=", "field"=>"account_id", "value"=>rex_request("search_account_id","int"));
        }
        */

        // var_dump($ignore_fields);

        if (!in_array('from', $ignore_fields) && rex_request('search_from', 'string') != '') {
            $filter[] = ['type' => 'orlike', 'field' => 'from,from_emails', 'value' => rex_request('search_from', 'string')];
            $linkvars['search_from'] = rex_request('search_from', 'string');
        }

        if (!in_array('to', $ignore_fields) && rex_request('search_to', 'string') != '') {
            $filter[] = ['type' => 'orlike', 'field' => 'to,to_emails,cc', 'value' => rex_request('search_to', 'string')];
            $linkvars['search_to'] = rex_request('search_to', 'string');
        }

        if (!in_array('subject', $ignore_fields) && rex_request('search_subject', 'string') != '') {
            $filter[] = ['type' => 'like', 'field' => 'subject', 'value' => rex_request('search_subject', 'string')];
            $linkvars['search_subject'] = rex_request('search_subject', 'string');
        }

        if (!in_array('from', $ignore_fields) && rex_request('search_date_from', 'string') != '') {
            if (($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_from', 'string')))) {
                $filter[] = ['type' => '>=', 'field' => 'created', 'value' => $date_object->format('Y-m-d 00:00')];
                $linkvars['search_date_from'] = $date_object->format('Y-m-d');
            }
        }

        if (!in_array('to', $ignore_fields) && rex_request('search_date_to', 'string') != '') {
            if (($date_object = DateTime::createFromFormat('Y-m-d', rex_request('search_date_to', 'string')))) {
                $filter[] = ['type' => '<=', 'field' => 'created', 'value' => $date_object->format('Y-m-d 23:59')];
                $linkvars['search_date_to'] = $date_object->format('Y-m-d');
            }
        }

        if (!in_array('project_id', $ignore_fields) && rex_request('search_project_id', 'int') != 0
            && ($project = pz::getUser()->getProjectById(rex_request('search_project_id', 'int')))
        ) {
            $filter[] = ['type' => '=', 'field' => 'project_id', 'value' => $project->getId()];
            $linkvars['search_project_id'] = rex_request('search_project_id', 'string');
        }

        if (!in_array('unread', $ignore_fields) && rex_request('search_unread', 'int') != 0) {
            $filter[] = ['type' => '=', 'field' => 'readed', 'value' => '0'];
            $linkvars['search_unread'] = '1';
        }

        if (!in_array('my', $ignore_fields) && rex_request('search_my', 'int') == 1) {
            $filter[] = ['type' => '=', 'field' => 'user_id', 'value' => pz::getUser()->getId()];
            $linkvars['search_my'] = rex_request('search_my', 'int');
        }

        if (!in_array('noprojects', $ignore_fields) && rex_request('search_noprojects', 'int') != 0) {
            $filter[] = ['type' => '=', 'field' => 'project_id', 'value' => '0'];
            $linkvars['search_noprojects'] = '1';
        }

        if (!in_array('intrash', $ignore_fields)) {
            if (rex_request('search_intrash', 'int') != 1) {
                $filter[] = ['type' => '=', 'field' => 'trash', 'value' => 0];
            } else {
                $linkvars['search_intrash'] = 1;
            }
        }

        return ['filter' => $filter, 'linkvars' => $linkvars];
    }

    public static function getEmailListOrders($orders = [], $p, $ignore_fields = [])
    {
        $orders['subjectdesc'] = ['orderby' => 'subject', 'sort' => 'desc', 'name' => pz_i18n::msg('email_orderby_subjectdesc'),
                                       'link' => "javascript:pz_loadPage('emails_list','" .
                                           pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'subjectdesc'])) .
                                           "')", ];
        $orders['subjectasc'] = ['orderby' => 'subject', 'sort' => 'asc', 'name' => pz_i18n::msg('email_orderby_subjectasc'),
                                      'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                          pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'subjectasc'])) .
                                          "')", ];
        $orders['attachmentdesc'] = ['orderby' => 'has_attachments', 'sort' => 'desc', 'name' => pz_i18n::msg('email_orderby_attachmentdesc'),
                                          'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                              pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'attachmentdesc'])) .
                                              "')", ];
        $orders['vonasc'] = ['orderby' => 'from', 'sort' => 'asc', 'name' => pz_i18n::msg('email_orderby_vonasc'),
                                  'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                      pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'vonasc'])) .
                                      "')", ];
        $orders['vondesc'] = ['orderby' => 'from', 'sort' => 'desc', 'name' => pz_i18n::msg('email_orderby_vondesc'),
                                   'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                       pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'vondesc'])) .
                                       "')", ];
        $orders['createdesc'] = ['orderby' => 'created', 'sort' => 'desc', 'name' => pz_i18n::msg('email_orderby_createdesc'),
                                      'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                          pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'createdesc'])) .
                                          "')", ];
        $orders['createasc'] = ['orderby' => 'created', 'sort' => 'asc', 'name' => pz_i18n::msg('email_orderby_createasc'),
                                     'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                                         pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'createasc'])) .
                                         "')", ];

        $current_order = 'createdesc';
        if (array_key_exists(rex_request('search_orderby'), $orders)) {
            $current_order = rex_request('search_orderby');
        }

        $orders[$current_order]['active'] = true;

        $p['linkvars']['search_orderby'] = $current_order;

        return ['orders' => $orders, 'p' => $p, 'current_order' => $current_order];
    }

    public function getTitleFunctions($p, $ignore_fields = [])
    {
        $return = [];

        $return['read'] = '<li class="entry"><a href="javascript:void(0);" class="emails-read" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', $this->function, array_merge($p['linkvars'], ['mode' => 'read_current_emails'])) . '\');"><span class="title">' . pz_i18n::msg('read_current_emails') . '</span></a></li>';

        $return['unread'] = '<li class="entry"><a href="javascript:void(0);" class="emails-unread" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', $this->function, array_merge($p['linkvars'], ['mode' => 'unread_current_emails'])) . '\');"><span class="title">' . pz_i18n::msg('unread_current_emails') . '</span></a></li>';

        $return['delete'] = '<li class="entry"><a href="javascript:void(0);" class="emails-delete" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', $this->function, array_merge($p['linkvars'], ['mode' => 'delete_current_emails'])) . '\');"><span class="title">' . pz_i18n::msg('delete_current_emails') . '</span></a></li>';

        $return['trash'] = '<li class="entry"><a href="javascript:void(0);" class="emails-trash" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', $this->function, array_merge($p['linkvars'], ['mode' => 'trash_current_emails'])) . '\');"><span class="title">' . pz_i18n::msg('trash_current_emails') . '</span></a></li>';

        foreach ($ignore_fields as $ignore_field) {
            unset($return[$ignore_field]);
        }

        $projects = [];
        foreach (pz::getUser()->getEmailProjects() as $project) {
            $return['project_id-'.$project->getId()] = '<li class="entry">
			   <a href="javascript:void(0);" class="wrapper emails-project_id-'.$project->getId().'" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', $this->function, array_merge($p['linkvars'], ['mode' => 'move_current_emails_to_project_id', 'email_project_id' => $project->getId()])) . '\');">
          <div class="links">
  					<span class="title">'.pz_i18n::msg('move').'</span>
	   		  </div>
					<span class="name">'.htmlspecialchars($project->getName()).'</span>
				</a>
				</li>';
        }

        return '
      <span class="headline-list"><ul class="sl2 sl2b selected"><li class="selected option"><span class="selected option" onclick="pz_screen_select(this);">Optionen</span>
        <div class="flyout">
          <div class="content">
            <ul class="entries">
              '.implode('', $return).'
            </ul>
          </div>
        </div>
      </li></ul>
      </span>';
    }

    public function executeEmailsFunction($mode = '', $emails = [])
    {
        switch ($mode) {

            case 'move_current_emails_to_project_id':

                $email_project_id = rex_request('email_project_id', 'int', 0);
                if (!($project = pz::getUser()->getProjectById($email_project_id))) {
                    return false;
                }

                $return = '<script language="Javascript">';
                foreach ($emails as $email) {
                    $email->moveToProjectId($project->getId());
                    $email->updateStatus(1); // mark as: not in inbox
                    $return .= '$(".email-' . $email->getId() . '").addClass("email-hasproject");';

                    // rename project OR hide email
                    $return .= '$(".email-' . $email->getId() . ' .email-project-name").html("' . htmlspecialchars($project->getName()) . '");';
                    $return .= '$(".email-' . $email->getId() . ' .label").removeAttr("class").attr("class","label ' . pz_label_screen::getColorClass($project->getLabelId()) . '");';

                    // OR hide / in inbox fex
                    // $return .= 'pz_hide(".email-' . $email->getId() . '");';
                }

                $return .= '$(".emails-project_id-'.$project->getId().'").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';

                return $return;

            case 'unread_current_emails':
                $return = '<script language="Javascript">';
                foreach ($emails as $email) {
                    $email->unreaded();
                    $return .= '$(".email-' . $email->getId() . '").removeClass("email-readed").addClass("email-unreaded");';
                }
                $return .= '$(".emails-unread").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'read_current_emails':
                $return = '<script language="Javascript">';
                foreach ($emails as $email) {
                    $email->readed();
                    $return .= '$(".email-' . $email->getId() . '").removeClass("email-unreaded").addClass("email-readed");';
                }
                $return .= '$(".emails-read").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'delete_current_emails':
                $return = '<script language="Javascript">';
                foreach ($emails as $email) {
                    if ($email->isTrash() && !$email->hasProject() && $email->delete()) {
                        $return .= 'pz_hide(".email-' . $email->getId() . '");';
                    }
                }
                $return .= '$(".emails-delete").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'trash_current_emails':
                $return = '<script language="Javascript">';
                foreach ($emails as $email) {
                    $email->trash();
                    $return .= 'pz_hide(".email-' . $email->getId() . '");';
                }
                $return .= '$(".emails-trash").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

        }

        return false;
    }

    // ------------------------------------------------------------------- Pages

    public function getEmails($mode = '', $p = [])
    {
        switch ($mode) {

            case 'download_emails':

                $return = '<script language="Javascript">';
                $emails = [];
                $timer = new rex_timer();
                $email_accounts = pz_email_account::getAccounts(pz::getUser()->getId(), 1);

                foreach ($email_accounts as $email_account) {
                    $email_account->downloadEmails();
                    $emails = array_merge($emails, $email_account->getEmails());
                }
                // $return.= 'alert("'.count($emails).' E-mails downloaded");';

                $return .= '$(".emails-download").removeClass("bt-loading");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';

                return $return;

        }
    }

    public function getEmail($p = [])
    {
        $email_id = rex_request('email_id', 'int', 0);
        if ($email_id < 1) {
            return false;
        }

        if (!($email = pz::getUser()->getEmailById($email_id))) {
            return false;
        }

        $mode = rex_request('mode', 'string', '');

        switch ($mode) {
            case 'view':
                if (!$email->getReaded()) {
                    $email->readed();
                }
                $pz_email_screen = new pz_email_screen($email);
                $return = $pz_email_screen->getDetailView();

                $return .= '<script language="Javascript">';
                $return .= '$(".email-' . $email->getId() . '").removeClass("email-unreaded");';
                $return .= '$(".email-' . $email->getId() . '").addClass("email-readed");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';

                return $return;

            case 'view_element_by_content_id':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $content_id = rex_request('content_id', 'string', 0);
                if ($element = $pz_eml->getElementByContentId($content_id)) {
                    // ob_end_clean();
                    // header("Cache-Control: no-cache, must-revalidate");
                    // header("Cache-Control","private");
                    // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // in the past
                    // header("Content-Transfer-Encoding: binary");
                    // header("Content-Length: ".$element->getSize());
                    pz::setHeader('content-type', $element->getContentType());
                    pz::setHeader('filename', $element->getFileName());
                    return $element->getBody();
                }
                return false;

            case 'view_header':
                $pz_email_screen = new pz_email_screen($email);
                $return = $pz_email_screen->getHeaderView();
                return $return;

            case 'view_firstbody':
                $pz_eml = $email->getProzerEml();
                $return = $pz_eml->getFirstText();
                pz::setHeader('content-type', 'text/plain');
                return $return;

            case 'view_ashtml':

                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);

                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    $body = $element->getBody();
                    $content_type = $element->getContentType();
                    $search = '#cid:([a-zA-Z-0-9\\\/_.]*)#i';
                    $replace = pz::url('screen', 'emails', 'email', ['mode' => 'view_element_by_content_id', 'email_id' => $email->getId(), 'content_id' => '']) . "\${1}";
                    $body = preg_replace($search, $replace, $body);

                    if ($element->getContentTypeCharset() != '') {
                        pz::setHeader('charset', $element->getContentTypeCharset());
                        $body = mb_convert_encoding($body, $element->getContentTypeCharset(), 'UTF-8');
                    }
                    if ($content_type != '') {
                        pz::setHeader('content-type', $content_type);
                    }
                    pz::setHeader('filename', $element->getFileName());
                    return $body;
                }
                return false;

            case 'view_element':

                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);

                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    $body = $element->getBody();
                    $content_type = $element->getContentType();

                    if ($element->getContentType() == 'text/html') {
                        $search = '#cid:([a-zA-Z-0-9\\\/_.]*)#i';
                        $replace = pz::url('screen', 'emails', 'email', ['mode' => 'view_element_by_content_id', 'email_id' => $email->getId(), 'content_id' => '']) . "\${1}";
                        $body = preg_replace($search, $replace, $body);
                        if ($element->getContentTypeCharset() != '') {
                            pz::setHeader('charset', $element->getContentTypeCharset());
                            $body = mb_convert_encoding($body, $element->getContentTypeCharset(), 'UTF-8');
                        }
                    }

                    pz::setHeader('content-type', pz::getMimetypeByFilename($element->getFileName()));
                    pz::setHeader('filename', $element->getFileName());

                    return $body;
                }
                return false;

            case 'import':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);
                $project_id = rex_request('project_id', 'int', 0);
                $action = rex_request('action', 'bool', false);

                $element = $pz_eml->getElementByElementId($element_id);

                $p = [];
                if ($action) {
                    try {
                        $importStatus = pz_sabre_caldav_backend::getImportStatus($element->getBody());
                        pz_sabre_caldav_backend::import($element->getBody(), $project_id);
                        if ('CANCEL' === $importStatus['method']) {
                            $type = 'deleted';
                        } else {
                            $type = $importStatus['event'] ? 'updated' : 'created';
                        }
                        $p['info_message'] = pz_i18n::msg('import_success_'.$type);
                    } catch (Exception $e) {
                        $p['warning_message'] = pz_i18n::msg('import_error');
                    }
                }

                $importStatus = pz_sabre_caldav_backend::getImportStatus($element->getBody());

                $pz_email_screen = new pz_email_screen($email);
                return $pz_email_screen->getImportFlyoutView($element, $importStatus, $p);

            case 'download':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);
                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    pz::setDownloadHeaders($element->getFileName(), $element->getBody());
                    return $element->getBody();
                }
                return false;

            case 'download_source':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);
                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    pz::setDownloadHeaders($element->getFileName(), $element->getSource());
                    return $element->getSource();
                }
                return false;

            case 'element2clipboard':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);
                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    $clip = pz_clip::createAsSource($element->getBody(), $element->getFileName(), $element->getSize(), $element->getContentType(), false);
                    return '<script>pz_loadClipboard();</script>';
                }
                return false;

            case 'element_source2clipboard':
                $pz_eml = $email->getProzerEml();
                $pz_eml->setMailFilename($email->getId());
                $element_id = rex_request('element_id', 'string', 0);
                if ($element = $pz_eml->getElementByElementId($element_id)) {
                    $clip = pz_clip::createAsSource($element->getSource(), $element->getFileName(), $element->getSize(), $element->getContentType(), false);
                    return '<script>pz_loadClipboard();</script>';
                }
                return false;

            case 'move_to_project_id_update_status':
            case 'move_to_project_id':
                $email_project_id = rex_request('email_project_id', 'int', 0);
                if (!($project = pz::getUser()->getProjectById($email_project_id))) {
                    return false;
                }
                $email->moveToProjectId($email_project_id);
                $project_ids = pz_project::getProjectIds($this->getProjects());
                $status = $email->getStatus();
                $return = '<script language="Javascript">';
                if ($mode == 'move_to_project_id_update_status') {
                    $email->updateStatus(1);
                    $status = 1;
                } else {
                    // only project id update
                    $return .= '$(".email-' . $email->getId() . ' .email-project-name").html("' . htmlspecialchars($project->getName()) . '");';
                    $return .= '$(".email-' . $email->getId() . '").addClass("email-hasproject");';
                    $return .= '$(".email-' . $email->getId() . ' .label").removeAttr("class").attr("class","label ' . pz_label_screen::getColorClass($project->getLabelId()) . '");';
                }

                if ($status == 1 || !in_array($project->getId(), $project_ids)) {
                    $return .= 'pz_hide(".email-' . $email->getId() . '");';
                }
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'unproject':
                $email->removeFromProject();
                $email->updateStatus(0);
                $return = '<script language="Javascript">';
                $return .= '$(".email-' . $email->getId() . ' .email-project-name").html("' . htmlspecialchars(pz_i18n::msg('please_select_project_for_email')) . '");';
                $return .= '$(".email-' . $email->getId() . '").removeClass("email-hasproject");';
                $return .= '$(".email-' . $email->getId() . ' .label").removeAttr("class").attr("class","label labelc");';
                $return .= '</script>';
                return $return;

            case 'trash':
                $email->trash();
                $return = '<script language="Javascript">';
                $return .= 'pz_hide(".email-' . $email->getId() . '");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'untrash':
                $email->untrash();
                $return = '<script language="Javascript">';
                $return .= 'pz_hide(".email-' . $email->getId() . '");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'delete':
                $email->delete();
                $return = '<script language="Javascript">';
                $return .= 'pz_hide(".email-' . $email->getId() . '");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

            case 'unread':
                $email->unreaded();
                $return = '<script language="Javascript">';
                $return .= '$(".email-' . $email->getId() . '").removeClass("email-readed");';
                $return .= '$(".email-' . $email->getId() . '").addClass("email-unreaded");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';
                return $return;

        }
    }

    public function getInboxPage($p = [])
    {
        $p['title'] = pz_i18n::msg('emails_inbox');
        $p['title_search'] = pz_i18n::msg('emails_inbox_search');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'inbox';

        $p['layer_search'] = 'emails_search';
        $p['layer_list'] = 'emails_list';

        $p['list_links'] = [];
        $p['list_links'][] = '<a class="emails-download bt5" href="javascript:void(0);" onclick="if($(this).hasClass(\'bt-loading\')) return false; $(this).addClass(\'bt-loading\'); pz_exec_javascript(\'' . pz::url('screen', 'emails', 'emails', array_merge(['mode' => 'download_emails'])) . '\');"><span>' . pz_i18n::msg('download_emails') . '</span></a>';

        $s1_content = '';
        $s2_content = '';

        $projects = $this->getProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $filter = [];
        $filter[] = ['type' => 'plain', 'value' => '( status=0 || project_id=0 )'];

        $tracker_date = pz::getDateTime()->format('Y-m-d H:i:s');

        // postload - new emails
        $date = rex_request('date', 'string');
        if ($date != '') {
            if (($from_date = DateTime::createFromFormat('Y-m-d H:i:s', $date, pz::getDateTimeZone()))) {
                $filter[] = ['type' => 'plain', 'value' => '( created>"'.$from_date->format('Y-m-d H:i:s').'" and created <= "'.$tracker_date.'")'];
            }
        }

        $result = self::getEmailListFilter($filter, $p['linkvars'], ['intrash']);
        $filter = $result['filter'];
        $p['linkvars'] = $result['linkvars'];
        $p['linkvars']['mode'] = 'list';

        $orders = [];
        $result = self::getEmailListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $mode = rex_request('mode', 'string');
        if (in_array($mode, $this->emails_modes)) {
            $pager = new pz_pager(2000);
            $emails = pz::getUser()->getInboxEmails($filter, $projects, [], $pager);
            return $this->executeEmailsFunction($mode, $emails);
        }

        $pager = new pz_pager();
        $pager_screen = new pz_pager_screen($pager, $p['layer_list']);

        $emails = pz::getUser()->getInboxEmails($filter, $projects, [$orders[$current_order]], $pager);

        $p['trackerlink'] = pz::url('screen', 'emails', 'inbox', array_merge($p['linkvars'], ['mode' => 'getnew', 'date' => $tracker_date]));
        $p['javascript'] = 'pz_add_tracker("inbox_emails", "'.$p['trackerlink'].'", 5000, 0);';
        $p['xform_warning'] = (count($filter) > 1) ? 'xform-warning' : 'xform-info';

        $p['list_title_links'] = [];
        $p['list_title_links'][] = $this->getTitleFunctions($p, ['delete']);

        $return = pz_email_screen::getInboxListView($emails, $p, $orders, $pager_screen);

        if ($mode == 'getnew') {
            $emails_screen = '';
            if (isset($from_date)) {
                foreach ($emails as $email) {
                    if ($e = new pz_email_screen($email)) {
                        $emails_screen .= $e->getBlockView($p);
                    }
                }
            }

            $return = '<script>'.$p['javascript'].'$("#emails_list article:first").before("'.str_replace(["\n", "\r", '"'], ['', '', '\"'], $emails_screen).'");</script>';
            return $return;
        } elseif ($mode == 'list') {
            return $return;
        }

        $s1_content .= pz_email_screen::getEmailsSearchForm($p, ['intrash']);
        $s2_content .= $return;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getOutboxPage($p = [])
    {
        $p['title'] = pz_i18n::msg('emails_outbox');
        $p['title_search'] = pz_i18n::msg('emails_outbox_search');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'outbox';

        $p['layer_search'] = 'emails_search';
        $p['layer_list'] = 'emails_list';

        $s1_content = '';
        $s2_content = '';

        $projects = $this->getProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $filter = [];
        $filter[] = ['type' => '=', 'field' => 'user_id', 'value' => pz::getUser()->getId()];

        $result = self::getEmailListFilter($filter, $p['linkvars'], ['intrash']);
        $filter = $result['filter'];
        $p['linkvars'] = $result['linkvars'];

        $orders = [];
        $result = self::getEmailListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $mode = rex_request('mode', 'string');
        if (in_array($mode, $this->emails_modes)) {
            $pager = new pz_pager(2000);
            $emails = pz::getUser()->getOutboxEmails($filter, $projects, [], $pager);
            return $this->executeEmailsFunction($mode, $emails);
        }

        $p['list_title_links'] = [];
        $p['list_title_links'][] = $this->getTitleFunctions($p, ['delete']);

        $pager = new pz_pager();
        $pager_screen = new pz_pager_screen($pager, $p['layer_list']);

        $emails = pz::getUser()->getOutboxEmails($filter, $projects, [$orders[$current_order]], $pager);

        $p['linkvars']['mode'] = 'list';
        $return = pz_email_screen::getOutboxListView($emails, $p, $orders, $pager_screen);

        if ($mode == 'list') {
            return $return;
        }

        $s1_content .= pz_email_screen::getEmailsSearchForm($p, ['intrash']);
        $s2_content .= $return;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getSpamPage($p = [])
    {
        $p['title'] = pz_i18n::msg('emails_spam');
        $p['title_search'] = pz_i18n::msg('emails_spam_search');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'spam';

        $p['layer_search'] = 'emails_search';
        $p['layer_list'] = 'emails_list';

        $s1_content = '';
        $s2_content = '';

        $projects = $this->getProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $filter = [];
        $result = self::getEmailListFilter($filter, $p['linkvars'], ['intrash']);
        $filter = $result['filter'];
        $p['linkvars'] = $result['linkvars'];

        $orders = [];
        $result = self::getEmailListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $emails = pz::getUser()->getSpamEmails($filter, $projects, [$orders[$current_order]]);

        $return = '';

        $mode = rex_request('mode', 'string');
        switch ($mode) {

            case 'emails_search':
                return pz_email_screen::getEmailsSearchForm($p, ['intrash']);
            default:
                break;
        }

        $p['linkvars']['mode'] = 'list';
        $return .= pz_email_screen::getSpamListView($emails, $p, $orders);

        if ($mode == 'list') {
            return $return;
        }

        $s1_content .= pz_email_screen::getEmailsSearchForm($p, ['intrash']);
        $s2_content .= $return;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getTrashPage($p = [])
    {
        $p['title'] = pz_i18n::msg('emails_trash');
        $p['title_search'] = pz_i18n::msg('emails_trash_search');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'trash';

        $p['layer_search'] = 'emails_search';
        $p['layer_list'] = 'emails_list';

        $s1_content = '';
        $s2_content = '';

        $projects = $this->getProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $filter = [];
        $result = self::getEmailListFilter($filter, $p['linkvars'], ['intrash']);
        $filter = $result['filter'];
        $p['linkvars'] = $result['linkvars'];

        $orders = [];
        $result = self::getEmailListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $mode = rex_request('mode', 'string');
        if (in_array($mode, $this->emails_modes)) {
            $pager = new pz_pager(2000);
            $emails = pz::getUser()->getTrashEmails($filter, $projects, [], $pager);
            return $this->executeEmailsFunction($mode, $emails);
        }

        $pager = new pz_pager();
        $pager_screen = new pz_pager_screen($pager, $p['layer_list']);

        $emails = pz::getUser()->getTrashEmails($filter, $projects, [$orders[$current_order]], $pager);

        $p['linkvars']['mode'] = 'list';

        $p['list_title_links'] = [];
        $p['list_title_links'][] = $this->getTitleFunctions($p, ['trash']);

        $return = pz_email_screen::getTrashListView($emails, $p, $orders, $pager_screen);

        $mode = rex_request('mode', 'string');

        /* switch ($mode) {
          case 'emails_search':
            return pz_email_screen::getEmailsSearchForm($p, array('intrash'));
        } */

        if ($mode == 'list') {
            return $return;
        }

        $s1_content .= pz_email_screen::getEmailsSearchForm($p, ['intrash']);
        $s2_content .= $return;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    private function getSearchPage($p = [])
    {
        $p['title'] = pz_i18n::msg('emails_search');
        $p['title_search'] = pz_i18n::msg('emails_search_search');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'search';

        $p['layer_search'] = 'emails_search';
        $p['layer_list'] = 'emails_list';

        $s1_content = '';
        $s2_content = '';

        $filter = [];
        // $filter[] = array("field"=>"trash", "value"=>0);
        $filter[] = ['field' => 'draft', 'value' => 0];

        $result = self::getEmailListFilter($filter, $p['linkvars'], []); // array('intrash')
        $filter = $result['filter'];
        $p['linkvars'] = $result['linkvars'];

        // pz::debug('filter', $filter);

        $orders = [];
        $result = self::getEmailListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $projects = [];

        $mode = rex_request('mode', 'string');
        if (in_array($mode, $this->emails_modes)) {
            $pager = new pz_pager(2000);
            $emails = pz::getUser()->getAllEmails($filter, $projects, [], $pager);
            return $this->executeEmailsFunction($mode, $emails);
        }

        $pager = new pz_pager();
        $pager_screen = new pz_pager_screen($pager, $p['layer_list']);

        $emails = pz::getUser()->getAllEmails($filter, $projects, [$orders[$current_order]], $pager);

        $p['linkvars']['mode'] = 'list';

        $p['list_title_links'] = [];
        $p['list_title_links'][] = $this->getTitleFunctions($p, ['delete']);

        $return = pz_email_screen::getSearchListView($emails, $p, $orders, $pager_screen);

        if ($mode == 'list') {
            return $return;
        }

        $s1_content .= pz_email_screen::getEmailsSearchForm($p, []);
        $s2_content .= $return;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getEmailForm($p = [])
    {
        $return = '';
        $p['title'] = pz_i18n::msg('email_create');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'emails';
        $p['function'] = 'create';

        $s1_content = '';
        $s2_content = '';

        $filter = [];
        $projects = [];
        $orders = [
            ['orderby' => 'created', 'sort' => 'DESC'],
        ];
        $emails = pz::getUser()->getDraftsEmails($filter, $projects, $orders);

        // ------------ Reply Mail

        $reply_email_id = rex_request('reply_email_id', 'int');
        if ($reply_email_id > 0 && $email = pz::getUser()->getEmailById($reply_email_id)) {
            $_REQUEST['to'] = $email->getFromEmail();

            if (rex_request('reply_all', 'int') == 1) {
                $user_emails = [];
                $user_emails[] = '';
                $user_email = pz::getUser()->getEmail();
                if ($user_email != '' && $user_address = pz_address::getByEmail($user_email)) {
                    foreach ($user_address->getFields() as $field) {
                        if ($field->getVar('type') == 'EMAIL') {
                            $user_emails[] = $field->getVar('value');
                        }
                    }
                }

                $to = [];
                $to[] = $email->getFromEmail();
                $to = array_merge($to, explode(',', $email->getToEmails()));

                $cc = explode(',', $email->getCcEmails());

                $to = array_diff($to, $user_emails);
                $cc = array_diff($cc, $user_emails);

                $_REQUEST['to'] = implode(',', $to);
                $_REQUEST['cc'] = implode(',', $cc);
            }

            $_REQUEST['reply_id'] = $reply_email_id;
            $_REQUEST['project_id'] = $email->getProjectId();

            $body = ' ' . pz_i18n::msg('email_original');
            $body .= "\n" . pz_i18n::msg('email_from') . ': ' . $email->getFromEmail();
            $body .= "\n" . pz_i18n::msg('email_original_send') . ': ' . $email->getDate();
            $body .= "\n" . pz_i18n::msg('email_to') . ': ' . $email->getToEmails();
            $body .= "\n" . pz_i18n::msg('email_cc') . ': ' . $email->getCcEmails();
            $body .= "\n" . pz_i18n::msg('email_subject') . ': ' . $email->getSubject();
            $body .= "\n\n" . $email->getBody();

            $_REQUEST['body'] = "\n\n>" . str_replace("\n", "\n> ", $body);
            $_REQUEST['subject'] = 'RE: ' . $email->getSubject();
        }

        // ------------ Forward Mail

        $forward_email_id = rex_request('forward_email_id', 'int');
        if ($forward_email_id > 0 && $email = pz::getUser()->getEmailById($forward_email_id)) {
            $_REQUEST['forward_id'] = $forward_email_id;
            $_REQUEST['project_id'] = $email->getProjectId();

            $body = ' ' . pz_i18n::msg('email_original');
            $body .= "\n" . pz_i18n::msg('email_to') . ': ' . $email->getFromEmail();
            $body .= "\n" . pz_i18n::msg('email_original_send') . ': ' . $email->getDate();
            $body .= "\n" . pz_i18n::msg('email_to') . ': ' . $email->getToEmails();
            $body .= "\n" . pz_i18n::msg('email_cc') . ': ' . $email->getCcEmails();
            $body .= "\n" . pz_i18n::msg('email_subject') . ': ' . $email->getSubject();
            $body .= "\n\n" . $email->getBody();

            $_REQUEST['body'] = "\n\n>" . str_replace("\n", "\n> ", $body);
            $_REQUEST['subject'] = 'FW: ' . $email->getSubject();

            // TODO:
            // - attachments ziehen
            // - als clips anlegen und zuweisen

            $attachments = $email->getAttachments();
            $clips = [];
            foreach ($attachments as $attachment) {
                if ($attachment->hasParent()) {
                    if (($clip = pz_clip::createAsSource($attachment->getBody(), $attachment->getFileName(), $attachment->getSize(), $attachment->getContentType(), true))) {
                        $clips[] = $clip->getId();
                    }
                }
            }
            $_REQUEST['clip_ids'] = implode(',', $clips);

            /*
            $pz_eml = new pz_eml($email->getEml());
            $pz_eml->setMailFilename($email->getId());
            if($element = $pz_eml->getElementByElementId("0-0"))
            {
              $clip = pz_clip::createAsSource($pz_eml->getSource(), $pz_eml->getFileName(), $pz_eml->getSize(), $pz_eml->getContentType(), true);
              $_REQUEST["clip_ids"] = $clip->getId();
            }
            */
        }

        $calendar_event_id = rex_request('attachment_calendar_event_id', 'int');
        if ($calendar_event_id && $event = pz_calendar_event::get($calendar_event_id)) {
            $ics = pz_sabre_caldav_backend::export($event);
            $clip = pz_clip::createAsSource($ics, $event->getUri(), rex_string::size($ics), 'text/calendar', true);
            $_REQUEST['clip_ids'] = $clip->getId();
        }

        if (isset($_REQUEST['to'])) {
            $_REQUEST['to'] = trim($_REQUEST['to'], "\n\t\0\r\x0B, ");
        }
        if (isset($_REQUEST['cc'])) {
            $_REQUEST['cc'] = trim($_REQUEST['cc'], "\n\t\0\r\x0B, ");
        }
        if (isset($_REQUEST['bcc'])) {
            $_REQUEST['bcc'] = trim($_REQUEST['bcc'], "\n\t\0\r\x0B, ");
        }

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case 'add_email':
                return pz_email_screen::getAddForm($p);

            case 'delete_email':

                $email_id = rex_request('email_id', 'int', 0);
                // TODO - permission prüfen
                if ($email = pz_email::get($email_id)) {
                    $email->delete();
                    $p['info'] = '<p class="xform-info">' . pz_i18n::msg('email_account_delete') . '</p>';
                } else {
                    $p['info'] = '<p class="xform-warning">' . pz_i18n::msg('email_account_not_exists') . '</p>';
                }

                $return = '<script language="Javascript">';
                $return .= 'pz_hide(".email-' . $email->getId() . '");';
                $return .= 'pz_init_tracker("global");';
                $return .= '</script>';

                return $return;

            case 'list':
                $s2_content = pz_email_screen::getDraftsListView(
                    $emails,
                    array_merge($p, ['linkvars' => ['mode' => 'list']])
                );
                return $s2_content;

            case 'edit_email':
                $email_id = rex_request('email_id', 'int', 0);
                // TODO. permission to email prüfen.
                if ($email_id > 0 && $email = pz_email::get($email_id)) {
                    $cs = new pz_email_screen($email);
                    return $cs->getEditForm($p);
                }
                return '<p class="xform-warning">' . pz_i18n::msg('email_not_exists') . '</p>';

            case '':

                if (pz::getUser()->getDefaultEmailaccountId() && $account = pz_email_account::get(pz::getUser()->getDefaultEmailaccountId())) {
                    if (isset($_REQUEST['body'])) {
                        $_REQUEST['body'] = $account->getSignature() . $_REQUEST['body'];
                    } else {
                        $_REQUEST['body'] = $account->getSignature();
                    }
                }

                $s1_content .= pz_email_screen::getAddForm($p);
                $s2_content .= pz_email_screen::getDraftsListView(
                    $emails,
                    array_merge($p, ['linkvars' => ['mode' => 'list']])
                );
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        $return .= $f->parse('pz_screen_main.tpl');
        return $return;
    }

    // ------------------------------------------------------------------- Hover

    public function getMainFlyout()
    {
        return '
    <div class="flyout">
      <div class="content grid2col">

        <div class="column first">
          <dl class="navi-lev2">
            <dt class="hl2">E-Mail</dt>
            <dd>

              <ul class="lev2">
                <li class="lev2 active"><a class="lev2 bt3 active" href="#">Inbox</a><span class="info1"><span class="inner">10</span></li>
                <li class="lev2"><a class="lev2 bt3" href="#">Outbox</a></li>
                <li class="lev2"><a class="lev2 bt3" href="#">Drafts</a></li>
                <li class="lev2"><a class="lev2 bt3" href="#">Spam</a></li>
                <li class="lev2"><a class="lev2 bt3" href="#">Trash</a></li>

                <li class="lev2 last"><a class="lev2 bt3" href="#">New E-Email</a></li>
              </ul>
            </dd>
          </dl>
        </div>
        <div class="column last">
          <dl class="items">
            <dt class="hl2">letzte E-Mails</dt>

            <dd>
              <ul class="ls1 entries">
                <li class="entry first"><a class="email" href=""><span class="name">Yann Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry"><a class="email" href=""><span class="name">Anton Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry"><a class="email" href=""><span class="name">Kai Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>

                <li class="entry last"><a class="email" href=""><span class="name">Alfons Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
              </ul>
            </dd>

            <dt class="hl2">empfohlene E-Mails</dt>
            <dd>
              <ul class="ls1 entries">
                <li class="entry first"><a class="email" href=""><span class="name">Mulder Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>

                <li class="entry"><a class="email" href=""><span class="name">Addolorata Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry"><a class="email" href=""><span class="name">Ralph Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry last"><a class="email" href=""><span class="name">Adélaïde Sittler</span><span class="info">Do. 18.05.2011, 20:30</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
              </ul>

            </dd>
          </dl>
        </div>

      </div>
    </div>
  ';
    }
}
