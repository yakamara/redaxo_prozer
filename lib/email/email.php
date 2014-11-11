<?php

class pz_mailer extends PHPMailer {
				
	public function getMimeHeader() {
		return $this->MIMEHeader;
	}	

	public function getMimeBody() {
		return $this->MIMEBody;
	}	
	
}

class pz_email extends pz_model{

	public
		$vars = array(),
		$isEmail = FALSE,
		$header_raw,
		$body_raw,
		$from_address = NULL,
		$eml,
		$eml_element,
		$pz_eml,
		$body_html_element,
		$body_text_element,
		$attachments;
		
	private
	  $setvars = true;


	function __construct($vars = array())
	{
		$this->setVars($vars);
		if(count($vars)>5) {
			$this->isEmail = TRUE;
			return TRUE;

		} else if (isset($vars["id"]) &&  $vars["id"] != "") {

      $email_sql = pz_sql::factory();
  		$email_sql->setQuery('select * from pz_email where id = ?', array($vars["id"]));
  		$emails = $email_sql->getArray();
  		if(count($emails) != 1) return FALSE;
  		$this->isEmail = TRUE;
  		$vars = $emails[0];
  		$this->setVars($vars);
      return true;		

		}
		
		return FALSE;
	}


  // tricky use
  /*
  public function __call($m, $a) {

    if($this->setvars) {
      $email_sql = pz_sql::factory();
      $emails_array = $email_sql->getArray('select * from pz_email where id = ? LIMIT 2', array($this->getId()));
      foreach($emails_array as $email) {
        $this->__construct($email);
      }
    }

    $m = "__".$m;
    if (is_object($this)) return call_user_func_array(array(&$this, $m), $a);
    return true;
  }
  */

  // ---------------- static

	static function get($email_id = "")
	{
		if($email_id == "") return FALSE;
		$email_sql = pz_sql::factory();
		$email_sql->setQuery('select * from pz_email where id = ?', array($email_id));
		$emails = $email_sql->getArray();
		if(count($emails) != 1) return FALSE;
		return new pz_email($emails[0]);
	}


	static function getEmailsFromProjects($project_ids = array())
	{
		$p_sql = array();
		if(count($project_ids)>0) {
			foreach($project_ids as $pid) {
				$p_sql[] = ' e.project_id = ?';
				$params[] = $pid;
			}
		}
		
		$emails = pz_sql::factory();
		$emails->setQuery('select e.* from pz_email as e where e.id = ?', $params);
		$emails_array = $emails->getArray();
		
		$emails = array();
		foreach ($emails_array as $email) {
			$emails[] = new pz_email($email);
		}
		
		return $emails;
	}


  static function countAll(array $filter = array(), array $projects = array(), array $users = array(), array $orders = array(), $pager = "")
  {
    $pager = new pz_pager;
    pz_email::getAll( $filter, $projects, $users, $orders, $pager);
    return $pager->getRowCount();
  }


	static function getAll( array $filter = array(), array $projects = array(), array $users = array(), array $orders = array(), $pager = "")
	{
		$where = array();
		$params = array(); 

		$where_projects_users = "";
		$where_projects = array();
		foreach($projects as $p) {
			//$where_projects[] = $p->getId();
			$where_projects[] = 'project_id='.$p->getId();
		}

		if(count($where_projects)>0) {
			// $where_projects_users = 'FIND_IN_SET(project_id,"'.implode(",",$where_projects).'")';
			$where_projects_users = '('.implode(" OR ",$where_projects).')';
			
		}

		$where_users = array();
		foreach($users as $u) {
			$where_users[] = 'user_id='.$u->getId();
		}
		if(count($where_users)>0) {
			if($where_projects_users != "")
				$where_projects_users .= " OR ";
			// $where_projects_users = '('.$where_projects_users.' FIND_IN_SET(user_id,"'.implode(",",$where_users).'")'.')';
			$where_projects_users = '( '.$where_projects_users.' '.implode(" OR ",$where_users).')';
			
		}
		$where[] = $where_projects_users;
	

		$f = pz::getFilter($filter,$where,$params);
		$where = $f["where"];
		$params = $f["params"];
		$where_sql = $f["where_sql"];
	
		// ----- Orders
		// $orders[] = array("orderby" => "id", "sort" => "desc");
		$order_array = array();
		foreach($orders as $order) {
			$order_array[] = '`'.$order["orderby"].'` '.$order["sort"];
		}
		
		$order_sql = '';
		if(count($order_array)>0) {
		  $order_sql = ' order by '.implode(',',$order_array);
		}

    $sql = pz_sql::factory();
    if(is_object($pager)) {
      $emails_array = pz_model::query('SELECT id FROM pz_email '.$where_sql .' '.$order_sql.'', $params, $pager);
      
    } else {
      $emails_array = $sql->getArray('SELECT id FROM pz_email '.$where_sql .' '.$order_sql.' LIMIT 2000', $params);
    
    }
    
    $emails = array();
    foreach ($emails_array as $email) {
      $emails[] = new pz_email($email);
    }
    return $emails;
	}


	// ---------------- getter

	public function getVars() {
		return $this->vars;	
	}

	public function getId() {
		return intval($this->vars["id"]);
	}

	public function getProjectId() {
		return $this->vars["project_id"];
	}

	public function setProjectId($project_id) {
		$this->vars["project_id"] = $project_id;
	}

  public function getProject() {
    if($this->vars["project_id"] == 0)
      return false;
    return pz_project::get($this->vars["project_id"]);
  }

	public function getTo() {
		return $this->vars["to"];
	}

	public function getToEmails() {
		return $this->vars["to_emails"];
	}

	public function getCc() {
		return $this->vars["cc"];
	}

	public function getCcEmails() {
		return $this->vars["cc_emails"];
	}

	public function getBcc() {
		return $this->vars["bcc"];
	}

	public function getBccEmails() {
	  // because here are only sent emails
		return $this->vars["bcc"];
	}
	
	public function getFrom() {
		return $this->vars["from"];
	}

	public function getFromEmail() {
		return $this->vars["from_emails"];
	}

	public function getFromAddress() {
		if(!$this->from_address)
			$this->from_address = pz_address::getByEmail($this->getFromEmail());
		return $this->from_address;
	}
	
	public function getCreateDate() 
	{
	  return $this->getDateTime()->format("Y-m-d");
	}
	
	public function getDate() 
	{
	  return $this->getDateTime()->format("Y-m-d");
	}

  public function getDateTime()
  {
    // DateTime::RFC822 ->  "Mon, 15 Aug 05 15:52:01 +0000"
    // DateTime::RFC1036 -> "Mon, 15 Aug 05 15:52:01 +0000"
    // DateTime::RSS  ->    "Mon, 15 Aug 2005 15:52:01 +0000";
    // DateTime::RFC850 ->  "Monday, 15-Aug-05 15:52:01 UTC";
    // DateTime::ISO8601->  "2005-08-15T15:52:01+0000"
    // DateTime::RFC1123    "Mon, 15 Aug 2005 15:52:01 +0000"
    // DateTime::W3C        "2005-08-15T15:52:01+00:00"
    // 'Y-m-d H:i:s'        "2013-06-27 22:14:57"
    // does not work - 'D, d M Y H:i:s O (T)' "Wed, 26 Jun 2013 10:56:08 +0200 (CEST)"
    
    $dts = array( DateTime::RFC822, DateTime::RFC1036, DateTime::RSS, DateTime::RFC850, DateTime::ISO8601, DateTime::RFC1123, DateTime::W3C, 'Y-m-d H:i:s');
    
    $date = $this->vars["date"];
    if($date == "")
    	$date = $this->vars["created"];

    $date = preg_replace('@\((.*?)\)@siU','',$date); // delete stuff like "(EDT)" ..
    $date = trim($date);

    foreach($dts as $dt) {
      if(($d = DateTime::createFromFormat($dt, $date, pz::getDateTimeZone()) )) {
        return $d;
      }
    }
    return new DateTime();
  }

	public function getSubject() {
		$subject = $this->vars["subject"];
		if($subject != "") {
			return $subject;
		}
		return pz_i18n::msg("no_subject_entered");
	}

	public function getEml() {
		if($this->eml == "")
		{
		  if($this->getId() != "")
			  $this->eml = file_get_contents($this->getFilePath());
		  else
			  $this->eml = $this->header_raw.$this->body_raw;
		}
		return $this->eml;
	}
  
  public function getProzerEml() 
  {
    if(!isset($this->pz_eml))
      $this->pz_eml = new pz_eml($this->getEml());
    return $this->pz_eml;
  }

	public function getBody() 
	{
		return $this->vars["body"];
	}

	public function getMessageHTML() 
	{
		return $this->vars["body_html"];
	}

	public function getMessageId() 
	{
		return $this->vars["message_id"];
	}

	public function getAccountId() 
	{
		return $this->vars["account_id"];
	}

	public function getReplyId() 
	{
		return $this->vars["reply_id"];
	}

	public function getForwardId() 
	{
		return $this->vars["forward_id"];
	}

	public function getCreateUserId() 
	{
		return $this->vars["create_user_id"];
	}

	public function getUserId() 
	{
		return $this->vars["user_id"];
	}
	
	// only send mail
	public function getClipIds() {
		return $this->vars["clip_ids"];
	}
	
	public function getSend() {
		return $this->vars["send"];
	}

	// original mail
	public function getRepliedId() {
		return $this->vars["replied_id"];
	}

	public function getForwardedId() {
		return $this->vars["forwarded_id"];
	}

	public function getStatus() {
		$status = $this->vars["status"];
		if($status != 1)
			$status = 0;
		return $status;
	}

	public function getReaded() {
		$readed = $this->vars["readed"];
		if($readed != 1)
			$readed = 0;
		return $readed;
	}

	public function getHeader() {
		return $this->vars["header"];
	}

  public function getLabelId() {
  
    // TODO
    return 3;
  
  }

	public function isDraft() 
	{
		if($this->vars["draft"] == 1) 
			return TRUE;
		return FALSE;
	}

	public function isTrash() 
	{
		if($this->vars["trash"] == 1) 
			return TRUE;
		return FALSE;
	}

	public function hasProject() 
	{
		if($this->vars["project_id"] > 0) 
			return TRUE;
		return FALSE;
	
	}

	public function hasAttachments() 
	{
		if($this->vars["has_attachments"] == 1)
			return TRUE;
		return FALSE;
		
		// here: too much performance
		// $pz_eml = new pz_eml($this->getEml());
		// return $pz_eml->hasRealAttachments();
	}

  public function getAttachments()
  {
    $ignore_attachment_elements = array();
    if($this->hasBodyHTML() && $this->getBodyHTMLElement()->hasParent())
		{
		  $ignore_attachment_elements[] = $this->getBodyHTMLElement()->getElementId();
		}
		if($this->getBodyTextElement() && $this->getBodyTextElement()->hasParent())
		{
			$ignore_attachment_elements[] = $this->getBodyTextElement()->getElementId();
    }
  
    $this->attachments = array();
    $attachment_elements = $this->getProzerEml()->getAttachmentElements();
		foreach($attachment_elements as $e)
		{
			if(!in_array($e->getElementId(),$ignore_attachment_elements))
			{
			  $this->attachments[] = $e;
      }
    }
    return $this->attachments;
  }


  public function getRawHeader()
  {
    return $this->header_raw;
  }

  public function getRawBody()
  {
    return $this->body_raw;
  }

	// ---------------- setter

	public function setRawHeader($header_raw) {
		$this->header_raw = $header_raw;
		$this->vars["header"] = $header_raw;
	}
	
	public function setRawBody($body_raw) {
		$this->body_raw = $body_raw;
	}

	public function setBody($body) {
		$this->vars["body"] = $body;
	}

	public function setId($id = 0) {
		$this->vars["id"] = $id;
	}

	public function setMessageId($message_id = "") {
		$this->vars["message_id"] = $message_id;
	}

	public function setUserId($user_id = "")
	{
		$this->vars["user_id"] = $user_id;
	}

	public function setAccountId($account_id = 0)
	{
		$this->vars["account_id"] = $account_id;
	}

	public function setTo($to = "") {
		$this->vars["to"] = $to;
	}

	public function setFrom($from = "") {
		$this->vars["from"] = $from;
	}

	public function setCc($cc = "") {
		$this->vars["cc"] = $cc;
	}

	public function setBcc($bcc = "") {
		$this->vars["bcc"] = $bcc;
	}

	public function setSubject($subject = "") {
		$this->vars["subject"] = $subject;
	}

	public function setContentType($content_type = "") {
		$this->vars["content_type"] = $content_type;
	}

	public function setDate($date = "") {
		$this->vars["date"] = $date;
	}

	public function setReplyTo($reply_to = "") {
		$this->vars["reply_to"] = $reply_to;
	}

	public function setImportance($importance = "") {
		$this->vars["importance"] = $importance;
	}

	public function setCreated($created = "")
	{
		$this->vars["created"] = $created;
	}

	public function setUpdated($updated = "")
	{
		$this->vars["updated"] = $updated;
	}

 	// TODO: only because text/plain exists doesnt mean it is text part of mail
 	// TODO: only because text/html exists doesnt mean it is HTML part of mail

	public function hasBodyText()
	{
		$pz_eml = new pz_eml($this->getEml());
    	$this->body_text_element = $pz_eml->getFirstContentTypeElement("text/plain",false);
    	if($this->body_text_element)
    		return true;
    	return false;
	}

	public function hasBodyHTML()
	{
    $this->body_html_element = $this->getProzerEml()->getFirstContentTypeElement("text/html",false);
    if($this->body_html_element)
      return true;
    return false;
	}

	public function getBodyTextElement()
	{
    return $this->getProzerEml()->getFirstContentTypeElement("text/plain",false);
	}

	public function getBodyHTMLElement()
	{
    return $this->getProzerEml()->getFirstContentTypeElement("text/html",false);
	}


	// ---- func

	public function getFolder()
	{
		$id = $this->getId();
		$email_dir = intval($id/10000)*10000;
		$dir = rex_path::addonData('prozer', 'emails/'.$email_dir.'/');
		if(!is_dir($dir)) {
			rex_dir::create($dir);
		}
		$dir = rex_path::addonData('prozer', 'emails/'.$email_dir.'/'.$id.'/');
		if(!is_dir($dir)) {
			rex_dir::create($dir);
		}
		return $dir;		
	}

	public function getFilePath()
	{
		$id = $this->getId();
		$email_dir = intval($id/10000)*10000;
		return rex_path::addonData('prozer', 'emails/'.$email_dir.'/'.$id.'/'.$id.'.eml');
	}

	public function save($pz_eml = NULL)
	{
		
		if($this->getMessageId() == "")
		{
			$message_id = md5(time()+"prozer"+date("Ymd")+microtime())."_prozer";
			$this->setMessageId($message_id);
		}
		
		$this->setCreated(pz::getDateTime()->format("Y-m-d H:i:s"));
		$this->setUpdated(pz::getDateTime()->format("Y-m-d H:i:s"));
		
		$get_email = pz_sql::factory();
		// $get_email->debugsql = 1;
		$get_email->setQuery('select id from pz_email where message_id = ?',array($this->getMessageId()));
		
		if($get_email->getRows() == 0)
		{
			// email does not exist
			$add_email = pz_sql::factory();
			// $add_email->debugsql = 1;
			$add_email->setTable('pz_email');
			foreach($this->getVars() as $k => $v)
			{
				$add_email->setValue($k,$v);
			}
			
      		if(!isset($pz_eml))
			{
			  $pz_eml = new pz_eml($this->header_raw.$this->body_raw);
			}

			if($pz_eml->hasRealAttachments())
			{
				$add_email->setValue("has_attachments",1);
			}
			
			$add_email->insert();
			$this->vars["id"] = $get_email->getLastId();

			if(!isset($this->vars["id"]) || $this->vars["id"] == "")
				return FALSE;
			
			if($this->header_raw != "" && $this->body_raw != "") 
			{
				$eml = $this->header_raw.$this->body_raw;
				$filepath = $this->getFilePath();
				rex_file::put($filepath,$eml);
				
			}

		}

		return TRUE;
	}


	public function sendDraft() 
	{

		if($email_account = pz_email_account::get($this->getAccountId()))
		{

		  ob_start();
		  try 
		  {

        // Während des Mailversandes als Draft markiert.
        $u = pz_sql::factory();
        $u->setTable("pz_email");
        $u->setWhere(array('id'=>$this->getId()));
        $u->setValue("draft",1);
        $u->update();


  			$mail = new pz_mailer();
  
  			$mail->From             = $email_account->getEmail();
  	    $mail->FromName         = $email_account->getName();
  	    $mail->ConfirmReadingTo = "";
  	    $mail->Mailer           = "smtp";
  
   	    $mail->Host             = $email_account->getSMTPHost();
   	    $mail->SMTP_PORT        = $email_account->getSMTPPort();
  		    
  	    $mail->CharSet          = "utf-8";
  	    $mail->WordWrap         = "1000";
  	    // $mail->Encoding         = "base64";
  	    $mail->Priority         = "normal";
  	    $mail->SMTPAuth         = TRUE;
  	    $mail->Username         = $email_account->getSMTPLogin();
  	    $mail->Password         = $email_account->getSMTPPassword();
  
  			$mail->SetFrom($email_account->getEmail(), $email_account->getName());
  			$mail->Subject = $this->getSubject();
  
        $mail->addCustomHeader('X-PROZER: '.pz::getProperty("version"));
  
  			// TODO
  			// - richtig splitten, Cc, Bcc nach email und name..
  			$tos = explode(",",$this->getTo());
  			foreach($tos as $to) {
  				$mail->AddAddress($to, $to);
  			}
  
  			if($this->getCc() != "") {
  				$ccs = explode(",",$this->getCc());
  				foreach($ccs as $cc) {
  					$mail->AddCC($cc, $cc);
  				}
  			}
  
  			if($this->getBcc() != "") {
  				$bccs = explode(",",$this->getBcc());
  				foreach($bccs as $bcc) {
  					$mail->AddBCC($bcc, $bcc);
  				}
  			}
  			
  			if($this->getMessageHTML() != "") {
  				$mail->AltBody = $this->getBody();
  				$mail->MsgHTML($this->getMessageHTML());
  			}else {
  				$mail->Body = $this->getBody();
  			}
  		
  			$clip_ids = explode(",",$this->getClipIds());
  			$clips = array();
  			foreach($clip_ids as $clip_id) 
  			{
  				$clip_id = (int) $clip_id;
  				if(($clip = pz_clip::get($clip_id))) 
  				{
  					if(file_exists($clip->getPath()))
  					{
  						$mail->AddAttachment($clip->getPath(), $clip->getFilename());
  						$clips[] = $clip->getId();
  					}
  				}
  			}

    		if ($mail->Send() != 1)
    		{
		      ob_end_clean();
		      $this->saveToHistory('error', 'send', $mail->ErrorInfo);
    			return false;
    		}
    		
      } catch (phpmailerException $e) {
        // $e->getMessage()
        $this->saveToHistory('error', 'send', $mail->ErrorInfo);
		    ob_end_clean();
        return false;
      }
		  ob_end_clean();

	
			$u = pz_sql::factory();
			// $u->debugsql = 1;
			$u->setTable("pz_email");
			$u->setWhere(array('id'=>$this->getId()));
			$u->setValue("status",1);
			$u->setValue("date",date("Y-m-d H:i:s"));
			$u->setValue("send",1);
			$u->setValue("readed",1);
			$u->setValue("draft",0);
			
			if(count($clips)>0)
				$u->setValue("has_attachments",1);
			
			$u->update();

			$filepath = $this->getFilePath();
			rex_file::put($filepath,$mail->getMIMEHeader().$mail->getMIMEBody());

			$this->setRawHeader($mail->getMIMEHeader());
			$this->refreshHeaderInfo();

			$reply_id = (int) $this->getReplyId();
			
			if($reply_id > 0)
			{
				$u = pz_sql::factory();
				$u->setTable("pz_email");
				$u->setWhere(array('id'=>$reply_id));
				$u->setValue("replied_id",$this->getId());
				$u->update();
			}

			$forward_id = (int) $this->getForwardId();
			if($forward_id > 0)
			{
				$u = pz_sql::factory();
				$u->setTable("pz_email");
				$u->setWhere(array('id'=>$forward_id));
				$u->setValue("forwarded_id",$this->getId());
				$u->update();
			}

			return TRUE;			
		
		}
		$this->saveToHistory('error', 'send', 'account is missing');
		return FALSE;
	
	}
	
	public function trash() {
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set trash=1, project_id = 0 where id = ?', array($this->getId()));
		$this->update();
		$this->saveToHistory('update','trash');
	}

	public function untrash() {
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set trash=0 where id = ?', array($this->getId()));
		$this->update();
		$this->saveToHistory('update','untrash');
	}

	public function updateStatus($status = 0) {
		if($status != 1)
			$status = 0;
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set status=? where id = ?', array($status,$this->getId()));
		$this->update();	
	}

	public function readed() {
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set readed=1 where id = ?', array($this->getId()));
		$this->update();
	}

	public function unreaded() {
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set readed=0 where id = ?', array($this->getId()));
		$this->update();
	}

	public function moveToProjectId($project_id = 0) 
	{
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set trash=0,project_id=? where id = ?', array($project_id,$this->getId()));
		$this->update();
		
		$this->setProjectId($project_id);
		$this->saveToHistory('update','movetoproject');
	}

	public function removeFromProject() 
	{
		$u = pz_sql::factory();
		$u->setQuery('update pz_email set trash=0,project_id=? where id = ?', array(0,$this->getId()));
		$this->update();
		$this->saveToHistory('update','removefromproject');
	}

	public function update() 
	{
		
	}

	public function create() 
	{
		$this->saveToHistory('add');
	}

	public function delete() 
	{

		$this->saveToHistory('delete');

		if($this->getId() == "") {
			return FALSE;
    }

		if($this->isDraft()) {
			$d = pz_sql::factory();
			$d->setQuery('delete from pz_email where id = ?', array($this->getId()));
			return TRUE;
	
		}else if($this->isTrash()) {
			$d = pz_sql::factory();
			$d->setQuery('delete from pz_email where id = ?', array($this->getId()));
  		rex_dir::delete($this->getFolder());
      return TRUE;		  
		  
		}
		return FALSE;
	}

	// --------------------------------------------------------------------------

	public function refreshHeaderInfo() 
	{

		$headerinfo = pz_eml::parseHeaderToArray($this->getHeader());
		
		$update = array();
		if(@$headerinfo["from"] != "") {
			$update["from"] = $headerinfo["from"];
			$update["from_emails"] = $headerinfo["from_emails"];
		}
		if(@$headerinfo["to"] != "") {
			$update["to"] = $headerinfo["to"];
			$update["to_emails"] = $headerinfo["to_emails"];
		}
		if(@$headerinfo["cc"] != "") {
			$update["cc"] = $headerinfo["cc"];
			$update["cc_emails"] = $headerinfo["cc_emails"];
		}

		if(@$headerinfo["subject"] != "") {
			$update["subject"] = $headerinfo["subject"];
		}
		if(@$headerinfo["content_type"] != "") {
			$update["content_type"] = $headerinfo["content_type"];
		}

		if(count($update)>0) {
			$u = pz_sql::factory();
			// $u->debugsql = 1;
			$u->setTable('pz_email');
			$u->setWhere(array('id' => $this->getId()));
			foreach($update as $k => $v) {
				$u->setValue($k,$v);
			}
			$u->update();

		}

		return TRUE;
	}

  public function saveToHistory($mode = 'update', $func = '', $message = '')
  {
    $sql = pz_sql::factory();
    $sql->setTable('pz_history')
      ->setValue('control', 'email')
      ->setValue('func', $func)
      ->setValue('data_id', $this->getId())
      ->setValue('project_id', $this->getProjectId())
      ->setValue('user_id', pz::getUser()->getId())
      ->setRawValue('stamp', 'NOW()')
      ->setValue('mode', $mode)
      ->setValue('message', $message);
      
    if($mode != 'delete')
    {
      $data = $this->vars;
      unset($data['vt']);
      $sql->setValue('data', json_encode($data));
    }
    $sql->insert();
  }




}





