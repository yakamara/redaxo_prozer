<?php

class pz_calendars_controller_screen extends pz_calendars_controller {

	var $name = "calendars";
	var $function = "";
	var $functions = array("day",  "week", "usertime", "projectjob", "api"); // "list", "month", "projectjob",
	var $function_default = "day";
	var $navigation = array("day", ); // "week", "usertime", "projectjob","list", "month", "projectjob", 

	function controller($function) {

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["linkvars"] = array();
		
		switch($this->function)
		{
			case("day"):
				return $this->getDayPage($p);
			case("week"):
				return $this->getWeekPage($p);
			case("api"):
				return $this->getAPI($p);
			case("usertime"):
			case("projectjob"):
				return $this->getDayPage($p);
				break;
			default:
				return "";
				break;

		}
	}

	private function getNavigation($p = array(),$flyout = "")
	{
		return pz_screen::getNavigation(
			$p,
			$this->navigation, 
			$this->function, 
			$this->name, 
			$flyout
		);
	}

	private function getProjects()
	{
		$projects = pz::getUser()->getCalendarProjects();
		if(!isset($_REQUEST["project_ids"]))
		{
			$project_ids = rex_request::session("pz_calendar_project_ids","array");
			if(count($project_ids) == 0) {
				$project_ids = pz_project::getProjectIds($projects);
			}
		}else
		{
			$project_ids = explode(",",rex_request("project_ids","string"));
		}

		$return_projects = array();
		$prooved_project_ids = array();
		foreach($projects as $project)
		{
			if(in_array($project->getId(),$project_ids)) {
				$return_projects[] = $project;			
				$prooved_project_ids[] = $project->getId();
			}	
		}
		rex_request::setSession("pz_calendar_project_ids",$prooved_project_ids);
		return $return_projects;
	}

	// ------------------------------------------------------------------- Views

	/*
	private function getProjectsSelectionFlyout($p = array(),$project_ids)
	{
		$entries = array();
		
		$i = -1;
		$title = '';
		$values = array();
		
		foreach(pz::getUser()->getCalendarProjects() as $project)
		{
			$i++;
			$entries[$i]['id'] = $project->getId();
            $entries[$i]['title'] = pz::cutText($project->getName(),100).' ['.$project->getId().']';
            $entries[$i]['title_short'] = pz::cutText($project->getName(),10,10);
			// aktualisiere die layer -- search, list, mit den project_ids und den linkvars..
		}
		
		$f = new rex_fragment();
		$f->setVar('layer_id', 'projects_dropdown', false);
        $f->setVar('class_ul', 'w3', false);
        $f->setVar('entries', $entries, false);
        $f->setVar('multiselect_field', 'project_ids', false);
        $f->setVar('selected_values', $project_ids, false);
        $f->setVar('text_selected', rex_i18n::msg("projects_selected"), false);
        $f->setVar('refresh_layer', array($p["layer_search"],$p["layer_list"]), false);
        
        return $f->parse('pz_screen_multiselect_dropdown');
	}
	*/


	private function getAPI($p)
	{
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("dayview_event_change"):
				
				$event_id = rex_request("event_id","int");
				$event_from_pixel = rex_request("event_from_pixel","int"); // format: 150
				$event_duration_pixel = rex_request("event_duration_pixel","int"); // format: 600
				$event_position_pixel = rex_request("event_position_pixel","int"); // 0.....50
				
				$return = array();
				if($event = pz_calendar_event::get($event_id))
				{

					if(pz::getUser()->getEventEditPerm($event))
					{

						$from = $event->getFrom();
						$to = $event->getTo();
											
						$from_time_array = pz_calendar_event_screen::getDayViewPixel2Time($event_from_pixel);
						$to_time_array = pz_calendar_event_screen::getDayViewPixel2Time($event_from_pixel+$event_duration_pixel);
	
						$from->setTime($from_time_array["h"],$from_time_array["m"]);
						$to->setTime($to_time_array["h"],$to_time_array["m"]);
	
						if($from->format("d") == $to->format("d") && $from->format("Hi")<$to->format("Hi"))
						{
							$event->setFrom($from);
							$event->setTo($to);
						}
						
						$event->save();	

						$event_position = pz_calendar_event_screen::getDayViewPixel2Position($event_position_pixel);
						
						$event_screen = new pz_calendar_event_screen($event);
						$content = $event_screen->getDayView($p,$event_position);
						
						$return["html"] = $content;

					}else
					{

					}
					
				}else
				{
				}

				return json_encode($return);
						
			
		}
			
		
		
		
	}



	// ------------------------------------ page views

	public function getDayPage($p = array())
	{
		$p["mediaview"] = "screen";
		$p["controll"] = "calendars";
		$p["function"] = "day";
		$p["layer_list"] = "calendar_events_day_list";
		$p["layer_search"] = "calendar_events_day_search";

		$s1_content = "";
		$s2_content = "";

		$request_day = rex_request("day","string"); // 20112019 Ymd
		if(!$day = DateTime::createFromFormat('Ymd', $request_day)) {
			$day = new DateTime();
		}
		
		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);
	
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("delete_calendar_event"):
				// TODO : Permcheck
				$calendar_event_id = rex_request("calendar_event_id","int",0);
				if($calendar_event_id > 0 && $event = pz_calendar_event::get($calendar_event_id)) {
					$cs = new pz_calendar_event_screen($event);
					return $cs->getDeleteForm($p);
				}
				
				return '<div id="calendar_event_form"><p class="xform-warning">'.rex_i18n::msg("calendar_event_not_exists").'</p></div>';
		
			case("add_calendar_event"):
				return pz_calendar_event_screen::getAddForm($p);
				break;
			case("search"):
				$month_firstday = clone $day;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $day;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);
				// $jobs =  pz::getUser()->getJobs($project_ids, $month_firstday, $month_lastday);
				// $events = array_merge($events, $jobs);
				return pz_calendar_event_screen::getSearch(
							$project_ids, 
							$events, 
							array_merge( $p, array("linkvars" => array( "mode" =>"search", "project_ids" => implode(",",$project_ids) ) ) ), 
							$day
						);
				break;
			case("list"):
				$events = pz::getUser()->getEvents($project_ids,$day);
				// $jobs =  pz::getUser()->getJobs($project_ids, $day);
				// $events = array_merge($events, $jobs);
				return pz_calendar_event_screen::getDayListView(
							$events,
							array_merge( $p, array("linkvars" => array( "mode" =>"list", "project_ids" => implode(",",$project_ids), "day" => $day->format('Ymd') ) ) ),
							$day
						);
				break;
			case("edit_calendar_event"):
				// TODO : Permcheck
				$calendar_event_id = rex_request("calendar_event_id","int",0);
				if($calendar_event_id > 0 && $event = pz_calendar_event::get($calendar_event_id)) {
					$cs = new pz_calendar_event_screen($event);
					return $cs->getEditForm($p);
				}
				
				return '<div id="calendar_event_form"><p class="xform-warning">'.rex_i18n::msg("calendar_event_not_exists").'</p></div>';
				break;
			case(""):

				$month_firstday = clone $day;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $day;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);
				// $jobs =  pz::getUser()->getJobs($project_ids, $month_firstday, $month_lastday);
				// $events = array_merge($events, $jobs);
				$s1_content .= pz_calendar_event_screen::getSearch(
							$project_ids, 
							$events, 
							array_merge( $p, array("linkvars" => array( "mode" =>"search", "project_ids" => implode(",",$project_ids), "day" => $day->format('Ymd') ) ) ), 
							$day
						);

				$events = pz::getUser()->getEvents($project_ids, $day);
				// $jobs =  pz::getUser()->getJobs($project_ids, $day);
				// $events = array_merge($events, $jobs);

				$s2_content = pz_calendar_event_screen::getDayListView(
						$events,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", "project_ids" => implode(",",$project_ids), "day" => $day->format('Ymd') ) ) ),
						$day
					);
				$s1_content .= pz_calendar_event_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
		
	}
	
	public function getWeekPage($p = array())
	{
		$p["mediaview"] = "screen";
		$p["controll"] = "calendars";
		$p["function"] = "week";
		$p["layer_list"] = "calendar_events_week_list";
		$p["layer_search"] = "calendar_events_week_search";

		$s1_content = "";
		$s2_content = "";

		$request_day = rex_request("day","string"); // 20112019 Ymd
		if(!$day = DateTime::createFromFormat('Ymd', $request_day)) {
			$day = new DateTime();
		}
		
		$day_last = clone $day;
		$day_last->modify("+7 days");
		
		$projects = $this->getProjects();
		$project_ids = pz_project::getProjectIds($projects);
	
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_event"):
				return pz_calendar_event_screen::getAddForm($p);
				break;
			case("search"):
				$month_firstday = clone $day;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $day;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);

				return pz_calendar_event_screen::getSearch(
								$project_ids, 
								$events, 
								array_merge(
									$p,
									array("linkvars" => array(
										"mode" =>"search",
										"project_ids" => implode(",",$project_ids)
										) 
									)
								), 
								$day
								);
				break;
			case("list"):
				$events = pz::getUser()->getEvents($project_ids,$day,$day_last);
				return pz_calendar_event_screen::getWeekListView(
					$events,
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"project_ids" => implode(",",$project_ids),
							"day" => $day->format('Ymd')
							) 
						)
					),
					$day
				);
				break;
			case("edit_event"):
				$event_id = rex_request("event_id","int",0);
				if($event_id > 0 && $event = pz_calendar_event::get($event_id)) {
					$cs = new pz_calendar_event_screen($event);
					return $cs->getEditForm($p);
				}else {
					return '<p class="xform-warning">'.rex_i18n::msg("event_not_exists").'</p>';
				}
				break;
			case(""):

				$month_firstday = clone $day;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $day;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);
				$s1_content .= pz_calendar_event_screen::getSearch(
						$project_ids, 
						$events, 
						array_merge(
							$p,
							array("linkvars" => array(
								"mode" =>"search",
								"project_ids" => implode(",",$project_ids),
								"day" => $day->format('Ymd')
								) 
							)
						), 
						$day);

				$events = pz::getUser()->getEvents($project_ids, $day, $day_last);

				$s2_content = pz_calendar_event_screen::getWeekListView(
					$events,
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"project_ids" => implode(",",$project_ids),
							"day" => $day->format('Ymd')
							) 
						)
					),
					$day
				);
				$s1_content .= pz_calendar_event_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p, pz_project_controller_screen::getProjectsFlyout($p)), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
		
	}





	public function getProjectjobPage($p = array())
	{
		$p["mediaview"] = "screen";
		$p["controll"] = "calendars";
		$p["function"] = "projectjob";
		$p["layer_list"] = "calendar_projectjob_list";
		$p["layer_search"] = "calendar_projectjob_search";
		
		$s1_content = "";
		$s2_content = "";

		$request_day = rex_request("day","string"); // 20112019 Ymd
		if(!$week_start = DateTime::createFromFormat('Ymd', $request_day)) {
			$week_start = new DateTime();
		}
		
		$week_end = clone $week_start;
		$week_end->modify("+7 days");
		
		$projects = $this->getProjects();
		
		
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("search"):
				$month_firstday = clone $day;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $day;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($projects, $month_firstday, $month_lastday);
				$jobs =  pz::getUser()->getJobs($projects, $month_firstday, $month_lastday);
				$events = array_merge($events, $jobs);
				return pz_calendar_event_screen::getSearch($projects, $events, $p, $day);
				break;
			case("list"):
				$events = pz::getUser()->getEvents($projects,$week_start,$week_end);
				$jobs =  pz::getUser()->getJobs($projects,$week_start,$week_end);
				$events = array_merge($events, $jobs);
				return pz_calendar_event_screen::getProjectjobListView(
					$events,
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"project_ids" => rex_request("project_ids"),
							"day" => $day->format('Ymd')
							) 
						)
					),
					$day
				);
				break;
			case(""):

				$month_firstday = clone $week_start;
				$month_firstday->modify("first day of this month");
				$month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days
				$month_lastday = clone $week_start;
				$month_lastday->modify("+1 month");
				$month_lastday->modify("last day of this month");
				$events = pz::getUser()->getEvents($projects, $month_firstday, $month_lastday);
				$jobs =  pz::getUser()->getJobs($projects, $month_firstday, $month_lastday);
				$events = array_merge($events, $jobs);
				$s1_content .= pz_calendar_event_screen::getSearch($projects, $events,$p,$week_start);

				$events = pz::getUser()->getEvents($projects, $week_start, $week_end);
				$jobs =  pz::getUser()->getJobs($projects, $week_start, $week_end);
				$events = array_merge($events, $jobs);

				$s2_content = pz_calendar_event_screen::getProjectjobListView(
					$projects,
					$events,
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"search_name" => rex_request("search_name"),
							"project_ids" => rex_request("project_ids"),
							"day" => $week_start->format('Ymd')
							) 
						)
					),
					$week_start
				);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
		
	}



}