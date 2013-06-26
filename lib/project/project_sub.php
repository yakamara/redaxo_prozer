<?php

class pz_project_sub extends pz_model
{

	var $vars = array();
  var $project = null;

	public function __construct($vars) 
	{
	
  	$this->project = pz_project::get($vars["project_id"]);
		if(!$this->project)
		  return false;
		parent::__construct($vars);
	}

	public function getId()
	{
		return $this->getVar("id");
	}

	public function getName()
	{
		return $this->getVar("name");
	}

	public function setName($name)
	{
		return $this->setVar("name",$name);
	}

	public function getProject()
	{
		return $this->project;
	}

  // ----------------------------

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_project_sub where id = '.$id.' LIMIT 2');

		$project_subs = $sql->getArray();
		if(count($project_subs) != 1) return FALSE;

		return new static($project_subs[0]);
	}


  // ----------------------------

	public function update() 
	{
	}

	public function create() 
	{
	}

	public function delete() 
	{
	  pz_calendar_event::resetProjectSubs($this->getId());
		$sql = rex_sql::factory();
		$sql->setQuery('delete from pz_project_sub where id = ?',array($this->getId()));

	}


}

?>
