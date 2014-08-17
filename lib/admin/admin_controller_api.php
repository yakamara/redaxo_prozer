<?php

// http://prozer.modulvier.com/api/admin/jobs/?login=jan&apitoken=xxx&from=20111201&to=20111220&mode=all
// http://prozer.modulvier.com/api/admin/jobs/?login=jan&apitoken=xxx&from=20111201&to=20111220

// http://prozer.modulvier.com/api/admin/refresh/?login=jan&apitoken=xxx
// http://prozer.modulvier.com/api/admin/cleanup/?login=jan&apitoken=xxx

class pz_admin_controller_api extends pz_admin_controller 
{

	var $name = "admin";
	var $function = "";
	var $functions = array("system");
	var $function_default = "";

	function controller($function) 
	{

    $format = rex_request("format", "string", "excel");
    
    if(pz::getUser()->isAdmin())
    {
      $this->functions[] = "refresh";
      $this->functions[] = "cleanup";
      $this->functions[] = "jobs";
      $this->functions[] = "users";
    }

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

    $return = array();
		switch($this->function)
		{
		  case("users"):
		    $return = $this->getUsers();
		    break;
			case("jobs"): 
				$return = $this->getJobs();
				break;
      case("refresh"): 
      	$return = $this->getRefreshPage();
      	break;
      case("cleanup"):
        $format = rex_request("format", "string", "csv");
      	$return = $this->getCleanUpPage();
      	break;
			default: 
	      $return = array();
		}
		
		return pz_api::send($return, $format);
		
	}

	// ------------------------------------------------------- page views

  private function getRefreshPage()
  {
    $return = array();
  
    // Files have filesize and mimetype
    $gf = pz_sql::factory();
    $files = $gf->getArray('select * from pz_project_file where is_directory = 0 and filesize = 0 and mimetype = ""');
    foreach($files as $file)
    {
      $f = new pz_project_file($file);
      $f->updateFilesizeAndMimetype();
    }
  
    // refresh address fulltext fields
    $all = pz_address::getAll();
    foreach($all as $one)
    {
      $one->updateUriAndVT();
    }
    
    // update label css
    pz_labels::update();
    
    // TODO: 
    // not now because of performance
    // - emails has attachment flag

    
    return $return;
  }

	private function getEmptyPage() 
	{
		return array();	
	}

	private function getCleanUpPage() 
	{
    $return = array();
    $return[] = array("info" => "cleanup start");

	  // Once a day
  
    $date = pz::getDateTime();
    $date->modify("-6 months");

    $filter = array();
		$filter[] = array("type"=>"<", "field"=>"created", "value"=>$date->format("Y-m-d 00:00"));
	  $filter[] = array("type"=>"=", "field"=>"trash", "value"=>1);
		$emails = pz::getUser()->getTrashEmails($filter);

    $deleted = 0;
    $untrashed = 0;
    foreach($emails as $email) {
      if ($email->getProjectId() >0) {
        $untrashed++;
        $email->untrash();  
      } else {
        $deleted++;
        $email->delete();
      }
    }  

    $return[] = array("info" => $untrashed. ' emails untrashed');
    $return[] = array("info" => $deleted.' emails deleted');
      
    // TODO:
    // - cleanup clips / alle die älter als 6 Monate versteckt und nicht verwendet sind - löschen
    // -- clip.delete();
    // . nur clips aus emails die nicht in bearbeitung sind.
    // . nur clips die nicht in kalenderterminen sind
    // . nur clips die nicht freigegeben sind.

    
    // Log
    $date = pz::getDateTime();
    $date->modify("-6 months");

    $filter = array();
		$filter[] = array("type"=>"<", "field"=>"stamp", "value"=>$date->format("Y-m-d 00:00"));
		$entries = pz_history::get($filter);

    $deleted = 0;
    foreach($entries as $entry) {
      $deleted++;
      $entry->delete();
    }
    $return[] = array("info" => $deleted.' logentries deleted');
    $return[] = array('info' => 'cleanup finished');

		return $return;
	}

	private function getJobs()
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
		
		$return = array();
		foreach($jobs as $job) {
			if($e = new pz_calendar_event_api($job)) {
				$return[] = $e->getVars();
			}
		}
		
		return $return;

	}

  public function getUsers()
  {
    $users = array();
    foreach(pz::getUsers() as $user)
    {
      $a_api = new pz_user_api($user);
      $users[] = $a_api->getDataArray();
    }
    return $users;
  }


}