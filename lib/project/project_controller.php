<?php

class pz_project_controller extends pz_controller {

	function isVisible() {
		return FALSE;	
	}

	function setProject($project_id)
	{
		$filter = array();
		$filter[] = array("field"=>"id","type"=>"=","value"=>$project_id);
		$project = pz::getUser()->getAllProjects($filter);
		if(count($project) != 1) { 
			return FALSE; 
		}
		
		$this->project = current($project);
		$this->project_id = $project_id;

		if(pz::getUser()->isAdmin()) {
			
			$vars = array(
				"id" => -1,
				"admin" => 1,
				"calendar" => 1,
				"calendar_jobs" => 1,
				"files" => 1,
				"emails" => 1,
				"wiki" => 1
			);
			$this->projectuser = new pz_projectuser($vars, pz::getUser(), $project);
			return TRUE;
		}else
		{
			if($this->projectuser = $this->project->getProjectuserByUserId(pz::getUser()->getId())) {
				return TRUE;
			}
		}
		
		return FALSE;
	}

}