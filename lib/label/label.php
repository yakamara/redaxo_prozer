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

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_label where id = '.$id).' LIMIT 2';

		$labels = $sql->getArray();
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

	public function delete() {
		pz_labels::update();

	}


}

?>