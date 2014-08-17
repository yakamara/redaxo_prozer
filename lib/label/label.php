<?php

class pz_label extends pz_model{

	var $vars = array();
	var $isLabel = FALSE;

	static $var_labels = 6;

	public function __construct($vars) {
		$this->isLabel = TRUE;
		// parent noch setzen
		parent::__construct($vars);
	}

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;

		$sql = pz_sql::factory();
		$labels = $sql->getArray('select * from pz_label where id = ? LIMIT 2', array($id));

		if(count($labels) != 1) return FALSE;

		return new static($labels[0]);
	}

	public function getId()
	{
		return $this->getVar("id");
	}

	public function getName()
	{
		return $this->getVar("name");
	}

	public function getColor()
	{
		return $this->getVar("color");
	}

	public function getBorder()
	{
		return $this->getVar('border');
	}

	public function update() {
		pz_labels::update();
	}

	public function create() {
		pz_labels::update();
	}

	public function hasProjects() 
	{
		$sql = pz_sql::factory();
		$projects = $sql->getArray('select * from pz_project where label_id = ? LIMIT 2',array($this->getId()));
		if(count($projects)>0)
			return true;
		return false;
	}

	public function delete() 
	{
		$sql = pz_sql::factory();
		$sql->setQuery('delete from pz_label where id = ?',array($this->getId()));
		pz_labels::update();

	}


}