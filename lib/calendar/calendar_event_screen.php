<?php

class pz_calendar_event_screen{

	public $event,$user,$user_name, $label,$project;
	
	function __construct($event) 
	{
		$this->calendar_event = $event;
		$this->user_name = rex_i18n::msg("username_not_avaiable");
		if($this->user = pz_user::get($this->calendar_event->getUserId())) {
			$this->user_name = $this->user->getName();
		}
		$this->label_id = "1";
		if($this->project = pz_project::get($this->calendar_event->getProjectId())) {
			$this->label_id = $this->project->getLabelId();
		}
		
	}


	// --------------------------------------------------------------- Day

	static function getDayViewPositions($event, $position = 0)
	{
		
		$from = $event->getFrom();
		$to = $event->getTo();
		$duration = $event->getDuration();
		
		$top = ($from->format("H")*60)+$from->format("i");
		$left = ($position*40);
		$height = ($duration->format("%h")*60)+$duration->format("%i");
		$width = 188;
		
		return array(
			"top" => $top,
			"left" => $left,
			"height" => $height,
			"width" => $width
			);

	}

	static function getDayViewPixel2Time($pixel)
	{
		$hours = (int) ($pixel/60);
		$minutes = (int) ($pixel-($hours*60));
		return array("h"=>$hours,"m"=>$minutes);
	}

	static function getDayViewPixel2Position($pixel)
	{
		$position = (int) ($pixel/40);
		return $position;
	}

	// --------------------------------------------------------------- views

	function getDayView($p = array(), $position = 0) 
	{
		
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();

		$style = pz_calendar_event_screen::getDayViewPositions($this->calendar_event, $position);
		
		// $height = 
		
		// 9:30 - 10:30
		// labelc7 labelb7
		
		$info = "";
		$resize = '';
		$edit_classes = "";
		$a_pre = "";
		$a_post = "";
		if(pz::getUser()->getId() == $this->calendar_event->getUserId())
		{
			$info = '<span class="editable">[editable]</span>';	
			$resize = '<span class="resize">resize</span>';
			$edit_classes = "dragable resizeable";
			
			$a_pre = '<a href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">';
			$a_post = '</a>';
			
		}else
		{
			$info = '<span class="noeditable">no edit</span>';
		}
		
		$return = '
		    <article class="event label labelc'.$this->label_id.' labelb'.$this->label_id.' '.$edit_classes.'" style="top: '.$style["top"].'px; left:'.$style["left"].'px; height: '.$style["height"].'px; width:'.$style["width"].'px" id="event-'.$this->calendar_event->getId().'">
		      <div class="event-info labelb'.$this->label_id.'">
           <header>
             <hgroup>
               <h2 class="hl7">'.$a_pre.$info.'<span class="name">'.$from->format("H:i").' - '.$to->format("H:i").'</span><span class="info"> | '.$this->calendar_event->getTitle().' | '.$this->user_name.'</span>'.$a_post.'</h2>
             </hgroup>
           </header>
           <section class="content">
             <p>'.$this->calendar_event->getDescription().'</p>
           </section>
           '.$resize.'
          </div>
	     </article>';
	     return $return;
	}
	
	function getDayAlldayView($p = array()) 
	{
		$a_pre = "";
		$a_post = "";
		if(pz::getUser()->getId() == $this->calendar_event->getUserId())
		{
			$a_pre = '<a href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">';
			$a_post = '</a>';
		}
		
		$return = '<li class="entry">'.$a_pre.'<span class="label labelc'.$this->label_id.'"><span class="name">'.$this->calendar_event->getTitle().'</span> - <span class="title">'.$this->user_name.'</span></span>'.$a_post.'</li>';
	    return $return;
	}

	static function getDayListView($events = array(), $p = array(), $day)
	{	
		
		$return = "";
		$content = "";
		$content_allday = "";
		
		if(count($events) > 0)
		{
			$position = 0;
			foreach($events as $event)
			{
				$e = new pz_calendar_event_screen($event);
				if($e->calendar_event->isAllDay()) {
					$content_allday .= $e->getDayAlldayView($p);
				}else {
					$content .= $e->getDayView($p,$position);
					$position++;
				}
			}
		}
		
		$grid = '';
		for ($i = 0; $i <= 23; $i++)
		{
		  $id = '';
		  if ($i == 7)
		    $id = ' id="dragstart"';
		    
		  if ($i == 23)
		    $id = ' id="dragend"';
		    
				$grid .= '<dt'.$id.' class="hour title">'.$i.':00</dt>';
				$grid .= '<dd class="hour box"></dd>';
		}

		/*
		  Berechnung Terminposition
		  1 Minute = 1px
		  0 Punkt  = 7:00 Uhr

		  top     = Startzeit (in Minuten) - 7 Stunden (420 Minuten)
		  height  = Endzeit - Startzeit (in Minuten)
		*/

		// Timeline - Jetzt - 7 Stunden (0 Punkt) / 60 (1 Stunde 60px hoch)
		$timeline_position = ceil((time() - mktime(7, 0, 0)) / 60);
		$timeline_position = ceil((time() - mktime(0, 0, 0)) / 60);
		
		$timeline = "";
		$today = new DateTime();
		if($day->format("Ymd") == $today->format("Ymd"))
			$timeline = '<div class="timeline" style="top: '.$timeline_position.'px;"><span class="icon"></span><span class="line"></span></div>';

		// Links

		$link_refresh = pz::url("screen","calendars","day",
			array_merge(
				$p["linkvars"],
				array(
					"mode"=>"list",
					"day"=>$day->format("Ymd"),
					"project_ids" => "___value_ids___"	
				)
			)
			);

		$link_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"))))."')";

		$day->modify('-1 day');
		$link_previous = "javascript:pz_loadPage('calendar_events_day_list','".pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
		$day->modify('+2 day');
		$link_next = "javascript:pz_loadPage('calendar_events_day_list','".pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
		$day->modify('-1 day');
		
		$today = new DateTime();
		$link_today = "javascript:pz_loadPage('calendar_events_day_list','".pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"list","day"=>$today->format("Ymd"))))."')";

		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.$day->format(rex_i18n::msg("format_ddmy")).' <span class="info">('.rex_i18n::msg("calendarweek").' '.$day->format("W").')</span></h1>
	          </div>
	        </header>';

		$return .= '
		  <div class="calendar view-day clearfix">
		    <header class="header">
		      <div class="grid3col">
				      <div class="column first">
				        <a class="bt2" href="'.$link_add.'">'.rex_i18n::msg("new_event").'</a>
				      </div>
				      
				      <div class="column last">
                <ul class="pagination">
                  <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.rex_i18n::msg("previous").'</span></a></li>
                  <li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.rex_i18n::msg("next").'</span></a></li>
                  <li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.rex_i18n::msg("today").'</span></a></li>
                </ul>
				      </div>
		      </div>
		    </header>

		    <div class="header clearfix">
		      <ul class="allday">
		      	'.$content_allday.'
			  </ul>
		    </div>

		    <div class="wrapper">
            <div class="grid clearfix calendargrid">
              <dl class="hours">
                '.$grid.'
              </dl>
            </div>
  			    <div class="events clearfix">
				    '.$content.'
				    </div>
  			    '.$timeline.'
				  </div>
		  </div>
		  ';
		
		$return .= '<script language="Javascript"><!--	
		pz_set_calendarday_offset();
		pz_set_calendarday_dragresize_init();
		--></script>';
		
		return '<div class="design2col" id="calendar_events_day_list" data-url="'.$link_refresh.'">'.$return.'</div>';
	}





	// --------------------------------------------------------------- Week

	function getWeekAlldayView($p = array()) 
	{
		$return = '<li class="box"><span class="label labelc'.$this->label_id.'"><span class="name">'.$this->calendar_event->getTitle().'</span> - <span class="title">'.$this->user_name.'</span></span></li>';
	    return $return;
	}

	function getWeekView($p = array(), $position = 0) 
	{
		
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();

		$style = pz_calendar_event_screen::getDayViewPositions($this->calendar_event, $position);
		
		// $height = 
		
		// 9:30 - 10:30
		// labelc7 labelb7
		
		$return = '
		    <article class="event label labelc'.$this->label_id.' labelb'.$this->label_id.' dragable resizeable" style="top: '.$style["top"].'px; left:'.$style["left"].'px; height: '.$style["height"].'px; width:'.$style["width"].'px" id="event-'.$this->calendar_event->getId().'">
		      <div class="event-info labelb'.$this->label_id.'">
           <header>
             <hgroup>
               <h2 class="hl7"><span class="name">'.$this->calendar_event->getTitle().'</span></h2>
             </hgroup>
           </header>
           <section class="content">
             <p>'.$this->calendar_event->getDescription().'</p>
           </section>
          </div>
	     </article>';
	     return $return;
	}

	static function getWeekListView($events = array(), $p = array(), $day)
	{

		$day_clone = clone $day;
		$day_last = clone $day;
		$day_last->modify("+6 days");
		
		$return = "";
		$content = "";
		$content_allday = "";
		
		if(count($events) > 0)
		{
			$position = 0;
			foreach($events as $event)
			{
				$e = new pz_calendar_event_screen($event);
				if($e->calendar_event->isAllDay()) {
					$content_allday .= $e->getWeekAlldayView($p);
				}else {
					$content .= $e->getWeekView($p,$position);
					$position++;
				}
			}
		}


		$grid = '';
		
		for($d=0;$d<8;$d++)
		{
			if($d>1)
				$day_clone->modify("+1 day");

			if($d == 0)
			{
				$grid .= '<li class="weekday first">'; // erste Spalte immer ein first
				
				$grid .= '<dl>';
				$grid .= '<dt class="title"></dt>';
				$grid .= '<dd class="box">';
				
				// ganztaegig
				$grid .= '<ul class="allday clearfix">';
				$grid .= '<li class="box"></li>';
				$grid .= '<li class="box"></li>';
				$grid .= '</ul>';
				
				// Stundenliste
				$grid .= '<ul class="hours clearfix">';
				for ($j = 0; $j < 24; $j++)
				{
					$grid .= '<li class="hour title">'.$j.':00</li>';
				}
				
				$grid .= '</ul>';
				$grid .= '</dd></dl></li>';
				
			}else
			{
				$class = array();
				if($day_clone->format("N") == 6 or $day_clone->format("N") == 7) {
					$class[] = "weekday";
					// TODO weekend
				}else
				{
					$class[] = "weekday";
				}

				if($day_clone->format("Ymd") == date("Ymd")) {
					$class[] = "active";
				}

				if($d == 7) {
					$class[] = "last";
				}

				$key = "mon";				
				
				// elseif ($key === 'sun')
				// $grid .= '<li class="weekday last" rel="weekday-'.$key.'">';
				// Bspl fuer aktiver Wochentag
				// $grid .= '<li class="weekday active" rel="weekday-'.$key.'">';
				$grid .= '<li class="'.implode(" ",$class).'" rel="weekday-'.$key.'">';
				
				$grid .= '<dl>';
				$grid .= '<dt class="title">'.$day_clone->format(rex_i18n::msg("format_d_month")).'</dt>';
				$grid .= '<dd class="box">';
				
				// ganztaegig
				$grid .= '<ul class="allday clearfix">';
				for ($j = 1; $j <= 1; $j++)
				{
					$grid .= '<li class="box">Geburtstag</li>';
					$grid .= '<li class="box">Urlaub</li>';
				}
				$grid .= '</ul>';
				
				// Stundenliste
				$grid .= '<ul class="hours clearfix">';
				for ($j = 0; $j < 24; $j++)
				{
					$grid .= '<li class="hour box"></li>';
				}
				
				$grid .= '</ul>';
				$grid .= '</dd></dl></li>';
				
			}
			
		}
		
		/*
		Berechnung Terminposition
		1 Minute = 1px
		0 Punkt  = 7:00 Uhr
		
		top     = Startzeit (in Minuten) - 7 Stunden (420 Minuten)
		height  = Endzeit - Startzeit (in Minuten)
		*/
	
		// Timeline - Jetzt - 7 Stunden (0 Punkt) / 60 (1 Stunde 60px hoch)
		$timeline = ceil((time() - mktime(7, 0, 0)) / 60);

		$link_refresh = pz::url("screen","calendars","day",
			array_merge(
				$p["linkvars"],
				array(
				"mode"=>"list",
				"day"=>$day->format("Ymd"),
				"project_ids" => "___value_ids___"	
				)
			)
		);

		$link_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"))))."')";

		$day->modify('-7 day');
		$link_previous = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
		$day->modify('+14 day');
		$link_next = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
		$day->modify('-7 day');
		
		$today = new DateTime();
		$link_today = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$today->format("Ymd"))))."')";

		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.$day->format(rex_i18n::msg("format_dmy")).' - '.$day_last->format(rex_i18n::msg("format_dmy")).' <span class="info">('.rex_i18n::msg("calendarweek").' '.$day->format("W").')</span></h1>
	          </div>
	        </header>';

		$return .= '

		<div class="calendar view-week clearfix">

			<header class="header">
			<div class="grid3col">
				<div class="column first">
				<a class="bt2" href="'.$link_add.'">'.rex_i18n::msg("new_event").'</a>
				</div>
				<div class="column last">
				<ul class="pagination">
				<li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.rex_i18n::msg("previous").'</span></a></li>
				<li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.rex_i18n::msg("next").'</span></a></li>
				<li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.rex_i18n::msg("today").'</span></a></li>
				</ul>
				</div>
			</div>
			</header>

			<div class="wrapper">
			
				<div class="grid clearfix">
				<ul class="weekdays">
				'.$grid.'
				</ul>
				</div>
				
				<div class="events clearfix">
				'.$content.'
				</div>
				
				<div class="timeline" style="top: '.$timeline.'px;"><span class="icon"></span><span class="line"></span></div>
			
			</div>
		</div>
		';

		return '<div class="design2col" id="calendar_events_week_list" data-url="'.$link_refresh.'">'.$return.'</div>';

	}


	// --------------------------------------------------------------- static views

	static function getSearch($projects, $events, $p = array(), $day)
	{
		
		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("calendar_day_list").'</h1>
	          </div>
	        </header>';
		
		$month_1 = clone $day;
		$month_2 = clone $day;

		$link_refresh = pz::url("screen","calendars",$p["function"],
				array_merge(
					$p["linkvars"],
					array(
						"mode" => "search",
						"day"=>$month_2->format("Ymd"),
						"project_ids" => "___value_ids___"
					)
				)
				);

		
		$month_2->modify("-2 month");

		$link_previous = "javascript:pz_loadPage('".$p["layer_search"]."','".pz::url("screen","calendars",$p["function"],
				array_merge(
					$p["linkvars"],
					array("mode"=>"search","day"=>$month_2->format("Ymd"))
				)
				)."')";
		
		$month_2->modify("+4 month");

		$link_next = "javascript:pz_loadPage('".$p["layer_search"]."','".pz::url("screen", "calendars", $p["function"],
				array_merge(
					$p["linkvars"],
					array("mode"=>"search","day"=>$month_2->format("Ymd"))
				)
				)."')";

		$month_2->modify("-1 month");
		
		$return .= '
	  <div class="calendar view-overview clearfix">
		
      <div class="grid2col full">
        <div class="column first">
          <header>
            <h2 class="hl3">'.$month_1->format(rex_i18n::msg("format_month")).'</h2>
            <ul class="pagination">
              <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.rex_i18n::msg("previous").'</span></a></li>
            </ul>
          </header>

          <section class="content">
            '.pz_calendar_event_screen::getMonthCalendar($events, $p, $day, $month_1).'
          </section>
        </div>
        <div class="column last">
          <header>
            <h2 class="hl3">'.$month_2->format(rex_i18n::msg("format_month")).'</h2>
            <ul class="pagination">
              <li class="last next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.rex_i18n::msg("next").'</span></a></li>
            </ul>
          </header>

          <section class="content">
            '.pz_calendar_event_screen::getMonthCalendar($events, $p, $day, $month_2).'
          </section>
        </div>
      </div>
      
      </div>
        ';

		return '<div class="design1col" id="'.$p["layer_search"].'" data-url="'.$link_refresh.'">'.$return.'</div>';
	}



	// --------------------------------------------------------------- Small Month

   /*
     CSS Klassen fuer <td>s
     - weekday   = Tag liegt in der Woche
     - weekend   = Tag ist am Wochenende

     - selected  = Tag liegt in der ausgewaehlten Ansicht (Tages-, Wochen-, Monatsansicht)
     - event     = Tag hat Termine

     - month-before  = Tag liegt im vorherigen Monat
     - month-after   = Tag liegt im naechsten Monat

     - holiday = Tag ist ein Feiertag
     - today   = heutiger Tag

     <a>s immer ein title Attribut mitgeben (wurde wegen Uebersichtlichkeit weggelassen)
 	     Bspl
         <a title="Freitag, 3. Juni 2011">3</a>

   */

	static function getMonthCalendar($events, $p = array(), $day, $month)
	{
		$return = "";
		// $return = count($events);
		
		$available_events = array();
		foreach($events as $event)
		{
			if(is_object($event))
				if($from = @$event->getFrom())
				 	$available_events[$from->format("Ymd")] = $from->format("Ymd");
		}

	    $return .= '
	        <table class="overview">
	          <colgroup span="8"></colgroup>
	          <thead>
	          <tr>
	            <th title="'.rex_i18n::msg("calendarweek").'">'.rex_i18n::msg("calendarweek_short").'</th>
	            <th title="'.rex_i18n::msg("monday").'">'.rex_i18n::msg("monday_short").'</th>
	            <th title="'.rex_i18n::msg("tuesday").'">'.rex_i18n::msg("tuesday_short").'</th>
	            <th title="'.rex_i18n::msg("wednesday").'">'.rex_i18n::msg("wednesday_short").'</th>
	            <th title="'.rex_i18n::msg("thursday").'">'.rex_i18n::msg("thursday_short").'</th>
	            <th title="'.rex_i18n::msg("friday").'">'.rex_i18n::msg("friday_short").'</th>
	            <th title="'.rex_i18n::msg("saturday").'">'.rex_i18n::msg("saturday_short").'</th>
	            <th title="'.rex_i18n::msg("sunday").'">'.rex_i18n::msg("sunday_short").'</th>
	          </tr>
	          </thead>
	          <tbody>';
	    
	    $today = new DateTime();
	    
	    $current_month = $month->format("m");
	    
		$month->modify("first day of this month"); 
		$month->modify("-".($month->format("N"))." days");

	    for($i = 0; $i < 5; $i++)
	    {
	    	$return .= '<tr>';
			$return .= '<td class="calendarweek">'.$month->format("W").'</td>';
	    	for($j=0;$j<7;$j++)
	    	{
	    		
	    		// $day->modify('-1 day');
	    		// calendar_events_day_list

	    		$month->modify("+1 day");
				$link = "javascript:pz_loadPage('".$p["layer_list"]."','".pz::url("screen","calendars",$p["function"],
						array_merge(
							$p["linkvars"],
							array("mode"=>"list","day"=>$month->format("Ymd"))
						)
						)."')";


				$classes = array();
				if($month->format("N")>5) $classes[] = "weekend";
				else $classes[] = "weekday";	
	    		
	    		if($month->format("Ymd") == $today->format("Ymd")) $classes[] = "today";

	    		if(isset($available_events[$month->format("Ymd")])) $classes[] = "event"; 
	    		
	    		if($current_month > $month->format("m")) $classes[] = "month-before";
	    		elseif($current_month < $month->format("m")) $classes[] = "month-after";
	    		
	    		$return .= '<td class="'.implode(" ",$classes).'"><a href="'.$link.'">'.$month->format("j").'</a></td>';	
	    	}
	    	$return .= '</tr>';
	    }

		$return .= '
	          </tbody>
	        </table>';
	
	    return $return;
	  }



	// --------------------------------------------------------------- Projectjob


	public function getProjectjobListView($projects, $events, $p, $month)
	{

		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("calendar_day_list").'</h1>
	          </div>
	        </header>';
  
  		$flyout = "";
  
	    $th = '<tr>';
	    $th .= '<th>'.rex_i18n::msg("project").'</th>';
	    for ($i = 1; $i <= $month->format("t"); $i++)
	    {
	      $j = ($i <= 9) ? '0'.$i : $i;
	      $th .= '<th>'.$j.'.</th>';
	    }
	    $th .= '</tr>';
	    
	    
	    $tr = array();
	    foreach($projects as $project_id)
	    {
	    	$tr[$project_id] = '<tr>';
	    	$tr[$project_id] = '<th>project</th>';
	    	
			for ($i = 1; $i <= $month->format("t"); $i++)
			{
				$tr[$project_id] .= '<td>'.$j.'</td>';
			}
	    	$tr[$project_id] .= '</tr>';
	    	
	    }
	    
	    
	    // for($d = 1;$d < $month->format("t");$d++) { }
	    
	    
	    
	    /*
	    
	    $tr = '';
	    for ($i = 1; $i <= 10; $i++)
	    {
	      $td = '';
         $z = rand(1, 11);
         for ($j = 1; $j <= 31; $j++)
         { 
           $c = '';
           if ($j == $z)
           {
             $c = str_replace('###placeholder###', '<span class="bloat">&nbsp;</span>', $flyout);
             $td .= '<td class="label labelc'.$z.'">'.$c.'</td>';
           }
           else
           {
             $td .= '<td></td>';
           }
         }
         
          $tr .= '
			      <tr>
 				      <th>'.$project[array_rand($project)].'</th>
			        '.$td.'
			      </tr>';
	    }
	    */

		$return .= '
		<div class="calendar projectjob clearfix">
		  <header class="header">
		    <div class="grid3col">
		      <div class="column first">
		        <a class="bt5" href="#">Excel Export</a>
		        <a class="bt5" href="#">Monat</a>
		      </div>
		      <div class="column">
		        <p class="info">Juni. 2011 (KW 22-27)</p>
		      </div>
		      <div class="column last">
		            <ul class="pagination">
		              <li class="first prev"><a class="page prev bt5" href=""><span class="inner">zurück</span></a></li>
		              <li class="next"><a class="page next bt5" href=""><span class="inner">vorwärts</span></a></li>
		              <li class="last"><a class="bt5" href=""><span class="inner">Heute</span></a></li>
		            </ul>
		      </div>
		    </div>
		  </header>
		  
		  <table class="tbl2">
		    <thead>
		          <tr>
		            '.$th.'
		          </tr>
		        </thead>
		    <tbody>
		      '.implode("",$tr).'
		        </tbody>
		  </table>
		  
		</div>
		';
 
		return '<div class="design2col" id="calendar_projectjob_list">'.$return.'</div>';

	}


















	// --------------------------------------------------------------- Formviews

	public function getEditForm($p = array()) 
	{
    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("calendar_event_edit").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("form_id", "calendar_event_edit_form");

		$xform->setObjectparams("main_table",'pz_calendar_event');
		$xform->setObjectparams("main_id",$this->calendar_event->getId());
		$xform->setObjectparams("main_where",'id='.$this->calendar_event->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('calendar_event_edit','calendar_event_edit_form','".pz::url('screen','calendars',$p["function"],array("mode"=>'edit_calendar_event'))."')");

		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setHiddenField("calendar_event_id",$this->calendar_event->getId());
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));

		$xform->setValueField("textarea",array("title",rex_i18n::msg("calendar_event_title")));
		$projects = pz::getUser()->getCalendarProjects();
		$xform->setValueField("pz_select_screen",array("project_id",rex_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("text",array("location",rex_i18n::msg("calendar_event_location")));
		$xform->setValueField("checkbox",array("booked",rex_i18n::msg("calendar_event_booked")));
		$xform->setValueField("checkbox",array("allday",rex_i18n::msg("calendar_event_allday")));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1"));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
		$xform->setValueField("pz_datetime_screen",array("from",rex_i18n::msg("calendar_event_from")));
		$xform->setValueField("pz_datetime_screen",array("to",rex_i18n::msg("calendar_event_to")));
		$xform->setValueField("text",array("url",rex_i18n::msg("calendar_event_url")));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("calendar_event_description")));

		$xform->setValidateField("mysql_datetime",array("from",rex_i18n::msg("error_calendar_from_datetime")));
		$xform->setValidateField("mysql_datetime",array("to",rex_i18n::msg("error_calendar_to_datetime_wrong")));
		$xform->setValidateField("pz_project_id",array("project_id",rex_i18n::msg("error_calendar_event_project_id")));
		$xform->setValidateField("empty",array("title",rex_i18n::msg("error_calendar_event_title_empty")));
		$xform->setValidateField("compare_fields",array("from","to",">",rex_i18n::msg("error_calendar_event_fromto_compare")));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
		
			$value_pool = $xform->getObjectparams("value_pool");
			$data = $value_pool["sql"];
		
			$format = 'Y-m-d H:i:s';
			$from = DateTime::createFromFormat($format, $data["from"]);
			$to = DateTime::createFromFormat($format, $data["to"]);

			$created = DateTime::createFromFormat($format, $data["created"]);
			$updated = DateTime::createFromFormat($format, $data["updated"]);

			$event = pz_calendar_event::get($this->calendar_event->getId());
			$event->setTitle($data["title"]);
			$event->setProjectId($data["project_id"]);
			$event->setLocation($data["location"]);
			$event->setBooked($data["booked"]);
			$event->setAllDay($data["allday"]);
			$event->setFrom($from);
			$event->setTo($to);
			$event->setDescription($data["description"]);
			$event->setUrl($data["url"]);
			$event->setCreated($created);
			$event->setUpdated($updated);
			$event->setUserId(pz::getUser()->getId());

			// setSequence($sequence)
			// $alarm = pz_calendar_alarm::create();
			// $alarm->setAction()
			// etc.
			// $event->setAlarms(array($alarm));
			
			$event->save();
		
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("calendar_event_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('calendar_events_day_list','calendar_event_edit_form',pz::url('screen','calendars','day',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		
		$delete_link = pz::url("screen","calendars",$p["function"],array("calendar_event_id"=>$this->calendar_event->getId(),"mode"=>"delete_calendar_event"));
		$return .= '<div class="xform">
				<p><a class="bt5" onclick="check = confirm(\''.rex_i18n::msg("calendar_event_confirm_delete",htmlspecialchars($this->calendar_event->getTitle())).'\'); if (check == true) pz_loadPage(\'calendar_event_form\',\''.$delete_link.'\')" href="javascript:void(0);">- '.rex_i18n::msg("delete_calendar_event").'</a></p>
				</div>';
		
		
		$return = '<div id="calendar_event_form"><div id="calendar_event_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}

	function getDeleteForm($p = array())
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("delete_calendar_event").'</h1>
	          </div>
	        </header>';

		$title = $this->calendar_event->getTitle();
		$this->calendar_event->delete();
		
		$return = $header.'<p class="xform-info">'.rex_i18n::msg("delete_calendar_deleted", htmlspecialchars($title)).'</p>';
		$return .= pz_screen::getJSLoadFormPage($p["layer_list"],$p["layer_search"],pz::url('screen','calendars',$p["function"],array("mode"=>'list')));

		$return = '<div id="calendar_event_form"><div id="calendar_event_delete" class="design1col xform-delete">'.$return.'</div></div>';

		return $return;
	}



	static function getAddForm($p = array()) 
	{
		$header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("calendar_event_add").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		$xform->setObjectparams("form_id", "calendar_event_add_form");
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('calendar_event_form','calendar_event_add_form','".pz::url('screen','calendars','day',array("mode"=>'add_calendar_event'))."')");
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField("textarea",array("title",rex_i18n::msg("calendar_event_title")));
		$projects = pz::getUser()->getCalendarProjects();
		$xform->setValueField("pz_select_screen",array("project_id",rex_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("text",array("location",rex_i18n::msg("calendar_event_location")));
		$xform->setValueField("checkbox",array("booked",rex_i18n::msg("calendar_event_booked")));
		$xform->setValueField("checkbox",array("allday",rex_i18n::msg("calendar_event_allday")));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1"));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));
		$xform->setValueField("pz_datetime_screen",array("from",rex_i18n::msg("calendar_event_from")));
		$xform->setValueField("pz_datetime_screen",array("to",rex_i18n::msg("calendar_event_to")));
		$xform->setValueField("text",array("url",rex_i18n::msg("calendar_event_url")));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("calendar_event_description")));

		/*
			TODO
			jquery
				wenn ganztag angeklickt, dann time ausblenden
					+ uhrzeit from auf 0, to auf 23
			TODO
				wiederholung
				erinnerung
				einladungen
				rule_id
				base_from
				sequence
				vt
		*/

		$xform->setValidateField("mysql_datetime",array("from",rex_i18n::msg("error_calendar_from_datetime")));
		$xform->setValidateField("mysql_datetime",array("to",rex_i18n::msg("error_calendar_to_datetime")));
		$xform->setValidateField("pz_project_id",array("project_id",rex_i18n::msg("error_calendar_event_project_id")));
		$xform->setValidateField("empty",array("title",rex_i18n::msg("error_calendar_event_title_empty")));
		$xform->setValidateField("compare_fields",array("from","to",">",rex_i18n::msg("error_calendar_event_fromto_compare")));
		
		$form = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
		
			$value_pool = $xform->getObjectparams("value_pool");
			$data = $value_pool["sql"];
		
			$format = 'Y-m-d H:i:s';
			$from = DateTime::createFromFormat($format, $data["from"]);
			$to = DateTime::createFromFormat($format, $data["to"]);

			$created = DateTime::createFromFormat($format, $data["created"]);
			$updated = DateTime::createFromFormat($format, $data["updated"]);

			$event = pz_calendar_event::create();
			$event->setTitle($data["title"]);
			$event->setProjectId($data["project_id"]);
			$event->setLocation($data["location"]);
			$event->setBooked($data["booked"]);
			$event->setAllDay($data["allday"]);
			$event->setFrom($from);
			$event->setTo($to);
			$event->setDescription($data["description"]);
			$event->setUrl($data["url"]);
			$event->setCreated($created);
			$event->setUpdated($updated);
			$event->setUserId(pz::getUser()->getId());

			// setSequence($sequence)
			// $alarm = pz_calendar_alarm::create();
			// $alarm->setAction()
			// etc.
			// $event->setAlarms(array($alarm));
			
			$event->save();

			if($event = pz_calendar_event::get($event->getId())) {
			
				$return = "";
				// $return = $header.'<p class="xform-info">'.rex_i18n::msg("calendar_event_added").'</p>';
				$return .= pz_screen::getJSUpdateLayer($p["layer_list"],pz::url('screen','calendars',$p["function"],array("mode"=>'list')));
				// $return .= pz_screen::getJSLoadFormPage('calendar_events_day_list','calendar_event_add_form',pz::url('screen','calendars','day',array("mode"=>'list')));

				$cs = new pz_calendar_event_screen($event);
				$return .= $cs->getEditForm($p);

				return $return;
			}else
			{
				$return = $header.'<p class="xform-warning">'.rex_i18n::msg("error_calendar_event_not_added").'</p>'.$form;
			}

		}else {
			$return = $header.$form;	
		}

		$return = '<div id="calendar_event_form"><div id="calendar_event_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}


}


?>