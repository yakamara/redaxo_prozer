<?php

class pz_email_account extends pz_model{

	var $vars = array();
	var $emails = array();

	public function __construct($vars) {
		parent::__construct($vars);
	}

	static public function get($id = "", $user_id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;
		
		$where = ' where id = '.$id;
		if($user_id != "")
		{
			$user_id = (int) $user_id;
			$where .= ' and user_id = '.$user_id;
		}
		
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$sql->setQuery('select * from pz_email_account '.$where.' LIMIT 2');
		
		$email_accounts = $sql->getArray();
		if(count($email_accounts) != 1) return FALSE;
				
		$class = get_called_class();
		return new $class($email_accounts[0]);
	}

	public function getId()
	{
		return $this->getVar("id");
	}

	public function getName()
	{
		return $this->getVar("name");
	}
	
	public function getEmail()
	{
		return $this->getVar("email");
	}
	
	public function getEmails()
	{
		// saved emails after download
		return $this->emails;
	}
	
	
	public function getDeleteEmails()
	{
		if($this->vars["delete_emails"] == 1)
		{
			return TRUE;	
		}
		return FALSE;
	}
	
	public function getPOP3()
	{
		$return = array();
		$return["host"] = $this->getVar("pop3");
		$return["login"] = $this->getVar("login");
		$return["password"] = $this->getVar("password");
		return $return;
	}
 
 	public function getUserId()
 	{
 		return $this->getVar("user_id");
 	}
 
 	public function getPort()
 	{
 		return $this->getVar("port");
 	}
 	
 	public function getHost()
 	{
 		return $this->getVar("host");
 	}
 
 	public function getLogin()
 	{
 		return $this->getVar("login");
 	}

 	public function getPassword()
 	{
 		return $this->getVar("password");
 	}
 	
 	public function getSSL()
 	{
 		if($this->getVar("ssl") == 1)
 			return TRUE;
 		return FALSE;
 	} 
 
 	public function getMailboxtype()
 	{
 		if($this->getVar("mailboxtype") == "imap")
 			return "imap";
 		return "pop3";	
 	}
 
 	public function getSMTPHostInfo()
 	{
 		return $this->getVar("smtp");
 	}

 	public function getSMTPHost()
 	{
 	  $smtphostinfo = explode(":",$this->getSMTPHostInfo());
 	  return $smtphostinfo[0];
 	}
 
 	public function getSMTPPort()
 	{
 	  $smtphostinfo = explode(":",$this->getSMTPHostInfo());
 	  if(count($smtphostinfo)>1)
 	    return intval($smtphostinfo[1]);
 	  return 25;
 	}
 
 	public function getSMTPLogin()
 	{
 		return $this->getVar("login");
 	}

 	public function getSMTPPassword()
 	{
 		return $this->getVar("password");
 	}
 	
 	public function getSignature()
 	{
 		return $this->getVar("signature");
 	}
 
  public function getLastLoginDate()
  {
    $format = 'Y-m-d H:i:s';
    return DateTime::createFromFormat($format, $this->getVar("last_login"));
  }

  public function getLastLoginFinishedDate()
  {
    $format = 'Y-m-d H:i:s';
    return DateTime::createFromFormat($format, $this->getVar("last_login_finished"));
  }
 
  // -------------------------
 
	static function getAccounts($user_id = "", $status = "")
	{
		$where = array();
		$params = array();
		
		if($user_id != "")
		{	
			$params[] = (int) $user_id;
			$where[] = ' user_id = ? ';
		}

		if($status != "")
		{	
			$params[] = (int) $status;
			$where[] = ' status = ? ';
		}

		$sql_where = "";
		if(count($params)>0)
			$sql_where = ' where '.implode(" AND ",$where);

		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$es = $sql->getArray('select * from pz_email_account '.$sql_where, $params);
		
		$email_accounts = array();
		foreach($es as $e)
		{
			$email_accounts[] = new pz_email_account($e);
		}
		return $email_accounts;
	}

	static function getAsArray($user_id = "") {
	
		$return = array();
		foreach(self::getAccounts($user_id) as $account) {
			$label = '"'.$account->getName().'" <'.$account->getEmail().'>';
      $id = $account->getId();
      $return[] = array("label"=> $label, "id"=>$id);
		}
		return $return;
		
	}

	public function delete() {
	
		$d = rex_sql::factory();
		$d->setTable("pz_email_account");
		$d->setWhere(array("id"=>$this->getid()));
		$d->delete();	
			
		return TRUE;
		
	}


	public function downloadEmails()
	{
    $now = new DateTime("now");
    $last_login = $this->getLastLoginDate();
    $last_login_finished = $this->getLastLoginFinishedDate();    

    // emaildownload in progress and not longer than 10min.
    if($this->getVar("login_failed") == 0 && $last_login->diff($now)->format('%i')<20)
    {
      return rex_i18n::msg("email_account_last_login_working");
    }

		$u = rex_sql::factory();
		$u->setTable('pz_email_account');
		$u->setWhere(array('id'=>$this->getId()));
		$u->setValue("last_login",date("Y-m-d H:i:s"));
		$u->setValue("login_failed",0);
		$u->update();
		
		$return = "";
		
		# localhost pop3 with and without ssl
		# $authhost="{localhost:995/pop3/ssl/novalidate-cert}";
		# $authhost="{localhost:110/pop3/notls}";
		
		# localhost imap with and without ssl
		# $authhost="{localhost:993/imap/ssl/novalidate-cert}";
		# $authhost="{localhost:143/imap/notls}";

		$emails = 0;

		$authhost = $this->getHost();
		if($this->getMailboxtype() == "pop3" && $this->getSSL()) {
			$authhost .= ':995/pop3/ssl/novalidate-cert';
		}elseif($this->getMailboxtype() == "pop3") {
			$authhost .= ':110/pop3/notls';
		}elseif($this->getSSL()) {
			$authhost .= ':993/imap/ssl/novalidate-cert';
		}else {
			$authhost .= ':143/imap/notls';
		}
		$authhost = '{'.$authhost.'}INBOX';

		$delete = array();

		if ($mbox = @imap_open($authhost,$this->getLogin(),$this->getPassword()))
		{
			$emails = imap_headers($mbox);
			
			if ($emails !== false)
			{
				$email_id = 0;
			  foreach ($emails as $email) 
			  {

					$email_id++;
			    $email_header = imap_fetchheader($mbox,$email_id);
					$email_body = imap_body($mbox,$email_id);

					if($email_header != "") 
					{
						$email = new pz_email();
						$email->setRawHeader($email_header);
						$email->setRawBody($email_body);
						$email_header_array = pz_eml::parseHeaderToArray($email_header);
						
						if(isset($email_header_array["to"])) $email->setTo($email_header_array["to"]);
						if(isset($email_header_array["cc"])) $email->setCc($email_header_array["cc"]);
						if(isset($email_header_array["from"])) $email->setFrom($email_header_array["from"]);
	
						if(isset($email_header_array["to_emails"])) $email->setVar("to_emails", $email_header_array["to_emails"]);
						if(isset($email_header_array["cc_emails"])) $email->setVar("cc_emails", $email_header_array["cc_emails"]);
						if(isset($email_header_array["from_emails"])) $email->setVar("from_emails", $email_header_array["from_emails"]);
	
						if(isset($email_header_array["subject"])) $email->setSubject($email_header_array["subject"]);
						if(isset($email_header_array["content-type"])) $email->setContentType($email_header_array["content-type"]);
						if(isset($email_header_array["date"])) $email->setDate($email_header_array["date"]);
						if(isset($email_header_array["reply-to"])) $email->setReplyTo($email_header_array["reply-to"]);
						if(isset($email_header_array["importance"])) $email->setImportance($email_header_array["importance"]);
	
						// if(isset($email_header_array["message-id"])) $email->setMessageId($email_header_array["message-id"]);
						// else { 
    
							$key = "";
							foreach($email_header_array as $k => $v) {
								$key .= $k.$v;
							}	
							$message_id = $this->getUserId()."_".md5($key);
							$email->setMessageId($message_id);
						
						// }
						// echo "<br />".$message_id;

            unset($email_header);
            unset($email_body);
	
						$eml = new pz_eml($email->getRawHeader().$email->getRawBody());
						$email->setBody($eml->getFirstText());
	
						$email->setUserId($this->getUserId());
						$email->setAccountId($this->getId());
	
						if(!$email->save($eml))
						{
							
						}else
						{
							// $this->emails[] = $email;
							if($this->getDeleteEmails()) {
								imap_delete($mbox,$email_id);
							}
						}
						
						// if memory is to high . here 1/2 of memory limit 
						// .. break and load next emails later

            if( (memory_get_usage(true)*2) > pz::getIniGetInBytes(ini_get("memory_limit")))
            {
              break;
            }
						
					}
			  }
			    
				if($this->getDeleteEmails()) {
					imap_expunge ($mbox);
				}

				$return .= rex_i18n::msg("email_account_download_ok",$authhost);

			}else
			{
				$return .= rex_i18n::msg("email_account_download_failed",$authhost);
			}

			imap_close($mbox);

			$login_failed = -1;

  		$u = rex_sql::factory();
  		$u->setTable('pz_email_account');
  		$u->setWhere(array('id'=>$this->getId()));
  		$u->setValue("last_login_finished",date("Y-m-d H:i:s"));
  		$u->setValue("login_failed",$login_failed);
  		$u->update();


		}else 
		{
			$login_failed = 1;

  		$u = rex_sql::factory();
  		$u->setTable('pz_email_account');
  		$u->setWhere(array('id'=>$this->getId()));
  		$u->setValue("login_failed",$login_failed);
  		$u->update();

		}
				
		return $return;
		
	}

}

?>