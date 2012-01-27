<?php

// http://prozer.modulvier.com/api/tools/jobs/?login=jan&apitoken=b6660eaed16bbc782ab8a1ce76aabb1d&from=20111201&to=20111220&mode=all
// http://prozer.modulvier.com/api/tools/jobs/?login=jan&apitoken=b6660eaed16bbc782ab8a1ce76aabb1d&from=20111201&to=20111220


class pz_tools_controller_api extends pz_tools_controller {

	var $name = "tools";
	var $function = "";
	var $functions = array("jobs","system");
	var $function_default = "system";

	function controller($function) {

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		switch($this->function)
		{
			case("jobs"): 
				return $this->getJobsPage();
			case("system"): 
				return $this->getSystemPage();
			default: 
				return array();
		}
	}

	// ------------------------------------------------------- page views

	private function getSystemPage() 
	{
		return 'ok';	
	}


	private function getJobsPage()
	{
		$start = rex_request('from',"int","");
		$end = rex_request('to',"int","");

		if($start == "" || !$from = DateTime::createFromFormat('Ymd', $start)) {
			$from = new DateTime();
		}
		
		if($end == "" || !$to = DateTime::createFromFormat('Ymd', $end)) {
			$to = clone $from;
			$from->modify("-1 month");
		}
		
		$mode = rex_request('mode', 'string');
		if($mode == "all" && pz::getUser()->isAdmin()) { 
			// all
			$user_ids = array();
			foreach(pz_user::getUsers() as $user) {
				$user_ids[] = $user->getId();	
			}
			
			$project_ids = array();
			foreach(pz::getUser()->getProjects() as $p) {
				$project_ids[] = $p->getid();	
			}
			
			$jobs = pz_calendar_event::getAll($project_ids, $from, $to, true, $user_ids);
		
		}else
		{
			// my jobs
			$project_ids = array();
			$projects = pz::getUser()->getMyProjects();
			foreach($projects as $project) { $project_ids[] = $project->getId();} 
			// $jobs = pz_calendar_event::getAll($project_ids, null, null, true, pz::getUser()->getId(), array('from'=>'desc'));
			 $jobs = pz_calendar_event::getAll($project_ids, $from, $to, true, pz::getUser()->getId(), array('from'=>'desc'));
			
		}
		
		$jobs_list = pz_calendar_event_api::getJobsCSVListView($jobs);
		
		return $jobs_list;

	}


}