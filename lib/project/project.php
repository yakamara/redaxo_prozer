<?php

class pz_project extends pz_model{

	public $vars = array();
	private $isProject = FALSE;
	private $label;
	public $customer = NULL;
	public $users = array();
	private $directory = null;

	function __construct($vars = array())
	{
		if(count($vars)>5) {
			$this->setVars($vars);
			$this->isProject = TRUE;
			$this->customer = pz_customer::get($this->getVar("customer_id"));
			return TRUE;
		}
		return FALSE;
	}

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;
		$project_sql = rex_sql::factory();
		$project_sql->setQuery('select * from pz_project where id='.$id.' LIMIT 2');
		$projects = $project_sql->getArray();
		if(count($projects) != 1) return FALSE;
		return new self($projects[0]);
	}

	// -------------------------------------------------------------------------

	public function getId()
	{
		return (int) $this->vars['id'];
	}

	public function getName()
	{
		return $this->vars['name'];
	}

	public function getDescription()
	{
		return $this->vars['description'];
	}

	public function getLabel()
	{
	  return $this->label ?: $this->label = pz_label::get($this->vars['label_id']);
	}

	public function getLabelId()
	{
	  return $this->vars['label_id'];
	}

	public function getInlineImage()
	{
		if(is_object($this->customer))
		{
			return $this->customer->getInlineImage();
		}
		return "/layout_prozer/css/project.png";
	}

	public function hasCustomer()
	{
    	if($this->customer) {
    		return TRUE;
    	}
		return FALSE;
	}

	public function hasCalendar()
	{
		if($this->vars["has_calendar"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasJobs()
	{
		if($this->vars["has_calendar"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasFiles()
	{
		if($this->vars["has_files"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasEmails()
	{
		if($this->vars["has_emails"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasWiki()
	{
		if($this->vars["has_wiki"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function getJobs(DateTime $from = null, DateTime $to = null, $fulltext = '')
	{
		$jobs = pz_calendar_event::getAll(array($this->getId()), $from, $to, true, null, array('from'=>'desc'), $fulltext);
		return $jobs;
	}

	/*
	public function hasEvents(DateTime $from = null, DateTime $to = null)
	{
		$jobs = pz_calendar_event::getAllEvents(array($this->getId()), $from, $to);
		return $jobs;
	}
	*/

	public function getInfoStream()
	{
		$stream = array();
		return $stream;
	}

	public function getHistoryStream()
	{
		$stream = array();
		return $stream;
	}

	public function getUsers() {

		if(count($this->users)>0) {
			return $this->users;
		}

		$s = rex_sql::factory();
		$projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id='.$this->getId());

		foreach($projectusers as $projectuser)
		{
			$user = pz_user::get($projectuser["user_id"]);
			$this->users[] = new pz_projectuser($projectuser,$user,$this);
		}
		return $this->users;
	}

	public function getProjectuserById($puser_id = 0) {

		$s = rex_sql::factory();
		$projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id='.$this->getId().' and pu.id='.$puser_id);

		if(count($projectusers) == 1) {
			$projectuser = current($projectusers);
			$user = pz_user::get($projectuser["user_id"]);
			return new pz_projectuser($projectuser,$user,$this);
		}
		return FALSE;
	}

	public function getProjectuserByUserId($user_id = 0) {

		$s = rex_sql::factory();
		$projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id='.$this->getId().' and pu.user_id='.$user_id);

		if(count($projectusers) == 1) {
			$projectuser = current($projectusers);
			$user = pz_user::get($projectuser["user_id"]);
			return new pz_projectuser($projectuser,$user,$this);
		}
		return FALSE;
	}

	public function getAdmins() {

		$admins = array();
		foreach($this->getUsers() as $projectuser)
		{
			if($projectuser->isAdmin())
				$admins[] = $projectuser->getUser();
		}
		return $admins;
	}

	public function getFolder() {
		return rex_path::addonData('prozer', 'projects/'.$this->getId());
	}

	public function getFilesFolder() {
		return rex_path::addonData('prozer', 'projects/'.$this->getId().'/files');
	}

	public function getDirectory()
	{
	  return $this->directory ?: $this->directory = new pz_project_root_directory($this);
	}

	public function addUser($user_id,$admin = 0, $perm = array()) {

		$a = rex_sql::factory();
		// $a->debugsql = 1;
		$a->setTable('pz_project_user');
		$a->setValue("user_id",$user_id);

		$a->setValue("project_id",$this->getId());

		$a->setRawValue("created",'NOW()');
		$a->setRawValue("updated",'NOW()');

		$a->setValue("admin",$admin);

		if(!isset($perm["calendar"]) || $perm["calendar"] != 1) { $perm["calendar"] = 0; }
		$a->setValue("calendar", $perm["calendar"]);

		if(!isset($perm["wiki"]) || $perm["wiki"] != 1) { $perm["wiki"] = 0; }
		$a->setValue("wiki", $perm["wiki"]);

		if(!isset($perm["files"]) || $perm["files"] != 1) { $perm["files"] = 0; }
		$a->setValue("files", $perm["files"]);

		if(!isset($perm["webdav"]) || $perm["webdav"] != 1) { $perm["webdav"] = 0; }
		$a->setValue("webdav", $perm["webdav"]);

		if(!isset($perm["caldav"]) || $perm["caldav"] != 1) { $perm["caldav"] = 0; }
		$a->setValue("caldav", $perm["caldav"]);

		if(!isset($perm["caldav_jobs"]) || $perm["caldav_jobs"] != 1) { $perm["caldav_jobs"] = 0; }
		$a->setValue("caldav_jobs", $perm["caldav_jobs"]);

		$a->insert();

	}

	public function deleteUser($projectuser_id) {

		if($projectuser = $this->getProjectuserById($projectuser_id)) {
			if($projectuser->delete())
				return TRUE;
		}
		return FALSE;
	}


	public function getEmails()
	{
		$projects = array();
		$projects[] = $this;
		$filter = array();
		return pz_email::getAll($projects, $filter);
	}


	public function update() {

		// Dinge die durchgeführt werden, nach einem Update
		// prüfen ob Ordner erstellt sind und nachgenerieren

	  pz_sabre_caldav_backend::incrementCtag($this->vars['id']);
	}

	public function create() {

		// project folder
		rex_dir::create($this->getFilesFolder());

		// User anlegen
		$this->addUser(pz::getUser()->getId(),1);

		pz_sabre_caldav_backend::incrementCtag($this->vars['id']);

		return $this->getFilesFolder();

	}

	public function delete() {

		// Ordner löschen

		// Projektuser löschen

		// Projektdatensatz löschen

		// ...

	  pz_sabre_caldav_backend::incrementCtag($this->vars['id']);

	}

	// -------------------------------------------------------------------

	static function getProjectIds($projects)
	{
		$ids = array();
		foreach($projects as $project)
		{
			$ids[] = $project->getId();
		}
		return $ids;
	}

	static function getProjectsAsString($projects, $cutText = 100)
	{
		$return = array();
		foreach($projects as $project)
		{
			$name = pz::cutText($project->getName(), $cutText).' ['.$project->getId().']';
			$name = str_replace(array('=',','),'',$name);
			$return[] = $name.'='.$project->getId();
		}
		return implode(",",$return);
	}





}