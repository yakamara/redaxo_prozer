<?php

class pz_email_screen
{
    /** @var pz_email */
    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    // ------------------ LIST VIEWS

    public static function getInboxListView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        return pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);
    }

    public static function getOutboxListView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        return pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);
    }

    public static function getSearchListView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        return pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);
    }

    public static function getDraftsListView($emails, $p = [], $orders = [])
    {
        $p['title'] = pz_i18n::msg('email_drafts');
        $p['layer'] = 'emails_list';
        $paginate_screen = new pz_paginate_screen($emails);
        $content = $paginate_screen->getPlainView($p);

        $list = '';
        $first = ' first';
        foreach ($paginate_screen->getCurrentElements() as $email) {
            if ($e = new pz_email_screen($email)) {
                $list .= '<li class="lev1 entry entry-email'.$first.'">'.$e->getDraftView($p).'</li>';
                if ($first == '') {
                    $first = ' first';
                } else {
                    $first = '';
                }
            }
        }

        $content = $content.'<ul class="entries view-block clearfix">'.$list.'</ul>';

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        $link_refresh = pz::url('screen', $p['controll'], $p['function'],
            array_merge(
                $p['linkvars'],
                [
                    'mode' => 'list',
                    'email_project_ids' => '___value_ids___',
                ]
            )
        );

        return '<div id="emails_list" class="design1col email-drafts" data-url="'.$link_refresh.'">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    public static function getSpamListView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        return pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);
    }

    public static function getTrashListView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        return pz_email_screen::getPagedEmailsBlockView($emails, $p, $orders, $pager_screen);
    }

    public static function getEmailsBlockView($emails, $p = [], $orders = [], $pager_screen = '')
    {
        $p['layer'] = 'emails_list';
        $paginate_screen = new pz_paginate_screen($emails);
        $content = $paginate_screen->getPlainView($p);

        $list = '';
        foreach ($paginate_screen->getCurrentElements() as $email) {
            if ($e = new pz_email_screen($email)) {
                $list .= $e->getBlockView($p);
            }
        }

        $content = $content.$list;
        $content .= $paginate_screen->setPaginateLoader($p, '#emails_list');

        if ($paginate_screen->isScrollPage()) {
            return $content;
        }

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        $link_refresh = pz::url('screen', $p['controll'], $p['function'],
            array_merge(
                $p['linkvars'],
                [
                    'mode' => 'list',
                    'email_project_ids' => '___value_ids___',
                ]
            )
        );

        if (isset($p['list_links'])) {
            $f->setVar('links', $p['list_links'], false);
        }

        if (isset($p['list_title_links'])) {
            $f->setVar('title_links', $p['list_title_links'], false);
        }

        $f->setVar('orders', $orders);
        $return = $f->parse('pz_screen_list.tpl');
        if (count($emails) == 0) {
            $return .= '<div class="yform-warning">'.pz_i18n::msg('no_emails_found').'</div>';
        }

        return '<div id="emails_list" class="design2col" data-url="'.$link_refresh.'">'.$return.'ddd</div>';
    }

    public static function getPagedEmailsBlockView($emails, $p = [], $orders = [], $pager_screen)
    {

        // $paginate = $pager_screen->getPlainView($p);

        $list = '';
        foreach ($emails as $email) {
            if ($e = new pz_email_screen($email)) {
                $list .= $e->getBlockView($p);
            }
        }

        $list .= '<script>
		$(document).ready(function() {
		';
        if (isset($p['javascript'])) {
            $list .= $p['javascript'];
        }
        $list .= '
		});
		</script>';

        if (is_object($pager_screen)) {
            if ($pager_screen->isScrollPage()) {
                return $pager_screen->getScrollView($p, $list);
            }

            $content = $pager_screen->getPlainView($p, $list);
        } else {
            $content = $list;
        }

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        $link_refresh = pz::url('screen', $p['controll'], $p['function'],
            array_merge(
                $p['linkvars'],
                [
                    'mode' => 'list',
                    'email_project_ids' => '___value_ids___',
                ]
            )
        );

        if (isset($p['list_links'])) {
            $f->setVar('links', $p['list_links'], false);
        }

        if (isset($p['list_title_links'])) {
            $f->setVar('title_links', $p['list_title_links'], false);
        }

        $f->setVar('orders', $orders);
        $return = $f->parse('pz_screen_list.tpl');

        if (count($emails) == 0) {
            $p['yform_warning'] = (isset($p['yform_warning']))? $p['yform_warning'] :'yform-info';
            $return .= '<div class="'.$p['yform_warning'].'">'.pz_i18n::msg('no_emails_found').'</div>';
        }

        return '<div id="emails_list" class="design2col" data-url="'.$link_refresh.'">'.$return.'</div>';
    }

    public static function getEmailsMatrixView($emails, $p = [])
    {
        $p['layer'] = 'emails_list';
        $paginate_screen = new pz_paginate_screen($emails);
        $paginate_screen->setListAmount(9);
        $content = $paginate_screen->getPlainView($p);

        $first = ' first';
        foreach ($paginate_screen->getCurrentElements() as $email) {
            if ($e = new pz_email_screen($email)) {
                $content .= '<li class="lev1 entry'.$first.'">'.$e->getMatrixView($p).'</li>';
                if ($first == '') {
                    $first = ' first';
                } else {
                    $first = '';
                }
            }
        }
        $content = '<ul class="entries view-matrix clearfix">'.$content.'</ul>';

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        $return = $f->parse('pz_screen_list.tpl');
        if (count($emails) == 0) {
            $return .= '<div class="yform-warning">'.pz_i18n::msg('no_emails_found').'</div>';
        }

        return '<div id="emails_list" class="design3col">'.$return.'</div>';
    }

    /*
      private function getEmailsTableView($emails,$p = array())
      {

          $paginate_screen = new pz_paginate_screen($emails);
          $paginate = $paginate_screen->getPlainView($p);

          $content = "";
          foreach($paginate_screen->getCurrentElements() as $email) {
              $content .= '<tr>';
              $content .= '<td>'.$email.'</td>';
              $content .= '</tr>';
          }
          $content = $paginate.'
            <table class="projectemail tbl1">
            <thead><tr>
                <th></th>
                <th>'.pz_i18n::msg("customer").'</th>
                <th>'.pz_i18n::msg("project_name").'</th>
                <th>'.pz_i18n::msg("project_createdate").'</th>
                <th>'.pz_i18n::msg("project_admins").'</th>
                <th class="label"></th>
            </tr></thead>
            <tbody>
              '.$content.'
            </tbody>
            </table>';

          $f = new pz_fragment();
          $f->setVar('title', $p["title"], false);
          $f->setVar('content', $content , false);
          return '<div id="projectemail_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
      }
    */

    // ------------------ VIEWS

    public function getHeaderView($p = [])
    {
        $header = $this->email->getHeader();
        return '<div class="email-header"><pre>'.htmlspecialchars($header).'</pre></div>';
    }

    public function getDebugView($p = [])
    {
        $pz_eml = new pz_eml($this->email->getEml());
        return $pz_eml->getDebugInfo();
    }

    public function getDraftView($p = [])
    {
        $p['linkvars']['email_id'] = $this->email->getId();

        $link_open = "javascript:pz_loadPage('email_form','".pz::url('screen', 'emails', 'create', array_merge($p['linkvars'], ['mode' => 'edit_email', 'email_id' => $this->email->getId()]))."')";
        $link_delete = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'create', array_merge($p['linkvars'], ['mode' => 'delete_email']))."')";

        $image_to_address = '';
        $image_to_adresse_title = [];

        $to_emails = [];
        $to_emails = explode(',', $this->email->getTo());

        if (count($to_emails) > 0) {
            foreach ($to_emails as $to_email) {
                if ($to_address = pz_address::getByEmail($to_email)) {
                    if ($image_to_address == '') {
                        $image_to_address = $to_address->getInlineImage();
                    }
                    $image_to_adresse_title[] = htmlspecialchars($to_address->getFullName());
                } else {
                    $image_to_adresse_title[] = htmlspecialchars($to_email);
                }
            }
        }

        if ($image_to_address == '') {
            $image_to_address = pz_user::getDefaultImage();
        }
        $image_to_adresse_title = implode(', ', $image_to_adresse_title);

        $return = '
		  <article id="email-'.$this->email->getId().'" class="email draft block images label email-'.$this->email->getId().'">
            <header>
              <figure>'.pz_screen::getTooltipView('<img src="'.$image_to_address.'" width="40" height="40" />', $image_to_adresse_title).'</figure>
              <hgroup>
                <h2 class="hl7"><span class="name">'.$this->email->getVar('from').'</span><span class="info">'.$this->email->getVar('created').'</span></h2>
                <h3 class="hl7"><a href="'.$link_open.'"><span class="title">'.htmlspecialchars($this->email->getSubject()).'</span></a></h3>
              </hgroup>
              <ul class="sl2 functions">
                <li class="function">'.pz_screen::getTooltipView('<a class="tooltip trash" href="'.$link_delete.'"></a>', htmlspecialchars(pz_i18n::msg('delete'))).'</li>
              </ul>
            </header>

            <section class="content preview" id="email-content-preview-'.$this->email->getId().'">
              <p>'.pz::cutText($this->email->getBody(), '150').'&nbsp;</p>
            </section>

            <section class="content detail" id="email-content-detail-'.$this->email->getId().'"></section>

            <footer>
              <a class="label labelc'.$this->email->getVar('label_id').'" href="#">Label</a>
            </footer>
          </article>
        ';

        return $return;
    }

    public function getBlockView($p = [])
    {
        /*
              project-status kann
              - status0 -> nicht bearbeitet
              - status1 -> wurde bearbeitet
          */

        $p['linkvars']['email_id'] = $this->email->getId();

        $link_open = "javascript:pz_open_email('".$this->email->getId()."','".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'view']))."')";
        // $link_move_to_project_id = "javascript:pz_open_email('".$this->email->getId()."','".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"move_to_project_id")))."')";

        $link_status_0 = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'update_status', 'email_status' => 0]))."')";
        $link_status_1 = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'update_status', 'email_status' => 1]))."')";

        $link_unread = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'unread']))."')";
        $link_unproject = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'unproject']))."')";

        $link_untrash = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'untrash']))."')";
        $text_untrash = pz_i18n::msg('untrash');
        $link_trash = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'trash']))."')";
        $text_trash = pz_i18n::msg('trash');

        $link_delete = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'delete']))."')";
        $text_delete = pz_i18n::msg('delete');

        $link_forward = static::getAddLink(['forward_email_id' => $this->email->getId()]);
        $link_reply = static::getAddLink(['reply_email_id' => $this->email->getId()]);
        $link_replyall = static::getAddLink(['reply_email_id' => $this->email->getId(), 'reply_all' => 1]);
        $link_print = pz::url('screen', 'emails', 'email', ['mode' => 'view_firstbody', 'email_id' => $this->email->getId()]);


        $image_from = '';
        $image_from_tooltip = '';
        $addresses_from = pz_eml::parseAddressListAsArray($this->email->getFrom());

        $from_tooltip = [];
        foreach($addresses_from as $from) {
            if ( ($from_address = pz_address::getByEmail($from["email"])) ) {
                $from_link = pz::url('screen', 'addresses', 'show_address', array("search_name"=>$from["email"]));
                $from_tooltip[] =  '<a href="'.$from_link.'">&gt;</a> '.htmlspecialchars($from_address->getFullName()).' &lt;'.htmlspecialchars($from["email"]).'&gt; ';
                if ($image_from == "") {
                    $image_from = '<a href="'.$from_link.'"><img src="'.$from_address->getInlineImage().'" width="40" height="40" /></a>';
                }
            } else {
                $from_link = pz::url('screen', 'addresses', 'add_address', array("search_name" => $from["personal"], "address_field_email_value[]" => $from["email"], "address_field_email_label[]" => "WORK", "name" => $from["personal"]));
                $from_tooltip[] =  '<a href="'.$from_link.'">+ </a> '.htmlspecialchars($from["personal"]). " &lt;" . htmlspecialchars($from["email"]).'&gt;';
                if ($image_from == "") {
                    $image_from = '<img src="'.pz_user::getDefaultImage().'" width="40" height="40" />';
                }
            }
        }

        if ($image_from == "") {
            $image_from = '<img src="'.pz_user::getDefaultImage().'" width="40" height="40" />';
        }

        $image_from_tooltip = implode("<br />",$from_tooltip);
        $tooltip_from = pz_screen::getTooltipView($image_from, $image_from_tooltip);



        $image_to = '';
        $image_to_tooltip = '';
        $addresses_to = pz_eml::parseAddressListAsArray($this->email->getTo());

        $to_tooltip = [];
        foreach($addresses_to as $to) {
            if ( ($to_address = pz_address::getByEmail($to["email"])) ) {
                $to_link = pz::url('screen', 'addresses', 'show_address', array("search_name"=>$to["email"]));
                $to_tooltip[] =  '<a href="'.$to_link.'">&gt;</a> '.htmlspecialchars($to_address->getFullName()).' &lt;'.htmlspecialchars($to["email"]).'&gt; ';
                if ($image_to == "") {
                    $image_to = '<a href="'.$to_link.'"><img src="'.$to_address->getInlineImage().'" width="40" height="40" /></a>';
                }
            } else {
                $to_link = pz::url('screen', 'addresses', 'add_address', array("search_name" => $to["personal"], "address_field_email_value[]" => $to["email"], "address_field_email_label[]" => "WORK", "name" => $to["personal"]));
                $to_tooltip[] =  '<a href="'.$to_link.'">+ </a> '.htmlspecialchars($to["personal"]). " &lt;" . htmlspecialchars($to["email"]).'&gt;';
                if ($image_to == "") {
                    $image_to = '<img src="'.pz_user::getDefaultImage().'" width="40" height="40" />';
                }
            }
        }

        if ($image_to == "") {
            $image_to = '<img src="'.pz_user::getDefaultImage().'" width="40" height="40" />';
        }

        $image_to_tooltip = implode("<br />",$to_tooltip);
        $tooltip_to = pz_screen::getTooltipView($image_to, $image_to_tooltip);

        $project_name = pz_i18n::msg('please_select_project_for_email');

        $projects = [];
        $filter = [['field' => 'archived', 'value' => 0]];
        foreach (pz::getUser()->getEmailProjects($filter) as $project) {
            if ($this->email->getProjectid() == $project->getId()) {
                $project_name = $project->getName();
            }

            $link_move_status = "javascript:pz_exec_javascript('".pz::url('screen', 'emails', 'email', array_merge($p['linkvars'], ['mode' => 'move_to_project_id_update_status', 'email_project_id' => $project->getId()]))."')";

            /*
                  $link_move = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"move_to_project_id","email_project_id"=>$project->getId())))."')";
                  <a href="'.$link_move.'"><span class="title">'.pz_i18n::msg("move").'</span></a>
                  pz_i18n::msg("and_finished")
            */

            $projects[] = '<li class="entry first">
			   <a href="'.$link_move_status.'" class="wrapper">
					<div class="links">
  					<span class="title">'.pz_i18n::msg('move').'</span>
	   		  </div>
					<span class="name">'.htmlspecialchars($project->getName()).'</span>
				</a>
				</li>';
            // <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
        }

        $project_class = '';
        if ($this->email->hasProject() == 1) {
            $project_class = ' email-hasproject';
        }

        $readed_class = ' email-unreaded';
        if ($this->email->getReaded() == 1) {
            $readed_class = ' email-readed';
        }

        $attachment_class = ' email-hasnoattachments';
        if ($this->email->hasAttachments()) {
            $attachment_class = ' email-hasattachments';
        }

        $reply_class = '';
        if ($this->email->getRepliedId() > 0) {
            $reply_class = ' active';
        }

        $replyall_class = '';
        if ($this->email->getRepliedId() > 0) {
            $replyall_class = ' active';
        }

        $forward_class = '';
        if ($this->email->getForwardedId() > 0) {
            $forward_class = ' active';
        }

        $status_class = ' email-status-0';
        if ($this->email->getStatus() == 1) {
            $status_class = ' email-status-1';
        }

        $label_class = ' labelc';
        if ($this->email->getProject()) {
            $label_class = ' '.pz_label_screen::getColorClass($this->email->getProject()->getLabelId());
        }

        /*
        <a class="tooltip status status-0" href="'.$link_status_1.'"><span class="tooltip"><span class="inner">'.pz_i18n::msg("mark_as_status_1").'</span></span></a>
        <a class="tooltip status status-1" href="'.$link_status_0.'"><span class="tooltip"><span class="inner">'.pz_i18n::msg("mark_as_status_0").'</span></span></a>
        */

        $function_links = [];

        $function_links['unproject'] = '<li class="function unproject">'.pz_screen::getTooltipView('<a class="unproject" href="'.$link_unproject.'"></a>', pz_i18n::msg('mark_as_unproject')).'</li>';
        $function_links['unread'] = '<li class="function unread">'.pz_screen::getTooltipView('<a class="unread" href="'.$link_unread.'"></a>', pz_i18n::msg('mark_as_unread')).'</li>';
        $function_links['reply'] = '<li class="function reply">'.pz_screen::getTooltipView('<a class="reply'.$reply_class.'" href="'.$link_reply.'"></a>', pz_i18n::msg('reply')).'</li>';
        $function_links['replyall'] = '<li class="function replyall">'.pz_screen::getTooltipView('<a class="replyall'.$replyall_class.'" href="'.$link_replyall.'"></a>', pz_i18n::msg('replyall')).'</li>';
        $function_links['forward'] = '<li class="function forward">'.pz_screen::getTooltipView('<a class="forward'.$forward_class.'" href="'.$link_forward.'"></a>', pz_i18n::msg('forward')).'</li>';
        $function_links['print'] = '<li class="function print">'.pz_screen::getTooltipView('<a class="print" href="javascript:void(0)" onclick="window.open(\''.$link_print.'\'); return false;"></a>', pz_i18n::msg('print')).'</li>';

        $function_links['trash'] = '<li class="function trash">'.pz_screen::getTooltipView('<a class="trash" href="'.$link_trash.'"></a>', $text_trash).'</li>';

        $function_links['untrash'] = '<li class="function untrash">'.pz_screen::getTooltipView('<a class="untrash" href="'.$link_untrash.'"></a>', $text_untrash).'</li>';

        $function_links['delete'] = '<li class="function delete">'.pz_screen::getTooltipView('<a class="delete" href="'.$link_delete.'"></a>', $text_delete).'</li>';

        /*
        $function_links["options"] =
        <li class="last selected option split-v"><span class="selected option">'.pz_i18n::msg("options").'</span>
                          <div class="flyout">
                            <div class="content">
                              <ul class="entries">
                                <li class="entry first"><a href=""><span class="title">'.pz_i18n::msg("spam").'</span></a></li>
                                <li class="entry"><a href=""><span class="title">'.pz_i18n::msg("ham").'</span></a></li>
                              </ul>
                            </div>
                          </div>
                        </li>
        */

        if ($this->email->isTrash()) {
            unset($function_links['trash']);
        } else {
            unset($function_links['untrash']);
            unset($function_links['delete']);
        }

		$subject = pz::cutText($this->email->getSubject(),45);
		if (strlen($subject) != strlen($this->email->getSubject())) { 
				$subject = pz_screen::getTooltipView($subject, nl2br(htmlspecialchars(wordwrap( $this->email->getSubject(), 70, "\n"))));
		}

        $return = '
          <article id="email-'.$this->email->getId().'" class="email block images label email-'.$this->email->getId().$readed_class.$project_class.$status_class.$attachment_class.'">
            <header>
              <div class="grid2col">
                <div class="column first">
                  <figure class="figure-from">'.$tooltip_from.'</figure>
                  <figure class="figure-to">'.$tooltip_to.'</figure>
                  <hgroup class="data">
                    <h2 class="hl7"><span class="name">'.htmlspecialchars(pz::cutText($this->email->getVar('from'))).' </span><span class="info">'.strftime(pz_i18n::msg('show_datetime_normal'), pz::getUser()->getDateTime($this->email->getDateTime())->format('U')).'</span></h2>
                    <h3 class="hl7"><a href="'.$link_open.'"><span class="title">'.($subject).'</span></a></h3>
                  </hgroup>

                 </div>

                <div class="column last">

                  <ul class="sl1 sl1b sl-r">
                    <li class="selected"><span class="email-project-name selected"  onclick="pz_screen_select(this)">'.$project_name.'</span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            '.implode('', $projects).'
                          </ul>
                        </div>
                      </div>
                    </li>
                  </ul>

                  <ul class="sl2 functions">
                    '.implode('', $function_links).'
                  </ul>
                </div>
              </div>
            </header>

            <section class="content preview" id="email-content-preview-'.$this->email->getId().'">
              <p>'.htmlspecialchars(pz::cutText($this->email->getBody(), '110')).'&nbsp;</p>
            </section>

            <section class="content detail" id="email-content-detail-'.$this->email->getId().'"></section>

            <footer>
              <span class="label '.$label_class.'">Label</span>
            </footer>
          </article>
        ';

        return $return;
    }

    /*
        public function getMatrixView($p = array()) {

            $return = '
                  <article>
                <header>
                  <figure><img src="'.pz_user::getDefaultImage().'" width="40" height="40" alt="" /></figure>
                  <hgroup>
                    <h2 class="hl7"><span class="name">'.$this->email->getVar("from").'</span><span class="info">'.$this->email->getVar("date").'</span></h2>
                    <h3 class="hl7"><a href=""><span class="title">'.htmlspecialchars($this->email->getSubject()).'</span></a></h3>
                  </hgroup>
                </header>

                <section class="content">
                  <p>'.$this->email->getVar("description").'</p>
                </section>

                <footer>
                  <ul class="sl2">
                    <li class="selected option"><span class="selected option">Optionen</span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            <li class="entry first"><a href=""><span class="title">Spam</span></a></li>
                            <li class="entry"><a href=""><span class="title">Ham</span></a></li>
                          </ul>
                        </div>
                      </div>
                    </li>
                  </ul>
                  <span class="status email-status status1">E-Mail wurde bearbeitet</span>
                  <span class="label labelc'.$this->email->getVar('label_id').'">Label</span>
                </footer>
              </article>
            ';

            return $return;
        }
    */

    /*
        function getTableView($p = array())
        {

         $return = '
                  <tr>
                    <td><img src="'.pz_user::getDefaultImage().'" width="40" height="40" alt="" /></td>
                    <td><span class="name">'.$this->email->getVar("afrom","plain").'</span></td>
                    <td><span class="info">'.$this->email->getVar("stamp","datetime").'</span></td>
                    <td><a href=""><span class="title">'.htmlspecialchars($this->email->getSubject()).'</span></a></td>

                    <td>
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
                      <span class="status email-status status1">E-Mail wurde bearbeitet</span>
                    </td>
                    <td class="label labelc'.$this->email->getVar('label_id').'"></td>
                  </tr>
            ';

            return $return;
        }
    */

    public function getDetailView($p = [])
    {
        $pz_eml = $this->email->getProzerEml();
        $pz_eml->setMailFilename($this->email->getId());

        $ignore_attachment_elements = [];

        $body = '';
        if ($this->email->hasBodyHTML()) {
            // html und text
            // $body .= 'text and html:';

            // nur html
            // -> html2text
            // $body .= 'only html:';

            $body_text = $pz_eml->getFirstText();
            $body_text = pz_screen::prepareOutput($body_text);
            $body_text = pz_email_screen::prepareQuotes($body_text, '&gt;');
            $body_text = str_replace("\n", '<br />', $body_text);

            $a_view_text_link = pz::url('screen', 'emails', 'email',
                ['email_id' => $this->email->getId(), 'mode' => 'view_firstbody']
            );

            $a_view_html_link = pz::url('screen', 'emails', 'email',
                ['email_id' => $this->email->getId(), 'mode' => 'view_ashtml', 'element_id' => $this->email->getBodyHTMLElement()->getElementId()]
            );

            // iframe = $(\'#email-'.$this->email->getId().' .content-body-html iframe\');iframe.attr(\'src\',\''.$a_view_html_link.'\');iframe.load(function(){ this.style.height = this.contentWindow.document.body.offsetHeight + \'px\'; });

            $body .= '<section class="content-body-navigation">';
            $body .= '<ul class="content-navigation">';
            $body .= '<li class="navigation-text"><a class="active" href="javascript:void(0);" onclick="$(\'#email-'.$this->email->getId().' .content-body-html\').hide();$(\'#email-'.$this->email->getId().' .content-body-text\').show();$(\'#email-'.$this->email->getId().' .content-body-navigation li.navigation-text a\').addClass(\'active\');$(\'#email-'.$this->email->getId().' .content-body-navigation li.navigation-html a\').removeClass(\'active\')">'.pz_i18n::msg('email_textversion').'</a><a href="'.$a_view_text_link.'" target="_blank">'.pz_i18n::msg('email_textversion_popup').'</a></li>';
            $body .= '<li class="navigation-html"><a href="javascript:void(0);" onclick="$(\'#email-'.$this->email->getId().' .content-body-html\').show();$(\'#email-'.$this->email->getId().' .content-body-text\').hide();$(\'#email-'.$this->email->getId().' .content-body-navigation li.navigation-text a\').removeClass(\'active\');$(\'#email-'.$this->email->getId().' .content-body-navigation li.navigation-html a\').addClass(\'active\');$(\'#email-'.$this->email->getId().' .content-body-html iframe\').attr(\'src\',\''.$a_view_html_link.'\').load(function(){this.style.height = (this.contentWindow.document.body.offsetHeight+50) +\'px\';})">'.pz_i18n::msg('email_htmlversion').'</a><a href="'.$a_view_html_link.'" target="_blank">'.pz_i18n::msg('email_htmlversion_popup').'</a></li>';
            $body .= '</ul>';
            $body .= '</section>';

            $body .= '<section class="content">';
            $body .= '<div class="content-body-text" style="display:visible;">'.$body_text.'</div>';
            $body .= '<div class="content-body-html" style="display:none;"><iframe width="100%" height="600"></iframe></div>';
            $body .= '</section>';
        } else {
            // nur text
            // $body .= 'only text:';

            $body_text = $pz_eml->getFirstText();
            $body_text = pz_screen::prepareOutput($body_text);
            $body_text = pz_email_screen::prepareQuotes($body_text, '&gt;');
            $body_text = str_replace("\n", '<br />', $body_text);

            $body .= '<section class="content">'.$body_text.'</section>';
        }

        $attachments = [];
        $as = array_merge([$this->email->getProzerEml()], $this->email->getAttachments());
        /** @var pz_eml $a */
        foreach ($as as $k => $a) {
            $a_download_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'download', 'element_id' => $a->getElementId()]);
            $a_clipboard_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'element2clipboard', 'element_id' => $a->getElementId()]);
            $a_view_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'view_element', 'element_id' => $a->getElementId()]);

            if (!$a->hasParent()) {
                $a_download_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'download_source', 'element_id' => $a->getElementId()]);
                $a_clipboard_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'element_source2clipboard', 'element_id' => $a->getElementId()]);
                $a_view_link = $a_download_link;
            }

            $extension = pz::getExtensionByFilename($a->getFileName());
            $depth = '';
            $depth = str_pad('', $a->getDepth(), '_');
            $depth = str_replace('_', '__ ', $depth);

            // Spaces vermeiden
            $attachment = '';
            $messageBoxId = 'attachment-message-'.$a->getElementId();
            $attachment .= '<li class="attachment"><div id="'.$messageBoxId.'"></div>';
            // $attachment .= '<span class="preview"><img src="'.$a->getInlineImage().'" width="20" height="20" /></span>';


            // echo "<br />".$a->getFileName()."->".htmlspecialchars($a->getFileName())."**";


            $attachment .= '<span class="piped">
				                  <span class="link"><span class="file25 '.$extension.'"></span>'.$depth.'<a onclick="window.open(this.href); return false;" href="'.$a_view_link.'" title="'.htmlspecialchars($a->getFileName()).'">'.htmlspecialchars(pz::cutText($a->getFileName(), 40)).'</a></span>
				                  <span class="name" title="'.htmlspecialchars($a->getContentType()).'">'.htmlspecialchars(pz::cutText($a->getContentType())).'</span>
				                  <span class="info">'.pz::readableFilesize($a->getSize()).'</span>
				                </span>';
            $attachment .= '<ul class="functions">';
            $first = 'first ';
            if ('ics' === $extension) {
                $import_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'import', 'element_id' => $a->getElementId()]);
                $attachment .= '<li class="'.$first.'function"><a class="import" href="javascript:void(0)" onclick="pz_tooltipbox(this, \''.$import_link.'\')">'.pz_i18n::msg('import').'</a></li>';
                $first = '';
            }
            $attachment .= '<li class="'.$first.'function"><a class="download" target="_blank" href="'.$a_download_link.'">'.pz_i18n::msg('download').'</a></li>';
            $attachment .= '<li class="last function">'.pz_screen::getTooltipView('<a class="clipboard" href="javascript:void(0);" onclick="pz_exec_javascript(\''.$a_clipboard_link.'\')">'.pz_i18n::msg('copy_to_clipboard').'</a>', pz_i18n::msg('copy_to_clipboard')).'</li>';
            $attachment .= '</ul>';
            $attachment .= '</li>';
            $attachments[] = $attachment;
        }

        if (count($attachments) > 0) {
            $attachments = '
							<section class="attachments">
							<ul class="attachments">'.implode('', $attachments).'</ul>
							</section>';
        } else {
            $attachments = '';
        }

        $from = strip_tags($this->email->getFrom()).' | '.$this->email->getFromEmail();
        if ($address_from = $this->email->getFromAddress()) {
            $from = $address_from->getFullname().' | '.$this->email->getFromEmail();
        }

        $to = explode(',', $this->email->getToEmails());

        $cc = '';
        if ($this->email->getCcEmails() != '') {
            $cc = explode(',', htmlspecialchars($this->email->getCcEmails()));
            $cc = '<dt class="to">Cc:</dt>
                  <dd class="to">'.pz_screen::prepareOutput(implode(', ', $cc)).'</dd>';
        }

        $bcc = '';
        if ($this->email->getBccEmails() != '') {
            $bcc = explode(',', htmlspecialchars($this->email->getBccEmails()));
            $bcc = '<dt class="to">Bcc:</dt>
                  <dd class="bcc">'.pz_screen::prepareOutput(implode(', ', $bcc)).'</dd>';
        }

        $email_header = '';
        $header_show_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'view_header']);
        $email_header = '
			<div class="email-header-link">
			<a class="email-show_header" href="javascript:void(0);" onclick="
				$(this).parent().find(\'.email-show_header\').hide();
				$(this).parent().find(\'.email-hide_header\').show();
				pz_loadPage(\'#email-'.$this->email->getId().' .email-header\',\''.$header_show_link.'\');">'.pz_i18n::msg('email_show_header').'</a>
			<a class="email-hide_header" href="javascript:void(0);" onclick="
				$(this).parent().find(\'.email-show_header\').show();
				$(this).parent().find(\'.email-hide_header\').hide();
				$(\'#email-'.$this->email->getId().' .email-header\').hide();" style="display:none;">'.pz_i18n::msg('email_hide_header').'</a>
			</div>
			<div class="email-header"></div>
		';

        $return = '

	<! --------------------------------------------------------------------- E-Mail lesen //-->

        <div class="email-read">
          <header>

				'.$email_header.'

            <dl class="data">
              <dt class="from">From:</dt>
              <dd class="from">'.pz_screen::prepareOutput($from).'</dd>
              <dt class="to">To:</dt>
              <dd class="to">'.pz_screen::prepareOutput(implode(', ', $to)).'</dd>
              '.$cc.'
              '.$bcc.'
            </dl>

          </header>

          '.$attachments.'
          '.$body.'

          <footer>
          <!--
            <ul class="actions">
              <li class="first action"><a class="close" href="">Close</a></li>
              <li class="action"><a class="up" href="">Up</a></li>
              <li class="last action"><a class="down" href="">Down</a></li>
            </ul>
          //-->
          </footer>
        </div>
		';

        // $return .= $this->getDebugView();

        return '
			<section class="content detail" id="email-content-detail-'.$this->email->getId().'">
              '.$return.'
            </section>';
    }

    public function getImportFlyoutView(pz_eml $element, array $importStatus, array $p = [])
    {
        $event = pz_sabre_caldav_backend::getPreview($element->getBody());

        $calendar_screen = new pz_calendar_event_screen($event);

        $class = 'email-'.$this->email->getId().'-element-'.$element->getElementId().'-import';

        $actions = '';
        $buttons = [];

        /** @var null|pz_calendar_event $existsingEvent */
        $existsingEvent = $importStatus['event'];
        $import_link = pz::url('screen', 'emails', 'email', ['email_id' => $this->email->getId(), 'mode' => 'import', 'action' => 1, 'element_id' => $element->getElementId()]);
        if ('CANCEL' == $importStatus['method']) {
            $actions .= '<p class="yform-warning">'.pz_i18n::msg('import_info_cancel').'</p>';

            if ($existsingEvent && pz::getUser()->getEventDeletePerm($importStatus['event'])) {

                $buttons[] = '<li><a class="bt17" href="javascript:void(0);" onclick="check = confirm(\''.
                    str_replace(array("'","\n","\r"),array("","",""),pz_i18n::msg("calendar_event_confirm_delete",htmlspecialchars($existsingEvent->getTitle()))).'\'); if (check == true) pz_loadPage(\'.'.$class.'\',\''.$import_link.'\')">'.pz_i18n::msg("import_delete").'</a></li>';
            }
        } elseif (!$importStatus['importable']) {
            if ($existsingEvent && $existsingEvent->getUserId() != pz::getUser()->getId()) {
                $actions .= pz_i18n::msg('import_error_exists_other_user');
            } else {
                $actions .= pz_i18n::msg('import_error_exists_newer');
            }
        } elseif ($importStatus['event']) {
            $buttons[] = '<li><a class="bt5" href="javascript:void(0);" onclick="pz_loadPage(\'.'.$class.'\',\''.$import_link.'\')">'.pz_i18n::msg("import_update").'</a></li>';
        } else {
            $projects = [];
            $first = ' first';
            foreach (pz::getUser()->getCalendarCalProjects() as $project) {
                $projects[] = '
                    <li class="entry'.$first.'">
                        <a href="javascript:pz_loadPage(\'.'.$class.'\', \''.$import_link.'&amp;project_id='.$project->getId().'\')" class="wrapper">
                            <span class="name">'.htmlspecialchars($project->getName()).'</span>
                        </a>
                    </li>';
                $first = '';
            }
            $actions .= '<ul class="sl1">
                    <li class="selected"><span class="email-project-name selected"  onclick="pz_screen_select(this)">'.pz_i18n::msg('import_create').'</span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            '.implode('', $projects).'
                          </ul>
                        </div>
                      </div>
                    </li>
                  </ul>';
        }

        if ($existsingEvent && pz::getUser()->getEventViewPerm($existsingEvent)) {
            $view_link = pz::url('screen', 'calendars', '', ['day' => $existsingEvent->getFrom()->format('Ymd')]);
            $buttons[] = '<li><a class="bt5" href="'.$view_link.'">'.pz_i18n::msg("import_view").'</a></li>';
        }

        if ($buttons) {
            $actions .= '<ul class="buttons">'.implode($buttons).'</ul>';
        }

        $p['actions'] = $actions;

        $view = $calendar_screen->getFlyoutEventView($p, true, true);

        return '<div class="design1col '.$class.'">'.$view.'</div>';
    }

    // ------------------------------------------------------------------- LINKS

    public static function getAddLink($linkvar = [])
    {
        return pz::url('screen', 'emails', 'create', $linkvar);
    }

    // ------------------------------------------------------------------- FORMS


    public static function getEmailsSearchForm($p = [], $ignore_fields = [])
    {
        $link_refresh = pz::url(
            'screen',
            $p['controll'],
            $p['function'],
            array_merge($p['linkvars'], ['mode' => 'emails_search', 'project_ids' => '___value_ids___'])
        );

        if (!isset($p['title_search'])) {
            $p['title_search'] = pz_i18n::msg('search_for_emails');
        }

        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.$p['title_search'].'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        $yform->setObjectparams('real_field_names', true);
        $yform->setObjectparams('form_showformafterupdate', true);
        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('".$p['layer_list']."','emails_search_form','".pz::url('screen', $p['controll'], $p['function'], $p['linkvars'])."')");
        $yform->setObjectparams('form_id', 'emails_search_form');

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl', 'runtime']);
        // $yform->setValueField("text",array("search_name",pz_i18n::msg("search_email_fulltext")));

        if (!in_array('fulltext', $ignore_fields)) {
            $yform->setValueField('text', ['search_fulltext', pz_i18n::msg('search_email_fulltext')]);
        }
        if (!in_array('subject', $ignore_fields)) {
            $yform->setValueField('text', ['search_subject', pz_i18n::msg('search_email_subject')]);
        }
        if (!in_array('from', $ignore_fields)) {
            $yform->setValueField('text', ['search_from', pz_i18n::msg('search_email_from')]);
        }
        if (!in_array('to', $ignore_fields)) {
            $yform->setValueField('text', ['search_to', pz_i18n::msg('search_email_to')]);
        }

        // $yform->setValueField('select',array('search_label', pz_i18n::msg('label'), pz_labels::getAsString(),"","",1,pz_i18n::msg("please_choose")));
        // $yform->setValueField('select',array('search_customer', pz_i18n::msg('customer'), pz_customers::getAsString(),"","",1,pz_i18n::msg("please_choose")));
        // $yform->setValueField('select',array('search_account_id', pz_i18n::msg('email_account'), pz::getUser()->getEmailaccountsAsString(),"","",1,pz_i18n::msg("please_choose")));

        if (!in_array('date_from', $ignore_fields)) {
            $yform->setValueField('pz_date_screen', ['search_date_from', pz_i18n::msg('search_date_from')]);
        }
        if (!in_array('date_to', $ignore_fields)) {
            $yform->setValueField('pz_date_screen', ['search_date_to', pz_i18n::msg('search_date_to')]);
        }

        if (!in_array('project_id', $ignore_fields)) {
            $filter = [['field' => 'archived', 'value' => 0]];
            $projects = pz::getUser()->getEmailProjects($filter);
            $yform->setValueField('select', ['search_project_id', pz_i18n::msg('project'), pz_project::getProjectsAsString($projects), '', '', 0, pz_i18n::msg('please_choose')]);
        }

        if (!in_array('unread', $ignore_fields)) {
            $yform->setValueField('checkbox', ['search_unread', pz_i18n::msg('search_email_unread')]);
        }
        if (!in_array('my', $ignore_fields)) {
            $yform->setValueField('checkbox', ['search_my', pz_i18n::msg('search_email_my')]);
        }
        if (!in_array('noprojects', $ignore_fields)) {
            $yform->setValueField('checkbox', ['search_noprojects', pz_i18n::msg('search_email_noprojects')]);
        }
        if (!in_array('intrash', $ignore_fields)) {
            $yform->setValueField('checkbox', ['search_intrash', pz_i18n::msg('search_email_intrash')]);
        }

        $yform->setValueField('submit', ['submit', pz_i18n::msg('search'), '', 'search']);

        $return .= $yform->getForm();

        $return = '<div id="emails_search" class="design1col yform-search" data-url="'.$link_refresh.'">'.$return.'</div>';
        return $return;
    }

    public static function getAddForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('email_add').'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $accounts = pz_email_account::getAsArray(pz::getUser()->getId());

        if (count($accounts) == 0) {
            $return = $header.'<p class="yform-warning">'.pz_i18n::msg('email_account_not_exists').'</p>';
        } else {
            if (!($account_id_default = pz::getUser()->getDefaultEmailAccountId())) {
                $account_id_default = 0;
            }

            $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('email_form','email_add_form','".pz::url('screen', 'emails', 'create', ['mode' => 'add_email'])."')");
            $yform->setObjectparams('form_id', 'email_add_form');
            $yform->setObjectparams('form_showformafterupdate', 1);
            $yform->setObjectparams('real_field_names', true);

            $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);

            $yform->setValueField('select', ['account_id', pz_i18n::msg('email_account'), $accounts, '', $account_id_default, 0]);
            $yform->setValueField('pz_email_screen', ['to', pz_i18n::msg('email_to')]);
            $yform->setValueField('pz_email_screen', ['cc', pz_i18n::msg('email_cc')]);
            $yform->setValueField('pz_email_screen', ['bcc', pz_i18n::msg('email_bcc')]);

            $yform->setValueField('text', ['subject', pz_i18n::msg('email_subject')]);
            $yform->setValueField('pz_attachment_screen', ['clip_ids', pz_i18n::msg('email_attachments')]);
            $yform->setValueField('pz_email_textarea', ['body', pz_i18n::msg('email_body')]);
            // $yform->setValueField("textarea",array("html",pz_i18n::msg("email_html"),"","0"));

            $filter = [['field' => 'archived', 'value' => 0]];
            $projects = pz::getUser()->getEmailProjects($filter);
            $yform->setValueField('select', ['project_id', pz_i18n::msg('project'), pz_project::getProjectsAsString($projects), '', '', 0, pz_i18n::msg('please_choose')]);

            $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
            $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

            $yform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);
            $yform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);

            $yform->setValueField('hidden', ['reply_id', rex_request('reply_id', 'int', 0)]);
            $yform->setValueField('hidden', ['forward_id', rex_request('forward_id', 'int', 0)]);

            $yform->setValueField('checkbox', ['draft', pz_i18n::msg('save_as_draft')]);
            $yform->setValueField('hidden', ['user_id', pz::getUser()->getId()]);

            if (rex_request('draft', 'int') != 1) {
                $yform->setValidateField('empty', ['subject', pz_i18n::msg('error_email_subject_empty')]);
                $yform->setValidateField('empty', ['body', pz_i18n::msg('error_email_body_empty')]);
                $yform->setValidateField('empty', ['to', pz_i18n::msg('error_email_to_empty')]);
            }

            // if(rex_request("reply_id","int",0)>0)
            // 	$yform->setValueField("checkbox",array("move_replymail_to_project",pz_i18n::msg("move_replymail_to_project"),0,1,"no_db"));

            $yform->setActionField('db', ['pz_email']);

            $return = $yform->getForm();

            if ($yform->getObjectparams('actions_executed')) {
                if (rex_request('draft', 'string') != '1') {
                    $email_id = $yform->getObjectparams('main_id');

                    if ($email = pz_email::get($email_id)) {
                        if (!$email->sendDraft()) {
                            $return = $header.'<p class="yform-warning">'.pz_i18n::msg('email_send_failed').'</p>'.$return;
                            $email->delete();
                        } else {

                            // TODO ..
                            // move_replymail_to_project

                            $return = $header.'<p class="yform-info">'.pz_i18n::msg('email_send').'</p>';
                            $return .= pz_screen::getJSUpdatePage(pz::url('screen', 'emails', 'inbox'));
                        }
                    }
                } else {
                    $return = $header.'<p class="yform-info">'.pz_i18n::msg('email_saved_in_drafts').'</p>';
                }

                $return .= pz_screen::getJSUpdateLayer('emails_list', pz::url('screen', 'emails', 'create', ['mode' => 'list']));
                // $return .= "4".pz_screen::getJSLoadFormPage('emails_list','email_search_form',pz::url('screen','emails',$p["function"],array("mode"=>'list')));
            } else {
                $return = $header.$return;
            }
        }

        $return = '<div id="email_form" class="design2col"><div id="email_add" class="design2col yform-add">'.$return.'</div></div>';

        return $return;
    }

    public function getEditForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('email_edit').'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $accounts = pz_email_account::getAsArray(pz::getUser()->getId());

        if (count($accounts) == 0) {
            $return = $header.'<p class="yform-warning">'.pz_i18n::msg('email_account_not_exists').'</p>';
        } else {
            $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('email_edit','email_edit_form','".pz::url('screen', 'emails', 'create', ['mode' => 'edit_email'])."')");
            $yform->setObjectparams('form_id', 'email_edit_form');
            $yform->setObjectparams('form_showformafterupdate', 1);
            $yform->setObjectparams('real_field_names', true);

            $yform->setObjectparams('main_table', 'pz_email');
            $yform->setObjectparams('main_id', $this->email->getId());
            $yform->setObjectparams('main_where', 'id='.$this->email->getId());
            $yform->setObjectparams('getdata', true);
            $yform->setHiddenField('email_id', $this->email->getId());

            $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);

            $yform->setValueField('select', ['account_id', pz_i18n::msg('email_account'), $accounts, '', '', 0]);
            $yform->setValueField('pz_email_screen', ['to', pz_i18n::msg('email_to')]);
            $yform->setValueField('pz_email_screen', ['cc', pz_i18n::msg('email_cc')]);
            $yform->setValueField('pz_email_screen', ['bcc', pz_i18n::msg('email_bcc')]);

            $yform->setValueField('text', ['subject', pz_i18n::msg('email_subject'), '', '0']);
            $yform->setValueField('pz_attachment_screen', ['clip_ids', pz_i18n::msg('email_attachments')]);
            $yform->setValueField('pz_email_textarea', ['body', pz_i18n::msg('email_body')]);
            // $yform->setValueField("textarea",array("html",pz_i18n::msg("email_html"),"","0"));
            $filter = [['field' => 'archived', 'value' => 0]];
            $projects = pz::getUser()->getEmailProjects($filter);
            $yform->setValueField('select', ['project_id', pz_i18n::msg('project'), pz::getProjectsAsArray($projects), '', '', 0, pz_i18n::msg('please_choose')]);

            $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
            $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

            $yform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);
            $yform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
            $yform->setValueField('checkbox', ['draft', pz_i18n::msg('save_as_draft')]);
            $yform->setValueField('hidden', ['user_id', pz::getUser()->getId()]);

            if (rex_request('draft', 'int') != 1) {
                $yform->setValidateField('empty', ['subject', pz_i18n::msg('error_email_subject_empty')]);
                $yform->setValidateField('empty', ['body', pz_i18n::msg('error_email_body_empty')]);
                $yform->setValidateField('empty', ['to', pz_i18n::msg('error_email_to_empty')]);
            }

            $yform->setActionField('db', ['pz_email', 'id='.$this->email->getId()]);

            $return = $yform->getForm();

            if ($yform->getObjectparams('actions_executed')) {
                if (rex_request('draft', 'string') != '1') {
                    if ($email = pz_email::get($this->email->getId())) {
                        if (!$email->sendDraft()) {
                            $return = $header.'<p class="yform-warning">'.pz_i18n::msg('email_send_failed_saved_in_drafts').'</p>'.$return;
                        } else {
                            $return = $header.'<p class="yform-info">'.pz_i18n::msg('email_send').'</p>';
                        }
                    }
                } else {
                    $return = $header.'<p class="yform-info">'.pz_i18n::msg('email_saved_in_drafts').'</p>'.$return;
                }
            } else {
                $return = $header.$return;
            }
        }

        $return .= pz_screen::getJSUpdateLayer('emails_list', pz::url('screen', 'emails', 'create', ['mode' => 'list']));
        $return = '<div id="email_form" class="design2col"><div id="email_edit" class="design2col yform-add">'.$return.'</div></div>';

        return $return;
    }

    static public function prepareQuotes($text, $quote = '>')
    {
        $regex = "/^((?:\h*(?:".preg_quote($quote, '/').")+\h*)*)(.*)$/m";
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        $indent = 0;
        $text = '';
        foreach ($matches as $match) {
            $newIndent = substr_count($match[1], $quote);
            $diff = $newIndent - $indent;
            if ($diff > 0) {
                $text .= str_repeat('<div class="email-quote">', $diff);
            } elseif ($diff < 0) {
                $text .= str_repeat('</div>', -$diff);
            }
            $text .= $match[2] . "\n";
            $indent = $newIndent;
        }
        $text .= str_repeat('</div>', $indent);
        return $text;
    }

}

/*
    $return = '


<! --------------------------------------------------------------------- E-Mail schreiben //-->
    <div class="design2col">
    <div class="email email-write">
      <form action="" method="">
      <header>
        <div class="grid2col">
          <div class="column first">

            <dl class="data">
              <dt class="from">From:</dt>
              <dd class="from">
                <ul class="sl1">
                  <li class="first last selected"><span class="selected">E-Mail-Adresse auswählen</span>
                    <div class="flyout">
                      <div class="content">
                        <ul class="entries">
                          <li class="entry first"><a href=""><span class="title">jan@yakamara.de</span></a></li>
                          <li class="entry"><a href=""><span class="title">ehe@janundlisa.de</span></a></li>
                          <li class="entry"><a href=""><span class="title">info@tarzanundlisa.de</span></a></li>
                        </ul>
                      </div>
                    </div>
                  </li>
                </ul>
              </dd>

              <dt class="to"><label for="">To:</label></dt>
              <dd class="to"><input type="text" name="" value="" /><a class="tooltip add bt9" href=""><span class="tooltip"><span class="inner">Add Recipient</span></span></a></dd>

              <dt class="copy"><label for="">Copy</label></dt>
              <dd class="copy"><input type="text" name="" value="" /><a class="tooltip add bt9" href=""><span class="tooltip"><span class="inner">Add Copy Recipient</span></span></a></dd>

              <dt class="subject"><label for="">Subject</label></dt>
              <dd class="subject"><input type="text" name="" value="" /></dd>
            </dl>

          </div>

          <div class="column last">
            <ul class="sl1">
              <li class="selected"><span class="selected">Bitte wählen Sie ein Projekt...</span>
                <div class="flyout">
                  <div class="content">
                    <ul class="entries">
                      <li class="entry first"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                      <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                      <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                      <li class="entry last"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                    </ul>
                  </div>
                </div>
              </li>
            </ul>

            <ul class="sl2 functions tooltip">
              <li class="first function"><a class="attachment tooltip" href=""><span class="tooltip"><span class="inner">Add an Attachment</span></span></a></li>

              <li class="last selected option split-v"><span class="selected option">Optionen</span>
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
          </div>
        </div>
      </header>

      <section class="attachments">
        <ul class="attachments entries">
          <li class="first attachment entry"><span class="name">Golfen 004.jpg</span><span class="info">5.04 MB</span>
            <ul class="functions">
              <li class="last function"><a class="delete" href="">Delete</a></li>
            </ul>
          </li>
          <li class="attachment entry"><span class="name">Golfen 004.jpg</span><span class="info">8.04 MB</span>
            <ul class="functions">
              <li class="last function"><a class="delete" href="">Delete</a></li>
            </ul>
          </li>
          <li class="last attachment entry"><span class="name">Golfen 004.jpg</span><span class="info">10.33 MB</span>
            <ul class="functions">
              <li class="last function"><a class="delete" href="">Delete</a></li>
            </ul>
          </li>
        </ul>
      </section>

      <section class="editor">
        Editor
      </section>

      <section class="content">
        <textarea name=""></textarea>
      </section>

      <footer>
        <ul class="actions">
          <li class="first action"><a class="close" href="">Close</a></li>
          <li class="action"><a class="up" href="">Up</a></li>
          <li class="last action"><a class="down" href="">Down</a></li>
        </ul>
      </footer>

      </form>
    </div>

  </div>';

    return $return;

    */
