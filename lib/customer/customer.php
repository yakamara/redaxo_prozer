<?php

class pz_customer extends pz_model{

	var $vars = array();
	var $isCustomer = FALSE;

	static $var_labels = 6;
	static private $customers = array();


	public function __construct($vars) {
		$this->isCustomer = TRUE;
		parent::__construct($vars);
	}

	static public function get($id = "")
	{
		$id = (int) $id;
		if($id == 0) 
			return FALSE;

		if(isset(pz_customer::$customers[$id]))
			return pz_customer::$customers[$id];
		
		$class = get_called_class();

		$sql = rex_sql::factory();
		
		$customers = $sql->getArray('select * from pz_customer where id = ? LIMIT 2', array($id));
		if(count($customers) != 1) return FALSE;
		
		pz_customer::$customers[$id] = new $class($customers[0]);
		return pz_customer::$customers[$id];
	}

	public function getId()
	{
		return $this->getVar("id");
	}

	public function getName()
	{
		return $this->getVar("name");
	}
	
	public function getFolder() {
		return rex_path::addonData('prozer', 'customers/'.$this->getId());	
	}
	
	public function getInlineImage()
	{
		if($this->getVar("image_inline") != "") {
			return $this->getVar("image_inline");
		}
		
		if($this->getVar("image") == 1 && $image_path = $this->getFolder().'/'.$this->getId().'.png') {
			return pz::makeInlineImage($image_path, "m");
		}		
		
		return pz_customer::getDefaultImage();
		
	}
	
	static public function getDefaultImage() {
		return "/assets/addons/prozer/css/customer.png";
	}
	
	public function hasProjects() 
	{
		$sql = rex_sql::factory();
		$customers = $sql->getArray('select * from pz_project where customer_id = ? LIMIT 2',array($this->getId()));
		if(count($customers)>0)
			return true;
		return false;
	}
	
	
	public function update() {
		
	}

	public function create() {

		rex_dir::create($this->getFolder());
	}

	public function delete() {
		$sql = rex_sql::factory();
		$sql->setQuery('delete from pz_customer where id = ?',array($this->getId()));
		return true;
	}




}

?>