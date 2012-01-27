<?php

class pz_calendar_event_api{

	function __construct($event) 
	{
		$this->calendar_event = $event;
		if(!is_object($this->calendar_event)) {
			echo pz::error('CALEVENT_MISSING');
			exit;
		}
		
	}


	public function inp2csv($inp)
	{
		if(is_int($inp))
			return $inp;

		$search = array(",",'"',"\n","\r");
		$replace = array(";",'""',"","");
		$inp = str_replace($search,$replace,$inp);
		return '"'.$inp.'"';
	}


	// --------------------------------------------------------------- views

	public function getJobsCSVView($p = array()) 
	{
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();
		$duration = ($duration->format("%d")*24*60)+($duration->format("%h")*60)+$duration->format("%i");

		$return = "\n";
		$return .= $this->calendar_event->getId().',';	// job_id
		$return .= $this->inp2csv($this->calendar_event->getProject()->getName()).','; // project_name
		$return .= $this->inp2csv($this->calendar_event->getUser()->getName()).','; // user_name
		$return .= $this->inp2csv($from->format("Ymd")).','; // d_start
		$return .= $this->inp2csv($to->format("Ymd")).','; // d_end
		$return .= $this->inp2csv($from->format("Hi")).','; // t_start
		$return .= $this->inp2csv($to->format("Hi")).','; // t_end
		$return .= $this->inp2csv($duration).','; // duration
		$return .= $this->inp2csv($this->calendar_event->getTitle()).','; // subject
		$return .= $this->inp2csv($this->calendar_event->getDescription()).','; // description
		$return .= $this->inp2csv($this->calendar_event->getUser()->getId()).','; // user_id
		$return .= $this->inp2csv($this->calendar_event->getProject()->getId()).','; // project_id
		return $return;
	
	}

	
	// ---------------------------------------------------------- list views

	static function getJobsCSVListView($events = array())
	{
		$return = 'job_id,project_name,user_name,d_start,d_end,t_start,t_end,duration,subject,description,user_id,project_id';
		foreach($events as $event)
		{
			if($e = new pz_calendar_event_api($event))
			{
				$return .= $e->getJobsCSVView();
			}
		}
		return $return;

	}

}


?>