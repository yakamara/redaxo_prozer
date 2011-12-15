<?php

class pz_projectuser extends pz_model{

	public $vars = array();
	private $isProjectuser = FALSE;
	public $project = NULL;
	public $user = NULL;

	function __construct($vars = array(), $user, $project)
	{
		if(count($vars)>5) {
			$this->setVars($vars);
			$this->user = $user;
			$this->project = $project;
			return TRUE;
		}
		return FALSE;
	}

	public function getId()
	{
		return $this->getVar("id");	
	}

	public function getUser()
	{
		return $this->user;	
	}

	public function hasCalendar()
	{
		if($this->vars["calendar"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasJobs()
	{
		if($this->vars["calendar"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasFiles()
	{
		if($this->vars["files"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasEmails()
	{
		if($this->vars["emails"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasWiki()
	{
		if($this->vars["wiki"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function isAdmin()
	{
		if($this->vars["admin"] == 1 || $this->vars["admin"] == 1) {
			return TRUE;
		}
		return FALSE;
	}

	public function delete()
	{
		$a = rex_sql::factory();
		// $a->debugsql = 1;
		$a->setTable('pz_project_user');
		$a->setWhere(
			array(
					"id" => $this->getId(),
					"project_id" => $this->project->getId()
				)
			);

		$a->delete();
		return TRUE;	
	}



}