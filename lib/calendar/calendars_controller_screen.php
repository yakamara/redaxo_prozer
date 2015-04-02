<?php

class pz_calendars_controller_screen extends pz_calendars_controller
{
    public $name = 'calendars';
    public $function = '';
    public $functions = ['day',  'week', 'customerplan', 'event']; // ,"week""projectevent", "projectjob", "usertime", "projectjob", "list", "month",,
    public $function_default = 'day';
    public $navigation = ['day', 'week', 'customerplan']; // ,"week""projectevent", "projectjob", "week", "usertime", "projectjob","list", "month", "projectjob",

    public function controller($function)
    {
        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];
        $p['linkvars'] = [];
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = $this->function;

        switch ($this->function) {
            case('day'):
                return $this->getDayPage($p);

            case('customerplan'):
                return $this->getCustomerplanPage($p);

            case('event'):
                return $this->getEventPage($p);

            case('week'):
                return $this->getWeekPage($p);

            /*
                        case("projectevent"):
                            return $this->getProjecteventPage($p);

                  case("projectjob"):
                      return $this->getProjectjobPage($p);
            */

            default:
                return '';
                break;

        }
    }

    private function getNavigation($p = [], $flyout = '')
    {
        return pz_screen::getNavigation(
            $p,
            $this->navigation,
            $this->function,
            $this->name
        );
    }

    // ------------------------------------------------------------------- Views

    private function getEventPage($p)
    {
        $mode = rex_request('mode', 'string');
        switch ($mode) {

            // edit / add

            case('add_calendar_event'):
                return pz_calendar_event_screen::getAddForm($p);

            case('edit_calendar_event'):
                $calendar_event_id = rex_request('calendar_event_id', 'string');
                if (($event = pz_calendar_event::get($calendar_event_id)) && pz::getUser()->getEventEditPerm($event)) {
                    $cs = new pz_calendar_event_screen($event);
                    return $cs->getEditForm($p);
                }
                return '<div id="calendar_event_form"><p class="xform-warning">'.pz_i18n::msg('calendar_event_not_exists').'</p></div>';

            // inside functions
            /*
                      case("set_project_sub_id"):
                        $return = array();
                        $return["function_status"] = 0;
                            $calendar_event_id = rex_request("calendar_event_id","string",0);
                            $calendar_event_project_sub_id = rex_request("calendar_event_project_sub_id","int",0);
                            if(
                                $calendar_event_id > 0 &&
                                $calendar_event_project_sub_id > 0 &&
                                ($event = pz_calendar_event::get($calendar_event_id)) &&
                                (pz::getUser()->getEventEditPerm($event)) &&
                                ($project_sub = pz_project_sub::get($calendar_event_project_sub_id)) &&
                                $project_sub->getProject()->getId() == $event->getProject()->getId()
                                )
                            {
                      $calendar_event->setProjectSubId($calendar_event_project_sub_id);
                      $event->save();
                          $return["mode"] = $mode;
                          $return["function_status"] = 1;
                          $return["calendar_event_id"] = $event->getId();
                          $return["calendar_event_project_sub_id"] = $calendar_event_project_sub_id;
                            }
                    return json_encode($return);;
            */
            case('move_event_by_day'):
                $return = [];
                $return['function_status'] = 0;
                $calendar_event_id = rex_request('calendar_event_id', 'string', 0);
                $calendar_event_days = rex_request('calendar_event_move_days', 'int', 0); // +/- days
                if ($calendar_event_id > 0 && $calendar_event_days != 0 && ($event = pz_calendar_event::get($calendar_event_id)) && (pz::getUser()->getEventEditPerm($event))) {
                    if ($calendar_event_days > 0) {
                        $calendar_event_days = $calendar_event_days;
                    }
                    $from = $event->getFrom();
                    $to = $event->getTo();
                    $from->modify($calendar_event_days.' days');
                    $to->modify($calendar_event_days.' days');
                    $event->setFrom($from);
                    $event->setTo($to);
                    $event->save();
                    $return['mode'] = $mode;
                    $return['function_status'] = 1;
                    $return['calendar_event_id'] = $event->getId();
                }
                return json_encode($return);

            case('set_attandee_status'):
                $return = '';
                $calendar_event_id = rex_request('calendar_event_id', 'string', 0);
                $attandee_status = rex_request('attandee_status', 'string', '');
                if ($calendar_event_id > 0 && ($event = pz_calendar_event::get($calendar_event_id)) && in_array($attandee_status, pz_calendar_attendee::getStatusArray())) {
                    $save = false;
                    $as = pz_calendar_attendee::getAll($event);
                    if (is_array($as)) {
                        $attandees = [];
                        foreach ($as as $a) {
                            $attandee = pz_calendar_attendee::create();
                            $attandee->setUserId($a->getUserId());
                            $attandee->setEmail($a->getEmail());
                            $attandee->setName($a->getName());
                            if (in_array($attandee->getEmail(), pz::getUser()->getEmails())) {
                                $attandee->setStatus($attandee_status);
                            } else {
                                $attandee->setStatus($a->getStatus());
                            }
                            $attendees[] = $attandee;
                        }
                        $event->setAttendees($attendees);
                        pz_calendar_attendee::saveAll($event);
                    }
                    $cs = new pz_calendar_event_screen($event);
                    $return .= $cs->getFlyoutEventView($p, true); // disable normal functions
                    $return .= '<script>pz_tracker();</script>';
                } else {
                    $return .= '<p class="xform-warning">'.pz_i18n::msg('calendar_event_not_exists').'</p>';
                }
                return $return;

            case('delete_calendar_event'):
                $calendar_event_id = rex_request('calendar_event_id', 'string');
                if (($event = pz_calendar_event::get($calendar_event_id)) && pz::getUser()->getEventEditPerm($event)) {
                    $cs = new pz_calendar_event_screen($event);
                    $return = $cs->getDeleteForm($p);
                    $event->delete();
                    return $return;
                }
                return '<div id="calendar_event_form"><p class="xform-warning">'.pz_i18n::msg('calendar_event_not_exists').'</p></div>';

            case('copy2job_calendar_event'):
                $calendar_event_id = rex_request('calendar_event_id', 'string');
                if (($event = pz_calendar_event::get($calendar_event_id))) {
                    $cs = new pz_calendar_event_screen($event);
                    $return = $cs->getCopyForm($p);
                    $event->copy2Job();
                    return $return;
                }
                return '<div id="calendar_event_form"><p class="xform-warning">'.pz_i18n::msg('calendar_event_not_exists').'</p></div>';

            case('get_flyout_calendar_event'):
                $return = '';
                $calendar_event_id = rex_request('calendar_event_id', 'string');
                if (($event = pz_calendar_event::get($calendar_event_id))) {
                    $cs = new pz_calendar_event_screen($event);
                    $return .= '<div id="calendar_event_view" class="design1col">'.$cs->getFlyoutEventView($p).'</div>';
                    $return .= '<script>$("#calendar_event_view .flyout").css("display","block");</script>';
                } else {
                    $return .= '<div id="calendar_event_view" class="design1col"><p class="xform-warning">'.pz_i18n::msg('calendar_event_not_exists').'</p></div>';
                }
                return $return;

            case('move_event'):
                $return = [];
                $return['status'] = 0;
                $calendar_event_id = rex_request('calendar_event_id', 'string', 0);
                $calendar_event_minutes = rex_request('calendar_event_move_minutes', 'int', 0); // +/- minutes
                if ($calendar_event_id > 0 && $calendar_event_minutes != 0 && ($event = pz_calendar_event::get($calendar_event_id)) && (pz::getUser()->getEventEditPerm($event))) {
                    $from = $event->getFrom();
                    $to = $event->getTo();
                    $from->modify($calendar_event_minutes.' minutes');
                    $to->modify($calendar_event_minutes.' minutes');
                    $event->setFrom($from);
                    $event->setTo($to);
                    $event->save();
                    $return['mode'] = $mode;
                    $return['status'] = 1;
                    $return['calendar_event_id'] = $event->getId();
                    $event_screen = new pz_calendar_event_screen($event);
                    $content = $event_screen->getEventView($from, $p);
                    $return['calendar_event_dayview'] = $content;
                }
                return json_encode($return);

            case('extend_event_by_minutes'):
                $return = [];
                $return['status'] = 0;
                $calendar_event_id = rex_request('calendar_event_id', 'string', 0);
                $calendar_event_extend_minutes = rex_request('calendar_event_extend_minutes', 'int', 0); // +/- minutes
                if ($calendar_event_id > 0 && $calendar_event_extend_minutes != 0 && ($event = pz_calendar_event::get($calendar_event_id)) && (pz::getUser()->getEventEditPerm($event))) {
                    $to = $event->getTo();
                    $to->modify($calendar_event_extend_minutes.' minutes');

                    $from = $event->getFrom();
                    $event->setFrom($from);
                    $event->setTo($to);
                    $event->save();
                    $return['mode'] = $mode;
                    $return['status'] = 1;
                    $return['calendar_event_id'] = $event->getId();
                    $event_screen = new pz_calendar_event_screen($event);
                    $content = $event_screen->getEventView($from, $p);
                    $return['calendar_event_dayview'] = $content;
                }
                return json_encode($return);

        }
        return '';
    }

    // ------------------------------------ page views


    public function getDayPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'day';
        $p['layer_list'] = 'calendar_events_day_list';
        $p['layer_search'] = 'calendar_events_day_search';

        $p['headline'] = pz_i18n::msg('calendar_day_list');

        $s1_content = '';
        $s2_content = '';

        $request_day = rex_request('day', 'string'); // 20112019 Ymd
        if (!$day = DateTime::createFromFormat('Ymd', $request_day)) {
            $day = new DateTime();
        }

        $next_day = clone $day;
        $next_day->modify('+1 day');

        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $job_projects = pz::getUser()->getCalendarJobsProjects();
        $job_project_ids = pz_project::getProjectIds($job_projects);

        $project_ids = array_merge($project_ids, $job_project_ids);
//     $project_ids = $project_ids + $job_project_ids;

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('search'):
                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                $events = pz::getUser()->getAllEvents($project_ids, $month_firstday, $month_lastday);
                $return = pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge($p, ['linkvars' => ['mode' => 'search', 'project_ids' => implode(',', $project_ids)]]),
                    $day
                );
                return $return;

            case('list'):
                $events = pz::getUser()->getAllEvents($project_ids, $day, clone $day);
                $return = pz_calendar_event_screen::getDayListView(
                    $events,
                    array_merge($p, ['linkvars' => ['mode' => 'list', 'project_ids' => implode(',', $project_ids), 'day' => $day->format('Ymd')]]),
                    $day
                );
                return $return;

            case(''):

                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                $events = pz::getUser()->getAllEvents($project_ids, $month_firstday, $month_lastday);

                $s1_content .= pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge($p, ['linkvars' => ['mode' => 'search', 'project_ids' => implode(',', $project_ids), 'day' => $day->format('Ymd')]]),
                    $day
                );

                $events = pz::getUser()->getAllEvents($project_ids, $day, clone $day);

                $s2_content = pz_calendar_event_screen::getDayListView(
                    $events,
                    array_merge($p, ['linkvars' => ['mode' => 'list', 'project_ids' => implode(',', $project_ids), 'day' => $day->format('Ymd')]]),
                    $day
                );

                $attandee_events = pz::getUser()->getAttandeeEvents($day, null, [pz_calendar_attendee::ACCEPTED, pz_calendar_attendee::TENTATIVE, pz_calendar_attendee::DECLINED]);
                $s1_content .= pz_calendar_event_screen::getAttendeeListView($p, $attandee_events);
                $s1_content .= pz_calendar_event_screen::getAddForm($p);
                break;

        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getWeekPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'week';
        $p['layer_list'] = 'calendar_events_week_list';
        $p['layer_search'] = 'calendar_events_week_search';

        $p['headline'] = pz_i18n::msg('calendar_week_list');

        $days = 14;

        $s1_content = '';
        $s2_content = '';

        $request_day = rex_request('day', 'string'); // 20112019 Ymd
        if (!$day = DateTime::createFromFormat('Ymd', $request_day)) {
            $day = new DateTime();
        }

        $day->modify('monday this week');

        $day_last = clone $day;
        $day_last->modify('+'.($days - 1).' days');

        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('search'):
                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                // $events = pz::getUser()->getAllEvents($project_ids, $month_firstday, $month_lastday);
                $events = pz_calendar_event::getAll($project_ids, $month_firstday, $month_lastday);

                return pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge($p, ['linkvars' => ['mode' => 'search', 'project_ids' => implode(',', $project_ids)]]),
                    $day
                );

            case('list'):
                // $events = pz::getUser()->getAllEvents($project_ids, $day, $day_last);
                $events = pz_calendar_event::getAll($project_ids, $day, $day_last);
                return pz_calendar_event_screen::getWeekListView(
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => implode(',', $project_ids),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day
                );

            case(''):

                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                // $events = pz::getUser()->getAllEvents($project_ids, $month_firstday, $month_lastday);
                $events = pz_calendar_event::getAll($project_ids, $month_firstday, $month_lastday);

                $s1_content .= pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'search',
                            'project_ids' => implode(',', $project_ids),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day);

                // $events = pz::getUser()->getAllEvents($project_ids, $day, $day_last);
                $events = pz_calendar_event::getAll($project_ids, $day, $day_last);

                $s2_content = pz_calendar_event_screen::getWeekListView(
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => implode(',', $project_ids),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day
                );

                $attandee_events = pz::getUser()->getAttandeeEvents($day, null, [pz_calendar_attendee::ACCEPTED, pz_calendar_attendee::TENTATIVE, pz_calendar_attendee::DECLINED]);

                $s1_content .= pz_calendar_event_screen::getAddForm($p);
                break;

        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getCustomerplanPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'customerplan';
        $p['layer_list'] = 'calendar_customerplan_list';
        $p['layer_search'] = 'calendar_customerplan_search';

        $p['headline'] = pz_i18n::msg('calendar_customer_list');

        $s1_content = '';
        $s2_content = '';

        $request_day = rex_request('day', 'string'); // 20112019 Ymd

        // Ansichten:
        // - 14 tägig
        // - Monat

        if (!$day = DateTime::createFromFormat('Ymd', $request_day)) {
            $day = new DateTime();
        }

        $customerplan_view = rex_request('customerplan_view', 'string');
        switch ($customerplan_view) {
            case('month'):
                $day->modify('first day of this month');
                $end = clone $day;
                $end->modify('+31 days');
                break;

            default:
                $customerplan_view = '2weeks';
                $day->modify('Monday this week');
                $end = clone $day;
                $end->modify('+14 days');
                break;

        }

        // customer

        $customer_id = rex_request('customer_id', 'int');
        $customers = pz::getUser()->getActiveCustomers();
        $customer = null;

        if (array_key_exists($customer_id, $customers)) {
            $customer = $customers[$customer_id];
        } else {
            $customer_id = 0;
        }

        // nur projekte eines bestimmten kunden

        $all_calendar_projects = pz::getUser()->getCalendarProjects();
        $projects = [];
        foreach ($all_calendar_projects as $pid => $calendar_project) {
            if ($calendar_project->getCustomerId() == $customer_id) {
                $projects[$pid] = $calendar_project;
            }
        }

        $project_ids = pz_project::getProjectIds($projects);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('search'):
                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                // $events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);
                $events = pz_calendar_event::getAll($project_ids, $month_firstday, $month_lastday, false);
                return pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => ['mode' => 'search', 'customerplan_view' => $customerplan_view, 'customer_id' => $customer_id, 'project_ids' => implode(',', $project_ids)]]
                    ),
                    $day
                );

            case('list'):
                // $events = pz::getUser()->getEvents($project_ids, $day, $end);
                $events = pz_calendar_event::getAll($project_ids, $day, $end, false);

                return pz_calendar_event_screen::getCustomerplanlistView(
                    $customer,
                    $customers,
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => ['mode' => 'list', 'customerplan_view' => $customerplan_view, 'customer_id' => $customer_id, 'day' => $day->format('Ymd')]]
                    ),
                    $day,
                    $end
                );

            case(''):

                $month_firstday = clone $day;
                $month_firstday->modify('first day of this month');
                $month_firstday->modify('-'.($month_firstday->format('N') - 1).' days'); // -kw days
                $month_lastday = clone $day;
                $month_lastday->modify('+1 month');
                $month_lastday->modify('last day of this month');
                // $events = pz::getUser()->getEvents($project_ids, $month_firstday, $month_lastday);
                $events = pz_calendar_event::getAll($project_ids, $month_firstday, $month_lastday, false);

                $s1_content .= pz_calendar_event_screen::getSearch(
                    $project_ids,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => ['mode' => 'search', 'customerplan_view' => $customerplan_view, 'customer_id' => $customer_id, 'day' => $day->format('Ymd')]]
                    ),
                    $day
                );

                // $events = pz::getUser()->getEvents($project_ids, $day, $end);
                $events = pz_calendar_event::getAll($project_ids, $day, $end, false);

                $s2_content = pz_calendar_event_screen::getCustomerplanListView(
                    $customer,
                    $customers,
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => ['customer_id' => $customer_id, 'customerplan_view' => $customerplan_view, 'day' => $day->format('Ymd')]]
                    ),
                    $day,
                    $end
                );

                $attandee_events = pz::getUser()->getAttandeeEvents();
                $s1_content .= pz_calendar_event_screen::getAttendeeListView($p, $attandee_events);
                $s1_content .= pz_calendar_event_screen::getAddForm($p);
                break;

        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getProjectEventPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'projectevent';
        $p['layer_list'] = 'calendar_projectevent_list';
        $p['layer_search'] = 'calendar_projectevent_search';

        $s1_content = '';
        $s2_content = '';

        $request_day = rex_request('day', 'string'); // 20112019 Ymd
        if (!$day = DateTime::createFromFormat('Ymd', $request_day)) {
            $day = new DateTime();
        }

        $days = 35;
        $days = 4 * 7 * 3;

        $end = clone $day;
        $end->modify('+'.$days.' days');

        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            /*
              case("search"):
                  $events = pz::getUser()->getAllEvents($project_ids, $month_firstday, $month_lastday);
                  return pz_calendar_event_screen::getSearch($project_ids, $events, $p, $day, $end);
                  break;
              */

            case('list'):
                $events = pz_calendar_event::getAll($project_ids, $day, $end);
                return pz_calendar_event_screen::getProjecteventListView(
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => rex_request('project_ids'),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day,
                    $end
                );
                break;

            case(''):
                $events = pz_calendar_event::getAll($project_ids, $day, $end);
                $s2_content = pz_calendar_event_screen::getProjecteventListView(
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => rex_request('project_ids'),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day,
                    $end
                );
                break;
            default:
                break;
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getProjectJobPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'projectjob';
        $p['layer_list'] = 'calendar_projectjob_list';

        $s1_content = '';
        $s2_content = '';

        $request_day = rex_request('day', 'string'); // 20112019 Ymd
        if (!$day = DateTime::createFromFormat('Ymd', $request_day)) {
            $day = new DateTime();
        }

        $day->modify('first day of this month');
        // $month_firstday->modify("-".($month_firstday->format("N")-1)." days"); // -kw days

        $days = 35;

        $end = clone $day;
        $end->modify('last day of this month');

        $days_diff = $day->diff($day);
        $days = $days_diff->format('%a');

        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('list'):
                $events = pz_calendar_event::getAll($project_ids, $day, $end, true);
                return pz_calendar_event_screen::getProjectjobListView(
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => rex_request('project_ids'),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day,
                    $end
                );
                break;

            case(''):
                $events = pz_calendar_event::getAll($project_ids, $day, $end, true);
                $s2_content = pz_calendar_event_screen::getProjectjobListView(
                    $projects,
                    $events,
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'project_ids' => rex_request('project_ids'),
                            'day' => $day->format('Ymd'),
                        ],
                        ]
                    ),
                    $day,
                    $end
                );
                break;
            default:
                break;
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    // --------------------- SPÄTER


    public function getMonthPage($p = [])
    {
        $p['mediaview'] = 'screen';
        $p['controll'] = 'calendars';
        $p['function'] = 'month';
        $p['layer_list'] = 'calendar_events_month_list';
        $p['layer_search'] = 'calendar_events_month_search';

        $s1_content = '<div class="design1col">&nbsp;</div>';
        $s1_content .= '<div id="calendar_event_view"></div>';
        $s2_content = '';

        /*
        $request_year_month = rex_request("ym","string"); // 201104
        if(!$from = DateTime::createFromFormat('Ym', $request_year_month.'00')) {
        }
        */

        $from = DateTime::createFromFormat('Ymd', date('Ym00'));
        $to = DateTime::createFromFormat('Ymd', date('Ym30'));

        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        $events = pz::getUser()->getAllEvents($project_ids, $from, $to);
        $s2_content = pz_calendar_event_screen::getMonthListView($events, $p, $from);

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }
}
