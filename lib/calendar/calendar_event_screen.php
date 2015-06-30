<?php

class pz_calendar_event_screen{

    public $event,$user,$user_name, $label,$project;

    function __construct($event)
    {
        $this->calendar_event = $event;
        $this->user_name = pz_i18n::msg("username_not_available");

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
        $e = array();
        if(count($events) > 0)
        {
            foreach($events as $event) {
                $a = new pz_calendar_event_screen($event);
                $e[] = $a->getFlyoutEventView($p, true);
            }
        }

        $info = "";
        if(count($e)>0)
            $info = '<span class="info1"><span class="inner">'.count($e).'</span></span>';

        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1"><a href="javascript:void(0);" onclick="$(\'#calendar_event_attendee_list_view\').toggleClass(\'hidden\')"><span class="info-relative">'.pz_i18n::msg("calendar_event_attendees_unanswered").$info.'</span></a></h1>
	          </div>
	        </header>';

        $return = '<div id="calendar_event_attendee_view" class="design1col">'.$header.'<div id="calendar_event_attendee_list_view" class="hidden">'.implode("",$e).'</div></div>';
        return $return;
    }





    // --------------------------------------------------------------- views

    /*
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
              $edit = '<br /><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("edit").'</a>';

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
              $location = '<li>'.pz_i18n::msg('location').': '.htmlspecialchars($this->calendar_event->getLocation()).'</li>';

          $url = '';
          if($this->calendar_event->getUrl() != "")
              $url = '<li><a href="http://'.$this->calendar_event->getUrl().'" target="_blank">http://'.htmlspecialchars($this->calendar_event->getUrl()).'</a></li>';

          $job = '';
          if($this->calendar_event->isBooked() != "")
              $url = '<li>'.pz_i18n::msg('is_job').'</li>';

          $attandees = '';

          $user_emails = pz::getUser()->getEmails();

          $as = pz_calendar_attendee::getAll($this->calendar_event);
          if(is_array($as) && count($as)>0) {

              $attandees .= '<li><h2 class="hl2">'.pz_i18n::msg('calendar_event_attendees').'</h2>';

              $me = null;
              $attandees_list = array();
              foreach($as as $a) {
                  $attandees_list[] = $a->getName().' / '.$a->getEmail().' ['.pz_i18n::msg('calendar_event_attendee_'.strtolower($a->getStatus())).']';
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
                          $attandees .= '<a class="bt3" href="javascript:void(0);">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a>';
                      else
                          $attandees .= '<a class="bt5" href="javascript:void(0);" onclick="'.$link.'">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a>';
                  }
              }

          }

          $return = 	'
                      <div class="flyout event-'.$this->calendar_event->getId().'">
                        <div class="content">
                          <div class="output">
                            <a class="tooltip close bt5" href="javascript:void(0);" onclick="$(this).parent().parent().parent().css(\'display\',\'none\');">'.pz_screen::getTooltipView('<span class="icon"></span>',pz_i18n::msg("close")).'</a>
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
      */






    // --------------------------------------------------------------- views

    public function getTooltipEventView($p = array())
    {
        $from = $this->calendar_event->getFrom();
        $to = $this->calendar_event->getTo();
        $duration = $this->calendar_event->getDuration();

        $info = array();
        if($this->calendar_event->isAllDay())
        {
            $info[] = pz_i18n::msg("allday");
        }else {
            $info[] = pz_i18n::msg('time').': '.$from->format(pz_i18n::msg("format_time")).'h';
        }

        $info[] = pz_i18n::msg('calendar_event_title').': '.htmlspecialchars($this->calendar_event->getTitle());
        $info[] = pz_i18n::msg("user").': '.htmlspecialchars($this->user_name);

        if($this->calendar_event->getLocation() != "")
            $info[] = pz_i18n::msg("location").': '.htmlspecialchars($this->calendar_event->getLocation());

        return implode("<br />",$info);

    }

    public function getFlyoutEventView($p = array(), $disable_functions = false)
    {

        $from = $this->calendar_event->getFrom();
        $to = $this->calendar_event->getTo();
        $duration = $this->calendar_event->getDuration();

        $info = "";
        $resize = '';
        $edit_classes = "";
        $edit = array();

        if(pz::getUser()->getEventEditPerm($this->calendar_event))
        {
            $edit[] = '<li><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\');pz_tooltipbox_close();">'.pz_i18n::msg("edit").'</a></li>';

            $edit[] = '<li><a class="bt17" href="javascript:void(0);" onclick="check = confirm(\''.
                str_replace(array("'","\n","\r"),array("","",""),pz_i18n::msg("calendar_event_confirm_delete",htmlspecialchars($this->calendar_event->getTitle()))).'\'); if (check == true) pz_loadPage(\'.event-flyout-view.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"delete_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("delete").'</a></li>';
        }

        if(!$this->calendar_event->isBooked() && !$this->calendar_event->isAllDay())
        {
            $edit[] = '<li><a class="bt5" href="javascript:pz_loadPage(\'.event-flyout-view.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"copy2job_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("calendar_event_copy2job").'</a></li>';
        }

        if(count($edit) > 0 && !$disable_functions)
        {
            $edit = '<div class="split-h split-h1"></div><ul class="buttons">'.implode($edit).'</ul>';
        }else {
            $edit = "";
        }

        $date_info = '';
        if($this->calendar_event->isAllday())
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = pz::strftime(pz_i18n::msg("show_date_normal"),$from->format("U")); // $from->format("d.m.Y");
            else
                $date_info = pz::strftime(pz_i18n::msg("show_date_normal"),$from->format("U")).' - '.pz::strftime(pz_i18n::msg("show_date_normal"),$to->format("U"));
        else
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = pz::strftime(pz_i18n::msg("show_date_normal"),$from->format("U")).', <span>'.pz::strftime(pz_i18n::msg("show_time_normal"),$from->format("U")).' - '.pz::strftime(pz_i18n::msg("show_time_normal"),$to->format("U")).'</span>';
            else
                $date_info = $from->format("d.m.Y H:i").' - '.$to->format("d.m.Y H:i");

        $date_info = $date_info != '' ? '<h2>'.$date_info.'</h2>' : '';

        if ($this->calendar_event->getDescription() != '')
            $date_info .= '<p>'.nl2br(pz_screen::prepareOutput($this->calendar_event->getDescription())).'</p>';

        $event_infos = array();
        if($this->calendar_event->getLocation() != "")
            $event_infos[] = '<dt>'.pz_i18n::msg('location').':</dt><dd>'.htmlspecialchars($this->calendar_event->getLocation()).'</dd>';

        if($this->calendar_event->getUrl() != "")
        {
            $url = $this->calendar_event->getUrl();
            if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://')
                $url = 'http://'.$url;

            $event_infos[] = '<dt>'.pz_i18n::msg("calendar_event_url").':</dt><dd><a href="'.$url.'" target="_blank">'.htmlspecialchars(pz::cutText($url)).'</a></dd>';
        }

        if($this->calendar_event->isBooked())
        {
            $event_infos[] = '<dt>'.pz_i18n::msg('job').':</dt><dd>'.pz_i18n::msg('is_job').'</dd>';

        }

        $project_name = $this->calendar_event->getProject()->getName();
        if( $this->calendar_event->getProjectSubId() != 0 && ( $project_sub = pz_project_sub::get($this->calendar_event->getProjectSubId()) )  )
            $project_name .= ' / '.$project_sub->getName();

        $event_infos[] = '<dt>'.pz_i18n::msg('project').':</dt><dd><span class="label-color-block '.pz_label_screen::getColorClass($this->calendar_event->getProject()->getLabelId()).'"></span>'.htmlspecialchars($project_name).'</dd>';

        $event_infos[] = '<dt>'.pz_i18n::msg('user').':</dt><dd>'.htmlspecialchars($this->user_name).'</dd>';


        if($this->calendar_event->hasRule())
        {
            $event_infos[] = '<dt>'.pz_i18n::msg('calendar_event_rule').':</dt><dd>'.pz_i18n::msg('calendar_event_has_rule').'</dd>';
            $event_infos[] = '<dt>'.pz_i18n::msg('calendar_event_frequence').':</dt><dd>'.$this->calendar_event->getRule()->getFrequence().'</dd>';
            $event_infos[] = '<dt>'.pz_i18n::msg('calendar_event_interval').':</dt><dd>'.$this->calendar_event->getRule()->getInterval().'</dd>';

        }

        $clips = "";
        $clips_array = $this->calendar_event->getClips();
        if(count($clips_array)>0)
        {
            $show_clips = array();
            foreach($clips_array as $clip)
            {
                $clip_name = $clip->getFilename();
                if($clip->isReleased())
                {
                    $show_clips[] = '<li><a href="'.$clip->getUri().'">'.htmlspecialchars($clip_name).'</a></li>';
                }else
                {
                    if(pz::getUser()->getClipDownloadPerm($clip))
                        $show_clips[] = '<li><a href="'.$clip->getDownloadLink().'">'.htmlspecialchars($clip_name).'</a> ['.pz_i18n::msg('clip_is_not_released').']</li>';
                    else
                        $show_clips[] = '<li>'.htmlspecialchars($clip_name).' ['.pz_i18n::msg('clip_is_not_released').']</li>';
                }
            }
            $clips = '<div class="split-h split-h1"></div><h2>'.pz_i18n::msg('attachments').'</h2><ul>'.implode("",$show_clips).'</ul>';
        }



        $attandees = '';
        $actions = '';

        $user_emails = pz::getUser()->getEmails();

        $as = pz_calendar_attendee::getAll($this->calendar_event);
        if(is_array($as) && count($as)>0)
        {

            $attandees .= '<div class="split-h split-h1"></div><h2>'.pz_i18n::msg('calendar_event_attendees').'</h2>';

            $me = null;
            $attandees_list = '';
            foreach($as as $a)
            {
                $attandees_list .= '<li class="status-'.strtolower($a->getStatus()).'">'.$a->getName().' / '.$a->getEmail().' ['.pz_i18n::msg('calendar_event_attendee_'.strtolower($a->getStatus())).']</li>';

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
                    $link = 'pz_loadPage(\'.event-flyout-view.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"set_attandee_status","calendar_event_id"=>$this->calendar_event->getId(),"attandee_status" => $v))).'\')';

                    if($me->getStatus() == $v)
                        $actions .= '<li><a class="bt3" href="javascript:void(0);">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';
                    else
                        $actions .= '<li><a class="bt5" href="javascript:void(0);" onclick="'.$link.'">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';
                }
                $actions .= '</ul>';
            }

        }

        $info_message = '';
        if(isset($p["info_message"]))
        {
            $info_message = '<p class="xform-info">'.$p["info_message"].'</p>';
        }

        $warning_message = '';
        if(isset($p["warning_message"]))
        {
            $warning_message = '<p class="xform-warning">'.$p["warning_message"].'</p>';
        }


        $return = 	'
					<div class="bucket event-flyout-view event-'.$this->calendar_event->getId().'">
            <div class="content">
              <div class="output">
                <header>
                  <h1 class="hl2">'.$this->calendar_event->getTitle().'</h1>
                </header>

                <div class="formatted">
                  '.$info_message.'
                  '.$warning_message.'
                  '.$date_info.'
                  <div class="split-h split-h1"></div><dl>'.implode("",$event_infos).'</dl>
                  '.$clips.'
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


    // --------------------------------------------------------------- List


    public function getBlockListView($p = array())
    {
        $from = $this->calendar_event->getFrom();
        $to = $this->calendar_event->getTo();
        $duration = $this->calendar_event->getDuration();

        $info = "";
        $resize = '';

        /*
            $edit_classes = "";
            $edit = array();

            if(pz::getUser()->getEventEditPerm($this->calendar_event))
            {
                $edit[] = '<li><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"edit_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("edit").'</a></li>';

                $edit[] = '<li><a class="bt17" href="javascript:void(0);" onclick="check = confirm(\''.
                str_replace(array("'","\n","\r"),array("","",""),pz_i18n::msg("calendar_event_confirm_delete",htmlspecialchars($this->calendar_event->getTitle()))).'\'); if (check == true) pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"delete_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("delete").'</a></li>';

            }

        if(!$this->calendar_event->isBooked() && !$this->calendar_event->isAllDay())
        {
          $edit[] = '<li><a class="bt5" href="javascript:pz_loadPage(\'calendar_event_form\',\''.pz::url("screen","calendars","day",array_merge($p["linkvars"],array("mode"=>"copy2job_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).'\')">'.pz_i18n::msg("calendar_event_copy2job").'</a></li>';
        }

        if(count($edit) > 0)
        {
        }
        $edit = '<div class="split-h split-h1"></div><ul class="buttons">'.implode($edit).'</ul>';
        $edit = "";
        */

        $date_info = '';
        if($this->calendar_event->isAllday())
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = $from->format("d.m.Y");
            else
                $date_info = $from->format("d.m.Y").' - '.$to->format("d.m.Y");
        else
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = $from->format("H:i").' - '.$to->format("H:i");
            else
                $date_info = $from->format("d.m.Y H:i").' - '.$to->format("d.m.Y H:i");



//    $date_info = $date_info != '' ? '<h2>'.$date_info.'</h2>' : '';

        $text = '';

        if ($this->calendar_event->getDescription() != '')
            $text .= '<p>'.nl2br(htmlspecialchars($this->calendar_event->getDescription())).'</p>';
        /*
            $event_infos = '';
                if($this->calendar_event->getLocation() != "")
                    $event_infos .= '<dt>'.pz_i18n::msg('location').':</dt><dd>'.htmlspecialchars($this->calendar_event->getLocation()).'</dd>';

                if($this->calendar_event->getUrl() != "")
                {
                  $url = $this->calendar_event->getUrl();
                  if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://')
                    $url = 'http://'.$url;

                    $event_infos .= '<dt>'.pz_i18n::msg("calendar_event_url").':</dt><dd><a href="'.$url.'" target="_blank">'.htmlspecialchars($url).'</a></dd>';
                }

                if($this->calendar_event->isBooked() != "")
                    $event_infos .= '<dt>'.pz_i18n::msg('job').':</dt><dd>'.pz_i18n::msg('is_job').'</dd>';

                $event_infos .= '<dt>'.pz_i18n::msg('project').':</dt><dd>'.$this->calendar_event->getProject()->getName().'</dd>';

                if($this->calendar_event->hasRule())
                {
                    $event_infos .= '<dt>'.pz_i18n::msg('calendar_event_rule').':</dt><dd>'.pz_i18n::msg('calendar_event_has_rule').'</dd>';
                    $event_infos .= '<dt>'.pz_i18n::msg('calendar_event_frequence').':</dt><dd>'.$this->calendar_event->getRule()->getFrequence().'</dd>';
                    $event_infos .= '<dt>'.pz_i18n::msg('calendar_event_interval').':</dt><dd>'.$this->calendar_event->getRule()->getInterval().'</dd>';

                }

                $formatted .= $event_infos != '' ? '<div class="split-h split-h1"></div><dl>'.$event_infos.'</dl>' : '';

                $attandees = '';

                $user_emails = pz::getUser()->getEmails();

                $as = pz_calendar_attendee::getAll($this->calendar_event);
                if(is_array($as) && count($as)>0)
                {

                    $attandees .= '<div class="split-h split-h1"></div><h2>'.pz_i18n::msg('calendar_event_attendees').'</h2>';

                    $me = null;
                    $attandees_list = '';
                    foreach($as as $a)
                    {
                        $attandees_list .= '<li class="status-'.strtolower($a->getStatus()).'">'.$a->getName().' / '.$a->getEmail().' ['.pz_i18n::msg('calendar_event_attendee_'.strtolower($a->getStatus())).']</li>';

                        if(in_array($a->getEmail(),$user_emails))
                        {
                            $me = $a;
                        }
                    }

                    if ($attandees_list != '')
                      $attandees .= '<ul>'.$attandees_list.'</ul>';


                  $actions = '';
                    if($me)
                    {
                        $actions .= '<div class="split-h split-h1"></div><ul class="buttons">';
                        foreach(pz_calendar_attendee::getStatusArray() as $k => $v)
                        {
                            $link = 'pz_loadPage(\'.event-'.$this->calendar_event->getId().'\',\''.pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"set_attandee_status","calendar_event_id"=>$this->calendar_event->getId(),"attandee_status" => $v))).'\')';


                            if($me->getStatus() == $v)
                                $actions .= '<li><a class="bt3" href="javascript:void(0);">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';
                            else
                                $actions .= '<li><a class="bt5" href="javascript:void(0);" onclick="'.$link.'">'.pz_i18n::msg('calendar_event_attendee_'.strtolower($v)).'</a></li>';
                        }
                        $actions .= '</ul>';
                    }

                }
                $formatted .= $attandees;
        */
        $image_from_address = $this->calendar_event->getUser()->getInlineImage();
        $image_from_adresse_title = $this->calendar_event->getUser()->getName();



        $classes = array();
        $classes[] = 'event';
        $classes[] = 'label';
        $classes[] = pz_label_screen::getBorderColorClass($this->label_id); // background color
        $classes[] = pz_label_screen::getColorClass($this->label_id); // border color


        $return = '
		    <article class="'.implode(" ",$classes).'" id="event-'.$this->calendar_event->getId().'">
		      <div class="event-info labelb'.$this->label_id.'">
           <header>
             <figure class="figure-from">'.pz_screen::getTooltipView('<img src="'.$image_from_address.'" width="40" height="40" />', htmlspecialchars($image_from_adresse_title)).'</figure>
             <hgroup class="scope one">
               <h1 class="hl7">'.$date_info.'<span class="title">'.$this->calendar_event->getTitle().'</span><br /><span class="name">'.$this->user_name.'</span></h1>
             </hgroup>
           </header>
           <section class="content scope one">
            '.$text.'
           </section>
          </div>
	     </article>';

        /*
                   <footer>
                      <a class="bt5" href="#">editieren</a>
                    </footer>
        */
        return $return;

    }


    public function getBlockView($p = array())
    {
        if ($this->calendar_event->isBooked()) {
            $event_screen = new pz_calendar_event_screen($this->calendar_event);
            return $event_screen->getJobBlockView($p);
        }


        $from = $this->calendar_event->getFrom();
        $to = $this->calendar_event->getTo();
        $duration = $this->calendar_event->getDuration();

        $info = "";
        $resize = '';

        $date_info = '';
        if($this->calendar_event->isAllday())
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = $from->format("d.m.Y");
            else
                $date_info = $from->format("d.m.Y").' - '.$to->format("d.m.Y");
        else
            if($from->format("Ymd") == $to->format("Ymd"))
                $date_info = $from->format("H:i").' - '.$to->format("H:i");
            else
                $date_info = $from->format("d.m.Y H:i").' - '.$to->format("d.m.Y H:i");



        $text = '';

        if ($this->calendar_event->getDescription() != '')
            $text .= '<p>'.nl2br(htmlspecialchars($this->calendar_event->getDescription())).'</p>';

        $image_from_address = $this->calendar_event->getUser()->getInlineImage();
        $image_from_adresse_title = $this->calendar_event->getUser()->getName();



        $classes = array();
        $classes[] = 'event';
        $classes[] = 'block';
        $classes[] = 'image';
        $classes[] = 'label';
        $classes[] = pz_label_screen::getBorderColorClass($this->label_id); // background color
        $classes[] = pz_label_screen::getColorClass($this->label_id); // border color


        $return = '
      <article class="'.implode(" ",$classes).'" id="event-'.$this->calendar_event->getId().'">
        <div class="event-info labelb'.$this->label_id.'">
          <header>
            <figure>
              '.pz_screen::getTooltipView('<img src="'.$image_from_address.'" width="40" height="40" />', htmlspecialchars($image_from_adresse_title)).'
            </figure>
            <hgroup class="data">
              <h2 class="hl7">
                <span class="piped">
                  <span>'.$date_info.'</span>
                  <span class="title">'.$this->calendar_event->getTitle().'</span>
                </span>
                <span class="name">'.$this->user_name.'</span>
             </h2>
            </hgroup>
          </header>
          <section class="content">
          '.$text.'
          </section>
        </div>
      </article>';


        return $return;

    }




    public function getJobBlockView($p = array())
    {

        $user = $this->calendar_event->getUser();
        $project = pz_project::get($this->calendar_event->getProjectId());
        $duration = (($this->calendar_event->getDuration()->format("%d")*24)+$this->calendar_event->getDuration()->format("%h")).'h ';
        if($this->calendar_event->getDuration()->format("%I") != 0) $duration .= $this->calendar_event->getDuration()->format("%I").'m';

        $dur = $this->calendar_event->getFrom()->format(pz_i18n::msg("format_d_m_y"))."<br /><nobr>".$this->calendar_event->getFrom()->format(pz_i18n::msg("format_h_i")).'h - '.$this->calendar_event->getTo()->format(pz_i18n::msg("format_h_i")).'</nobr>';

        $dur2 = $this->calendar_event->getCreated()->format(pz_i18n::msg("format_d_m_y"));

        /*
                  <th colspan="2">'.pz_i18n::msg("project").'</th>
                  <th>'.pz_i18n::msg("title").'</th>
                  <th>'.pz_i18n::msg("description").'</th>
                  <th>'.pz_i18n::msg("hours").'</th>
                  <th>'.pz_i18n::msg("duration").'</th>
                  <th>'.pz_i18n::msg("createdate").'</th>
        */

        $description = $this->calendar_event->getDescription();
        // $description = 'Kohle die Wasser reinigt, aufwendige japanische Lederkunst, außergewöhnliche Wohn-Accessoires und die Verbindung aus Holz und Glas';
        $description = $description != '' ? '<br /><br /><h4 class="hl4">'.pz_i18n::msg("description").'</h4>'.$description : '';

        $return = '
      		<article class="job block image label">
            <header>

              <figure>
                <img src="'.$this->calendar_event->getProject()->getInlineImage().'" />
              </figure>
              <figure>
                <img src="'.$this->calendar_event->getUser()->getInlineImage().'" />
              </figure>

              <section class="data">
                <div class="grid2col">
                  <div class="column first">
                    <span class="name">'.$this->calendar_event->getProject()->getName().'</span><br />
                    <span class="title">'.$this->calendar_event->getTitle().'</span>
                    '.$description.'
                  </div>
                  <div class="column last">
                    <dl class="facts">
                      <dt>'.pz_i18n::msg("hours").'</dt>
                      <dd>'.$duration.'</dd>
                      <dt>'.pz_i18n::msg("duration").'</dt>
                      <dd>'.$dur.'</dd>
                      <dt>'.pz_i18n::msg("createdate").'</dt>
                      <dd>'.$dur2.'</dd>
                    </dl>
                  </div>
                </div>
              </section>

            </header>

            <section class="content scope one">

            </section>

            <footer>
              <span class="label labelc'.$this->calendar_event->getProject()->getLabelId('label_id').'">Label</span>
            </footer>
          </article>';
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
            foreach($events as $event)
            {
                $e = new pz_calendar_event_screen($event);
                if ($e->calendar_event->isAllDay()) {
                    $content_allday .= $e->getDayEventAlldayView($p);
                }else {
                    $content .= $e->getEventView($day, $p);
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

        $link_event_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"),"booked"=>0)))."')";
        $link_job_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"),"booked"=>1)))."')";

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
            pz::strftime(pz_i18n::msg("show_date_long"), $day->getTimestamp()).
            ' <span class="info">('.pz_i18n::msg("calendarweek").' '.pz::dateTime2dateFormat($day,"W").')</span></h1>
	          </div>
	        </header>';

        $return .= '
		  <div class="calendar view-day clearfix">
		    <header class="header">
		      <div class="grid3col">
				      <div class="column first">
				        <a class="bt2" href="'.$link_event_add.'">'.pz_i18n::msg("new_event").'</a>
				        <a class="bt2" href="'.$link_job_add.'">'.pz_i18n::msg("new_job").'</a>
				      </div>

				      <div class="column last">
                <ul class="pagination">
                  <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
                  <li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
                  <li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
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
		pz_set_calendarday_init();
		pz_set_calendar_addform_date("'.pz::dateTime2dateFormat($day,"Y-m-d").'");
		--></script>';

        return '<div class="design2col" id="calendar_events_day_list" data-list-type="calendar" data-url="'.$link_refresh.'">'.$return.'</div>';
    }

    public function getEventView($day, $p = array())
    {

        $from = pz::getUser()->getDateTime($this->calendar_event->getFrom());
        $to = pz::getUser()->getDateTime($this->calendar_event->getTo());
        $duration = $this->calendar_event->getDuration();
        $duration_in_minutes = ($duration->format("%h")*60)+$duration->format("%i");
        $attandees = $this->calendar_event->getAttendees();

        $job = 0;
        if($this->calendar_event->isJob()) {
            $job = 1;
        }

        $classes = array();
        $classes[] = 'event';
        $classes[] = 'event-'.$this->calendar_event->getId();
        $classes[] = 'label';
        // $classes[] = pz_label_screen::getBorderColorClass($this->label_id); // background color
        // $classes[] = pz_label_screen::getColorClass($this->label_id); // border color

        $me = false;
        if(pz::getUser()->getId() == $this->calendar_event->getUserId())
            $me = true;

        if($this->calendar_event->isRuleEvent()) {
            $editable = '<span class="not-editable">'.pz_i18n::msg("not_editable").'</span>';
            $classes[] = "event-rule";

        } else if($me) {
            $editable = '<span class="editable">'.pz_i18n::msg("editable").'</span>';
            if ($from->format("Ymd") == $to->format("Ymd")) {
                $classes[] = "dragable";
                $classes[] = "resizeable";
            }

        }else {
            $editable = '<span class="not-editable">'.pz_i18n::msg("not_editable").'</span>';

        }

        $attachments = '<span class="has-not-attachments">'.pz_i18n::msg("has_not_attachments").'</span>';
        if($this->calendar_event->hasClips()) {
            $attachments = '<span class="has-attachments">'.pz_i18n::msg("has_attachments").'</span>';
        }

        // - Termin ohne Einladungen -> für mich sichbar
        // - Termin mit Einladungen, auch für mich -> sichtbar und markiert
        // - Termin mit Einladungen, nicht für mich -> nicht sichtbar
        // - Termin mit Einladungen, auch für mich, von mir bestaetigt ->

        // - Wiederholungstermine markieren
        // - Alarmtermine markieren

        // einladung mit schraegen flaechen

        if(count($attandees) > 0)
            $classes[] = 'event_attandees';

        if(!$me && count($attandees) > 0) {
            $for_me = false;
            $user_emails = pz::getUser()->getEmails();
            foreach($attandees as $a)
            {
                if(in_array($a->getEmail(),$user_emails)) {
                    $for_me = true;
                    $classes[] = 'event_attandees_for_me';
                }
            }
            if(!$for_me)
                return '';
        }

        $info = array();
        $info[] = '<span class="time">'.pz::strftime(pz_i18n::msg("show_time_normal"),$from->format("U"))
            .' - '.pz::strftime(pz_i18n::msg("show_time_normal"),$to->format("U"))
            .'</span>';
        $info[] = $this->calendar_event->getTitle();
        $info[] = $this->user_name;

        $minute_start = $from->format("i");
        if($minute_start > 44) $minute_start = 45;
        else if($minute_start > 29) $minute_start = 30;
        else if($minute_start > 14) $minute_start = 15;
        else if($minute_start >= 0) $minute_start = 0;

        $data_attr = array();
        $data_attr[] = 'data-event-id="'.$this->calendar_event->getId().'"';
        $data_attr[] = 'data-event-date-start="'.$from->format("Ymd").'"';
        $data_attr[] = 'data-event-day-start="'.$from->format("d").'"';
        $data_attr[] = 'data-event-hour-start="'.$from->format("G").'"';
        $data_attr[] = 'data-event-minute-start="'.$from->format("i").'"';
        $data_attr[] = 'data-event-minute-duration="'.$duration_in_minutes.'"';
        $data_attr[] = 'data-event-day-end="'.$to->format("d").'"';
        $data_attr[] = 'data-event-hour-end="'.$to->format("G").'"';
        $data_attr[] = 'data-event-minute-end="'.$to->format("i").'"';
        $data_attr[] = 'data-event-date-end="'.$to->format("Ymd").'"';
        $data_attr[] = 'data-event-job="'.$job.'"';
        $data_attr[] = 'data-event-project_id="'.$this->calendar_event->getProject()->getId().'"';
        $data_attr[] = 'data-event-user_id="'.$this->calendar_event->getUserId().'"';
        $data_attr[] = 'data-event-attandees="'.count($attandees).'"';

        if($this->calendar_event->isAllDay()) {
            $data_attr[] = 'data-event-isallday="1"';
        } else {
            $data_attr[] = 'data-event-isallday="0"';
        }

        $url = pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId())));
        $flyout_link = "pz_tooltipbox(this, '".$url."')";

        $return = '
		    <article class="'.implode(" ",$classes).'" '.implode(" ",$data_attr).' id="event-'.$this->calendar_event->getId().'">
		      <div class="event-info labelb'.$this->label_id.' labelc'.$this->label_id.'">
           <header>
             <hgroup>
               <h2 class="hl7"><a href="javascript:void(0);" onclick="'.$flyout_link.'">'.$editable.$attachments.implode(" ",$info).'</span></a></h2>
             </hgroup>
           </header>
           <section class="content">
             <p>'.$this->calendar_event->getDescription().'</p>
           </section>
          </div>
	     </article>';
        return $return;
    }

    public function getDayEventAlldayView($p = array())
    {
        /*
          $flyout_link = 'pz_loadPage(\'calendar_event_view\',\''.
          pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId()))).
          '\')';
          */

        $url = pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId())));
        $flyout_link = "pz_tooltipbox(this, '".$url."')";

        $me = false;
        if(pz::getUser()->getId() == $this->calendar_event->getUserId())
            $me = true;

        $editable = '<span class="not-editable">'.pz_i18n::msg("not_editable").'</span>';
        if($me)
            $editable = '<span class="editable">'.pz_i18n::msg("editable").'</span>';

        $attachments = '<span class="has-not-attachments">'.pz_i18n::msg("has_not_attachments").'</span>';
        if($this->calendar_event->hasClips())
            $attachments = '<span class="has-attachments">'.pz_i18n::msg("has_attachments").'</span>';


        $info = array();
        $info[] = $this->calendar_event->getTitle();
        $info[] = $this->user_name;

        $return = '<li class="entry"><a href="javascript:void(0);" onclick="'.$flyout_link.'"><span class="label '.pz_label_screen::getColorClass($this->label_id).'">'.$editable.$attachments.implode(" | ",$info).'</span></a></li>';
        return $return;
    }






    // --------------------------------------------------------------- Week

    static function getWeekListView($events = array(), $p = array(), $day)
    {

        $days = 7;

        $day_clone = clone $day;
        $day_last = clone $day;
        $day_last->modify("+".($days-1)." days");

        $content = "";
        if(count($events) > 0) {
            foreach($events as $event) {
                $e = new pz_calendar_event_screen($event);
                $content .= $e->getEventView($day, $p);
            }
        }

        $return = "";
        $grid = '';
        $grid_title = '';
        for($d=0; $d < $days; $d++) {

            $day_clone = clone $day;
            $day_clone->modify('+'.$d.' days');

            if($d == 0) {
                $grid_title .= '<li class="weekday first">&nbsp;</li>';

                $grid .= '<li class="weekday first">'; // erste Spalte immer ein first
                $grid .= '<dl>';
                $grid .= '<dd class="box">';
                $grid .= '<ul class="hours clearfix">';
                for ($j = 0; $j < 24; $j++) { $grid .= '<li class="hour title">'.$j.':00</li>'; }
                $grid .= '</ul>';
                $grid .= '</dd></dl></li>';

            }

            $class = array();
            if($day_clone->format("N") == 6 or $day_clone->format("N") == 7) { $class[] = "weekday"; $class[] = "weekend";
            }else { $class[] = "weekday"; }
            if($day_clone->format("Ymd") == date("Ymd")) { $class[] = "active"; }
            if($d == $days) { $class[] = "last"; }

            $grid .= '<li class="'.implode(" ",$class).'" data-grid="day" data-day="'.$day_clone->format("d").'" data-month="'.$day_clone->format("m").'" data-year="'.$day_clone->format("Y").'" data-date="'.$day_clone->format("Ymd").'">';
            $grid .= '<dl><dd class="box">';
            // Stundenliste
            $grid .= '<ul class="hours clearfix">';
            for ($j = 0; $j < 24; $j++)	{
                $grid .= '<li class="hour box"></li>';
            }
            $grid .= '</ul>';
            $grid .= '</dd></dl></li>';

            $grid_title .= '<li class="'.implode(" ",$class).'" data-grid="allday" data-day="'.$day_clone->format("d").'" data-month="'.$day_clone->format("m").'" data-year="'.$day_clone->format("Y").'" data-date="'.$day_clone->format("Ymd").'"><dl>';
            $grid_title .= '<dt class="title">'.pz::strftime(pz_i18n::msg("show_day_short2"),$day_clone->getTimestamp()).'</dt>';
            $grid_title .= '</dl></li>';

        }

        /*
        Berechnung Terminposition
        1 Minute = 1px
        0 Punkt  = 7:00 Uhr

        top     = Startzeit (in Minuten) - 7 Stunden (420 Minuten)
        height  = Endzeit - Startzeit (in Minuten)
        */

        $link_refresh = pz::url("screen","calendars","week",
            array_merge(
                $p["linkvars"],
                array(
                    "mode"=>"list",
                    "day"=>$day->format("Ymd"),
                    "calendar_project_ids" => "___value_ids___"
                )
            )
        );

        $link_event_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"),"booked"=>0)))."')";
        $link_job_add = "javascript:pz_loadPage('calendar_event_form','".pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$day->format("Ymd"),"booked"=>1)))."')";

        $return = '
      <header>
        <div class="header">
          <h1 class="hl1">'.
            pz::strftime(pz_i18n::msg("show_date_normal"), $day->getTimestamp()).' - '.
            pz::strftime(pz_i18n::msg("show_date_normal"), $day_last->getTimestamp()).
            ' <span class="info">('.pz_i18n::msg("calendarweek").' '.pz::dateTime2dateFormat($day,"W").')</span></h1>
        </div>
      </header>';

        $day->modify('-'.($days).' day');
        $link_previous = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
        $day->modify('+'.($days+$days).' day');
        $link_next = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$day->format("Ymd"))))."')";
        $day->modify('-'.($days-1).' day');

        $today = new DateTime();
        $link_today = "javascript:pz_loadPage('calendar_events_week_list','".pz::url("screen","calendars","week",array_merge($p["linkvars"],array("mode"=>"list","day"=>$today->format("Ymd"))))."')";


        $return .= '

		<div class="calendar view-week clearfix" data-days="'.$days.'">

			<header class="header">
			<div class="grid3col">
				<div class="column first">
				        <!-- <a href="javascript:void(0);" onclick="pz_toggleSection();" class="toggle bt5"><span class="icon">'.pz_i18n::msg("calendar_resize").'</span></a> -->
				        <a class="bt2" href="'.$link_event_add.'">'.pz_i18n::msg("new_event").'</a>
				        <a class="bt2" href="'.$link_job_add.'">'.pz_i18n::msg("new_job").'</a>
				      </div>
				<div class="column last">
				<ul class="pagination">
				<li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
				<li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
				<li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
				</ul>
				</div>
			</div>
			</header>

      <div class="header grid clearfix">
        <ul class="weekdays">
  			'.$grid_title.'
  			</ul>
  			<div class="allday"></div>
      </div>

			<div class="wrapper">

				<div class="grid clearfix calendargrid">
				<ul class="weekdays">
				'.$grid.'
				</ul>
				</div>

				<div class="events clearfix">
				'.$content.'
				</div>

			</div>
		</div>
		';

        // <div class="timeline" style="top: '.$timeline.'px;"><span class="icon"></span><span class="line"></span></div>

        $return .= '<script language="Javascript"><!--
    // $(".calendar.view-week").pz_cal_week({ days: '.$days.' })
    pz_set_calendarweek_init();
		--></script>';

        return '<div class="design2col" id="calendar_events_week_list" data-list-type="calendar" data-url="'.$link_refresh.'">'.$return.'</div>';

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
				        <a class="bt2" href="#">'.pz_i18n::msg("new_entry").'</a>
				      </div>
				      <div class="column">
				        <p class="info">ff'.$month->format(pz_i18n::msg("format_month_y")).'(KW 22-27)</p>
				      </div>
				      <div class="column last">
                <ul class="pagination">
                  <li class="first prev"><a class="page prev bt5" href=""><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
                  <li class="next"><a class="page next bt5" href=""><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
                  <li class="last"><a class="bt5" href=""><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
                </ul>
				      </div>
		      </div>
		    </header>
		    <div class="header clearfix">
		      <ul class="titles">
				      <li class="day title">'.pz_i18n::msg("monday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("tuesday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("wednesday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("thursday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("friday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("saturday_short").'</li>
				      <li class="day title">'.pz_i18n::msg("sunday_short").'</li>
				    </ul>
		    </div>
		    <div class="boxes clearfix">
				    '.$return.'
				  </div>
		  </div>


		  ';

        $f = new pz_fragment();
        $f->setVar('design', $design, false);
        $f->setVar('title', $p["title"], false);
        $f->setVar('content', $return , false);
        $f->setVar('paginate', '', false);

        return $f->parse('pz_screen_list.tpl');

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
                          <a class="tooltip close bt5" href="">'.pz_screen::getTooltipView('<span class="icon"></span>',pz_i18n::msg("close")).'</a>
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
              <a class="tooltip close bt2" href="">'.pz_screen::getTooltipView('<span class="icon"></span>',pz_i18n::msg("close")).'</a>
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
                            <a class="tooltip close bt5" href="">'.pz_screen::getTooltipView('<span class="icon"></span>',pz_i18n::msg("close")).'</a>

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




    // ---------------------------------------------------------- project events

    static function getOrderedListView($events, $p)
    {

        // $paginate_screen = new pz_paginate_screen($events);
        // $paginate = $paginate_screen->getPlainView($p);
        // foreach($paginate_screen->getCurrentElements() as $event) {

        if (count($events) > 0)	{
            $events_per_day = array();
            foreach ($events as $event) {
                $event_view = new pz_calendar_event_screen($event);
                $day = $event->getFrom()->format("Ymd");
                if (!isset($events_per_day[$day])) {
                    $events_per_day[$day] = array();
                }
                $events_per_day[$day][] = $event_view->getBlockListView($p);
            }

            ksort($events_per_day);

            end($events_per_day);
            $end = key($events_per_day);
            $end = DateTime::createFromFormat("Ymd",key($events_per_day));

            $today = new DateTime();
            $days = $today->diff($end)->format("%a");

            $content = '<div class="calendar view-project-stream clearfix">
                    <div class="wrapper">
                      <div class="grid clearfix">
                        <dl class="days">';
            for($day=0;$day<=$days;$day++) {
                $weekend = '';
                if ($today->format("N") > 5) {
                    $weekend = ' weekend';
                }
                $content .= '<dt class="day title'.$weekend.'">'.strftime(pz_i18n::msg("show_day_short2"),$today->format("U")).'</dt><dd class="day box'.$weekend.'">';

                if(isset($events_per_day[$today->format("Ymd")])) {
                    foreach($events_per_day[$today->format("Ymd")] as $event) {
                        $content .= $event;
                    }
                }

                $content .= '</dd>';
                $today->modify('+1 day');

            }

            $content .= '
      </dl>
      </div>
      </div>
      </div>';

            // calculates height ob day
            $content .= '
      <script type="text/javascript">
        $(document).ready(function()
        {
          $(".days dd.weekend").each(function(index) {
            var $height = $(this).outerHeight();
            $(this).prev().height($height);
          });
        });
      </script>';

        } else {
            $content = "";
        }

        // $paginate.

        $f = new pz_fragment();
        $f->setVar('title', $p["title"], false);
        $f->setVar('content', $content , false);

        $link_refresh = "";

        return '<div class="design2col" id="calendar_events_ordered_list" data-list-type="calendar" data-url="'.$link_refresh.'">'.$f->parse('pz_screen_list.tpl').'</div>';

    }





















    // ---------------------------------------------------------- table views

    static function getProjectJobsTableView($jobs,$p = array())
    {

        $paginate_screen = new pz_paginate_screen($jobs);
        $paginate = $paginate_screen->getPlainView($p);

        $list = "";
        foreach($paginate_screen->getCurrentElements() as $job) {

            $user = $job->getUser();
            $duration = (($job->getDuration()->format("%d")*24)+$job->getDuration()->format("%h")).'h ';
            if($job->getDuration()->format("%I") != 0) $duration .= $job->getDuration()->format("%I").'m';

            $list .= '<tr>
			              <td class="img1"><img src="'.$user->getInlineImage().'" /></td>
			              <td>'.$user->getName().'</td>
			              <td>'.$job->getTitle().'</td>
			              <td>'.$job->getDescription().'</td>
			              <td><nobr>'.$duration.'</nobr>&nbsp;</td>
			              <td>'.$job->getFrom()->format(pz_i18n::msg("format_d_m_y"))."<br /><nobr>".$job->getFrom()->format(pz_i18n::msg("format_h_i")).'h - '.$job->getTo()->format(pz_i18n::msg("format_h_i")).'</nobr></td>
			              <td>'.$job->getCreated()->format(pz_i18n::msg("format_d_m_y")).'</td>
			            </tr>';
        }


        $paginate_loader = $paginate_screen->setPaginateLoader($p, '#projectjobs_list');

        if($paginate_screen->isScrollPage()) {
            $content = '
        <table class="projectjobs tbl1">
        <tbody>
          '.$list.'
        </tbody>
        </table>'.$paginate_loader;
            return $content;
        }

        $content = $paginate.'
          <table class="projectjobs tbl1">
          <thead><tr>
              <th colspan="2">'.pz_i18n::msg("user").'</th>
              <th>'.pz_i18n::msg("title").'</th>
              <th>'.pz_i18n::msg("description").'</th>
              <th>'.pz_i18n::msg("hours").'</th>
              <th>'.pz_i18n::msg("duration").'</th>
              <th>'.pz_i18n::msg("createdate").'</th>
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>'.$paginate_loader;

        $link_refresh = pz::url("screen",$p["controll"],$p["function"],
            array_merge(
                $p["linkvars"],
                array(
                    "mode"=>"list"
                )
            )
        );

        $f = new pz_fragment();
        $f->setVar('title', $p["title"], false);
        if(isset($p["list_links"]))
            $f->setVar('links', $p["list_links"], false);
        $f->setVar('content', $content , false);
        return '<div id="'.$p["layer"].'" class="design2col" data-url="'.$link_refresh.'">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    static function getUserJobsTableView($jobs,$p = array())
    {

        $paginate_screen = new pz_paginate_screen($jobs);
        $paginate = $paginate_screen->getPlainView($p);

        $list = "";
        foreach($paginate_screen->getCurrentElements() as $job) {

            $project = pz_project::get($job->getProjectId());

            $duration = (($job->getDuration()->format("%d")*24)+$job->getDuration()->format("%h")).'h ';
            if($job->getDuration()->format("%I") != 0) $duration .= $job->getDuration()->format("%I").'m';
            
            $list .= '<tr>
			            <td class="img1"><img src="'.$project->getInlineImage().'" /></td>
			            <td>'.$project->getName().'</td>
			            <td><a class="title" href="javascript:pz_loadPage(\'calendar_event_form\',\'/screen/calendars/event/?mode=edit_calendar_event&calendar_event_id='.$job->getId().'\');pz_tooltipbox_close();">'.$job->getTitle().'</a></td>
			            <td>'.$job->getDescription().'</td>
			            <td><nobr>'.$duration.'</nobr>&nbsp;</td>
			            <td>'.$job->getFrom()->format(pz_i18n::msg("format_d_m_y"))."<br /><nobr>".$job->getFrom()->format(pz_i18n::msg("format_h_i")).'h - '.$job->getTo()->format(pz_i18n::msg("format_h_i")).'</nobr></td>
			            <td>'.$job->getCreated()->format(pz_i18n::msg("format_d_m_y")).'</td>
			          </tr>';
        }

        $paginate_loader = $paginate_screen->setPaginateLoader($p, '#'.$p["layer_list"]);

        if($paginate_screen->isScrollPage()) {
            $content = '
        <table class="userjobs tbl1">
        <tbody>
          '.$list.'
        </tbody>
        </table>'.$paginate_loader;
            return $content;
        }

        $content = $paginate.'
          <table class="userjobs tbl1">
          <thead><tr>
              <th colspan="2">'.pz_i18n::msg("project").'</th>
              <th>'.pz_i18n::msg("title").'</th>
              <th>'.pz_i18n::msg("description").'</th>
              <th>'.pz_i18n::msg("hours").'</th>
              <th>'.pz_i18n::msg("duration").'</th>
              <th>'.pz_i18n::msg("createdate").'</th>
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>'.$paginate_loader;

        $f = new pz_fragment();
        $f->setVar('title', $p["title"], false);
        if(isset($p["list_links"]))
            $f->setVar('links', $p["list_links"], false);
        $f->setVar('content', $content , false);
        return '<div id="'.$p["layer_list"].'" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
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
                    case('date'): $v = $job->getFrom()->format(pz_i18n::msg("format_d_m_y")); break;
                    case('start'): $v = $job->getFrom()->format(pz_i18n::msg("format_h_i")); break;
                    case('end'): $v = $job->getTo()->format(pz_i18n::msg("format_h_i")); break;
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

        if (!isset($p["headline"])) {
            $p["headline"] = pz_i18n::msg("calendar_day_list");
        }

        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.$p["headline"].'</h1>
	          </div>
	        </header>';

        $month_1 = clone $day;
        $month_2 = clone $day;

        $month_2->modify("first day of this month");

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
            <h2 class="hl3">'.pz_i18n::msg(strtolower($month_1->format("F")))." ".$month_1->format("Y").'</h2>
            <ul class="pagination">
              <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
            </ul>
          </header>

          <section class="content">
            '.pz_calendar_event_screen::getMonthCalendar($events, $p, $day, $month_1).'
          </section>
        </div>
        <div class="column last">
          <header>
            <h2 class="hl3">'.pz_i18n::msg(strtolower($month_2->format("F")))." ".$month_2->format("Y").'</h2>
            <ul class="pagination">
              <li class="last next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
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
            if(is_object($event)) {
                if($from = @$event->getFrom()) {
                    $available_events[$from->format("Ymd")] = $from->format("Ymd");
                }
            }
        }

        $return .= '
	        <table class="overview">
	          <colgroup span="8"></colgroup>
	          <thead>
	          <tr>
	            <th title="'.pz_i18n::msg("calendarweek").'">'.pz_i18n::msg("calendarweek_short").'</th>
	            <th title="'.pz_i18n::msg("monday").'">'.pz_i18n::msg("monday_short").'</th>
	            <th title="'.pz_i18n::msg("tuesday").'">'.pz_i18n::msg("tuesday_short").'</th>
	            <th title="'.pz_i18n::msg("wednesday").'">'.pz_i18n::msg("wednesday_short").'</th>
	            <th title="'.pz_i18n::msg("thursday").'">'.pz_i18n::msg("thursday_short").'</th>
	            <th title="'.pz_i18n::msg("friday").'">'.pz_i18n::msg("friday_short").'</th>
	            <th title="'.pz_i18n::msg("saturday").'">'.pz_i18n::msg("saturday_short").'</th>
	            <th title="'.pz_i18n::msg("sunday").'">'.pz_i18n::msg("sunday_short").'</th>
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
                //if($month->format("Ymd") == $day->format("Ymd")) $classes[] = "selected";

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


    // ------------------ Customerplan

    public function getCustomerplanEventView($p, $view_start, $view_end, $view_current)
    {
        // echo 'drin';

        $url = pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"get_flyout_calendar_event","calendar_event_id"=>$this->calendar_event->getId())));
        $flyout_link = 'pz_tooltipbox(this,\''.$url.'\');';

        $classes = array();
        $classes[] = "event-info";
        $classes[] = "label";
        $classes[] = 'event-'.$this->calendar_event->getId();
        $classes[] = pz_label_screen::getColorClass($this->calendar_event->getProject()->getLabelId());
        $classes[] = pz_label_screen::getBorderColorClass($this->calendar_event->getProject()->getLabelId());

        $event_diff = $view_start->diff($this->calendar_event->getFrom());
        $event_diff_days = $event_diff->format("%r%a");
        $position_width = $this->calendar_event->getDuration()->format("%d") + 1; // $event_diff->format("%a");

        if($event_diff_days < 0)
        {
            $position_width = $position_width + $event_diff_days;
            // longing events which started earlier than grid, no drag
            $event_diff_days = 0;
            $classes[] = 'not-draggable';

        }else
        {
            if(pz::getUser()->getEventEditPerm($this->calendar_event))
                $classes[] = 'draggable';
            else
                $classes[] = 'not-draggable';
        }

        $attachments = '<span class="has-not-attachments">'.pz_i18n::msg("has_not_attachments").'</span>';
        if($this->calendar_event->hasClips())
            $attachments = '<span class="has-attachments">'.pz_i18n::msg("has_attachments").'</span>';

        $event_content = htmlspecialchars($this->calendar_event->getTitle());
        $event_content = '<a href="javascript:void(0);" onclick="'.$flyout_link.'">'.$attachments.$event_content.'</a>';

        // $event_view = new pz_calendar_event_screen($event);
        // $event_content = pz_screen::getTooltipView($event_content,$event_view->getTooltipEventView($p));
        $return = '<div id="customer-event-id-'.$this->calendar_event->getId().'" class="'.implode(" ",$classes).'" data-event-position-top="0" data-event-position-left="'.$event_diff_days.'" data-event-day="'.$view_current->format("d").'" data-event-position-width="'.$position_width.'" data-event-days="'.($this->calendar_event->getDuration()->format("%d")+1).'" data-event-id="'.$this->calendar_event->getId().'">'.$event_content.'</div>';

        return $return;
    }




    public static function getCustomerplanListView($customer, $customers, $projects, $events, $p, $start, $end)
    {

        $diff = $start->diff($end);
        $diff_days = $diff->format("%a");

        // ----- customer select box

        $customers_out = array();

        $customer_out = pz_i18n::msg("no_customer");
        $customer_id = 0;
        if(is_a($customer,"pz_customer") && array_key_exists($customer->getId(),$customers))
        {
            $customer_out = htmlspecialchars($customers[$customer->getId()]->getName());
            $customer_link = pz::url($p["mediaview"], $p["controll"], $p["function"], array_merge($p["linkvars"],array("mode" => "", "customer_id" => 0)));
            $customers_out[0] = '<li class="entry"><a href="'.$customer_link.'"><span class="title">'.pz_i18n::msg("no_customer").'</span></a></li>';
            $customer_id = $customer->getId();
        }

        foreach($customers as $c)
        {
            $customer_link = pz::url($p["mediaview"], $p["controll"], $p["function"], array_merge($p["linkvars"],array("mode" => "", "customer_id" => $c->getid())));
            $customers_out[$c->getid()] = '<li class="entry"><a href="'.$customer_link.'"><span class="title">'.htmlspecialchars($c->getName()).'</span></a></li>';
        }

        // ----- project list table

        $return = '
	        <header>
	          <div class="grid2col header">
	            <div class="column first">
	              <h1 class="hl1">'.pz_i18n::msg("calendar_customerplan_list").'</h1>
	            </div>
	            <div class="column last">
                <ul class="sl1 view-layout">
                <li class="first selected"><span class="selected"  onclick="pz_screen_select(this)">'.pz_i18n::msg("customer").': '.$customer_out.'</span>
                  <div class="flyout">
                    <div class="content">
                      <ul class="entries sort">
      					  	    '.implode("",$customers_out).'
                      </ul>
                    </div>
                  </div>
                </li>
                </ul>
              </div>
	          </div>
	        </header>';

        $return .= pz_calendar_event_screen::getCustomerplanAllviewslistView($customer, $customers, $projects, $events, $p, $start, $end);

        $link_refresh = pz::url("screen","calendars","customerplan",
            array_merge(
                $p["linkvars"],
                array(
                    "mode"=>"list",
                    "day"=>$start->format("Ymd"),
                    "customer_id" => $customer_id,
                )
            )
        );

        return '<div class="design3col" id="calendar_customerplan_list" data-list-type="calendar" data-url="'.$link_refresh.'">'.$return.'</div>';

    }



    static function getCustomerplanAllviewslistView($customer, $customers, $projects, $events, $p, $start, $end)
    {

        $diff = $start->diff($end);
        $diff_days = $diff->format("%a");

        $return = '';

        // ----- show days
        $months = array();
        $today = new DateTime();
        $current = clone $start;
        $current_month = clone $current;
        $mkey = $current_month->format("m");
        $months[$mkey] = array("month"=>$current_month);
        for ($i = 1; $i <= $diff_days; $i++)
        {
            if($current_month->format("m") != $current->format("m"))
            {
                $current_month = clone $current;
                $mkey = $current_month->format("m");
                $months[$mkey] = array("month"=>$current_month);
            }
            if(!isset($months[$mkey]["days"]))
                $months[$mkey]["days"] = array();

            $class = array("customerplan-day");
            if($current->format("N")>5) $class[] = "weekend"; else $class[] = "weekday";
            if($current->format("Ymd") == $today->format("Ymd")) $class[] = "today";

            if($diff_days > 14)
                $d = utf8_encode(strftime(pz_i18n::msg("show_day_short"),$current->format("U")));
            else
                $d = utf8_encode(strftime(pz_i18n::msg("show_day_short_weekday"),$current->format("U")));

            $months[$mkey]["days"][] = '<li class="'.implode(" ",$class).'">'.$d.'</li>';
            $current->modify("+1 day");
        }

        $calendar = '';

        foreach($months as $month)
        {
            $calendar .= '<li class="customerplan-month">
                <span class="label">'.utf8_encode(strftime('%B',$month["month"]->format("U"))).'</span>
                <ul class="customerplan-days">'.implode("",$month["days"]).'</ul>'.
                '</li>';
        }
        $calendar = '<ul class="customerplan-months">'.$calendar.'</ul>';


        // ----- load and show project events
        $event_days = array();
        foreach($events as $event)
        {
            $project_id = $event->getProjectId();
            if(!isset($project_events[$project_id]))
                $project_events[$project_id] = array();

            $project_events[$project_id][] = $event;
        }



        // ----- events
        $spr_hasevents = false;
        $pr = array();
        foreach($projects as $project)
        {

            // project

            $project_screen = new pz_project_screen($project);
            $pkey = 'project-'.$project->getId();
            $pr[$pkey] = array();

            $pe = array();

            // ------------------------- project events

            $current = clone $start;

            $init = true;

            for ($i = 1; $i <= $diff_days; $i++)
            {
                if(isset($project_events[$project->getId()]))
                {
                    foreach($project_events[$project->getId()] as $event)
                    {
                        if(
                            $event->getProjectSubId() == 0 &&
                            (
                                (  $event->getFrom()->format("Ymd") == $current->format("Ymd")  ) // event start in viewport
                                ||
                                (  $event->getFrom()->format("Ymd") < $current->format("Ymd")  ) // events before viewport
                            )

                        )
                        {
                            $event_screen = new pz_calendar_event_screen($event);
                            $pe[$event->getId()] = $event_screen->getCustomerplanEventView($p, $start, $end, $current);
                        }
                    } // / foreach
                }
                $current->modify("+1 day");
            }

            // ------------------------- subproject events

            $sp = array();
            foreach($project->getProjectSubs() as $subproject_id => $subproject)
            {
                $spe = array();
                $current = clone $start;
                for ($i = 1; $i <= $diff_days; $i++)
                {
                    if(isset($project_events[$project->getId()]))
                    {
                        foreach($project_events[$project->getId()] as $event)
                        {
                            if(
                                $event->getProjectSubId() == $subproject_id &&
                                $event->getProjectSubId() != 0 &&
                                (
                                    (  $event->getFrom()->format("Ymd") == $current->format("Ymd")  ) // event start in viewport
                                    ||
                                    (  $event->getFrom()->format("Ymd") < $current->format("Ymd")  ) // events before viewport
                                )
                            )
                            {
                                $event_screen = new pz_calendar_event_screen($event);
                                $spe[$event->getId()] = $event_screen->getCustomerplanEventView($p, $start, $end, $current);
                            }
                        } // / foreach
                    }
                    $current->modify("+1 day");
                }

                if(count($spe)>0)
                {
                    $spr_hasevents = true;
                }

                $sp[] = '
        <dl class="customerplan-subproject-event" data-project-sub-id="'.$subproject_id.'">
          <dt>'.htmlspecialchars($subproject->getName()).'</dt>
          <dd>'.implode("",$spe).'</dd>
        </dl>';

            }

            $pr_classes = array();
            $pr_classes[] = 'customerplan-project';
            $pr_classes[] = $pkey;
            if(count($sp)>0)
            {
                $pr_classes[] = 'has-subprojects';
            }

            $spr_classes = array();
            $spr_classes[] = 'customerplan-subproject-events';
            if(!$spr_hasevents)
            {
                $spr_classes[] = 'hidden';
            }

            $pr[$pkey] = '
        <dl class="'.implode(" ",$pr_classes).'" data-project-id="'.$project->getId().'">
          <dt class="customerplan-project-name">'.$project_screen->getLabelView().'</dt>
          <dd class="customerplan-project-events"><div class="customerplan-project-events-wrapper">'.implode("",$pe).'</div></dd>
          <dd class="'.implode(" ",$spr_classes).'">'.implode("",$sp).'</dd>
        </dl>
      ';

        }

        $script = '<script>pz_set_customerplan_init();</script>';



        // ----- output

        $link_today = "javascript:pz_loadPage('calendar_customerplan_list','".pz::url("screen","calendars","customerplan",array_merge($p["linkvars"],array("mode"=>"list","day"=>$today->format("Ymd"))))."')";

        $linkdate = clone $start;
        $link_to_2weeksview = "pz_loadPage('calendar_customerplan_list','".
            pz::url("screen","calendars","customerplan",array_merge($p["linkvars"],array("mode"=>"list","customerplan_view"=>"2weeks","day"=>$linkdate->format("Ymd")))).
            "')";
        $link_to_monthview = "pz_loadPage('calendar_customerplan_list','".
            pz::url("screen","calendars","customerplan",array_merge($p["linkvars"],array("mode"=>"list","customerplan_view"=>"month","day"=>$linkdate->format("Ymd")))).
            "')";

        if($diff_days<15)
            $linkdate->modify("+7 days");
        else
            $linkdate->modify("first day of next month");

        $link_next = "javascript:pz_loadPage('calendar_customerplan_list','".pz::url("screen","calendars","customerplan",array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        if($diff_days<15)
            $linkdate->modify("-14 days");
        else
            $linkdate->modify("-2 months")->modify("first day of this month");

        $link_previous = "javascript:pz_loadPage('calendar_customerplan_list','".pz::url("screen","calendars","customerplan",array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        $link_event_add = "pz_loadPage('calendar_event_form','".pz::url("screen","calendars","event",array_merge($p["linkvars"],array("mode"=>"add_calendar_event","day"=>$start->format("Ymd"),"booked"=>0)))."')";

        if ($diff_days<15) {
            $info = pz::strftime(pz_i18n::msg("show_date_normal"), $start->format("U")).' - '.pz::strftime(pz_i18n::msg("show_date_normal"), $end->format("U"));
        } else {
            $info = pz::strftime(pz_i18n::msg("show_month_long"), $start->format("U"));
        }

        if ($diff_days<15) {
            $link_to_view = $link_to_monthview;
            $link_to_view_text = pz_i18n::msg("calendar_month_view");
        } else {
            $link_to_view = $link_to_2weeksview;
            $link_to_view_text = pz_i18n::msg("calendar_2weeks_view");
        }

        $return = '<div class="calendar customerplan customerplan-view-'.$p["linkvars"]["customerplan_view"].' clearfix" data-customerplan-view-days="'.$diff_days.'" data-customerplan-day-start="'.$start->format("N").'">
		  <header class="header">
		    <div class="grid3col">
		      <div class="column first">
		        <a href="javascript:void(0);" onclick="pz_toggleSection();" class="toggle bt5"><span class="icon">'.pz_i18n::msg("calendar_resize").'</span></a>
  		      <a class="bt2" href="javascript:void(0);" onclick="'.$link_event_add.'">'.pz_i18n::msg("new_event").'</a>
		      </div>
		      <div class="column">
            <p class="info">'.$info.'</p>
		      </div>
		      <div class="column last">
		        <ul class="pagination">
              <li class="next"><a class="bt2" href="javascript:void(0);" onclick="'.$link_to_view.'">'.$link_to_view_text.'</a></li>
              <li class=" prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
              <li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
              <li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
            </ul>
		      </div>
		    </div>
		  </header>

		  '.pz_calendar_event_screen::getCustomerplanStyle().'
      '.$calendar.'
      '.implode("",$pr).'
      '.$script.'

		</div>';

        return $return;

    }



    public static function getCustomerplanStyle()
    {

        $day_starts = array(
//                        7 => array('design2col' => 80, 'design3col' => 128),
            14 => array('design2col' => 40, 'design3col' => 64),
//                        4 => array('design2col' => 125, 'design3col' => 225),
//                        10 => array('design2col' => 50, 'design3col' => 90),
            31 => array('design2col' => 18, 'design3col' => 28),
        );

        $widths = array('design2col' => 760,
                        'design3col' => 1150,
        );

        $css = array();
        foreach ($widths as $design_col => $width)
        {
            foreach ($day_starts as $day => $array)
            {
                $css_width_full = $width;
                $css_width_name = $width - ($array[$design_col] * $day)-1;
                $css_width_day  = $array[$design_col];
                $css_width_days = $width - $css_width_name;

                $note = '
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] ul.customerplan-months {
      margin-left: '.$css_width_name.'px;
    }
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] li.customerplan-day {
      width: '.$css_width_day.'px;
    }
/*
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] li.customerplan-day:first-child {
      width: '.($css_width_day - 1).'px;
    }
*/
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] dl.customerplan-project dt,
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] dl.customerplan-subproject-event dt {
      width: '.$css_width_name.'px !important;
    }
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] dl.customerplan-project dd.customerplan-project-events,
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] dl.customerplan-subproject-event dd {
      margin-left: '.$css_width_name.'px;
    }
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] dl.customerplan-project dd.customerplan-project-events {
      width: '.$css_width_days.'px;
    }
    .'.$design_col.' [data-customerplan-view-days="'.$day.'"] div.event-info {
      width: '.$css_width_full.'px;
    }
    ';
                $css[$design_col]['css'][] = $note;

                for ($i = 0; $i <= $day; $i++)
                {
                    $n = $i * $array[$design_col];
                    $css[$design_col]['data_event_position'][]  = '.'.$design_col.' [data-customerplan-view-days="'.$day.'"] div.event-info[data-event-position-left="'.$i.'"] { left: '.$n.'px; }';
                    $css[$design_col]['data_event_days'][]      = '.'.$design_col.' [data-customerplan-view-days="'.$day.'"] div.event-info[data-event-position-width="'.$i.'"] { width: '.$n.'px; }';
                }
            }
        }

        $return = '';

        foreach ($css as $design)
        {
            $return .= implode("\n", $design['css'])."\n\n";
            $return .= implode("\n", $design['data_event_position'])."\n\n";
            $return .= implode("\n", $design['data_event_days'])."\n\n";
        }

        // Top Position
        $top = 25;
        $design['data_event_position_top'] = array();
        for ($i = 0; $i <= 20; $i++)
        {
            $design['data_event_position_top'][] = 'div.event-info[data-event-position-top="'.$i.'"] { top: '.$i*$top.'px; }';
        }
        $return .= implode("\n", $design['data_event_position_top'])."\n\n";

        return '<style>'.$return.'</style>';
    }






    // --------------------------------------------------------------- Projectevent


    public function getProjecteventListView($projects, $events, $p, $start, $end)
    {

        $diff = $start->diff($end);
        $diff_days = $diff->format("%a");

        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg("calendar_projectevent_list").'</h1>
	          </div>
	        </header>';

        $th = '';

        // ----- show months
        $th .= '<tr>';
        $current = clone $start;
        $current_month = clone $current;
        $colspan = 0;
        for ($i = 1; $i <= $diff_days; $i++)
        {
            if($current_month->format("m") != $current->format("m"))
            {
                $th .= '<td class="highlighted" colspan="'.$colspan.'">'.utf8_encode(strftime('%B',$current_month->format("U"))).'</td>';
                $current_month = clone $current;
                $colspan = 0;
            }
            $current->modify("+1 day");
            $colspan++;
        }
        if($colspan>1)
            $th .= '<td class="highlighted" colspan="'.$colspan.'">'.utf8_encode(strftime('%B',$current_month->format("U"))).'</td>';

        $th .= '</tr>';

        // ----- show days
        $th .= '<tr>';
        $current = clone $start;
        for ($i = 1; $i <= $diff_days; $i++)
        {
            if($current->format("N")>5) $class = " weekend"; else $class = " weekday";
            $th .= '<th class="highlighted'.$class.'">'.$current->format("d.").'</th>';
            $current->modify("+1 day");
        }
        $th .= '</tr>';

        // ----- load and show project events
        $event_days = array();
        foreach($events as $event)
        {
            $event_day = $event->getFrom()->format("Ymd");
            $project_id = $event->getProjectId();
            if(!isset($event_days[$project_id][$event_day]))
                $event_days[$project_id][$event_day] = array();

            if($event->getTo()->format("Ymd") > $event->getFrom()->format("Ymd"))
            {
                $current_diff = $event->getFrom()->diff($event->getTo());
                $current_diff_day = clone $event->getFrom();
                for($i=0;$i<=$current_diff->format("%a");$i++)
                {
                    $event_days[$project_id][$current_diff_day->format("Ymd")][] = $event;
                    $current_diff_day->modify("+1 day");
                }
            }else
            {
                $event_days[$project_id][$event_day][] = $event;
            }

        }

        // sort projekts by customer
        $customers = array();

        foreach($projects as $project)
        {
            $customer_id = $project->getCustomerId();
            if(!isset($customers[$customer_id]))
            {
                if($customer_id > 0)
                {
                    $name = $project->getCustomer()->getName();
                }else {
                    $name = pz_i18n::msg("customer_notexists");
                }
                $customers[$customer_id] = array('projects' => array(), 'name' => $name);
            }
            $customers[$customer_id]['projects'][] = $project;

        }

        // TODO CUSTOMERSORT By Name

        $tr_projects = array();
        $tr_projects['-'] = '<tr><th>&nbsp;</th></tr>';

        $tr = array();
        foreach($customers as $customer_id => $customer)
        {
            $customer_layer = 'calendar_event-customer-'.$customer_id;
            $customer_link = "$('.".$customer_layer."').toggle();";

            $tr[$customer_id] = '<tr><th class="lighter" colspan="'.($diff_days+1).'"><!--'.$customer["name"].'--></th></tr>';
            $tr_projects[$customer_id] = '<tr><th class="lighter" colspan="'.($diff_days+1).'"><a href="javascript:void(0);" onclick="'.$customer_link.'">'.$customer["name"].'</a></th></tr>';

            foreach($customer["projects"] as $project)
            {
                $trkey = $customer_id.'-'.$project->getId();
                // <a href="'.pz::url('screen','project','view',array('project_id'=>$project->getId())).'">
                $tr_projects[$trkey] = '<tr class='.$customer_layer.' style="display:none;"><th class="highlighted"><span class="label-color-block '.pz_label_screen::getColorClass($project->getLabelId()).'"></span>'.htmlspecialchars(pz::cutText($project->getName(),35)).'</th></tr>';

                $tr[$trkey] = '<tr class='.$customer_layer.' style="display:none;">';
                $current = clone $start;
                for ($i = 1; $i <= $diff_days; $i++)
                {
                    // TODO - check if event and set content
                    $content = '';
                    $classes = array();
                    $classes[] = "label";
                    if(isset($event_days[$project->getId()][$current->format("Ymd")]))
                    {
                        $classes[] = "label";
                        $classes[] = pz_label_screen::getColorClass($project->getLabelId());
                        if($current->format("Ymd") == date("Ymd"))
                            $classes[] = "today";
                        $day_events = array();
                        foreach($event_days[$project->getId()][$current->format("Ymd")] as $event)
                        {
                            $event_view = new pz_calendar_event_screen($event);
                            $day_events[] = $event_view->getTooltipEventView($p); // Location // Description

                        }
                        $content = pz_screen::getTooltipView("&nbsp;&nbsp;",implode("<br /><hr />",$day_events));
                    }

                    if($current->format("N")>5) $classes[] = "weekend"; else $classes[] = "weekday";
                    $tr[$trkey] .= '<td class="'.implode(" ",$classes).'">'.$content.'</td>';
                    $current->modify("+1 day");
                }
                $tr[$trkey] .= '</tr>';

            }

        }

        $link_today = "javascript:pz_loadPage('calendar_projectevent_list','".pz::url("screen","calendars","projectevent",array_merge($p["linkvars"],array("mode"=>"list","day"=>date("Ymd"))))."')";

        $linkdate = clone $start;
        $linkdate->modify("+".$diff_days." days");
        $link_next = "javascript:pz_loadPage('calendar_projectevent_list','".pz::url("screen","calendars","projectevent",array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        $linkdate->modify("-".((2*$diff_days)+1)." days");
        $link_previous = "javascript:pz_loadPage('calendar_projectevent_list','".pz::url("screen","calendars","projectevent",array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        $return .= '
		<div class="calendar projectevent clearfix">
		  <header class="header">
		    <div class="grid3col">
		      <div class="column first">
            &nbsp;
		      </div>
		      <div class="column">
            <p class="info">'.utf8_encode(strftime(pz_i18n::msg("show_date_long"),$start->format("U"))).' - '.utf8_encode(strftime(pz_i18n::msg("show_date_long"),$end->format("U"))).'</p>
		      </div>
		      <div class="column last">
		            <ul class="pagination">
		              <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
		              <li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
		              <li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
		            </ul>
		      </div>
		    </div>
		  </header>


		  <table class="tbl3" style="width:280px; float:left;">
		    <thead>
		          <tr>
		            <th>&nbsp;</th>
		          </tr>
		        </thead>
		    <tbody>
		      '.implode("",$tr_projects).'
		        </tbody>
		  </table>


		  <table class="tbl3" style="float:left;clear:none; overflow:auto;width:870px;display:block;">
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

        return '<div class="design3col" id="calendar_projectevent_list">'.$return.'</div>';

    }



    public function getProjectjobListView($projects, $events, $p, $start, $end)
    {

        $diff = $start->diff($end);
        $diff_days = $diff->format("%a");

        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg("calendar_projectjob_list").'</h1>
	          </div>
	        </header>';


        // ----- show days
        $th = '<tr>';
        $th .= '<th>&nbsp;</th>';
        $current = clone $start;
        for ($i = 1; $i <= $diff_days; $i++)
        {
            if($current->format("N")>5) $class = "weekend"; else $class = "weekday";
            $th .= '<th class="'.$class.'">'.$current->format("d.").'</th>';
            $current->modify("+1 day");
        }
        $th .= '<th>'.pz_i18n::msg('time_allofmonth').'</th></tr>';


        // ----- load and show project events
        $event_days = array();
        foreach($events as $event)
        {
            $event_day = $event->getFrom()->format("Ymd");
            $project_id = $event->getProjectId();
            if(!isset($event_days[$project_id][$event_day]))
                $event_days[$project_id][$event_day] = array();

            if($event->getTo()->format("Ymd") > $event->getFrom()->format("Ymd"))
            {
                $current_diff = $event->getFrom()->diff($event->getTo());
                $current_diff_day = clone $event->getFrom();
                for($i=0;$i<=$current_diff->format("%a");$i++)
                {
                    $event_days[$project_id][$current_diff_day->format("Ymd")][] = $event;
                    $current_diff_day->modify("+1 day");
                }
            }else
            {
                $event_days[$project_id][$event_day][] = $event;
            }

        }

        // sort projekts by customer
        $customers = array();

        foreach($projects as $project)
        {
            $customer_id = $project->getCustomerId();
            if(!isset($customers[$customer_id]))
            {
                if($customer_id > 0)
                {
                    $name = $project->getCustomer()->getName();
                }else {
                    $name = pz_i18n::msg("customer_notexists");
                }
                $customers[$customer_id] = array('projects' => array(), 'name' => $name);
            }
            $customers[$customer_id]['projects'][] = $project;

        }

        // TODO CUSTOMERSORT By Name

        $tr = array();
        foreach($customers as $customer_id => $customer)
        {
            $tr[$customer_id] = '<tr><th class="lighter" colspan="'.($diff_days+2).'">'.$customer["name"].'</th></tr>';
            foreach($customer["projects"] as $project)
            {
                $trkey = $customer_id.'-'.$project->getId();
                $tr[$trkey] = '<tr>';
                $tr[$trkey] .= '<th class="highlighted"><a href="'.pz::url('screen','project','view',array('project_id'=>$project->getId())).'">'.htmlspecialchars(pz::cutText($project->getName(),60)).'</th>';

                $all_hours = 0;
                $all_minutes = 0;

                $current = clone $start;
                for ($i = 1; $i <= $diff_days; $i++)
                {
                    $content = '';
                    $classes = array();
                    $classes[] = "label";
                    if(isset($event_days[$project->getId()][$current->format("Ymd")]))
                    {
                        $classes[] = "label";
                        $classes[] = pz_label_screen::getColorClass($project->getLabelId());
                        if($current->format("Ymd") == date("Ymd"))
                            $classes[] = "today";
                        $day_events = array();
                        $hours = 0;
                        $minutes = 0;
                        foreach($event_days[$project->getId()][$current->format("Ymd")] as $event)
                        {
                            $hours += $event->getDuration()->format("%h");
                            $minutes += $event->getDuration()->format("%i");
                            $event_view = new pz_calendar_event_screen($event);
                            $day_events[] = $event_view->getTooltipEventView($p); // Location // Description
                        }

                        $all_hours += $hours;
                        $all_minutes += $minutes;
                        $hfm = (int) ($minutes/60);
                        $hours += $hfm;
                        $minutes = $minutes - ($hfm*60);
                        if($hours == 0)
                            $content = $minutes."m";
                        else
                            $content = $hours.'h';

                        $content = pz_screen::getTooltipView('<span>'.$content.'</span>',implode("<br />",$day_events));
                    }

                    if($current->format("N")>5) $classes[] = "weekend"; else $classes[] = "weekday";
                    $tr[$trkey] .= '<td class="'.implode(" ",$classes).'">'.$content.'</td>';
                    $current->modify("+1 day");
                }

                $all_hfm = (int) ($all_minutes/60);
                $all_hours += $all_hfm;
                $all_minutes = $all_minutes - ($all_hfm*60);

                $all_info = 0;
                if($all_hours == 0)
                    $all_info = $all_minutes."m";
                else
                    $all_info = $all_hours.'h';

                $tr[$trkey] .= '<td class="highlighted projectmonth-time">'.$all_info.'</td>';


                $tr[$trkey] .= '</tr>';

            }

        }

        $link_today = "javascript:pz_loadPage('".$p["layer_list"]."','".pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>date("Ymd"))))."')";

        $linkdate = clone $end;
        $linkdate->modify("+1 day");
        $link_next = "javascript:pz_loadPage('".$p["layer_list"]."','".pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        $linkdate->modify("-1 month");
        $linkdate->modify("-1 day");
        $link_previous = "javascript:pz_loadPage('".$p["layer_list"]."','".pz::url("screen","calendars",$p["function"],array_merge($p["linkvars"],array("mode"=>"list","day"=>$linkdate->format("Ymd"))))."')";

        $return .= '
		<div class="calendar projectevent clearfix">
		  <header class="header">
		    <div class="grid3col">
		      <div class="column first">
          &nbsp;
		      </div>
		      <div class="column">
            <p class="info">'.utf8_encode(strftime(pz_i18n::msg("show_month_long"),$start->format("U"))).'</p>
		      </div>
		      <div class="column last">
		            <ul class="pagination">
		              <li class="first prev"><a class="page prev bt5" href="'.$link_previous.'"><span class="inner">'.pz_i18n::msg("previous").'</span></a></li>
		              <li class="next"><a class="page next bt5" href="'.$link_next.'"><span class="inner">'.pz_i18n::msg("next").'</span></a></li>
		              <li class="last"><a class="bt5" href="'.$link_today.'"><span class="inner">'.pz_i18n::msg("today").'</span></a></li>
		            </ul>
		      </div>
		    </div>
		  </header>

		  <table class="tbl3">
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

        return '<div class="design3col" id="'.$p["layer_list"].'">'.$return.'</div>';

    }



    // --------------------------------------------------------------- Projectjob





















    // --------------------------------------------------------------- Formviews

    function getCopyForm($p = array())
    {
        /*
          $title = $this->calendar_event->getTitle();
          $p["info_message"] = pz_i18n::msg("calendar_event_copied", htmlspecialchars($title));
         $return = $this->getFlyoutEventView($p);
      */
        $return = '<script>pz_refresh_calendar_lists();pz_tooltipbox_close();</script>';
        return $return;

    }

    function getDeleteForm($p = array())
    {
        // $return = $header.'<p class="xform-info">'.pz_i18n::msg("calendar_event_deleted", htmlspecialchars($title)).'</p>';
        $return = '<script>pz_refresh_calendar_lists();pz_remove_calendar_events_by_id('.$this->calendar_event->getId().');pz_tooltipbox_close()</script>';
        return $return;

    }

    static function getAddForm($p = array())
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg("calendar_event_add").'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform;
        // $xform->setDebug(TRUE);
        $xform->setObjectparams("form_id", "calendar_event_add_form");
        $xform->setObjectparams("form_action", "javascript:pz_loadFormPage('calendar_event_form','calendar_event_add_form','".pz::url('screen','calendars','event',array("mode"=>'add_calendar_event'))."')");
        $xform->setObjectparams("real_field_names",TRUE);
        $xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
        $xform->setObjectparams('form_showformafterupdate',1);

        $xform->setValueField("checkbox",array("booked",pz_i18n::msg("calendar_event_booked"),'',1));
        $xform->setValueField("text",array("title",pz_i18n::msg("calendar_event_title")));

        $projects = pz::getUser()->getCalendarProjects();
        $xform->setValueField("pz_select_screen",array("project_id",pz_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",0,pz_i18n::msg("please_choose")));

        $subprojects = array();
        $filter = array("field" => "project_id", "value" => rex_request("project_id","int"));
        if( rex_request("project_id","int") > 0 && ($project = pz::getUser()->getAllProjects($filter)) ) {
            $subprojects = $project;
        }
        $xform->setValueField("pz_select_screen",array("project_sub_id",pz_i18n::msg("project_sub"),pz_project::getProjectSubsAsString($subprojects),"","",0,pz_i18n::msg("please_choose")));

        // $xform->setValueField("html",array("timesplit",'<div class="split-h"></div>'));
        $xform->setValueField("checkbox",array("allday",pz_i18n::msg("calendar_event_allday")));

        $xform->setValueField("datestamp",array("created","mysql","","0","1"));
        $xform->setValueField("datestamp",array("updated","mysql","","0","0"));

        if(rex_request("allday","int") == 1)
        {
            $_REQUEST["from"]["hours"] = "00";
            $_REQUEST["from"]["minutes"] = "00";
            $_REQUEST["to"]["hours"] = 23;
            $_REQUEST["to"]["minutes"] = 45;
        }

        $xform->setValueField("pz_datetime_screen",array("from",pz_i18n::msg("calendar_event_from")));
        $xform->setValueField("pz_datetime_screen",array("to",pz_i18n::msg("calendar_event_to")));
        $xform->setValueField("pz_calendar_event_attendees",array("attendees",pz_i18n::msg("calendar_event_attendees")));
        $xform->setValueField("pz_attachment_screen",array("clip_ids",pz_i18n::msg("calendar_event_attachments")));
        $xform->setValueField("text",array("location",pz_i18n::msg("calendar_event_location")));
        $xform->setValueField("text",array("url",pz_i18n::msg("calendar_event_url")));

        $xform->setValueField("textarea",array("description",pz_i18n::msg("calendar_event_description")));
        $xform->setValidateField("mysql_datetime",array("from",pz_i18n::msg("error_calendar_from_datetime")));
        $xform->setValidateField("mysql_datetime",array("to",pz_i18n::msg("error_calendar_to_datetime")));

        $xform->setValidateField("pz_project_jobevent_id",array("project_id",rex_request("booked","int"),pz_i18n::msg("error_calendar_job_project_id"),pz_i18n::msg("error_calendar_event_project_id")));
        $xform->setValidateField("pz_project_id",array("project_id",pz_i18n::msg("error_calendar_event_project_id")));
        $xform->setValidateField("empty",array("title",pz_i18n::msg("error_calendar_event_title_empty")));
        $xform->setValidateField("pz_comparefields",array("from","to",">=",pz_i18n::msg("error_calendar_event_fromto_compare")));

        /*
            TODO
                wiederholung
                erinnerung
                einladungen
                rule_id
                base_from
                sequence
                vt

        */

        $jquery ='
		<script>

		$("#calendar_event_add_form #xform-formular-booked input, #calendar_event_add_form #xform-formular-allday input, #calendar_event_add_form #xform-formular-project_id select").bind("change",function() {
			calendar_event_add_form_updater();
		});

		function calendar_event_add_form_updater()
		{
			var booked = $("#calendar_event_add_form #xform-formular-booked input:checkbox:checked").val();
			var allday = $("#calendar_event_add_form #xform-formular-allday input:checkbox:checked").val();
			var project_id = $("#calendar_event_add_form #xform-formular-project_id select").val();
			var project_sub_id = $("#calendar_event_add_form #xform-formular-project_sub_id select").val();

			if(allday == 1)
			{
				$("#calendar_event_add_form #xform-formular-from .fafter, #calendar_event_add_form #xform-formular-from .xform-time").hide();
				$("#calendar_event_add_form #xform-formular-to .fafter, #calendar_event_add_form #xform-formular-to .xform-time").hide();

			}else
			{
				$("#calendar_event_add_form #xform-formular-from .fafter, #calendar_event_add_form #xform-formular-from .xform-time").show();
				$("#calendar_event_add_form #xform-formular-to .fafter, #calendar_event_add_form #xform-formular-to .xform-time").show();
			}

			if(booked == 1)
			{
				$("#calendar_event_add_form #xform-formular-allday input").removeAttr("checked");
				$("#calendar_event_add_form #xform-formular-allday").hide();
				$("#calendar_event_add_form .pz_address_fields_attandees").hide();
				$("div.pz_address_fields_attandees").find("div.data.pz_address_fields_attandees_clone").remove();
				$("#calendar_event_add_form #xform-formular-from").show();
				$("#calendar_event_add_form #xform-formular-to").show();
				$("#calendar_event_add_form #xform-formular-label-id").hide();
				$("#calendar_event_add_form #xform-formular-clip-ids").hide();

			}else
			{
				$("#calendar_event_add_form #xform-formular-allday").show();
				$("#calendar_event_add_form .pz_address_fields_attandees").show();
				$("#calendar_event_add_form #xform-formular-label-id").show();
				$("#calendar_event_add_form #xform-formular-clip-ids").show();

			}

      // Teilprojekte

      project_sub_ids = [];
      project_sub_name = [];
';

        foreach($projects as $project) {
            $sids = array();
            foreach($project->getProjectSubs() as $project_sub) {
                $sids[] = '"'.$project_sub->getId().'"';
                $jquery .= "\n".'project_sub_name['.$project_sub->getId().'] = "'.str_replace('"',"'",$project_sub->getName()).'";';
            }
            $jquery .= "\n".'project_sub_ids['.$project->getId().'] = ['.implode(",",$sids).'];';
        }

        $jquery .= '

      // project_sub_id;
      $("#calendar_event_add_form #xform-formular-project_sub_id select option").remove();

      if(project_id == "" || typeof(project_sub_ids[project_id]) == "undefined" ) {
        $("#calendar_event_add_form #xform-formular-project_sub_id").hide();

      }else {
        if(project_sub_ids[project_id].length == 0) {
          $("#calendar_event_add_form #xform-formular-project_sub_id").hide();
        }else {
          $("<option/>").val(0).text("'.pz_i18n::msg("please_choose").'").appendTo("#calendar_event_add_form #xform-formular-project_sub_id select");
          $.each(project_sub_ids[project_id], function(t, v) {

            $("<option/>").val(v).text(project_sub_name[v]).appendTo("#calendar_event_add_form #xform-formular-project_sub_id select");
            if(v == project_sub_id) {
              $("#calendar_event_add_form #xform-formular-project_sub_id select option[value=\'"+v+"\']").attr("selected", true);
            }
          });
          $("#calendar_event_add_form #xform-formular-project_sub_id select").trigger("liszt:updated");
          $("#calendar_event_add_form #xform-formular-project_sub_id").show();
        }
      }

		}

		calendar_event_add_form_updater();
		</script>
		';

        $form = $xform->getForm().$jquery;

        if($xform->getObjectparams("actions_executed"))
        {

            $value_pool = $xform->getObjectparams("value_pool");
            $data = $value_pool["sql"];

            $data = pz::stripSlashes($data);

            $format = 'Y-m-d H:i:s';
            $from = DateTime::createFromFormat($format, $data["from"]);
            $to = DateTime::createFromFormat($format, $data["to"]);

            $created = DateTime::createFromFormat($format, $data["created"]);
            $updated = DateTime::createFromFormat($format, $data["updated"]);

            $event = pz_calendar_event::create();
            $event->setTitle($data["title"]);
            $event->setProjectId($data["project_id"]);
            $event->setProjectSubId($data["project_sub_id"]);
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
            $event->setClipIds($data["clip_ids"]);

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

                $return = $header.'<p class="xform-info">'.pz_i18n::msg("calendar_event_added").'</p>';
                $return .= '<script>pz_refresh_calendar_lists();</script>';
                $return .= pz_screen::getJSDelayedUpdateLayer('calendar_event_add', pz::url('screen', $p['controll'], $p['function'], ['mode' => 'add_calendar_event', 'day' => $from->format('Ymd')]), 4000, 'xform-info');


            }else
            {
                $return = $header.'<p class="xform-warning">'.pz_i18n::msg("error_calendar_event_not_added").'</p>'.$form;
            }

        }else
        {
            $script = '<script>
                var refreshTimeout = refreshTimeout || null;
                if(!isEmpty(refreshTimeout)){ clearTimeout(refreshTimeout); }</script>';
            $return = $header.$form.$script;
        }
        $script = '<script>pz_toggleSection(1);</script>';
        $return = '<div id="calendar_event_form" class="design1col"><div id="calendar_event_add" class="design1col xform-add">'.$return.'</div>'.$script.'</div>';
        return $return;

    }





    public function getEditForm($p = array())
    {

        // TODOS
        // Linksvars in form

        $header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("calendar_event_edit").'</h1>
          </div>
        </header>';

        $xform = new rex_xform;
        // $xform->setDebug(TRUE);

        $xform->setObjectparams("form_id", "calendar_event_edit_form");

        $xform->setObjectparams("main_table",'pz_calendar_event');
        $xform->setObjectparams("main_id",$this->calendar_event->getId());
        $xform->setObjectparams("main_where",'id='.$this->calendar_event->getId());
        $xform->setObjectparams('getdata',true);
        $xform->setObjectparams("form_action", "javascript:pz_loadFormPage('calendar_event_edit','calendar_event_edit_form','".pz::url('screen','calendars',"event",array("mode"=>'edit_calendar_event'))."')");

        $xform->setObjectparams("real_field_names",TRUE);
        $xform->setObjectparams('form_showformafterupdate',1);
        $xform->setHiddenField("calendar_event_id",$this->calendar_event->getId());
        $xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
        if($this->calendar_event->isBooked())
        {
            $xform->setValueField("html",array("booked",'<div class="xform1 data xform-checkbox"><div class="flabel"><label>'.pz_i18n::msg("job").'</label></div><div class="felement">'.pz_i18n::msg("is_job").'</div></div>'));
        }
        $xform->setValueField("text",array("title",pz_i18n::msg("calendar_event_title")));

        $projects = pz::getUser()->getCalendarProjects();
        $xform->setValueField("pz_select_screen",array("project_id",pz_i18n::msg("project"),pz_project::getProjectsAsString($projects),"","",0,pz_i18n::msg("please_choose")));

        $subprojects = array($this->calendar_event->getProject()); // array();
        $filter = array("field" => "project_id", "value" => rex_request("project_id","int"));
        if( rex_request("project_id","int") > 0 && ($project = pz::getUser()->getAllProjects($filter)) ) {
            $subprojects = $project;
        }
        $xform->setValueField("pz_select_screen",array("project_sub_id",pz_i18n::msg("project_sub"),pz_project::getProjectSubsAsString($subprojects),"","",0,pz_i18n::msg("please_choose")));





        if(!$this->calendar_event->isBooked())
        {
            $xform->setValueField("checkbox",array("allday",pz_i18n::msg("calendar_event_allday")));
        }
        $xform->setValueField("datestamp",array("created","mysql","","0","1"));
        $xform->setValueField("datestamp",array("updated","mysql","","0","0"));

        if(rex_request("allday","int") == 1)
        {
            $_REQUEST["from"]["hours"] = "00";
            $_REQUEST["from"]["minutes"] = "00";
            $_REQUEST["to"]["hours"] = 23;
            $_REQUEST["to"]["minutes"] = 45;
        }

        $xform->setValueField("pz_datetime_screen",array("from",pz_i18n::msg("calendar_event_from")));
        $xform->setValueField("pz_datetime_screen",array("to",pz_i18n::msg("calendar_event_to")));

        if(!$this->calendar_event->isBooked())
        {
            $xform->setValueField("pz_calendar_event_attendees",array("attendees",pz_i18n::msg("calendar_event_attendees"),1));
            $xform->setValueField("pz_attachment_screen",array("clip_ids",pz_i18n::msg("calendar_event_attachments")));
        }

        $xform->setValueField("text",array("location",pz_i18n::msg("calendar_event_location")));
        $xform->setValueField("text",array("url",pz_i18n::msg("calendar_event_url")));
        $xform->setValueField("textarea",array("description",pz_i18n::msg("calendar_event_description")));

        $xform->setValidateField("mysql_datetime",array("from",pz_i18n::msg("error_calendar_from_datetime")));
        $xform->setValidateField("mysql_datetime",array("to",pz_i18n::msg("error_calendar_to_datetime_wrong")));

        $xform->setValidateField("pz_project_jobevent_id",array("project_id",$this->calendar_event->isBooked(),pz_i18n::msg("error_calendar_job_project_id"),pz_i18n::msg("error_calendar_event_project_id")));
        $xform->setValidateField("pz_project_id",array("project_id",pz_i18n::msg("error_calendar_event_project_id")));
        $xform->setValidateField("empty",array("title",pz_i18n::msg("error_calendar_event_title_empty")));
        $xform->setValidateField("pz_comparefields",array("from","to",">=",pz_i18n::msg("error_calendar_event_fromto_compare")));

        $jquery ='
		<script>

		$("#calendar_event_edit_form #xform-formular-booked input, #calendar_event_edit_form #xform-formular-allday input, #calendar_event_edit_form #xform-formular-project_id select").bind("change",function() {
			calendar_event_edit_form_updater();
		});

		function calendar_event_edit_form_updater()
		{
			booked = $("#calendar_event_edit_form #xform-formular-booked input:checkbox:checked").val();
			allday = $("#calendar_event_edit_form #xform-formular-allday input:checkbox:checked").val();
			project_id = $("#calendar_event_edit_form #xform-formular-project_id select").val();
			project_sub_id = $("#calendar_event_edit_form #xform-formular-project_sub_id select").val();

			if(allday == 1)
			{
				$("#calendar_event_edit_form #xform-formular-from .fafter, #calendar_event_edit_form #xform-formular-from .xform-time").hide();
				$("#calendar_event_edit_form #xform-formular-to .fafter, #calendar_event_edit_form #xform-formular-to .xform-time").hide();

			}else
			{
				$("#calendar_event_edit_form #xform-formular-from .fafter, #calendar_event_edit_form #xform-formular-from .xform-time").show();
				$("#calendar_event_edit_form #xform-formular-to .fafter, #calendar_event_edit_form #xform-formular-to .xform-time").show();
			}

			if(booked == 1)
			{
				$("#calendar_event_edit_form #xform-formular-allday input").removeAttr("checked");
				$("#calendar_event_edit_form #xform-formular-allday").hide();
				$("#calendar_event_edit_form .pz_address_fields_attandees").hide();
				$("#calendar_event_edit_form #xform-formular-from").show();
				$("#calendar_event_edit_form #xform-formular-to").show();
				$("#calendar_event_edit_form #xform-formular-clip-ids").hide();

			}else
			{
				$("#calendar_event_edit_form #xform-formular-allday").show();
				$("#calendar_event_edit_form .pz_address_fields_attandees").show();
				$("#calendar_event_edit_form #xform-formular-clip-ids").show();

			}

			// Teilprojekte

      project_sub_ids = [];
      project_sub_name = [];
';

        foreach($projects as $project) {
            $sids = array();
            foreach($project->getProjectSubs() as $project_sub) {
                $sids[] = '"'.$project_sub->getId().'"';
                $jquery .= "\n".'project_sub_name['.$project_sub->getId().'] = "'.str_replace('"',"'",$project_sub->getName()).'";';
            }
            $jquery .= "\n".'project_sub_ids['.$project->getId().'] = ['.implode(",",$sids).'];';
        }

        $jquery .= '

      // project_sub_id;
      $("#calendar_event_edit_form #xform-formular-project_sub_id select option").remove();

      if(project_id == "" || typeof(project_sub_ids[project_id]) == "undefined" ) {
        $("#calendar_event_edit_form #xform-formular-project_sub_id").hide();

      }else {
        if(project_sub_ids[project_id].length == 0) {
          $("#calendar_event_edit_form #xform-formular-project_sub_id").hide();
        }else {
          $("<option/>").val(0).text("'.pz_i18n::msg("please_choose").'").appendTo("#calendar_event_edit_form #xform-formular-project_sub_id select");
          $.each(project_sub_ids[project_id], function(t, v) {

            $("<option/>").val(v).text(project_sub_name[v]).appendTo("#calendar_event_edit_form #xform-formular-project_sub_id select");
            if(v == project_sub_id) {
              $("#calendar_event_edit_form #xform-formular-project_sub_id select option[value=\'"+v+"\']").attr("selected", true);
            }
          });
          $("#calendar_event_edit_form #xform-formular-project_sub_id select").trigger("liszt:updated");
          $("#calendar_event_edit_form #xform-formular-project_sub_id").show();
        }
      }

		}
		calendar_event_edit_form_updater();
		</script>
		';

        $return = $xform->getForm().$jquery;

        if($xform->getObjectparams("actions_executed")) {

            $value_pool = $xform->getObjectparams("value_pool");
            $data = $value_pool["sql"];

            $data = pz::stripSlashes($data);

            $format = 'Y-m-d H:i:s';
            $from = DateTime::createFromFormat($format, $data["from"]);
            $to = DateTime::createFromFormat($format, $data["to"]);

            $created = DateTime::createFromFormat($format, $data["created"]);
            $updated = DateTime::createFromFormat($format, $data["updated"]);

            $event = pz_calendar_event::get($this->calendar_event->getId());
            $event->setTitle($data["title"]);
            $event->setProjectId($data["project_id"]);
            $event->setProjectSubId($data["project_sub_id"]);
            $event->setLocation($data["location"]);
            // $event->setBooked($data["booked"]);
            if(isset($data["allday"]))
                $event->setAllDay($data["allday"]);
            $event->setFrom($from);
            $event->setTo($to);
            $event->setDescription($data["description"]);
            $event->setUrl($data["url"]);
            $event->setCreated($created);
            $event->setUpdated($updated);
            $event->setUserId(pz::getUser()->getId());
            if(!$this->calendar_event->isBooked())
                $event->setClipIds($data["clip_ids"]);

            // setSequence($sequence)
            // $alarm = pz_calendar_alarm::create();
            // $alarm->setAction()
            // etc.
            // $event->setAlarms(array($alarm));

            $event->save();

            if(!$this->calendar_event->isBooked())
            {
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
                $event->setUserId(pz::getUser()->getId());

            }

            $return = $header.'<p class="xform-info">'.pz_i18n::msg("calendar_event_updated").'</p>'.$return;
            $return .= '<script>pz_refresh_calendar_lists();</script>';

        }else
        {
            $return = $header.$return;
        }
        $script = '<script>pz_toggleSection(1);</script>';
        $return = '<div id="calendar_event_form" class="design1col"><div id="calendar_event_edit" class="design1col xform-edit">'.$return.'</div>'.$script.'</div>';

        return $return;

    }

}
