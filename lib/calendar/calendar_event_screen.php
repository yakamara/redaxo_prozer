<?php

class pz_calendar_event_screen{

	public $event,$user,$user_name, $label,$project;
	
	function __construct($event) 
	{
		$this->calendar_event = $event;
		$this->user_name = rex_i18n::msg("username_not_avaiable");
		
		if(!is_object($this->calendar_event)) {
			echo pz::error('CALEVENT_MISSING');
			exit;
		}
		
		if($this->user = pz_user::get($this->calendar_event->getUserId())) {
			$this->user_name = $this->user->getName();
		}
		$this->label_id = "1";
		if($this->project = pz_project::get($this->calendar_event->getProjectId())) {
			$this->label_id = $this->project->getLabelId();
		}
		
	}


	// --------------------------------------------------------------- attandee

	static function getAttendeeListView($p,$events)
	{
		$return = '';
		if(count($events) > 0) 
		{
			foreach($events as $event) {
				$a = new pz_calendar_event_screen($event);
				$return .= '<div class="calendar_event_view">'.$a->getEventView($p).'</div>';
			}
		}
	
		$return = '<div id="calendar_event_attendee_view" class="design1col">'.$return.'</div>';
		return $return;
	}





	// --------------------------------------------------------------- views

	public function getFlyoutView($p = array()) 
	{
	
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();

		$info = "";
		$resize = '';
		$edit_classes = "";
		$edit = "";

		if(pz::getUser()->getEventEditPerm($this->calendar_event))
		{
			$edit = '<br /><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.rex_i18n::msg("edit").'</a>';
			
		}

		$date_info = '';
		if($this->calendar_event->isAllday())
			if($from->format("Ymd") == $to->format("Ymd"))
				$date_info = $from->format("d.m.Y");
			else
				$date_info = $from->format("d.m.Y").' - '.$to->format("d.m.Y");
		else
			if($from->format("Ymd") == $to->format("Ymd"))
				$date_info = $from->format("d.m.Y").' _____ '.$from->format("H:i").' - '.$to->format("H:i");
			else
				$date_info = $from->format("d.m.Y H:i").' - '.$to->format("d.m.Y H:i");
				
		$location = '';
		if($this->calendar_event->getLocation() != "")
			$location = '<li>'.rex_i18n::msg('location').': '.htmlspecialchars($this->calendar_event->getLocation()).'</li>';

		$url = '';
		if($this->calendar_event->getUrl() != "")
			$url = '<li><a href="http://'.$this->calendar_event->getUrl().'" target="_blank">http://'.htmlspecialchars($this->calendar_event->getUrl()).'</a></li>';

		$job = '';
		if($this->calendar_event->isBooked() != "")
			$url = '<li>'.rex_i18n::msg('is_job').'</li>';

		$attandees = '';
		
		$user_emails = pz::getUser()->getEmails();
		
		$as = pz_calendar_attendee::getAll($this->calendar_event);
		if(is_array($as) && count($as)>0) {
			
			$attandees .= '<li><h2 class="hl2">'.rex_i18n::msg('calendar_event_attendees').'</h2>';
			
			$me = null;
			$attandees_list = array();
			foreach($as as $a) {
				$attandees_list[] = $a->getName().' / '.$a->getEmail().' ['.rex_i18n::msg('calendar_event_attendee_'.strtolower($a->getStatus())).']';
				if(in_array($a->getEmail(),$user_emails)) {
					$me = $a;
				}
			}
			$attandees .= implode('<br />',$attandees_list);
			$attandees .= '</li>';

			if($me)
			{	$attandees .= '<br />';
				foreach(pz_calendar_attendee::getStatusArray() as $k => $v)
				{
					$link = 'pz_loadPage(\'.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"set_attandee_status","calendar_event_id"=>$this->calendar_event->getId(),"attandee_status" => $v))).'\')';
					
					if($me->getStatus() == $v)
						$attandees .= '<a class="bt3" href="javascript:void(0);">'.rex_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a>';	
					else
						$attandees .= '<a class="bt5" href="javascript:void(0);" onclick="'.$link.'">'.rex_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a>';	
				}
			}
			
		}
		
		$return = 	'
					<div class="flyout event-'.$this->calendar_event->getId().'">
                      <div class="content">
                        <div class="output">
                          <a class="tooltip close bt5" href="javascript:void(0);" onclick="$(this).parent().parent().parent().css(\'display\',\'none\');"><span class="icon"></span><span class="tooltip"><span class="inner">'.rex_i18n::msg("close").'</span></span></a>
                          <h2 class="hl2">'.$this->calendar_event->getTitle().' | '.$this->user_name.'</h2>
                          <div class="split-h split-h1"></div>
                          <h2 class="hl2">'.$date_info.'</h2>
                          <p>'.$this->calendar_event->getDescription().'</p>
                          <div class="split-h split-h1"></div>
                          <ul>
                            '.$location.'
                            '.$url.'
                            <li>'.$job.'</li>
                            <li>'.$attandees.'</li>
                            <li>'.$edit.'</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    ';

		return $return;
	
	}
	
	





	// --------------------------------------------------------------- views

	public function getEventView($p = array()) 
	{
	
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();

		$info = "";
		$resize = '';
		$edit_classes = "";
		$edit = "";
		
		if(pz::getUser()->getEventEditPerm($this->calendar_event))
		{
			$edit = '<div class="split-h split-h1"></div><ul class="buttons"><li><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.rex_i18n::msg("edit").'</a></li></ul>';
		}

		$date_info = '';
		if($this->calendar_event->isAllday())
			if($from->format("Ymd") == $to->format("Ymd"))
				$date_info = $from->format("d.m.Y");
			else
				$date_info = $from->format("d.m.Y").' - '.$to->format("d.m.Y");
		else
			if($from->format("Ymd") == $to->format("Ymd"))
				$date_info = $from->format("d.m.Y").', <span>'.$from->format("H:i").' - '.$to->format("H:i").'</span>';
			else
				$date_info = $from->format("d.m.Y H:i").' - '.$to->format("d.m.Y H:i");
    
    $date_info = $date_info != '' ? '<h2>'.$date_info.'</h2>' : '';
    
    if ($this->calendar_event->getDescription() != '')
      $date_info .= '<p>'.$this->calendar_event->getDescription().'</p>';
    
    
    $event_infos = '';
		if($this->calendar_event->getLocation() != "")
			$event_infos .= '<dt>'.rex_i18n::msg('location').':</dt><dd>'.htmlspecialchars($this->calendar_event->getLocation()).'</dd>';

		if($this->calendar_event->getUrl() != "")
		{
		  $url = $this->calendar_event->getUrl();
		  if (substr($url, 0, 7) != 'http://')
		    $url = 'http://'.$url;
		    
			$event_infos .= '<dt>'.rex_i18n::msg("calendar_event_url").':</dt><dd><a href="'.$url.'" target="_blank">'.htmlspecialchars(pz::cutText($url)).'</a></dd>';
		}
    
		if($this->calendar_event->isBooked() != "")
			$event_infos .= '<dt>'.rex_i18n::msg('job').':</dt><dd>'.rex_i18n::msg('is_job').'</dd>';


		if($this->calendar_event->hasRule())
		{
			$event_infos .= '<dt>'.rex_i18n::msg('calendar_event_rule').':</dt><dd>'.rex_i18n::msg('calendar_event_has_rule').'</dd>';
			$event_infos .= '<dt>'.rex_i18n::msg('calendar_event_frequence').':</dt><dd>'.$this->calendar_event->getRule()->getFrequence().'</dd>';
			$event_infos .= '<dt>'.rex_i18n::msg('calendar_event_interval').':</dt><dd>'.$this->calendar_event->getRule()->getInterval().'</dd>';

		}

		$event_infos = $event_infos != '' ? '<div class="split-h split-h1"></div><dl>'.$event_infos.'</dl>' : '';

		$attandees = '';
		$actions = '';
		
		$user_emails = pz::getUser()->getEmails();
		
		$as = pz_calendar_attendee::getAll($this->calendar_event);
		if(is_array($as) && count($as)>0)
		{
			
			$attandees .= '<div class="split-h split-h1"></div><h2>'.rex_i18n::msg('calendar_event_attendees').'</h2>';
			
			$me = null;
			$attandees_list = '';
			foreach($as as $a)
			{
				$attandees_list .= '<li class="status-'.strtolower($a->getStatus()).'">'.$a->getName().' / '.$a->getEmail().' ['.rex_i18n::msg('calendar_event_attendee_'.strtolower($a->getStatus())).']</li>';
				
				if(in_array($a->getEmail(),$user_emails))
				{
					$me = $a;
				}
			}
			
			if ($attandees_list != '')
  			$attandees .= '<ul>'.$attandees_list.'</ul>';

			if($me)
			{
        $actions .= '<div class="split-h split-h1"></div><ul class="buttons">';
				foreach(pz_calendar_attendee::getStatusArray() as $k => $v)
				{
					$link = 'pz_loadPage(\'.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"set_attandee_status","calendar_event_id"=>$this->calendar_event->getId(),"attandee_status" => $v))).'\')';
					
					if($me->getStatus() == $v)
						$actions .= '<li><a class="bt3" href="javascript:void(0);">'.rex_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';	
					else
						$actions .= '<li><a class="bt5" href="javascript:void(0);" onclick="'.$link.'">'.rex_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';	
				}
        $actions .= '</ul>';
			}
			
		}
		
		$return = 	'
					<div class="bucket event-'.$this->calendar_event->getId().'">
            <div class="content">
              <div class="output">
                <a class="tooltip close bt5" href="javascript:void(0);" onclick="$(this).parent().parent().parent().css(\'display\',\'none\');"><span class="icon"></span><span class="tooltip"><span class="inner">'.rex_i18n::msg("close").'</span></span></a>
                
                <header>
                  <h1 class="hl2">'.$this->calendar_event->getTitle().' | '.$this->user_name.'</h1>
                </header>
                
                <div class="formatted">
                  '.$date_info.'
                  '.$event_infos.'
                  '.$attandees.'
                </div>
                
                
                
                '.$actions.'
                '.$edit.'
              </div>
            </div>
          </div>
                    ';

		return $return;
	
	}


	// --------------------------------------------------------------- Day

	
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
					$content .= $e->getDayView($day, $p, $position);
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
					"calendar_project_ids" => "___value_ids___"	
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
	            <h1 class="hl1">'.
	            
	            pz::dateTime2dateFormat($day,rex_i18n::msg("format_ddmy")).
	            
	            ' <span class="info">('.rex_i18n::msg("calendarweek").' '.pz::dateTime2dateFormat($day,"W").')</span></h1>
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
				<div id="calendar_event_flyout" class="sl5"></div>
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


	static function getDayViewPositions($day, $event, $position = 0)
	{
		
		$from = $event->getFrom();
		$to = $event->getTo();
		$duration = $event->getDuration();
		
		if($from->format("Ymd") == $day->format("Ymd")) {
			$top = ($from->format("H")*60)+$from->format("i");
			$height = ($to->format("H")*60)+$to->format("i")-$top;
			if($to->format("Ymd") > $day->format("Ymd")) {
				$height = (24*60) - $top;
			}

		}else {
			$top = 0;
			$height = ($to->format("H")*60)+$to->format("i");
			if($to->format("Ymd") > $day->format("Ymd")) {
				// whole day
				$height = (24*60);
			}
		}
		
		$left = ($position*50);
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

	public function getDayView($day, $p = array(), $position = 0) 
	{

		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();
		$attandees = $this->calendar_event->getAttendees();

		$style = pz_calendar_event_screen::getDayViewPositions($day, $this->calendar_event, $position);
		
		$classes = array();
		$classes[] = 'event';
		$classes[] = 'label';
		$classes[] = 'labelc'.$this->label_id; // background color
		$classes[] = 'labelb'.$this->label_id; // border color
		
		$info = "";
		$resize = '';
		if(pz::getUser()->getId() == $this->calendar_event->getUserId())
		{
			$info = '<span class="editable">[editable]</span>';	
			if($from->format("Ymd") == $to->format("Ymd")) {
				$resize = '<span class="resize">resize</span>';
				$classes[] = "dragable";
				$classes[] = "resizeable";
			}
		}else
		{
			$info = '<span class="noeditable">no edit</span>';
		}
		
		// - Termin ohne Einladungen
		// - Termin mit Einladungen, auch für mich
		// - Termin mit Einladungen, nicht für mich
		// - Termin mit Einladungen, auch für mich, von mir bestaetigt
		
		// - Wiederholungstermine markieren
		// - Alarmtermine markieren
		
		// einladung mit schraegen flaechen
		
		if(count($attandees) > 0)
			$classes[] = 'event_attandees';
		
		$flyout_link = 'pz_setZIndex(\'#event-'.$this->calendar_event->getId().'\');pz_loadPage(\'calendar_event_view\',\''.
		pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).
		'\')';
		
		$return = '
		    <article class="'.implode(" ",$classes).'" style="top: '.$style["top"].'px; left:'.$style["left"].'px; height: '.$style["height"].'px; width:'.$style["width"].'px" id="event-'.$this->calendar_event->getId().'">
		      <div class="event-info labelb'.$this->label_id.'">
           <header>
             <hgroup>
               <h2 class="hl7"><a href="javascript:void(0);" onclick="'.$flyout_link.'">'.$info.'<span class="name">'.$from->format("H:i").' - '.$to->format("H:i").'</span><span class="info"> | '.$this->calendar_event->getTitle().' | '.$this->user_name.'</span></a></h2>
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
	
	public function getDayAlldayView($p = array()) 
	{
		$flyout_link = 'pz_loadPage(\'calendar_event_view\',\''.
		pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).
		'\')';
		
		$return = '<li class="entry"><a href="javascript:void(0);" onclick="'.$flyout_link.'"><span class="label labelc'.$this->label_id.'"><span class="name">'.$this->calendar_event->getTitle().'</span> - <span class="title">'.$this->user_name.'</span></span></a></li>';
	    return $return;
	}






	// --------------------------------------------------------------- Week

	public function getWeekAlldayView($p = array()) 
	{
		$return = '<li class="box"><span class="label labelc'.$this->label_id.'"><span class="name">'.$this->calendar_event->getTitle().'</span> - <span class="title">'.$this->user_name.'</span></span></li>';
	    return $return;
	}
	public function getWeekView($p = array(), $position = 0) 
	{
/*
		
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$duration = $this->calendar_event->getDuration();

		$style = pz_calendar_event_screen::getDayViewPositions($day, $this->calendar_event, $position);
		
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
		*/
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
					"calendar_project_ids" => "___value_ids___"	
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





	
	// ---------------------------------------------------------- month views


	static function getMonthListView($events = array(), $p = array(), $month)
	{
		$p["view"] = "month2col";
		$design = (isset($p["view"])) ? 'design'.substr($p["view"], -4) : '';
		$p["title"] = "month";

		$return = "";
		foreach($events as $event)
		{
			if($e = new pz_calendar_event_screen($event))
			{
				$return .= $e->getMonthView($p);
			}
		}

		$return = '

		  <div class="calendar view-month clearfix">
		    <header class="header">
		      <div class="grid3col">
				      <div class="column first">
				        <a class="bt2" href="#">'.rex_i18n::msg("new_entry").'</a>
				      </div>
				      <div class="column">
				        <p class="info">ff'.$month->format(rex_i18n::msg("format_month_y")).'(KW 22-27)</p>
				      </div>
				      <div class="column last">
                <ul class="pagination">
                  <li class="first prev"><a class="page prev bt5" href=""><span class="inner">'.rex_i18n::msg("previous").'</span></a></li>
                  <li class="next"><a class="page next bt5" href=""><span class="inner">'.rex_i18n::msg("next").'</span></a></li>
                  <li class="last"><a class="bt5" href=""><span class="inner">'.rex_i18n::msg("today").'</span></a></li>
                </ul>
				      </div>
		      </div>
		    </header>
		    <div class="header clearfix">
		      <ul class="titles">
				      <li class="day title">'.rex_i18n::msg("monday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("tuesday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("wednesday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("thursday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("friday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("saturday_short").'</li>
				      <li class="day title">'.rex_i18n::msg("sunday_short").'</li>
				    </ul>
		    </div>
		    <div class="boxes clearfix">
				    '.$return.'
				  </div>
		  </div>
		  
		  
		  ';

		$f = new rex_fragment();
		$f->setVar('design', $design, false);
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $return , false);
		$f->setVar('paginate', '', false);

		return $f->parse('pz_screen_list');

	}

	public function getMonthView($p = array())
	{
		$class = "";
		
		$from = $this->calendar_event->getFrom();
		$to = $this->calendar_event->getTo();
		$title = $this->calendar_event->getTitle();
		
		$return = '
		      <div class="day box'.$class.'">
		        <span class="calendar-week">week</span>
            <dl class="day-list">
              <dt class="date">DARE</dt>
              <dd class="list">
                <ul class="sl4 events">
                  <li class="event has-flyout">
                    <a class="event-entry" href="">
                      <span class="event-time">13:00</span>
                      <span class="event-title">'.$title.'</span>
                    </a>
                    <div class="flyout">
                      <div class="content">
                        <div class="output">
                          <a class="tooltip close bt5" href=""><span class="icon"></span><span class="tooltip"><span class="inner">Schließen</span></span></a>
                          Info
                        </div>
                      </div>
                    </div>
                  </li>
                  <li class="event has-flyout">
                    <a class="event-entry" href="">
                      <span class="event-time">13:30 - 15:00</span>
                      <span class="event-title">zweiter Termin</span>
                    </a>
                    '.$this->getFlyoutView($p).'
                  </li>
                  <li class="event">
                    <a class="event-more" href="">
                      + 5 weitere
                    </a>
                  </li>
                </ul>
              </dd>
            </dl>';

    if (2 == 1) // BIG
      $return .= '
            <div class="day box'.$class.' day-big">
              <span class="calendar-week">DATE</span>
              <a class="tooltip close bt2" href=""><span class="icon"></span><span class="tooltip"><span class="inner">Schließen</span></span></a>
              <dl class="day-list">
                <dt class="date">DATE</dt>
                <dd class="list">
                  <ul class="sl4 events">
                    <li class="event has-flyout">
                      <a class="event-entry" href="">
                        <span class="event-time">13:00</span>
                        <span class="event-title">erster Termin</span>
                      </a>
                      <div class="flyout">
                        <div class="content">
                          <div class="output">
                            Info
                          </div>
                        </div>
                      </div>
                    </li>
                    <li class="event has-flyout">
                      <a class="event-entry" href="">
                        <span class="event-time">13:30 - 15:00</span>
                        <span class="event-title">zweiter Termin</span>
                      </a>
                      <div class="flyout">
                        <div class="content">
                          <div class="output">
                            <a class="tooltip close bt5" href=""><span class="icon"></span><span class="tooltip"><span class="inner">Schließen</span></span></a>

                            <h2 class="hl2">Besprechnung Webseite in Langen</h2>
                            <p>Fr, 13. Juli, 9:30-10:30 in Frankfurt</p>

                            <div class="split-h split-h1"></div>

                            <p>Besprechnung aller relevanten Screens, Budgeplanung und Definition der technischen Machbarkeit</p>

                            <div class="split-h split-h1"></div>

                            <ul>
                              <li>Hage Relaunch.</li>
                            </ul>

                            <div class="split-h split-h1"></div>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </dd>
              </dl>
            </div>';

    $return .= '
          </div>
        ';

		return $return;
	}



	// ---------------------------------------------------------- table views

	static function getProjectJobsTableView($jobs,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($jobs);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $job) {
			
			$user = $job->getUser();
			$duration = (($job->getDuration()->format("%d")*24*60)+$job->getDuration()->format("%h")).'h ';
			if($job->getDuration()->format("%I") != 0) $duration .= $job->getDuration()->format("%I").'m';
			
			$content .= '<tr>';
			$content .= '<td class="img1"><img src="'.$user->getInlineImage().'" /></td>';
			$content .= '<td>'.$user->getName().'</td>';
			$content .= '<td>'.$job->getTitle().'</td>';
			$content .= '<td>'.$job->getDescription().'</td>';
			$content .= '<td><nobr>'.$duration.'</nobr>&nbsp;</td>';
			$content .= '<td>'.$job->getFrom()->format(rex_i18n::msg("format_d_m_y"))."<br /><nobr>".$job->getFrom()->format(rex_i18n::msg("format_h_i")).'h - '.$job->getTo()->format(rex_i18n::msg("format_h_i")).'</nobr></td>';
			$content .= '<td>'.$job->getCreated()->format(rex_i18n::msg("format_d_m_y")).'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectjobs tbl1">
          <thead><tr>
              <th colspan="2">'.rex_i18n::msg("user").'</th>
              <th>'.rex_i18n::msg("title").'</th>
              <th>'.rex_i18n::msg("description").'</th>
              <th>'.rex_i18n::msg("hours").'</th>
              <th>'.rex_i18n::msg("duration").'</th>
              <th>'.rex_i18n::msg("createdate").'</th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		// $content = $this->getSearchPaginatePlainView().$content;
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		if(isset($p["list_links"]))
			$f->setVar('links', $p["list_links"], false);
		$f->setVar('content', $content , false);
		return '<div id="'.$p["layer_list"].'" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}

	static function getUserJobsTableView($jobs,$p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($jobs);
		$paginate = $paginate_screen->getPlainView($p);
		
		$content = "";
		foreach($paginate_screen->getCurrentElements() as $job) {
			
			$project = pz_project::get($job->getProjectId());
			$duration = (($job->getDuration()->format("%d")*24*60)+$job->getDuration()->format("%h")).'h ';
			if($job->getDuration()->format("%I") != 0) $duration .= $job->getDuration()->format("%I").'m';
			
			$content .= '<tr>';
			$content .= '<td class="img1"><img src="'.$project->getInlineImage().'" /></td>';
			$content .= '<td>'.$project->getName().'</td>';
			$content .= '<td>'.$job->getTitle().'</td>';
			$content .= '<td>'.$job->getDescription().'</td>';
			$content .= '<td><nobr>'.$duration.'</nobr>&nbsp;</td>';
			$content .= '<td>'.$job->getFrom()->format(rex_i18n::msg("format_d_m_y"))."<br /><nobr>".$job->getFrom()->format(rex_i18n::msg("format_h_i")).'h - '.$job->getTo()->format(rex_i18n::msg("format_h_i")).'</nobr></td>';
			$content .= '<td>'.$job->getCreated()->format(rex_i18n::msg("format_d_m_y")).'</td>';
			$content .= '</tr>';
		}
		$content = $paginate.'
          <table class="projectjobs tbl1">
          <thead><tr>
              <th colspan="2">'.rex_i18n::msg("project").'</th>
              <th>'.rex_i18n::msg("title").'</th>
              <th>'.rex_i18n::msg("description").'</th>
              <th>'.rex_i18n::msg("hours").'</th>
              <th>'.rex_i18n::msg("duration").'</th>
              <th>'.rex_i18n::msg("createdate").'</th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		// $content = $this->getSearchPaginatePlainView().$content;
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		if(isset($p["list_links"]))
			$f->setVar('links', $p["list_links"], false);
		$f->setVar('content', $content , false);
		return '<div id="'.$p["layer_list"].'" class="design2col">'.$f->parse('pz_screen_list').'</div>';
		return $f->parse('pz_screen_list');
	}








	// --------------------------------------------------------------- export views

	static function getExcelExport($jobs) {
		
		$keys = array('project','user','title','description','date','start','end','dur');
	
		$projects = array();
		$users = array();
		$export_jobs = 	array();
		foreach($jobs as $job) {
			$e = array();
			if(!isset($users[$job->getUserId()]))
				$users[$job->getUserId()] = pz_user::get($job->getUserId());
			if(!isset($projects[$job->getProjectId()]))
				$projects[$job->getProjectId()] = pz_project::get($job->getProjectId());
			
			foreach($keys as $k) {
				switch($k) {
					case('project'): $v = $projects[$job->getProjectId()]->getName(); break;
					case('user'): $v = $users[$job->getUserId()]->getName(); break;
					case('project'): $v = "YY"; break;
					case('title'): $v = $job->getTitle(); break;
					case('description'): $v = $job->getDescription(); break;
					case('date'): $v = $job->getFrom()->format(rex_i18n::msg("format_d_m_y")); break;
					case('start'): $v = $job->getFrom()->format(rex_i18n::msg("format_h_i")); break;
					case('end'): $v = $job->getTo()->format(rex_i18n::msg("format_h_i")); break;
					case('dur'): $v = $job->getDuration()->format("%h"); $m = ($job->getDuration()->format("%i")/60); $v +=$m; $v = number_format($v, 2, ',', ''); break;
					default: $v = '';
				}
				$e[$k] = $v;
			}
			$export_jobs[] = $e;
		}
		return pz::array2excel($export_jobs);
		
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
					"mode"=>"search",
					"day"=>$month_2->format("Ymd"),
					"calendar_project_ids" => "___value_ids___"
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
            <h2 class="hl3">'.pz::dateTime2dateFormat($month_1,rex_i18n::msg("format_month")).'</h2>
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
            <h2 class="hl3">'.pz::dateTime2dateFormat($month_2,rex_i18n::msg("format_month")).'</h2>
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
	    	for($j=0;$j<7;$j++)
	    	{
	    		
	    		// $day->modify('-1 day');
	    		// calendar_events_day_list
	    		$month->modify("+1 day");
	    		
	    		if($j == 0)
					$return .= '<td class="calendarweek">'.$month->format("W").'</td>';
	    		
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

	public function getDeleteEventLink($p) {
		$p["linkvars"]["mode"] = "delete_calendar_event";
		$p["linkvars"]["calendar_event_id"] = $this->calendar_event->getId();
		$delete_link = pz::url("screen","calendars",$p["function"],$p["linkvars"]);
		$return = '<div class="xform">
				<p><a class="bt17" onclick="check = confirm(\''.rex_i18n::msg("calendar_event_confirm_delete",htmlspecialchars($this->calendar_event->getTitle())).'\'); if (check == true) pz_loadPage(\'calendar_event_form\',\''.$delete_link.'\')" href="javascript:void(0);">- '.rex_i18n::msg("delete_calendar_event").'</a></p>
				</div>';
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
		
		$return = $header.'<p class="xform-info">'.rex_i18n::msg("delete_calendar_deleted", htmlspecialchars($title)).'</p>';
		$return .= pz_screen::getJSLoadFormPage($p["layer_list"],$p["layer_search"],pz::url('screen','calendars',$p["function"],array("mode"=>'list',"day"=>$this->calendar_event->getFrom()->format("Ymd"))));

		// pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>$from->format("Ymd"))) ).

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
		
		if(rex_request("allday","int") == 1)
		{
			$_REQUEST["from"]["hours"] = "00";
			$_REQUEST["from"]["minutes"] = "00";
			$_REQUEST["to"]["hours"] = 23;
			$_REQUEST["to"]["minutes"] = 45;
		}
		
		$xform->setValueField("pz_datetime_screen",array("from",rex_i18n::msg("calendar_event_from")));
		$xform->setValueField("pz_datetime_screen",array("to",rex_i18n::msg("calendar_event_to")));

		$xform->setValueField("pz_calendar_event_attendees",array("attendees",rex_i18n::msg("calendar_event_attendees")));

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
		
		$xform->setValidateField("compare_fields",array("from","to",">=",rex_i18n::msg("error_calendar_event_fromto_compare")));
		
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
			
				if(isset($data["attendees"]) && is_array($data["attendees"]))
				{
					$attendees = array();
					foreach($data["attendees"] as $a) {
						$at = pz_calendar_attendee::create();
						$at->setUserId($a["user_id"]);
						$at->setEmail($a["email"]);
						$at->setName($a["name"]);
						$at->setStatus($a["status"]); // pz_calendar_attendee::ACCEPTED
						$attendees[] = $at;
					}
					$event->setAttendees($attendees);
					pz_calendar_attendee::saveAll($event);
				}
			
				$return = "";
				$return = $header.'<p class="xform-info">'.rex_i18n::msg("calendar_event_added").'</p>';
				// $return .= pz_screen::getJSUpdateLayer($p["layer_list"],pz::url('screen','calendars',$p["function"],array("mode"=>'list')));
				// $return .= pz_screen::getJSLoadFormPage('calendar_events_day_list','calendar_event_add_form',pz::url('screen','calendars','day',array("mode"=>'list')));

				// $cs = new pz_calendar_event_screen($event);
				// $return .= $cs->getEditForm($p);

				// Add Form geht nicht, da sonst wieder datensaetz erstellt werden
				// $return .= pz_calendar_event_screen::getAddForm($p);

				$return .= '<script>pz_loadPage("'.$p["layer_list"].'","'.
					pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>$from->format("Ymd"))) ).
					'");</script>';

				// return $return;
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





	public function getEditForm($p = array()) 
	{
	
		// TODOS
		// Linksvars in form
	
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
		
		if(rex_request("allday","int") == 1)
		{
			$_REQUEST["from"]["hours"] = "00";
			$_REQUEST["from"]["minutes"] = "00";
			$_REQUEST["to"]["hours"] = 23;
			$_REQUEST["to"]["minutes"] = 45;
		}
		
		$xform->setValueField("pz_datetime_screen",array("from",rex_i18n::msg("calendar_event_from")));
		$xform->setValueField("pz_datetime_screen",array("to",rex_i18n::msg("calendar_event_to")));
		
		$xform->setValueField("pz_calendar_event_attendees",array("attendees",rex_i18n::msg("calendar_event_attendees")));
		
		$xform->setValueField("text",array("url",rex_i18n::msg("calendar_event_url")));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("calendar_event_description")));

		$xform->setValidateField("mysql_datetime",array("from",rex_i18n::msg("error_calendar_from_datetime")));
		$xform->setValidateField("mysql_datetime",array("to",rex_i18n::msg("error_calendar_to_datetime_wrong")));
		
		$xform->setValidateField("pz_project_id",array("project_id",rex_i18n::msg("error_calendar_event_project_id")));
		$xform->setValidateField("empty",array("title",rex_i18n::msg("error_calendar_event_title_empty")));
		
		
		$xform->setValidateField("compare_fields",array("from","to",">=",rex_i18n::msg("error_calendar_event_fromto_compare")));

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
		
			if(isset($data["attendees"]) && is_array($data["attendees"]))
			{
				$attendees = array();
				foreach($data["attendees"] as $a) {
					$at = pz_calendar_attendee::create();
					$at->setUserId($a["user_id"]);
					$at->setEmail($a["email"]);
					$at->setName($a["name"]);
					$at->setStatus($a["status"]); // pz_calendar_attendee::ACCEPTED
					$attendees[] = $at;
				}
				$event->setAttendees($attendees);
				pz_calendar_attendee::saveAll($event);
			}
		
			// array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId())))
		
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("calendar_event_updated").'</p>'.$return;
		
			$return .= '<script>pz_loadPage("'.$p["layer_list"].'","'.
				pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>$from->format("Ymd"))) ).
				'");</script>';
			
		}else
		{
			$return = $header.$return;	
		}
		
		$return .= $this->getDeleteEventLink($p);		
		
		$return = '<div id="calendar_event_form"><div id="calendar_event_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}










}


?>