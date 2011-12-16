<?php

class pz_email_screen{

	public $email;
	
	function __construct($email) 
	{
		$this->email = $email;
	}




	// ------------------ LIST VIEWS

	static function getInboxListView($emails, $p = array())
	{
		return pz_email_screen::getEmailsBlockView($emails,$p);
	}

	static function getOutboxListView($emails, $p = array())
	{
		return pz_email_screen::getEmailsBlockView($emails,$p);
	}

	static function getDraftsListView($emails, $p = array())
	{
		$p["title"] = rex_i18n::msg("email_drafts");
		$p["layer"] = "emails_list";
		$paginate_screen = new pz_paginate_screen($emails);
		$content = $paginate_screen->getPlainView($p);
		
		$list = '';
		$first = ' first';
		foreach($paginate_screen->getCurrentElements() as $email) {		
			if($e = new pz_email_screen($email)) {
				$list .= '<li class="lev1 entry entry-email'.$first.'">'.$e->getDraftView($p).'</li>';
				if($first == '')
					$first = ' first';
				else
					$first = '';
			}
		}
		
		$content = $content.'<ul class="entries view-block clearfix">'.$list.'</ul>';
	
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		
		$link_refresh = pz::url("screen",$p["controll"],$p["function"],
			array_merge(
				$p["linkvars"],
				array(
					"mode"=>"list",
					"project_ids" => "___value_ids___"	
				)
			)
			);
		
		return '<div id="emails_list" class="design1col" data-url="'.$link_refresh.'">'.$f->parse('pz_screen_list').'</div>';
		
	}
	
	static function getSpamListView($emails, $p = array())
	{
		return pz_email_screen::getEmailsBlockView($emails,$p);
	}

	static function getTrashListView($emails, $p = array())
	{
		return pz_email_screen::getEmailsBlockView($emails,$p);
	}

	static function getEmailsBlockView($emails, $p = array())
	{
		$p["layer"] = "emails_list";
		$paginate_screen = new pz_paginate_screen($emails);
		$content = $paginate_screen->getPlainView($p);
		
		$list = '';
		$first = ' first';
		foreach($paginate_screen->getCurrentElements() as $email) {		
			if($e = new pz_email_screen($email)) {
				$list .= '<li class="lev1 entry entry-email'.$first.'">'.$e->getBlockView($p).'</li>';
				if($first == '')
					$first = ' first';
				else
					$first = '';
			}
		}
		
		$content = $content.'<ul class="entries view-block clearfix">'.$list.'</ul>';
	
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		
		$link_refresh = pz::url("screen",$p["controll"],$p["function"],
			array_merge(
				$p["linkvars"],
				array(
					"mode"=>"list",
					"project_ids" => "___value_ids___"	
				)
			)
			);
		
		$return = $f->parse('pz_screen_list');
		if(count($emails) == 0) {
				$return .= '<div class="xform-warning">'.rex_i18n::msg("no_emails_found").'</div>';
		}
		
		return '<div id="emails_list" class="design2col" data-url="'.$link_refresh.'">'.$return.'</div>';
		
	}

	static function getEmailsMatrixView($emails, $p = array())
	{
		$p["layer"] = "emails_list";
		$paginate_screen = new pz_paginate_screen($emails);
		$paginate_screen->setListAmount(9);
		$content = $paginate_screen->getPlainView($p);
		
		$first = ' first';
		foreach($paginate_screen->getCurrentElements() as $email) {
			if($e = new pz_email_screen($email)) {
				$content .= '<li class="lev1 entry'.$first.'">'.$e->getMatrixView($p).'</li>';
				if($first == '')
					$first = ' first';
				else
					$first = '';
			}
		}
		$content = '<ul class="entries view-matrix clearfix">'.$content.'</ul>';
	
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		
		$return = $f->parse('pz_screen_list');
		if(count($emails) == 0) {
				$return .= '<div class="xform-warning">'.rex_i18n::msg("no_emails_found").'</div>';
		}
		
		return '<div id="emails_list" class="design3col">'.$return.'</div>';
		
	}







	// ------------------ VIEWS

	public function getDebugView($p = array())
	{
    	$pz_eml = new pz_eml($this->email->getEml());
		return $pz_eml->getDebugInfo();
	}

	public function getDraftView($p = array())
	{
    
    	$p["linkvars"]["email_id"] = $this->email->getId();
    
    	$link_open = "javascript:pz_loadPage('email_form','".pz::url("screen","emails","create",array_merge($p["linkvars"],array("mode" => "edit_email", "email_id"=>$this->email->getId())))."')";
    	
    	$link_delete = "javascript:pz_exec_javascript('".pz::url("screen","emails","create",array_merge($p["linkvars"],array("mode"=>"delete_email")))."')";

		$image_from_address = pz_user::getDefaultImage();
		if($address = $this->email->getFromAddress())
		{
			$image_from_address = $address->getInlineImage();
		}

		$return = '
		  <article id="email-'.$this->email->getId().'" class="email-'.$this->email->getId().'">
            <header>
              <div class="grid2col">
                <div class="column first">
                  <figure><img src="'.$image_from_address.'" width="40" height="40" alt="" /></figure>
                  <hgroup>
                    <h2 class="hl7"><span class="name">'.$this->email->getVar("from").'</span><span class="info">'.$this->email->getVar("created").'</span></h2>
                    <h3 class="hl7"><a href="'.$link_open.'"><span class="title">'.$this->email->getSubject().'</span></a></h3>
                  </hgroup>
                </div>
                
                <div class="column last">
                  <ul class="sl2 functions">
                    <li class="function"><a class="tooltip trash" href="'.$link_delete.'"><span class="tooltip"><span class="inner">'.rex_i18n::msg("delete").'</span></span></a></li>
                  </ul>
                </div>
              </div>
            </header>
            
            <section class="content preview" id="email-content-preview-'.$this->email->getId().'">
              <p>'.pz::cutText($this->email->getBody(),"60").'&nbsp;</p>
            </section>
            
            <section class="content detail" id="email-content-detail-'.$this->email->getId().'"></section>
            
            <footer>
              <a class="label labelc'.$this->email->getVar('label_id').'" href="#">Label</a>
            </footer>
          </article>
        ';
	
		return $return;
	}


	public function getBlockView($p = array())
	{
    
	    /*
	      project-status kann 
	      - status0 -> nicht bearbeitet
	      - status1 -> wurde bearbeitet
	    */
    
    	$p["linkvars"]["email_id"] = $this->email->getId();
    
    	$link_open = "javascript:pz_open_email('".$this->email->getId()."','".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"view")))."')";
    	// $link_move_to_project_id = "javascript:pz_open_email('".$this->email->getId()."','".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"move_to_project_id")))."')";
    	
    	$status = 1;
    	if($this->email->getStatus() == 1)
    		$status = 0;
		$link_status = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"update_status","email_status"=>$status)))."')";
		
		$link_unread = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"unread")))."')";
		$link_unproject = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"unproject")))."')";
		
		if($this->email->isTrash()) {
			$link_trash = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"untrash")))."')";
			$text_trash = rex_i18n::msg("untrash");
	    }else {
			$link_trash = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"trash")))."')";
			$text_trash = rex_i18n::msg("trash");
	    }
	    
		$link_forward = static::getAddLink(array("forward_email_id"=>$this->email->getId()));
		$link_reply = static::getAddLink(array("reply_email_id"=>$this->email->getId()));
		$link_replyall = static::getAddLink(array("reply_email_id"=>$this->email->getId(),"reply_all" => 1));
		$link_print = pz::url();

		$image_from_address = pz_user::getDefaultImage();
		if($address = $this->email->getFromAddress())
		{
			$image_from_address = $address->getInlineImage();
		}

		$project_name = rex_i18n::msg('please_select_project_for_email');

		$projects = array();
		foreach(pz::getUser()->getEmailProjects() as $project)
		{
			if($this->email->getProjectid() == $project->getId())
				$project_name = $project->getName();
				
			$link_move_status = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"move_to_project_id_update_status","email_project_id"=>$project->getId())))."')";
			$link_move = "javascript:pz_exec_javascript('".pz::url("screen","emails","email",array_merge($p["linkvars"],array("mode"=>"move_to_project_id","email_project_id"=>$project->getId())))."')";
			$projects[] = '<li class="entry first">
			   <div class="wrapper">
					<div class="links">
  					<a href="'.$link_move.'"><span class="title">'.rex_i18n::msg("move").'</span></a><a href="'.$link_move_status.'"><span class="title">'.rex_i18n::msg("and_finished").'</span></a>
	   		  </div>
					<span class="name">'.$project->getName().'</span>
				</div>
				</li>';
			// <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
		}

		$project_class = "";
		if($this->email->hasProject() == 1)
			$project_class = ' email-hasproject';

		$readed_class = " email-unreaded";
		if($this->email->getReaded() == 1)
			$readed_class = ' email-readed';

		$trash_class = "trash";
		if($this->email->isTrash())
			$trash_class = "untrash";

		$reply_class = "";
		if($this->email->getRepliedId() > 0)
			$reply_class = " active";

		$replyall_class = "";
		if($this->email->getRepliedId() > 0)
			$replyall_class = " active";

		$forward_class = "";
		if($this->email->getForwardedId() > 0)
			$forward_class = " active";

		$return = '
		  <article id="email-'.$this->email->getId().'" class="email-'.$this->email->getId().$readed_class.$project_class.'">
            <header>
              <div class="grid2col">
                <div class="column first">
                  <figure><img src="'.$image_from_address.'" width="40" height="40" alt="" /></figure>
                  <hgroup>
                    <h2 class="hl7"><span class="name">'.$this->email->getVar("from").'</span><span class="info">'.$this->email->getVar("created").'</span></h2>
                    <h3 class="hl7"><a href="'.$link_open.'"><span class="title">'.$this->email->getSubject().'</span></a></h3>
                  </hgroup>
                  <a class="tooltip status status'.$this->email->getStatus().' email-status-'.$this->email->getId().'" href="'.$link_status.'"><span class="tooltip"><span class="inner">E-Mail wurde bearbeitet</span></span></a>
                 </div>
                
                <div class="column last">

                  <ul class="sl1 sl1b sl-r">
                    <li class="selected"><span class="email-project-name selected">'.$project_name.'</span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            '.implode("",$projects).'
                          </ul>
                        </div>
                      </div>
                    </li>
                  </ul>
                
                  <ul class="sl2 functions">
                    <li class="function unproject"><a class="tooltip unproject" href="'.
                    	$link_unproject.'"><span class="tooltip"><span class="inner">'.rex_i18n::msg("mark_as_unproject").'</span></span></a></li>
                    <li class="function unread"><a class="tooltip unread" href="'.
                    	$link_unread.'"><span class="tooltip"><span class="inner">'.rex_i18n::msg("mark_as_unread").'</span></span></a></li>
                    <li class="function reply"><a class="tooltip reply'.$reply_class.'" href="'.$link_reply.'">'.
						'<span class="tooltip"><span class="inner">'.rex_i18n::msg("reply").'</span></span></a></li>
                    <li class="function replyall"><a class="tooltip replyall'.$replyall_class.'" href="'.$link_replyall.'">'.
						'<span class="tooltip"><span class="inner">'.rex_i18n::msg("replyall").'</span></span></a></li>
                    <li class="function forward"><a class="tooltip forward'.$forward_class.'" href="'.$link_forward.'">'.
						'<span class="tooltip"><span class="inner">'.rex_i18n::msg("forward").'</span></span></a></li>
                    <li class="function print"><a class="tooltip print" href="'.$link_print.'"><span class="tooltip">'.
						'<span class="inner">'.rex_i18n::msg("print").'</span></span></a></li>
                    <li class="function '.$trash_class.'"><a class="tooltip '.$trash_class.'" href="'.$link_trash.'"><span class="tooltip">'.
						'<span class="inner">'.$text_trash.'</span></span></a></li>
                    <li class="last selected option split-v"><span class="selected option">'.rex_i18n::msg("options").'</span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            <li class="entry first"><a href=""><span class="title">'.rex_i18n::msg("spam").'</span></a></li>
                            <li class="entry"><a href=""><span class="title">'.rex_i18n::msg("ham").'</span></a></li>
                          </ul>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </header>
            
            <section class="content preview" id="email-content-preview-'.$this->email->getId().'">
              <p>'.pz::cutText($this->email->getBody(),"60").'&nbsp;</p>
            </section>
            
            <section class="content detail" id="email-content-detail-'.$this->email->getId().'"></section>
            
            <footer>
              <a class="label labelc'.$this->email->getVar('label_id').'" href="#">Label</a>
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
                <h3 class="hl7"><a href=""><span class="title">'.$this->email->getSubject().'</span></a></h3>
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
                <td><a href=""><span class="title">'.$this->email->getSubject().'</span></a></td>
                
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



	public function getDetailView($p = array())
	{

    	$pz_eml = new pz_eml($this->email->getEml());
		$body = $pz_eml->getFirstText();
		$body = $this->prepareOutput($body);
		$body = str_replace("\n","<br />",$body);
		
		$attachments = array();

		$as = $pz_eml->getAttachmentElements();
		$first = "first ";
		$last = "";
		
		// $as[] = $pz_eml;
		
		foreach($as as $k => $a)
		{
		
			$a_download_link = pz::url("screen","emails","email",array("email_id"=>$this->email->getId(),"mode"=>"download","element_id"=>$a->getElementId()));
			$a_view_link = pz::url("screen","emails","email",array("email_id"=>$this->email->getId(),"mode"=>"view_element","element_id"=>$a->getElementId()));
		
			if(count($as) == ($k+1)) $last = "last";
			$attachments[] = '<li class="'.$first.$last.' attachment entry">
				<span class="preview"><img src="'.$a->getInlineImage().'" width="20" height="20" /></span>
				<span class="name"><a onclick="window.open(this.href); return false;" href="'.$a_view_link.'">'.$a->getFileName().'</a></span>
				<span class="type">'.$a->getContentType().'</span>
				<span class="info">'.pz::readableFilesize($a->getSize()).'</span>
                <ul class="functions">
                  <li class="first function"><a class="download" target="_blank" href="'.$a_download_link.'">Download</a></li>
                  <li class="last function"><a class="clipboard" href="">Clipboard</a></li>
                </ul>
              </li>';
			$first = "";
		}
		
		
		if(count($attachments)>0)
		{
			$attachments = '
							<section class="attachments">
							<ul class="attachments entries">'.implode("",$attachments).'</ul>
							</section>';
		}else
		{
			$attachments = '';
		}

		$from = strip_tags($this->email->getFrom()).' | '.$this->email->getFromEmail();
		if($address_from = $this->email->getFromAddress())
		{
			$from = $address_from->getFullname().' | '.$this->email->getFromEmail();
		}
		
		$to = explode(",",$this->email->getToEmails());

		$cc = "";
		if($this->email->getCcEmails() != "") {
			$cc = explode(",",htmlspecialchars($this->email->getCcEmails()));
			$cc = '<dt class="to">Cc:</dt>
                  <dd class="to">'.$this->prepareOutput(implode(", ",$cc)).'</dd>';
		}

		$return = '
	
	<! --------------------------------------------------------------------- E-Mail lesen //-->
	    
        <div class="email email-read">
          <header>
            <div class="grid2col">
              <div class="column first">
              
                <dl class="data">
                  <dt class="from">From:</dt>
                  <dd class="from">'.$this->prepareOutput($from).'</dd>
                  <dt class="to">To:</dt>
                  <dd class="to">'.$this->prepareOutput(implode(", ",$to)).'</dd>
                  '.$cc.'
                  <dt class="date">Date</dt>
                  <dd class="date">'.$this->email->getDate().'</dd>
                </dl>
			
              </div>
              
              <div class="column last">
              </div>
            </div>
          </header>
          
          '.$attachments.'
          
          <section class="content">
            '.$body.'
          </section>
          
          <footer>
            <ul class="actions">
              <li class="first action"><a class="close" href="">Close</a></li>
              <li class="action"><a class="up" href="">Up</a></li>
              <li class="last action"><a class="down" href="">Down</a></li>
            </ul>
          </footer>
        </div>
		';
	
		$return .= $this->getDebugView();
	
		return '
			<section class="content detail" id="email-content-detail-'.$this->email->getId().'">
              '.$return.'
            </section>';
	
		return $return;
	
	}

	// ------------------------------------------------------------------- LINKS

	static function getAddLink($linkvar = array()) {
		return pz::url('screen','emails','create',$linkvar);
	}

	// ------------------------------------------------------------------- FORMS

	static function getAddForm($p = array()) {
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("email_add").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$accounts = pz_email_account::getAsString(pz::getUser()->getId());
		
		if($accounts == "")
		{
			$return = $header.'<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
	
		}else{
		
			if(!($account_id_default = pz::getUser()->getDefaultEmailAccountId()))
				$account_id_default = 0;
		
			$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('email_form','email_add_form','".pz::url('screen','emails','create',array("mode"=>'add_email'))."')");
			$xform->setObjectparams("form_id", "email_add_form");
			$xform->setObjectparams('form_showformafterupdate',1);
			$xform->setObjectparams("real_field_names",TRUE);

			$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
	
			$xform->setValueField("pz_select_screen",array("account_id",rex_i18n::msg("email_account"),pz_email_account::getAsString(pz::getUser()->getId()),"",$account_id_default,0));
			$xform->setValueField("pz_email_screen",array("to",rex_i18n::msg("email_to")));
			$xform->setValueField("pz_email_screen",array("cc",rex_i18n::msg("email_cc")));
			$xform->setValueField("pz_email_screen",array("bcc",rex_i18n::msg("email_bcc")));
	
			$xform->setValueField("text",array("subject",rex_i18n::msg("email_subject")));
			$xform->setValueField("pz_attachment_screen",array("clip_ids",rex_i18n::msg("email_attachments")));
			$xform->setValueField("pz_email_textarea",array("body",rex_i18n::msg("email_body")));
			// $xform->setValueField("textarea",array("html",rex_i18n::msg("email_html"),"","0"));

			$projects = pz::getUser()->getEmailProjects();
			$xform->setValueField("pz_select_screen",array("project_id",rex_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",1,rex_i18n::msg("please_choose")));

			$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1"));
			$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
			$xform->setValueField("hidden",array("create_user_id",pz::getUser()->getId()));
			$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));
			
			$xform->setValueField("hidden",array("reply_id",rex_request("reply_id","int",0)));
			$xform->setValueField("hidden",array("forward_id",rex_request("forward_id","int",0)));
			
			$xform->setValueField("checkbox",array("draft",rex_i18n::msg("save_as_draft")));
			$xform->setValueField("hidden",array("user_id",pz::getUser()->getId()));

			if(rex_request("draft","int") != 1)
			{
				$xform->setValidateField("empty",array("subject",rex_i18n::msg("error_email_subject_empty")));
				$xform->setValidateField("empty",array("body",rex_i18n::msg("error_email_body_empty")));
				$xform->setValidateField("empty",array("to",rex_i18n::msg("error_email_to_empty")));
			}

			// if(rex_request("reply_id","int",0)>0)
			// 	$xform->setValueField("checkbox",array("move_replymail_to_project",rex_i18n::msg("move_replymail_to_project"),0,1,"no_db"));

			$xform->setActionField("db",array("pz_email"));
			
			$return = $xform->getForm();

			// http://jqueryui.com/demos/autocomplete/#multiple-remote

			$return .= '<script>

	$(document).ready(function() {
	
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}

		$("#xform-xform-cc input, #xform-xform-to input, #xform-xform-bcc input")
			// don t navigate away from the field on tab when selecting an item
			.bind( "keydown", function( event ) {
				if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).data( "autocomplete" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				source: function( request, response ) {
					$.getJSON( "/screen/addresses/addresses/", {
						mode: "get_emails",
						search_name: extractLast( request.term )
					}, response );
				},
				search: function() {
					// custom minLength
					var term = extractLast( this.value );
					if ( term.length < 3 ) {
						return false;
					}
				},
				focus: function() {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					return false;
				}
			});
	});

	</script>
	';
	
			if($xform->getObjectparams("actions_executed")) {
				if(rex_request("draft","string") != "1")
				{
					$email_id = $xform->getObjectparams("main_id");

					if($email = pz_email::get($email_id)) {
						if(!$email->sendDraft()) {
							$return = $header.'<p class="xform-warning">'.rex_i18n::msg("email_send_failed_saved_in_drafts").'</p>'.$return;
						}else {
						
							// TODO .. 
							// move_replymail_to_project
						
							$return = $header.'<p class="xform-info">'.rex_i18n::msg("email_send").'</p>';
						}
					}
				}else {
					$return = $header.'<p class="xform-info">'.rex_i18n::msg("email_saved_in_drafts").'</p>';
				}
				
				$return .= pz_screen::getJSUpdateLayer('emails_list',pz::url('screen','emails','create',array("mode"=>'list')));
				// $return .= "4".pz_screen::getJSLoadFormPage('emails_list','email_search_form',pz::url('screen','emails',$p["function"],array("mode"=>'list')));

			}else {
				$return = $header.$return;	
			}

		}

		$return = '<div id="email_form"><div id="email_add" class="design2col xform-add">'.$return.'</div></div>';

		return $return;	

	}
	
	public function getEditForm($p = array()) {
	
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("email_edit").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$accounts = pz_email_account::getAsString(pz::getUser()->getId());
		
		if($accounts == "")
		{
			$return = $header.'<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
	
		}else{
		
			$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('email_edit','email_edit_form','".pz::url('screen','emails','create',array("mode"=>'edit_email'))."')");
			$xform->setObjectparams("form_id", "email_edit_form");
			$xform->setObjectparams('form_showformafterupdate',1);
			$xform->setObjectparams("real_field_names",TRUE);
			
			$xform->setObjectparams("main_table",'pz_email');
			$xform->setObjectparams("main_id",$this->email->getId());
			$xform->setObjectparams("main_where",'id='.$this->email->getId());
			$xform->setObjectparams('getdata',true);
			$xform->setHiddenField("email_id",$this->email->getId());
			
			$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
	
			$xform->setValueField("pz_select_screen",array("account_id",rex_i18n::msg("email_account"),pz_email_account::getAsString(pz::getUser()->getId()),"","",0));
			$xform->setValueField("pz_email_screen",array("to",rex_i18n::msg("email_to")));
			$xform->setValueField("pz_email_screen",array("cc",rex_i18n::msg("email_cc")));
			$xform->setValueField("pz_email_screen",array("bcc",rex_i18n::msg("email_bcc")));
	
			$xform->setValueField("text",array("subject",rex_i18n::msg("email_subject"),"","0"));
			$xform->setValueField("pz_attachment_screen",array("clip_ids",rex_i18n::msg("email_attachments")));
			$xform->setValueField("pz_email_textarea",array("body",rex_i18n::msg("email_body")));
			// $xform->setValueField("textarea",array("html",rex_i18n::msg("email_html"),"","0"));
			$projects = pz::getUser()->getEmailProjects();
			$xform->setValueField("pz_select_screen",array("project_id",rex_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",1,rex_i18n::msg("please_choose")));

			$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1"));
			$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
			$xform->setValueField("hidden",array("create_user_id",pz::getUser()->getId()));
			$xform->setValueField("hidden",array("update_user_id",pz::getUser()->getId()));
			$xform->setValueField("checkbox",array("draft",rex_i18n::msg("save_as_draft")));
			$xform->setValueField("hidden",array("user_id",pz::getUser()->getId()));

			if(rex_request("draft","int") != 1)
			{
				$xform->setValidateField("empty",array("subject",rex_i18n::msg("error_email_subject_empty")));
				$xform->setValidateField("empty",array("body",rex_i18n::msg("error_email_body_empty")));
				$xform->setValidateField("empty",array("to",rex_i18n::msg("error_email_to_empty")));
			}

			$xform->setActionField("db",array('pz_email','id='.$this->email->getId()));
			
			$return = $xform->getForm();
	
			if($xform->getObjectparams("actions_executed")) {
				if(rex_request("draft","string") != "1")
				{
					if($email = pz_email::get($this->email->getId())) {
						if(!$email->sendDraft()) {
							$return = $header.'<p class="xform-warning">'.rex_i18n::msg("email_send_failed_saved_in_drafts").'</p>'.$return;
						}else {
							$return = $header.'<p class="xform-info">'.rex_i18n::msg("email_send").'</p>';
						}
					}
				}else {
					$return = $header.'<p class="xform-info">'.rex_i18n::msg("email_saved_in_drafts").'</p>'.$return;
				}
				

			}else {
				$return = $header.$return;	
			}

		}

		$return .= pz_screen::getJSUpdateLayer('emails_list',pz::url('screen','emails','create',array("mode"=>'list')));
		$return = '<div id="email_form"><div id="email_edit" class="design2col xform-add">'.$return.'</div></div>';

		return $return;	

	}


	public function prepareOutput($text, $specialchars = TRUE) {

		$text = preg_replace("#<style[^>]*>.*</style>#isU", "SSTTYYLLEE", $text);
		$text = preg_replace("#<script[^>]*>.*</script>#isU", "SSCCRRIIPPTT", $text);
		$text = preg_replace("#<!--.*-->#isU", "KKOOMMNNEENNTTAARR", $text);

		$text = pz_email_screen::setLinks($text);
		if($specialchars)
			$text = htmlspecialchars($text);
		$text = pz_email_screen::replaceLinks($text);
		return $text;
	}



	public function setLinks($text) {
	
		$urlsuch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\^\$!`\"'\|\[\]\{\}<>]*)/si";
		$urlsuch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\^\$!`\"'\|\[\]\{\}<>]*)/si";
		
		$urlreplace[]="\\1[URL]\\2\\4[/URL]";
		$urlreplace[]="[URL]\\1\\3[/URL]";

		$text = preg_replace($urlsuch, $urlreplace, $text);

		$emailsuch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
		$emailsuch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
		$emailreplace[]="\\1[EMAIL]\\2[/EMAIL]";
		$emailreplace[]="[EMAIL]\\0[/EMAIL]";
		if (strpos($text, "@")) {
			$text = preg_replace($emailsuch, $emailreplace, $text);
		}

		return $text;

	}

	public function replaceLinks($text) {
	
		$text = preg_replace("/\[URL\]www.(.*?)\[\/URL\]/si", "<a target=\"_blank\" href=\"http://www.\\1\">www.\\1</a>", $text);
		$text = preg_replace("/\[URL\](.*?)\[\/URL\]/si", "<a target=\"_blank\" href=\"\\1\">\\1</a>", $text);
		$text = preg_replace("/\[EMAIL\](.*?)\[\/EMAIL\]/si", "<a href=\"/screen/emails/create/?to=\\1\">\\1</a>", $text); 
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