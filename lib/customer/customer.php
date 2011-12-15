<?php

class pz_customer extends pz_model{

	var $vars = array();
	var $isCustomer = FALSE;

	static $var_labels = 6;

	public function __construct($vars) {
		$this->isCustomer = TRUE;
		parent::__construct($vars);
	}

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;
		
		$class = get_called_class();

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_customer where id = '.$id).' LIMIT 2';
		
		$customers = $sql->getArray();
		if(count($customers) != 1) return FALSE;
				
		return new $class($customers[0]);
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
		return "/layout_prozer/css/customer.png";
	}
	
	
	
	
	public function update() {
		
	}

	public function create() {

		rex_dir::create($this->getFolder());
	}

	public function delete() {
		
	}




}

?>