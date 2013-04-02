<?php

class pz_user extends rex_user
{

	static private $users;
	private $perms,$config,$inline_image,$emails,$active_user = NULL;

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

  public function getVars()
  {
    $v = array("id","email","status","name");
    $vars = array();
    foreach($v as $v) {
      $vars[$v] = $this->getValue($v);
    }
    return $vars;
  }


	public function getId()
	{
	  return $this->getValue('id');
	}

	public function getLogin()
	{
	  return $this->getLogin();
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

	  if(isset($this->emails) && is_array($this->emails))
	  	return $this->emails;

	  $emails = array();
	  if(($address = pz_address::getByEmail($this->getValue('email'))))
	  {
		  $emails = $address->getFieldsByType("EMAIL");
	  }else
	  {
	  	$emails[] = strtolower($this->getValue('email'));
	  }

	  $this->emails = $emails;

	  return $this->emails;
	}

	public function isActive()
	{
		if($this->getValue('status') == 1)
			return TRUE;
		return FALSE;
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
		if($this->inline_image != "")
		{
			return $this->inline_image;

		}elseif($this->getEmail() == "")
		{
			return pz_user::getDefaultImage();

		}elseif(($address = pz_address::getByEmail($this->getEmail())))
		{
			$this->inline_image = $address->getInlineImage();
			return $this->inline_image;

		}

		return pz_user::getDefaultImage();
	}

  // ----------------- static

	static public function getDefaultImage()
	{
		return "/assets/addons/prozer/css/user.png";
	}

  static public function get($id, $refresh = FALSE)
  {
    if(isset(pz_user::$users[$id]) && !$refresh)
    {
      return pz_user::$users[$id];
    }

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_user WHERE id = ? LIMIT 2', array($id));
    $user = null;
    if($sql->getRows() == 1)
    {
      $user = new self($sql);
    }
    return pz_user::$users[$id] = $user;
  }


  // ----------------- Date

  static function getDateTime($datetime = NULL)
  {
    // TODO: Userspecifix Timezone
    // current default: Europe/Berlin

    if(!$datetime)
      $datetime = new DateTime();

    $datetime->setTimezone(new DateTimeZone('Europe/Berlin'));
    return $datetime;
  }



  // -----------------

  public function saveToHistory($mode = 'update', $func = '')
  {

    $fields = array("id", "name", "status", "login", "login_tries", "lasttrydate", "last_login", "session_id", "cookiekey", "admin", "created", "updated", "address_id", "email", "account_id", "config", "perms", "comment");

    $sql = rex_sql::factory();
    $sql->setTable('pz_history')
      ->setValue('control', 'user')
      ->setValue('func', $func)
      ->setValue('data_id', $this->getId())
      ->setValue('user_id', pz::getUser()->getId())
      ->setRawValue('stamp', 'NOW()')
      ->setValue('mode', $mode);

    $data = array();
    $data["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $data["QUERY_STRING"] = $_SERVER["QUERY_STRING"];
    
    $data["SCRIPT_URI"] = "";
    if(isset($_SERVER["SCRIPT_URI"])) {
      $data["SCRIPT_URI"] = $_SERVER["SCRIPT_URI"];
    } else if (isset($_SERVER["SCRIPT_URI"])) {
      $data["SCRIPT_URI"] = $_SERVER["REQUEST_URI"];
    }

    switch($mode)
    {
      case("login"):
        break;
      case("update"):
      case("create"):
        $data['fields'] = array();
        foreach($fields as $field)
          $data['fields'][$field] = $this->getValue($field);
      break;
    }

    $sql->setValue('data', json_encode($data));
    $sql->insert();
  }


	public function update()
	{
		$this->saveToHistory('update');
	}

  public function passwordHash($password) {
    $password = rex_login::passwordHash($password);
    $u = rex_sql::factory();
  	// $u->debugsql = 1;
  	$u->setTable('pz_user');
  	$u->setWhere( array( 'id' => $this->getId() ) );
  	$u->setValue('password', $password );
  	$u->setValue('digest', sha1($password));
  	$u->update();

  }

  public function create()
	{
	  $this->saveToHistory('create');
	}

  public function delete()
  {
    $this->saveToHistory('delete');
  }

	// ----------------- User Perm

	public function setUserPerm(pz_user_perm $user_perm)
	{
		$this->user_perm = $user_perm;
	}

	public function isMe()
	{
		if(isset($this->user_perm))
			return FALSE;
		return TRUE;
	}

	public function getUserPerm()
	{
		return $this->user_perm;
	}

	public function getUserPerms()
	{
		return pz_user_perm::getUserPermsByUserId($this->getId());
	}

	public function getGivenUserPerms()
	{
		return pz_user_perm::getGivenUserPermsByUserId($this->getId());
	}

	// -----------------

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
			foreach($this->perms as $p)
			{
				if($perm != $p)
				{
					$perms[] = $p;
				}
			}
			$this->perms = $perms;
		}
	}

	public function savePerm()
	{
		$perms = array();
		foreach($this->perms as $p)
		{
			if(is_string($p))
			{
				$perms[] = $p;
			}
		}

		$u = rex_sql::factory();
		// $u->debugsql = 1;
		$u->setTable('pz_user');
		$u->setWhere(array('id'=>$this->getId()));
		$u->setValue('perms',serialize($perms));
		$u->update();
	}

  // -----------------

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


  // -------------------------------------------------------------------- Customers

  public function getActiveCustomers($filter = array())
  {
    return $this->getCustomers($filter, true);
  }

  public function getCustomers($filter = array(), $onlyActiveProjects = false)
  {

  	$params = array();
  	$where = array();

    if($onlyActiveProjects)
    {
  	  $where[] = 'id in (
  	    select
  	      p.customer_id
  	    from
  	      pz_project as p, pz_project_user as pu
  	    where
  	      pu.project_id=p.id and
  	      pu.user_id = '.pz::getUser()->getId().' and
  	      (pu.calendar = 1 OR pu.admin = 1) and
  	      p.archived=0 and p.archived IS NOT NULL

  	    )';

  	}

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

    $filter_return = pz::getFilter($nfilter, $where, $params);

  	$sql = rex_sql::factory();
  	// $sql->debugsql = 1;
    $sql->setQuery('SELECT c.* FROM pz_customer c '.$filter_return['where_sql'].' ORDER BY c.name',$filter_return['params']);

    $customers = array();
    foreach($sql->getArray() as $row)
    {
      $customer = new pz_customer($row);
      $customers[$customer->getId()] = $customer;
    }
    return $customers;

  }

  public function getCustomersAsString($filter = array())
  {
  	$return = array();
  	foreach(self::getCustomers($filter) as $customer)
  	{
  		$v = $customer->getName();
  		$v = str_replace('=','',$v);
  		$v = str_replace(',','',$v);
  		$return[] = $v.'='.$customer->getId();
  	}
  	return implode(",",$return);
  }

  // -------------------------------------------------------------------- Clip

  public function getClipDownloadPerm($clip)
  {
    // wenn clip von einem selbst ist
  	if($clip->getUser()->getId() == $this->getId())
  	  return true;

    // wenn der clip in einem event ist
    // - der ein Projekt hat, auf das man Zugreifen kann und rechte am kalender hat
    $events = pz_calendar_event::getEventsByClip($clip);
    foreach($events as $event)
    {
      if($this->getEventViewPerm($event))
        return true;
    }
  	return false;
  }


  // -------------------------------------------------------------------- Cal

  public function getAllEvents(array $projects, DateTime $from = null, DateTime $to = null)
  {
  	// !! time matters
  	$events = pz_calendar_event::getAll($projects, $from, $to);
  	$jobs = $this->getJobs($projects, $from, $to);
  	$attandee_events = pz_calendar_event::getAttendeeEvents($from, $to);
  	$events = $events + $jobs + $attandee_events;

	  return $events;
  }

  public function getEvents(array $projects, DateTime $from = null, DateTime $to = null)
  {
  	// !! time matters
  	return pz_calendar_event::getAll($projects, $from, $to, false, $this->getId() );
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

  	if($event->isRuleEvent())
  	  return FALSE;

  	if($event->getUserId() == $this->getId())
  		return TRUE;

   	// TODO: check if in project and/or projektadmin
  	return FALSE;
  }

  public function getEventViewPerm($event)
  {
  	// TODO:
  	// wenn man im projekt dieses events ist oder besitzer des events
  	$filter = array();
  	$filter[] = array("field" => "id", "value" => $event->getProject()->getId());
  	$projects = $this->getCalendarProjects();
  	if(count($projects)>0)
  	  return true;
    return false;
  }

  public function getAttandeeEvents(DateTime $from = null, DateTime $to = null, $ignore = array())
  {
    // $ignore = array(pz_calendar_attendee::NEEDSACTION, pz_calendar_attendee::ACCEPTED, pz_calendar_attendee::TENTATIVE, pz_calendar_attendee::DECLINED);
  	$events = pz_calendar_event::getAttendeeEvents($from, $to, $this, $ignore);
  	return $events;
  }

  public function countAttendeeEvents()
  {
    $ignore = array(pz_calendar_attendee::ACCEPTED, pz_calendar_attendee::TENTATIVE, pz_calendar_attendee::DECLINED);
    $events = pz_calendar_event::getAttendeeEvents(pz::getDateTime(), null, null, $ignore);
  	return count($events);
  }


  // -------------------------------------------------------------------- E-Mail Account

  public function getEmailaccounts()
  {
		return pz_email_account::getAccounts($this->getId());
  }

  public function getEmailaccountsAsString()
  {
		$return = array();
		foreach(pz_email_account::getAccounts($this->getId()) as $email_account)
		{
			$v = $email_account->getName();
			$v = str_replace('=','',$v);
			$v = str_replace(',','',$v);
			$return[] = $v.'='.$email_account->getId();
		}
		return implode(",",$return);
  }

  public function getDefaultEmailaccountId()
  {
		if($this->getValue("account_id")>0)
			return $this->getValue("account_id");

		$accounts = $this->getEmailaccounts();

		if(is_array($accounts) && count($accounts)>0)
		{
			$account = current($accounts);
			return $account->getId();
		}
		return FALSE;
  }


  // -------------------------------------------------------------------- Users

  public function getUsers($filter = array())
  {
    $filter[] = array("field"=>"status", "type" => "=", "value"=>1);
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

  public function getMyProjects($filter = array())
  {
	  $filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('', true, $filter);
  }

  public function getCalendarProjects($filter = array())
  {
    $filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('((pu.calendar = 1 OR pu.calendar_jobs = 1 OR pu.admin = 1) and (p.has_calendar = 1 OR p.has_calendar_jobs = 1))', true, $filter);
  }

  public function getCalendarJobsProjects($filter = array())
  {
    $filter[] = array("field" => "has_calendar_jobs", "value" => 1);
    $filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('(pu.calendar_jobs = 1 OR pu.admin = 1)', true, $filter);
  }

  public function getCalDavProjects($filter = array())
  {
  	$filter[] = array("field" => "has_calendar", "value" => 1);
  	$filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('(pu.caldav = 1 and (pu.calendar = 1 or pu.admin = 1) )', true, $filter);
  }

  public function getCalDavJobsProjects($filter = array())
  {
  	$filter[] = array("field" => "has_calendar_jobs", "value" => 1);
  	$filter[] = array("field" => "archived", "value" => 0);
    return $this->_getProjects('(pu.caldav_jobs = 1 and (pu.calendar_jobs = 1  or pu.admin = 1) )', true, $filter);
  }

  public function getWebDavProjects($filter = array())
  {
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
  	  		case("has_calendar_jobs"):
  	  		case("has_files"):
  	  		case("has_emails"):
  	  		case("label_id"):
  	  			$f["field"] = "p.".$f["field"];
  	  			$nfilter[] = $f;
  	  			break;
  		}
  	}

    // ----- Filter

  	$f = pz::getFilter($nfilter, $where, $params);
  	$where = $f["where"];
  	$params = $f["params"];
  	$where_sql = $f["where_sql"];

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT p.* FROM pz_project p'. $join .' '. $where_sql .' ORDER BY '.$orderby, $params);
    $projects = array();
    foreach($sql->getArray() as $row)
    {
      $projects[] = new pz_project($row);
    }
    return $projects;
  }


  // -------------------------------------------------------------------- emails


  public function countInboxEmails()
  {
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


  public function getInboxEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
  	$filter[] = array("field" => "send", "value" => 0);
  	$filter[] = array("field" => "trash", "value" => 0);
  	$filter[] = array("field" => "draft", "value" => 0);
  	$filter[] = array("field" => "spam", "value" => 0);
  	return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getOutboxEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
  	$filter[] = array("field" => "send", "value" => 1);
  	$filter[] = array("field" => "trash", "value" => 0);
  	return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getSpamEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
    $filter[] = array("field" => "spam", "value" => 1);
    return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getTrashEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
    $filter[] = array("field" => "trash", "value" => 1);
    return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getDraftsEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
    $filter[] = array("field" => "draft", "value" => 1);
    return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getAllEmails(array $filter = array(), array $projects = array(), $orders = array())
  {
    // $filter[] = array("field" => "trash", "value" => 0);
    // $filter[] = array("field" => "draft", "value" => 0);
    $filter[] = array("field" => "spam", "value" => 0);
    return pz_email::getAll($filter, $projects, array(pz::getUser()), $orders);
  }

  public function getEmailById($email_id)
  {

    if ($this->isAdmin()) {
      if (($email = pz_email::get($email_id))) {
        return $email;
      }
      return false;
    }

    $filter = array();
    $filter[] = array("field" => "id", "value" => $email_id);

    $projects = array();
    $projects = $this->getEmailProjects();

    $emails = pz_email::getAll($filter, $projects, array(pz::getUser()));;

    if(count($emails) != 1) {
    	return FALSE;
    }
    $email = current($emails);
    return $email;

  }

}






