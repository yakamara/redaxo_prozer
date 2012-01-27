<?php

class pz_user extends rex_user
{

	static private $users;
	private $perms,$config,$inline_image;

	public function __construct(rex_sql $sql)
	{
	  parent::__construct($sql);
	  $this->setRoleClass('pz_user_role');
	  
	  $this->perms = @unserialize($this->getValue("perms"));
	  $this->config = @unserialize($this->getValue("config"));
	  
	  if(!is_array($this->perms))
	  	$this->perms = array();

	  if(!is_array($this->config))
	  	$this->config = array();
	  
	}

	public function getId()
	{
	  return $this->getValue('id');
	}

	public function getLogin()
	{
	  return $this->getUserLogin();
	}

	public function getPassword()
	{
	  return $this->getValue('password');
	}

	public function getEmail()
	{
	  return strtolower($this->getValue('email'));
	}
	
	public function getEmails()
	{
	  // eigene Email nehmen - >addressbuch abfragen und zurück
	  if($this->getValue('email') == "")
	  	return array();

	  $emails = array();
	  if(($address = pz_address::getByEmail($this->getValue('email'))))
	  {
		$emails = $address->getFieldsByType("EMAIL");
	  }else
	  {
	  	$emails[] = strtolower($this->getValue('email'));
	  }
		
	  return $emails;
	}

	public function isActive()
	{
		if($this->getValue('status') == 1)
			return TRUE;
		return FALSE;	
	}

	public function getDigest()
	{
		return md5($this->getUserLogin() .':prozer:'. $this->getValue("password"));
	}

	public function getAPIKey()
	{
		return $this->getValue("digest");
	}
	
	public function getFolder()
	{
		return rex_path::addonData('prozer', 'users/'.$this->getId());
	}

	public function getInlineImage()
	{
		// $inline_image
		
		if($this->inline_image != "") {
			return $this->inline_image;
		
		}elseif($this->getEmail() == "") {
			return pz_user::getDefaultImage();
		
		}elseif(($address = pz_address::getByEmail($this->getEmail()))) 
		{
			$this->inline_image = $address->getInlineImage();
			return $this->inline_image;
		
		}
		
		return pz_user::getDefaultImage();
	}

	public function getDefaultImage() {
		return "/assets/addons/prozer/css/user.png";
	}

	static function digest($login,$password) 
	{
		return md5($login .':prozer:'. $password);
	}

	public function update() {
		
		$u = rex_sql::factory();
		// $u->debugsql = 1;
		$u->setTable('pz_user');
		$u->setWhere(array('id'=>$this->getId()));
		$u->setValue('digest',pz_user::digest($this->getLogin(),$this->getPassword()));
		$u->update();		
		
	}

	public function create() {
		$this->update();
	}

	public function hasPerm($perm)
	{
		if(in_array($perm,$this->perms))
			return TRUE;
		return FALSE;
	}

	public function addPerm($perm)
	{
		if(!in_array($perm,$this->perms) && is_string($perm))
			$this->perms[] = $perm;
	}

	public function removePerm($perm)
	{
		if(in_array($perm,$this->perms))
		{
			$perms = array();
			foreach($this->perms as $p) {
				if($perm != $p) {
					$perms[] = $p;
				}
			}
			$this->perms = $perms;
		}
	}

	public function savePerm()
	{
		$perms = array();
		foreach($this->perms as $p) {
			if(is_string($p))
				$perms[] = $p;
		}
		
		$u = rex_sql::factory();
		// $u->debugsql = 1;
		$u->setTable('pz_user');
		$u->setWhere(array('id'=>$this->getId()));
		$u->setValue('perms',serialize($perms));
		$u->update();
	}

	public function getConfig($key)
	{
		if(array_key_exists($key,$this->config))
			return $this->config[$key];
		return "";
	}

	public function setConfig($key,$value)
	{
		$this->config[$key] = $value;
	}
	
	public function saveConfig()
	{
		$u = rex_sql::factory();
		// $u->debugsql = 1;
		$u->setTable('pz_user');
		$u->setWhere(array('id'=>$this->getId()));
		$u->setValue('config',serialize($this->config));
		$u->update();
	}

	public function getStartpage()
	{
		$startpage = $this->getConfig("startpage");
		if($startpage == "")
			$startpage = "projects";
		return $startpage;
	}



  /**
   * @return pz_user
   */
  static public function get($id, $fresh = FALSE)
  {
    if(isset($users[$id]) && !$fresh)
    {
      return $users[$id];
    }

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_user WHERE id = ? LIMIT 2', array($id));
    $user = null;
    if($sql->getRows() == 1)
    {
      $user = new self($sql);
    }
    return $users[$id] = $user;
  }


  // -------------------------------------------------------------------- Customers

  public function getCustomers($filter = array())
  {

  	$params = array();

  	$where = '';
  	$nfilter = array();
  	foreach($filter as $f)
	{
		switch($f["field"])
		{
			case("archived"):
			case("name"):
				$nfilter[] = $f;
		}
	}

    foreach($nfilter as $f)
    {
    	if($where != "") $where .= ' AND ';
    	else $where = ' where ';

    	$where .= 'c.'.$f["field"].'';
    	switch($f["type"]) {
    		case("like"):
    			$where .= ' LIKE ? ';
    			$f["value"] = "%".$f["value"]."%";
    			break;
    		case("="):
			default:
				$where .= '= ? ';
    	}
    	$params[] = $f["value"];
    }

  	$sql = rex_sql::factory();
  	// $sql->debugsql = 1;
    $sql->setQuery('SELECT c.* FROM pz_customer c '.$where.' ORDER BY c.name',$params);
    $customers = array();
    foreach($sql->getArray() as $row)
    {
      $customers[] = new pz_customer($row);
    }
    return $customers;

  }

  // -------------------------------------------------------------------- Cal

  public function getEvents(array $projects, DateTime $from = null, DateTime $to = null)
  {
  	// !! time matters
  	$events = pz_calendar_event::getAll($projects, $from, $to);
  	$jobs = $this->getJobs($projects, $from, $to);
  	$events = array_merge($events,$jobs);
	return $events;
  }

  public function getJobs(array $projects, DateTime $from = null, DateTime $to = null)
  {
  	// !! time matters
  	$events = pz_calendar_event::getAll($projects, $from, $to, true, $this->getId());
  	return $events;
  }

  public function getJobTime(array $projects, DateTime $from = null, DateTime $to = null)
  {
    return pz_calendar_event::getJobTime($projects, $this->getId(), $from, $to);
  }

  public function getEventEditPerm($event)
  {
	// solange bis es eingebaut ist.
	
	if($event->hasRule())
		return FALSE;
	
	if($event->getUserId() == $this->getId())
		return TRUE;	
  	
 	// TODO: check if in project and/or projektadmin



	return FALSE;
  }

  public function getEventViewPerm($event)
  {
  	// TODO:
    return TRUE;	
  }

  public function getAttandeeEvents()
  {
  	$events = pz_calendar_attendee::getEventsByEmail($this->getEmails());
  	return $events;
  }

  public function countAttendeeEvents()
  {
  	return count($this->getAttandeeEvents());
  }


  // -------------------------------------------------------------------- E-Mail Account

  public function getEmailaccounts()
  {
		return pz_email_account::getAccounts($this->getId());
  }

  public function getEmailaccountsAsString() {
	
		$return = array();
		foreach(pz_email_account::getAccounts($this->getId()) as $email_account) {
			$v = $email_account->getName();
			$v = str_replace('=','',$v);
			$v = str_replace(',','',$v);
			$return[] = $v.'='.$email_account->getId();
		}
		return implode(",",$return);
		
  }

  public function getDefaultEmailaccountId() {

		if($this->getValue("account_id")>0)
			return $this->getValue("account_id");

		$accounts = $this->getEmailaccounts();

		if(is_array($accounts) && count($accounts)>0) {
			$account = current($accounts);
			return $account->getId();
		}
		
		return FALSE;
		
  }


  // -------------------------------------------------------------------- Users

  public function getUsers($filter = array())
  {
    return $users = pz::getUsers($filter);
  }


  // -------------------------------------------------------------------- Addresses

  public function getAddresses($fulltext = "")
  {
  	$filter = array();
  	if($fulltext != "")
  		$filter[] = array("field"=>"vt","type"=>"like","value"=>$fulltext);
  	$filter[] = array("field"=>"created_user_id","value"=>$this->getId());
  	return pz_address::getAll($filter);
  }


  // -------------------------------------------------------------------- Projects

  public function getArchivedProjects($filter = array())
  {
  	// Alle nicht archivierten (archived != 1) Projekte
  	// + in den man eingtragen ist (table:project_user) ODER man hat in seinen Rolle den Projekt Admin

	$filter[] = array("field" => "archived", "value" => 1);

    if($this->isAdmin())
      return $this->_getProjects('', false, $filter);

    return $this->_getProjects('', true, $filter);
  }

  /*
  	Alle, auch die archivierten Projekte
  */

  public function getAllProjects($filter = array())
  {
    if($this->isAdmin())
      return $this->_getProjects('', false, $filter);
    return $this->_getProjects('', true, $filter);
  }

  public function getProjects($filter = array())
  {
  	// Alle nicht archivierten (archived != 1) Projekte
  	// + in den man eingtragen ist (table:project_user)

	$filter[] = array("field" => "archived", "value" => 0);
	if($this->isAdmin()) 
		return $this->_getProjects('', false, $filter);

    return $this->_getProjects('', true, $filter);
  }

  /*
  	Alle aktuellen, nicht archivierten Projekte
  */

  public function getMyProjects($filter = array())
  {
  	// Alle nicht archivierten (archived != 1) Projekte
  	// + in den man eingtragen ist (table:project_user) ODER man hat in seinen Rolle den Projekt Admin

	$filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('', true, $filter);
  }

  public function getCalendarProjects($filter = array())
  {
  	// Alle getProjects
  	// + im Projekt ist Calendar aktiviert

	$filter[] = array("field" => "has_calendar", "value" => 1);
	$filter[] = array("field" => "archived", "value" => 0);

    return $this->_getProjects('(pu.calendar = 1 OR pu.admin = 1)', true, $filter);
  }

  public function getCalDavProjects($filter = array())
  {
  	// Alle getProjects
  	// + im Projekt ist Kalender aktiviert
  	// + Persönliche Einstellung, dieser Calendar ist freigeschaltet

	$filter[] = array("field" => "has_calendar", "value" => 1);
	$filter[] = array("field" => "archived", "value" => 0);

    return $this->_getProjects('(pu.caldav = 1 )', true, $filter);
  }

  public function getCalDavJobsProjects($filter = array())
  {
  	// Alle getProjects
  	// + im Projekt ist Kalender aktiviert
  	// + Persönliche Einstellung, dieser Calendar ist freigeschaltet

	$filter[] = array("field" => "has_calendar", "value" => 1);
	$filter[] = array("field" => "archived", "value" => 0);

    return $this->_getProjects('(pu.caldav_jobs = 1 )', true, $filter);
  }

  public function getWebDavProjects($filter = array())
  {
  	// Alle getProjects
  	// + im Projekt sind Files aktiviert
	// + Nur User, die das WebDavRecht haben
  	// + Persönliche Einstellung, dieser WebDav files Ordner ist freigeschaltet

	if(pz::getUser()->hasPerm('webdav') || pz::getUser()->isAdmin())
	{
		$filter[] = array("field" => "has_files", "value" => 1);
		$filter[] = array("field" => "archived", "value" => 0);
	    return $this->_getProjects('(pu.files = 1 OR pu.admin = 1)', true, $filter);
	}
	return array();
  }

  public function getEmailProjects($filter = array())
  {

	$filter[] = array("field" => "has_emails", "value" => 1);
	$filter[] = array("field" => "archived", "value" => 0);
	
    return $this->_getProjects('(pu.emails = 1 OR pu.admin = 1)', true, $filter);
  }

  public function getProjectById($project_id) 
  {
  	$filter = array();
	$filter[] = array("field" => "has_emails", "value" => 1);
	$filter[] = array("field" => "id", "value" => $project_id);
	$projects = $this->_getProjects('', true, $filter);

  	if(count($projects) != 1) 
  		return FALSE;
  	$project = current($projects);
	return $project;

  }

  private function _getProjects($where_string = '', $join = true, $filter = array(), $orderby = 'p.name')
  {
  	$where = array();
  	if($where_string != "") $where[] = $where_string;

    $params = array();
    if($join)
    {
      $join = ' INNER JOIN pz_project_user pu ON pu.project_id = p.id';
      $where[] = 'pu.user_id = ?';
      $params[] = $this->getId();
    }

    // ----- Filter

    $nfilter = array();
	foreach($filter as $f)
	{
		switch($f["field"]) {
	  		case("id"):
	  		case("name"):
	  		case("archived"):
	  		case("customer_id"):
	  		case("has_calendar"):
	  		case("has_files"):
	  		case("has_emails"):
	  		case("label_id"):
	  			$f["field"] = "p.".$f["field"];
	  			$nfilter[] = $f;
	  			break;
		}
	}

// echo '<pre>';var_dump($nfilter); echo '</pre>';

    // ----- Filter

	$f = pz::getFilter($nfilter,$where,$params);
	$where = $f["where"];
	$params = $f["params"];
	$where_sql = $f["where_sql"];

    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $sql->setQuery('SELECT p.* FROM pz_project p'. $join .' '. $where_sql .' ORDER BY '.$orderby, $params);
    $projects = array();
    foreach($sql->getArray() as $row)
    {
      $projects[] = new pz_project($row);
    }
    return $projects;
  }


  // -------------------------------------------------------------------- emails

  
  public function countInboxEmails() {
  
  	$filter = array();
    $filter[] = array("field" => "send", "value" => 0);
	$filter[] = array("field" => "trash", "value" => 0);
	$filter[] = array("field" => "draft", "value" => 0);
	$filter[] = array("field" => "spam", "value" => 0);
	$filter[] = array("field" => "status", "value" => 0);
	$filter[] = array("field" => "readed", "value" => 0);

	$projects = pz::getUser()->getEmailProjects();

	return count(pz_email::getAll($filter, $projects, array(pz::getUser())));
  }


  public function getInboxEmails(array $filter = array(), array $projects = array())
  {
	$filter[] = array("field" => "send", "value" => 0);
	$filter[] = array("field" => "trash", "value" => 0);
	$filter[] = array("field" => "draft", "value" => 0);
	$filter[] = array("field" => "spam", "value" => 0);
	
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getOutboxEmails(array $filter = array(), array $projects = array())
  {
	$filter[] = array("field" => "send", "value" => 1);
	$filter[] = array("field" => "trash", "value" => 0);
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getSpamEmails(array $filter = array(), array $projects = array())
  {
	$filter[] = array("field" => "spam", "value" => 1);
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getTrashEmails(array $filter = array(), array $projects = array())
  {
	$filter[] = array("field" => "trash", "value" => 1);
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getDraftsEmails(array $filter = array(), array $projects = array())
  {
	$filter[] = array("field" => "draft", "value" => 1);
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getAllEmails(array $filter = array(), array $projects = array())
  {
	// $filter[] = array("field" => "trash", "value" => 0);
	// $filter[] = array("field" => "draft", "value" => 0);
	$filter[] = array("field" => "spam", "value" => 0);
	return pz_email::getAll($filter, $projects, array(pz::getUser()));
  }

  public function getEmailById($email_id) 
  {
  	$projects = $this->getEmailProjects();
  	
  	$filter = array();
  	$filter[] = array("field" => "id", "value" => $email_id);
  	$emails = pz_email::getAll($filter, $projects, array(pz::getUser()));;

  	if(count($emails) != 1) 
  		return FALSE;
  	$email = current($emails);
	return $email;

  }


}






