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
		$from_address = NULL;


	function __construct($vars = array())
	{
		if(count($vars)>5) {
			$this->setVars($vars);
			$this->isEmail = TRUE;
			return TRUE;
		}
		return FALSE;
	}


	function get($email_id = "")
	{
		if($email_id == "") return FALSE;
		$email_sql = rex_sql::factory();
		$email_sql->setQuery('select * from pz_email where id = ? LIMIT 2', array($email_id));
		$emails = $email_sql->getArray();
		if(count($emails) != 1) return FALSE;
		return new pz_email($emails[0]);
	}


	function getEmailsFromProjects($project_ids = array())
	{
		$p_sql = array();
		if(count($project_ids)>0)
		{
			foreach($project_ids as $pid)
			{
				$p_sql[] = ' e.project_id = ?';
				$params[] = $pid;
			}
		}
		
		$emails = rex_sql::factory();
		$emails->setQuery('select * from pz_email as e where e.id = ?', $params);
		$emails_array = $emails->getArray();
		
		$emails = array();
		foreach($emails_array as $email)
		{
			$emails[] = new pz_email($email);
		}
		
		return $emails;
	}

	static function getAll( array $filter = array(), array $projects = array(), array $users = array())
	{
		$where = array();
		$params = array(); 

		// if project_id exist or user_id
		$where_projects_users = "";
		$where_projects = array();
		foreach($projects as $p)
		{
			$where_projects[] = $p->getId();
		}
		// $where_projects[] = 0;
		if(count($where_projects)>0) {
			$where_projects_users = 'FIND_IN_SET(project_id,"'.implode(",",$where_projects).'")';
		}
		$where_users = array();
		foreach($users as $u)
		{
			$where_users[] = $u->getId();
		}
		if(count($where_users)>0) {
			if($where_projects_users != "")
				$where_projects_users .= " OR ";
			$where_projects_users = '('.$where_projects_users.' FIND_IN_SET(user_id,"'.implode(",",$where_users).'")'.')';
		}
		
		$where[] = $where_projects_users;
	
	    // ----- Filter

		$f = pz::getFilter($filter,$where,$params);
		$where = $f["where"];
		$params = $f["params"];
		$where_sql = $f["where_sql"];
	
	    $sql = rex_sql::factory();
	    // $sql->debugsql = 1;
	    // $sql->setQuery('SELECT * FROM pz_email '.$where_sql .' order by id desc LIMIT 5000', $params); // ORDER BY p.name
	    // $emails_array = $sql->getArray();
	    $emails_array = $sql->getArray('SELECT * FROM pz_email '.$where_sql .' order by id desc LIMIT 5000', $params);
	    
	    $emails = array();
	    foreach($emails_array as $email)
	    {
	      $emails[] = new pz_email($email);
	    }
	    return $emails;
	}


	// ---------------- getter

	public function getVars() {
		return $this->vars;	
	}

	public function getId() {
		return $this->vars["id"];
	}

	public function getProjectId() {
		return $this->vars["project_id"];
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

	public function getBcc() {
		return $this->vars["bcc"];
	}

	public function getCcEmails() {
		return $this->vars["cc_emails"];
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
	
	public function getCreateDate() {
		return $this->vars["created"];
	}
	
	public function getDate() {
	
		$date = $this->vars["date"];
		if($date == "")
			$date = $this->vars["created"];
		return $date;
	}

	public function getSubject() {
		$subject = $this->vars["subject"];
		if($subject != "") {
			return $subject;
		}
		return rex_i18n::msg("no_subject_entered");
	}

	public function getEml() {
		return file_get_contents($this->getFilePath());
	}

	public function getBody() {
		return $this->vars["body"];
	}

	public function getBodyHTML() {
		return $this->vars["body_html"];
	}

	public function getMessageId() {
		return $this->vars["message_id"];
	}

	public function getAccountId() {
		return $this->vars["account_id"];
	}

	public function getReplyId() {
		return $this->vars["reply_id"];
	}

	public function getForwardId() {
		return $this->vars["forward_id"];
	}

	// only send mail
	public function getClipIds() {
		return $this->vars["clip_ids"];
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

	public function isDraft() {
		if($this->vars["draft"] == 1) 
			return TRUE;
		return FALSE;
	}

	public function isTrash() {
		if($this->vars["trash"] == 1) 
			return TRUE;
		return FALSE;
	}

	public function hasProject() {
		if($this->vars["project_id"] > 0) 
			return TRUE;
		return FALSE;
	
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

	public function save()
	{
		
		if($this->getMessageId() == "")
		{
			$message_id = md5(time()+"prozer"+date("Ymd")+microtime())."_prozer";
			$this->setMessageId($message_id);
		}
		
		$this->setCreated(date("Y-m-d H:i:s"));
		$this->setUpdated(date("Y-m-d H:i:s"));
		
		$get_email = rex_sql::factory();
		// $get_email->debugsql = 1;
		$get_email->setQuery('select id from pz_email where message_id = ?',array($this->getMessageId()));
		
		if($get_email->getRows() == 0)
		{
			// email does not exist
			$add_email = rex_sql::factory();
			// $add_email->debugsql = 1;
			$add_email->setTable('pz_email');
			foreach($this->getVars() as $k => $v)
			{
				$add_email->setValue($k,$v);
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


	public function sendDraft() {

		if($email_account = pz_email_account::get($this->getAccountId()))
		{
		
			
		
		
			$mail = new pz_mailer();

			$mail->From             = $email_account->getEmail();
		    $mail->FromName         = $email_account->getName();
		    $mail->ConfirmReadingTo = "";
		    $mail->Mailer           = "smtp";
		    $mail->Host             = $email_account->getSMTPHost();
		    $mail->CharSet          = "utf-8";
		    $mail->WordWrap         = "1000";
		    // $mail->Encoding         = "base64";
		    $mail->Priority         = "normal";
		    $mail->SMTPAuth         = TRUE;
		    $mail->Username         = $email_account->getSMTPLogin();
		    $mail->Password         = $email_account->getSMTPPassword();

			$mail->SetFrom($email_account->getEmail(), $email_account->getName());
			$mail->Subject = $this->getSubject();

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
			
			if($this->getBodyHTML() != "") {
				$mail->AltBody = $this->getBody();
				$mail->MsgHTML($this->getBodyHTML());
			}else {
				$mail->Body = $this->getBody();
			}
		
			$clip_ids = explode(",",$this->getClipIds());
			$clips = array();
			foreach($clip_ids as $clip_id) {
				$clip_id = (int) $clip_id;
				if($clip_id > 0 && $clip = pz_clipboard::getClipById($clip_id)) {
					// content_type / content_length
					$clip["path"] = pz_clipboard::getPath($clip["id"],$clip["user_id"]);
					if(file_exists($clip["path"]))
						$mail->AddAttachment($clip["path"], $clip["filename"]);
				}
			}
		
			if ($mail->Send() == 1)
			{
				$u = rex_sql::factory();
				// $u->debugsql = 1;
				$u->setTable("pz_email");
				$u->setWhere(array('id'=>$this->getId()));
				$u->setValue("status",1);
				$u->setValue("date",date("Y-m-d H:i:s"));
				$u->setValue("send",1);
				$u->setValue("readed",1);
				$u->setValue("draft",0);
				$u->update();

				$filepath = $this->getFilePath();
				rex_file::put($filepath,$mail->getMIMEHeader().$mail->getMIMEBody());

				$this->setRawHeader($mail->getMIMEHeader());
				$this->refreshHeaderInfo();

				$reply_id = (int) $this->getReplyId();
				
				if($reply_id > 0)
				{
					$u = rex_sql::factory();
					$u->setTable("pz_email");
					$u->setWhere(array('id'=>$reply_id));
					$u->setValue("replied_id",$this->getId());
					$u->update();
				}

				$forward_id = (int) $this->getForwardId();
				if($forward_id > 0)
				{
					$u = rex_sql::factory();
					$u->setTable("pz_email");
					$u->setWhere(array('id'=>$forward_id));
					$u->setValue("forwarded_id",$this->getId());
					$u->update();
				}

				return TRUE;			
			}
		
		}
		
		return FALSE;
	
	}


	
	public function trash() {
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set trash=1 where id = ?', array($this->getId()));
		$this->update();
	}

	public function untrash() {
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set trash=0 where id = ?', array($this->getId()));
		$this->update();
	}

	public function updateStatus($status = 0) {
		if($status != 1)
			$status = 0;
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set status=? where id = ?', array($status,$this->getId()));
		$this->update();	
	}

	public function readed() {
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set readed=1 where id = ?', array($this->getId()));
		$this->update();
	}

	public function unreaded() {
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set readed=0 where id = ?', array($this->getId()));
		$this->update();
	}

	public function moveToProjectId($project_id = 0) {
		$u = rex_sql::factory();
		$u->setQuery('update pz_email set trash=0,project_id=? where id = ?', array($project_id,$this->getId()));
		$this->update();	
	}

	public function update() {
	}

	public function create() {
	}

	public function delete() {

		if($this->getId() == "")
			return FALSE;

		if($this->isDraft())
		{
			$d = rex_sql::factory();
			$d->setQuery('delete from pz_email where id = ?', array($this->getId()));
			return TRUE;
		}
		// TODO
		// Aus der Datenbank löschen
		
		// getFolder()
		
		// Aus dem Filesystem löschen
		
		// Verknüpfungen
		// - in Projekten
		// - Historien
		// etc . löschen
		
		return FALSE;
	}

	// --------------------------------------------------------------------------

	public function refreshHeaderInfo() {

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

		if(count($update)>0)
		{
			$u = rex_sql::factory();
			// $u->debugsql = 1;
			$u->setTable('pz_email');
			$u->setWhere(array('id' => $this->getId()));
			foreach($update as $k => $v)
			{
				$u->setValue($k,$v);
			}
			$u->update();

		}

		return TRUE;
	}






}





