<?php

class pz_emails_controller_screen extends pz_emails_controller {

	public $name = "emails";
	public $function = "";
	public $functions = array("inbox", "outbox", "spam", "trash", "email", "create", "search", "setup"); // "history", "search",
	public $function_default = "inbox";
	public $navigation = array("inbox", "outbox", "spam", "trash", "search", "setup", "create"); // "history", "search",

	function controller($function = "") {

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["linkvars"] = array();

		switch($this->function)
		{
			case("inbox"):	return $this->getInboxPage($p);
			case("outbox"):	return $this->getOutboxPage($p);
			case("spam"):	return $this->getSpamPage($p);
			case("trash"):	return $this->getTrashPage($p);
			case("history"):return $this->getHistoryPage($p);
			case("search"):	return $this->getSearchPage($p);
			case("create"):	return $this->getEmailForm($p);
			case("api"):	return $this->controllerApi($p);
			case("email"):	return $this->getEmail($p);
			case("setup"):	return $this->getSetupPage($p);
		}
		return "";
	}

	private function getProjects()
	{
		$projects = pz::getUser()->getEmailProjects();
		if(!isset($_REQUEST["project_ids"]))
		{
			$project_ids = rex_request::session("pz_email_project_ids","array");
			if(count($project_ids) == 0) {
				$project_ids = pz_project::getProjectIds($projects);
			}
		}else
		{
			$project_ids = explode(",",rex_request("project_ids","string"));
		}

		$return_projects = array();
		$prooved_project_ids = array();
		foreach($projects as $project)
		{
			if(in_array($project->getId(),$project_ids)) {
				$return_projects[] = $project;			
				$prooved_project_ids[] = $project->getId();
			}	
		}
		rex_request::setSession("pz_email_project_ids",$prooved_project_ids);
		return $return_projects;
	}

	// ------------------------------------------------------------------- Views

	/*
	private function getProjectsSelectionFlyout($p = array(),$project_ids)
	{
		$entries = array();
		
		$i = -1;
		$title = '';
		$values = array();
		
		foreach(pz::getUser()->getEmailProjects() as $project)
		{
			$i++;
			$entries[$i]['id'] = $project->getId();
            $entries[$i]['title'] = pz::cutText($project->getName(),80).' ['.$project->getId().']';
            $entries[$i]['title_short'] = pz::cutText($project->getName(),7,10);
			// aktualisiere die layer -- search, list, mit den project_ids und den linkvars..
		}
		
		$f = new rex_fragment();
		$f->setVar('layer_id', 'projects_dropdown', false);
        $f->setVar('class_ul', 'w3', false);
        $f->setVar('entries', $entries, false);
        $f->setVar('multiselect_field', 'project_ids', false);
        $f->setVar('selected_values', $project_ids, false);
        $f->setVar('text_selected', rex_i18n::msg("projects_selected"), false);
        $f->setVar('refresh_layer', array($p["layer_list"]), false);
        
        return $f->parse('pz_screen_multiselect_dropdown');
	}
	*/


	private function getNavigation($p = array(),$flyout = "")
	{
		return pz_screen::getNavigation(
			$p,
			$this->navigation, 
			$this->function, 
			$this->name, 
			$flyout
		);
	}

	function getEmailsSearchForm ($p = array())
	{

		$link_refresh = pz::url("screen","emails",
							$this->function,
							array_merge( $p["linkvars"], array( "mode" => "emails_search", "project_ids" => "___value_ids___" ) )
						);
		
	    $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("search_for_emails").'</h1>
	          </div>
	        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('emails_list','emails_search_form','".pz::url('screen','emails',$this->function,array("mode"=>'list'))."')");
		$xform->setObjectparams("form_id", "emails_search_form");
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("project_name")));
		// $xform->setValueField('pz_select_screen',array('search_label', rex_i18n::msg('label'), pz_labels::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		// $xform->setValueField('pz_select_screen',array('search_customer', rex_i18n::msg('customer'), pz_customers::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		// $xform->setValueField('pz_date_screen',array('search_datetime', rex_i18n::msg('createdate')));
		
		$xform->setValueField('pz_select_screen',array('search_account_id', rex_i18n::msg('email_account'), pz::getUser()->getEmailaccountsAsString(),"","",1,rex_i18n::msg("please_choose")));
		
		$xform->setValueField("checkbox",array("search_mymails",rex_i18n::msg("only_my_emails")));
		
		
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));

		$return .= $xform->getForm();
		
		$return = '<div id="emails_search" class="design1col xform-search" data-url="'.$link_refresh.'">'.$return.'</div>';
		return $return;

	}




	// ------------------------------------------------------------------- Pages

	function getEmail($p = array())
	{
	
		$email_id = rex_request("email_id","int",0);
		if($email_id < 1) {
			return FALSE;
		}
				
		if(!($email = pz::getUser()->getEmailById($email_id))) {
			return FALSE;
		}
		
		$mode = rex_request("mode","string","");
		
		switch($mode)
		{
			case("view"):
				if(!$email->getReaded())
					$email->readed();
				$pz_email_screen = new pz_email_screen($email);
				$return = $pz_email_screen->getDetailView();

				$return.= '<script language="Javascript">';
				$return.= '$(".email-'.$email->getId().'").removeClass("email-unreaded");';
				$return.= '$(".email-'.$email->getId().'").addClass("email-readed");';
				$return.= '</script>';

				return $return;

			case("view_element"):

				$pz_eml = new pz_eml($email->getEml());
				$element_id = rex_request("element_id","string",0);
				if($element = $pz_eml->getElementByElementId($element_id))
				{
					// ob_end_clean();
					// header("Cache-Control: no-cache, must-revalidate");
					// header("Cache-Control","private");
					// header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // in the past
					header('Content-Disposition: inline; filename="'.$element->getFileName().'";'); // 
					header('Content-type: '.$element->getContentType());
					// header("Content-Transfer-Encoding: binary");
					// header("Content-Length: ".$element->getSize());
					
					return $element->getBody();
				}
				return FALSE;


			case("download"):
				$pz_eml = new pz_eml($email->getEml());
				$element_id = rex_request("element_id","string",0);
				if($element = $pz_eml->getElementByElementId($element_id))
				{
					// ob_end_clean();
					// header("Cache-Control: no-cache, must-revalidate");
					// header("Cache-Control","private");
					// header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // in the past
					header('Content-Disposition: attachment; filename="'.$element->getFileName().'";'); // 
					// header('Content-type: '.$element->getContentType());
					// header("Content-Transfer-Encoding: binary");
					// header("Content-Length: ".$element->getSize());
					return $element->getBody();
				}
				return FALSE;

			case("update_status"):
				$email_status = rex_request("email_status","int",0);
				if($email_status != 1)
					$email_status = 0;
				$email->updateStatus($email_status);
				$other_status = 1;
				if($email_status == 1)
					$other_status = 0;
				$return = '<script language="Javascript">';
				$return.= '$(".email-'.$email->getId().' .status").removeClass("status'.$other_status.'").addClass("status'.$email_status.'");';
				$project_ids = pz_project::getProjectIds($this->getProjects());
				if($email->getProjectId() > 0) 
						$return.= 'pz_hide(".email-'.$email->getId().'");';
				$return.= '</script>';
				return $return;
			
			case("move_to_project_id_update_status"):
			case("move_to_project_id"):
				$email_project_id = rex_request("email_project_id","int",0);
				if(!($project = pz::getUser()->getProjectById($email_project_id))) {
					return FALSE;
				}
				$email->moveToProjectId($email_project_id);
				$project_ids = pz_project::getProjectIds($this->getProjects());
				$status = $email->getStatus();
				$return = '<script language="Javascript">';
				if($mode == "move_to_project_id_update_status") {
					$email->updateStatus(1);
					$status = 1;
				}else {
					// only project id update
					$return.= '$(".email-'.$email->getId().' .email-project-name").html("'.htmlspecialchars($project->getName()).'");';
					$return.= '$(".email-'.$email->getId().'").addClass("email-hasproject");';
				}
				
				if($status == 1 || !in_array($project->getId(),$project_ids)) {
						$return.= 'pz_hide(".email-'.$email->getId().'");';
				}
				$return.= '</script>';
				return $return;

			case("unproject"):
				$email->moveToProjectId(0);
				$return = '<script language="Javascript">';
				$return.= '$(".email-'.$email->getId().' .email-project-name").html("'.htmlspecialchars(rex_i18n::msg('please_select_project_for_email')).'");';
				$return.= '$(".email-'.$email->getId().'").removeClass("email-hasproject");';
				$return.= '</script>';
				return $return;

			case("trash"):
				$email->trash();
				$return = '<script language="Javascript">';
				$return.= 'pz_hide(".email-'.$email->getId().'");';
				$return.= '</script>';
				return $return;

			case("untrash"):
				$email->untrash();
				$return = '<script language="Javascript">';
				$return.= 'pz_hide(".email-'.$email->getId().'");';
				$return.= '</script>';
				return $return;

			case("unread"):
				$email->unreaded();
				$return = '<script language="Javascript">';
				$return.= '$(".email-'.$email->getId().'").removeClass("email-readed");';
				$return.= '$(".email-'.$email->getId().'").addClass("email-unreaded");';
				$return.= '</script>';
				return $return;

			case("delete"):
				return '';
		}
	
	}


	function getInboxPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("emails_inbox");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "inbox";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);

		$filter = array();
		$filter[] = array("type"=>"plain", "value"=>"( (project_id>0 AND status=0) || (project_id=0))");
		
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		if(rex_request("search_mymails","int") == 1)
			$filter[] = array("type"=>"=", "field"=>"user_id", "value"=>pz::getUser()->getId());
		if(rex_request("search_account_id","int") != 0)
			$filter[] = array("type"=>"=", "field"=>"account_id", "value"=>rex_request("search_account_id","int"));
		
		$emails = pz::getUser()->getInboxEmails($filter,$projects);

		$return = "";
		
		$mode = rex_request("mode","string");
		switch($mode) {
		
			case("emails_search"):
				return $this->getEmailsSearchForm($p);
			default:
				break;
		}

		$return .= pz_email_screen::getInboxListView(
					$emails,
					array_merge( $p, array("linkvars" => array( "mode" =>"list"	) )	)
				);

		if($mode == "list") {
			return $return;
		}

		$s1_content .= $this->getEmailsSearchForm($p);
		$s2_content .= $return;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}
	

	public function getOutboxPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("emails_outbox");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "outbox";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);

		$filter = array();
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		if(rex_request("search_mymails","int") == 1)
			$filter[] = array("type"=>"=", "field"=>"user_id", "value"=>pz::getUser()->getId());
		
		$emails = pz::getUser()->getOutboxEmails($filter,$projects);

		$return = "";
		
		$mode = rex_request("mode","string");
		switch($mode) {
		
			case("emails_search"):
				return $this->getEmailsSearchForm($p);
			default:
				break;
		}
		
		$return .= pz_email_screen::getOutboxListView(
					$emails,
					array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
				);

		if($mode == "list") {
			return $return;
		}

		$s1_content .= $this->getEmailsSearchForm($p);
		$s2_content .= $return;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		
		return $f->parse('pz_screen_main');
	}


	public function getSpamPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("emails_spam");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "spam";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);

		$filter = array();
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		if(rex_request("search_mymails","int") == 1)
			$filter[] = array("type"=>"=", "field"=>"user_id", "value"=>pz::getUser()->getId());
		
		$emails = pz::getUser()->getSpamEmails($filter,$projects);

		$return = "";
		
		$mode = rex_request("mode","string");
		switch($mode) {
		
			case("emails_search"):
				return $this->getEmailsSearchForm($p);
			default:
				break;
		}
		
		$return .= pz_email_screen::getOutboxListView(
					$emails,
					array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
				);

		if($mode == "list") {
			return $return;
		}

		$s1_content .= $this->getEmailsSearchForm($p);
		$s2_content .= $return;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}


	public function getTrashPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("emails_trash");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "trash";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);

		$filter = array();
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		if(rex_request("search_mymails","int") == 1)
			$filter[] = array("type"=>"=", "field"=>"user_id", "value"=>pz::getUser()->getId());
		
		$emails = pz::getUser()->getTrashEmails($filter,$projects);

		$return = "";
		
		$mode = rex_request("mode","string");
		switch($mode) {
		
			case("emails_search"):
				return $this->getEmailsSearchForm($p);
			default:
				break;
		}
		
		$return .= pz_email_screen::getOutboxListView(
					$emails,
					array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
				);

		if($mode == "list") {
			return $return;
		}

		$s1_content .= $this->getEmailsSearchForm($p);
		$s2_content .= $return;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}


	private function getSearchPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("emails_search");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "search";

		$p["layer_search"] = "emails_search";
		$p["layer_list"] = "emails_list";

		$s1_content = "";
		$s2_content = "";

		$filter = array();
		$projects = array();
		
		if(rex_request("search_name","string") != "")
			$filter[] = array("type"=>"orlike", "field"=>"subject,body,to,cc", "value"=>rex_request("search_name","string"));
		if(rex_request("search_mymails","int") == 1)
			$filter[] = array("type"=>"=", "field"=>"user_id", "value"=>pz::getUser()->getId());
		if(rex_request("search_account_id","int") != 0)
			$filter[] = array("type"=>"=", "field"=>"account_id", "value"=>rex_request("search_account_id","int"));
		
		$filter[] = array("field"=>"trash", "value"=>0);
		
		$emails = pz::getUser()->getAllEmails($filter, $projects);

		$return = "";
		
		$mode = rex_request("mode","string");
		switch($mode) {
		
			case("emails_search"):
				return $this->getEmailsSearchForm($p);
			default:
				break;
		}

		$return .= pz_email_screen::getInboxListView(
					$emails,
					array_merge( $p, array("linkvars" => array( "mode" =>"list"	) )	)
				);

		if($mode == "list") {
			return $return;
		}

		$s1_content .= $this->getEmailsSearchForm($p);
		$s2_content .= $return;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}


	public function getSetupPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("email_setup");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "setup";

		$s1_content = "";
		$s2_content = "";

		$return = "";
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_email_account"):
				return pz_email_account_screen::getAddForm($p);
				break;
				
			case("delete_email_account"):
				$email_account_id = rex_request("email_account_id","int",0);
				if($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id,pz::getUser()->getId())) 
				{
					$email_account->delete();
					
					$p["info"] = '<p class="xform-info">'.rex_i18n::msg("email_account_delete").'</p>';
				}else {
					$p["info"] = '<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
				}
				
			case("list"):
				$email_accounts = pz::getUser()->getEmailaccounts();
				$return .= pz_email_account_screen::getAccountsListView(
								$email_accounts,
								array_merge( $p, array("linkvars" => array( "mode" =>"list" ) ) )
							);
				return $return;
				break;
			case("edit_email_account"):
				$email_account_id = rex_request("email_account_id","int",0);
				if($email_account_id > 0 && $email_account = pz_email_account::get($email_account_id)) {
					$cs = new pz_email_account_screen($email_account);
					return $cs->getEditForm($p);
				}else {
					return '<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
				}
				break;
			case(""):
				$email_accounts = pz::getUser()->getEmailaccounts();
				$s2_content = pz_email_account_screen::getAccountsListView(
						$email_accounts,
						array_merge( $p, array("linkvars" => array( "mode" =>"list" ) )
					)
				);
				$s1_content .= pz_email_account_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}


	public function getEmailForm($p = array()) 
	{
		$p["title"] = rex_i18n::msg("email_create");
		$p["mediaview"] = "screen";
		$p["controll"] = "emails";
		$p["function"] = "create";

		$s1_content = "";
		$s2_content = "";

		$filter = array();
		$projects = array();
		$emails = pz::getUser()->getDraftsEmails($filter,$projects);

		// ------------ Reply Mail
		// TODO
		// prüfen ob man noch Rechte auf diese Email hat.

		$reply_email_id = rex_request("reply_email_id","int");
		if($reply_email_id > 0 && $email = pz_email::get($reply_email_id)) {
		
			$_REQUEST["to"] = $email->getFromEmail();

			if(rex_request("reply_all","int") == 1)
			{
				$user_emails = array();
				$user_emails[] = "";
				$user_email = pz::getUser()->getEmail();
				if($user_email != "" && $user_address = pz_address::getByEmail($user_email) ) 
					foreach($user_address->getFields() as $field) 
						if($field->getVar("type") == "EMAIL") 
							$user_emails[] = $field->getVar("value");	

				$to = array();
				$to[] = $email->getFromEmail();
				$to = array_merge($to,explode(",",$email->getToEmails()));

				$cc = explode(",",$email->getCcEmails());

				$to = array_diff($to, $user_emails);
				$cc = array_diff($cc, $user_emails);
				
				$_REQUEST["to"] = implode(",",$to);
				$_REQUEST["cc"] = implode(",",$cc);
			
			}

			$_REQUEST["reply_id"] = $reply_email_id;
			$_REQUEST["project_id"] = $email->getProjectId();
			
			$_REQUEST["body"] = "\n\n>>".str_replace("\n","\n>> ",$email->getBody());
			$_REQUEST["subject"] = "RE: ".$email->getSubject();

		}

		$return = "";
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_email"):
				return pz_email_screen::getAddForm($p);
				
			case("delete_email"):

				$email_id = rex_request("email_id","int",0);
				// TODO - permission prüfen
				if($email = pz_email::get($email_id)) 
				{
					$email->delete();
					$p["info"] = '<p class="xform-info">'.rex_i18n::msg("email_account_delete").'</p>';
				}else {
					$p["info"] = '<p class="xform-warning">'.rex_i18n::msg("email_account_not_exists").'</p>';
				}
				
				$return = '<script language="Javascript">';
				$return.= 'pz_hide(".email-'.$email->getId().'");';
				$return.= '</script>';
				
				return $return;
				break;
			case("list"):
				$s2_content = pz_email_screen::getDraftsListView(
						$emails,
						array_merge( $p, array("linkvars" => array( "mode" =>"list"	) ) )
					);
				return $s2_content;

			case("edit_email"):
				$email_id = rex_request("email_id","int",0);
				// TODO. permission to email prüfen.
				if($email_id > 0 && $email = pz_email::get($email_id)) {
					$cs = new pz_email_screen($email);
					return $cs->getEditForm($p);
				}
				return '<p class="xform-warning">'.rex_i18n::msg("email_not_exists").'</p>';
				
			case(""):
				
				if(pz::getUser()->getDefaultEmailaccountId() && $account = pz_email_account::get(pz::getUser()->getDefaultEmailaccountId()) )
				{
					if(isset($_REQUEST["body"]))
						$_REQUEST["body"] = $account->getSignature().$_REQUEST["body"];
					else
						$_REQUEST["body"] = $account->getSignature();
				}
			
				$s1_content .= pz_email_screen::getAddForm($p);
				$s2_content = pz_email_screen::getDraftsListView(
						$emails,
						array_merge( $p, array("linkvars" => array( "mode" =>"list"	) ) )
					);
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);

		return $f->parse('pz_screen_main');
	}



















	
	// ------------------------------------------------------------------- Hover

	public function getMainFlyout()
	{
		return '';
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