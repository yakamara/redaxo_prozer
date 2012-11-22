<?php

class pz_history_entry extends pz_model
{

	var $vars = array();

	public function __construct($vars) 
	{
		parent::__construct($vars);
	}

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_history where id = ? LIMIT 2',array($id));

		$entries = $sql->getArray();
		if(count($entries) != 1) return FALSE;

		return new static($entries[0]);
	}
	
	public function getId() {
		return intval($this->vars["id"]);
	}
	
	public function delete() 
	{
    $d = rex_sql::factory();
		$d->setQuery('delete from pz_history where id = ?', array($this->getId()));
		return TRUE;
	
	}
	

}

?>