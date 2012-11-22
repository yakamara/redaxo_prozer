<?php

class pz_user_perm extends pz_model{

	var $vars = array();

	static private $user_perms = array();

	public function __construct($vars) {
		parent::__construct($vars);
	}

	static public function get($id = "")
	{
		$id = (int) $id;
		if($id == 0) 
			return FALSE;

		if(isset(pz_user_perm::$user_perms[$id]))
			return pz_user_perm::$user_perms[$id];
		
		$class = get_called_class();

		$sql = rex_sql::factory();
		
		$user_perms = $sql->getArray('select * from pz_user_perm where id = ? LIMIT 2', array($id));
		if(count($user_perms) != 1) return FALSE;
		
		pz_user_perm::$user_perms[$id] = new $class($user_perms[0]);
		return pz_user_perm::$user_perms[$id];
	}

	public function getId()
	{
		return $this->getVar("id");
	}

	public function getToUser() 
	{
		if($user = pz_user::get($this->getVar("to_user_id")))
			return $user;

		return NULL;
	}

	public function getFromUser() 
	{
		if($user = pz_user::get($this->getVar("user_id")))
			return $user;
		return NULL;
	}
	
	public function update() {
	}

	public function create() {
	}

	public function delete() {
		$sql = rex_sql::factory();
		$sql->setQuery('delete from pz_user_perm where id = ? and user_id = ?',array($this->getId(),pz::getLoginUser()->getId()));
		return true;
	}

	// ---------------------------------------------

	public function hasProjectsPerm() {
		// TODO
		return false;
	}

	public function hasCalendarReadPerm() {
		if($this->getVar("calendar_read"))
			return true;
		return false;
	}

	public function hasCalendarWritePerm() {
		if($this->getVar("calendar_write"))
			return true;
		return false;
	}

	public function hasEmailReadPerm() {
		if($this->getVar("email_read"))
			return true;
		return false;
	}

	public function hasEmailWritePerm() {
		if($this->getVar("email_write"))
			return true;
		return false;
	}

	// ----------------------------------------

	static function getUserPermsByUserId($user_id) {

		$return = array();	
		$sql = rex_sql::factory();
		$user_perms = $sql->getArray('select * from pz_user_perm where user_id = ?', array($user_id));
		
		foreach($user_perms as $user_perm) {
			$return[] = new pz_user_perm($user_perm);
		}
		
		return $return;
	
	}

	static function getGivenUserPermsByUserId($user_id) {

		$return = array();	
		$sql = rex_sql::factory();
		$user_perms = $sql->getArray('select * from pz_user_perm where to_user_id = ?', array($user_id));
		
		foreach($user_perms as $user_perm) {
			$return[] = new pz_user_perm($user_perm);
		}
		
		return $return;
	
	}



}

?>