<?php

class pz_sabre_caldav_backend extends Sabre_CalDAV_Backend_Abstract
{
  private $calendars = array();

  private $objects = array();

  private $items = array();

  public function __construct()
  {
  }

/**
   * Returns a list of calendars for a principal.
   *
   * Every project is an array with the following keys:
   *  * id, a unique id that will be used by other functions to modify the
   *    calendar. This can be the same as the uri or a database key.
   *  * uri, which the basename of the uri with which the calendar is
   *    accessed.
   *  * principalUri. The owner of the calendar. Almost always the same as
   *    principalUri passed to this method.
   *
   * Furthermore it can contain webdav properties in clark notation. A very
   * common one is '{DAV:}displayname'.
   *
   * @param string $principalUri
   * @return array
   */
  public function getCalendarsForUser($principalUri)
  {
    if(isset($this->calendars[$principalUri]))
    {
      return $this->calendars[$principalUri];
    }

    $calendars = array();

    $ctags = pz::getConfig('calendar_ctag', array());

    $normal = pz::getUser()->getCalDavProjects();
    $jobs   = pz::getUser()->getCalDavJobsProjects();
    $projects = array();
    foreach(array($normal, $jobs) as $type => $array)
    {
      foreach($array as $project)
      {
        $projects[] = array($type, $project);
      }
    }
    usort($projects, function($a, $b) {
      list($typeA, $projectA) = $a;
      list($typeB, $projectB) = $b;
      $nameA = str_replace(array('ä', 'ö', 'ü', 'ß'), array('a', 'o', 'u', 's'), mb_strtolower($projectA->getName()));
      $nameB = str_replace(array('ä', 'ö', 'ü', 'ß'), array('a', 'o', 'u', 's'), mb_strtolower($projectB->getName()));
      $cmp = strcasecmp($nameA, $nameB);
      if($cmp == 0)
      {
        return $typeA <= $typeB ? -1 : 1;
      }
      return $cmp;
    });

    foreach($projects as $i => $t_p)
    {
      list($type, $project) = $t_p;
      switch($type)
      {
        case 1:
          $id = $project->getId() .'_jobs';
          $nameAdd = rex_i18n::msg('jobs');
          $supported = array('VEVENT', 'VTODO');
          break;
        /*case 2:
          $id = $project->getId() .'_todos';
          $nameAdd = rex_i18n::msg('todos');
          break;*/
        default:
          $id = $project->getId() .'_events';
          $nameAdd = rex_i18n::msg('events');
          $supported = array('VEVENT');
      }
      $label = $project->getLabel();
      $color = '#FFFFFF';
      if($label)
      {
        $color = $type == 1 ? $label->getBorder() : $label->getColor();
      }
      $calendars[] = array(
        'id' => $id,
        'uri' => $id,
        'principaluri' => $principalUri,
        '{'. Sabre_CalDAV_Plugin::NS_CALENDARSERVER .'}getctag' => isset($ctags[$project->getId()]) ? $ctags[$project->getId()] : 0,
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}supported-calendar-component-set' => new Sabre_CalDAV_Property_SupportedCalendarComponentSet($supported),
        '{DAV:}displayname' => '['. $nameAdd .'] '. $project->getName(),
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}calendar-description' => $project->getDescription(),
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}calendar-timezone' => self::getTimezone(),
        '{http://apple.com/ns/ical/}calendar-order' => $i,
        '{http://apple.com/ns/ical/}calendar-color' => $color
      );
    }

    $this->calendars[$principalUri] = $calendars;

    return $calendars;
  }

  /**
   * Returns all calendar objects within a calendar object.
   *
   * Every item contains an array with the following keys:
   *   * id - unique identifier which will be used for subsequent updates
   *   * calendardata - The iCalendar-compatible calnedar data
   *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
   *   * lastmodified - a timestamp of the last modification time
   *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
   *   '  "abcdef"')
   *   * calendarid - The calendarid as it was passed to this function.
   *
   * Note that the etag is optional, but it's highly encouraged to return for
   * speed reasons.
   *
   * The calendardata is also optional. If it's not returned
   * 'getCalendarObject' will be called later, which *is* expected to return
   * calendardata.
   *
   * @param string $calendarId
   * @return array
   */
  public function getCalendarObjects($calendarId)
  {
    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    $objects = array();

    $items = pz_calendar_event::getAllBase($rawCalendarId, $jobs);
    foreach($items as $item)
    {
      $objects[] = $this->getCalendarObjectMeta($calendarId, $item);
      $this->items[$calendarId][$item->getUri()] = $item;
    }
    if($jobs)
    {
      $items = pz_calendar_todo::getAllBase($rawCalendarId);
      foreach($items as $item)
      {
        $objects[] = $this->getCalendarObjectMeta($calendarId, $item);
        $this->items[$calendarId][$item->getUri()] = $item;
      }
    }

    return $objects;
  }

  /**
   * Returns information from a single calendar object, based on it's object
   * uri.
   *
   * The returned array must have the same keys as getCalendarObjects. The
   * 'calendardata' object is required here though, while it's not required
   * for getCalendarObjects.
   *
   * @param string $calendarId
   * @param string $objectUri
   * @return array
   */
  public function getCalendarObject($calendarId, $objectUri)
  {
    if(isset($this->objects[$calendarId][$objectUri]['calendardata']))
    {
      return $this->objects[$calendarId][$objectUri];
    }

    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    if(!isset($this->items[$calendarId][$objectUri]))
    {
      $this->items[$calendarId][$objectUri] = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);
      if(!$this->items[$calendarId][$objectUri])
        $this->items[$calendarId][$objectUri] = pz_calendar_todo::getByProjectUri($rawCalendarId, $objectUri);
    }
    $item = $this->items[$calendarId][$objectUri];
    if(!$item)
    {
      return null;
    }

    $calendar = $this->getCalendarObjectData($item);

    $this->objects[$calendarId][$objectUri] = $this->getCalendarObjectMeta($calendarId, $item);
    $this->objects[$calendarId][$objectUri]['calendardata'] = $calendar;
    return $this->objects[$calendarId][$objectUri];
  }

  private function getCalendarObjectMeta($calendarId, pz_calendar_item $item)
  {
    $uri = $item->getUri();
    if(isset($this->objects[$calendarId][$uri]))
    {
      return $this->objects[$calendarId][$uri];
    }
    $timestamp = $item->getUpdated()->getTimestamp();
    $etag = (string) $timestamp;
    if(pz::hasConfig('calendar_etag_add/'. $uri))
    {
      $etag .= '1';
      pz::removeConfig('calendar_etag_add/'. $uri);
    }
    $this->objects[$calendarId][$uri] = array(
      'id' => $item->getId(),
      'uri' => $uri,
      'lastmodified' => $timestamp,
      'calendarid' => $calendarId,
      'etag' => '"'. $etag .'"'
    );
    return $this->objects[$calendarId][$uri];
  }

  private function getCalendarObjectData(pz_calendar_item $item)
  {
    $calendar = new Sabre_VObject_Component('vcalendar');
    $calendar->version = '2.0';
    $calendar->prodid = '-//prozer 2.0//';
    $calendar->calscale = 'GREGORIAN';

    if($item instanceof pz_calendar_todo)
    {
      $this->createTodoComponent($calendar, $item);
    }
    else
    {
      $this->createEventComponent($calendar, $item);
    }

    return $calendar->serialize();
  }

  private function createTodoComponent(Sabre_VObject_Component $calendar, pz_calendar_todo $pzTodo)
  {
    $todo = new Sabre_VObject_Component('vtodo');
    $todo->uid = substr($pzTodo->getUri(), 0, -4);
    $todo->created = self::getDateTime('created', $pzTodo->getCreated(), Sabre_VObject_Property_DateTime::UTC);
    $todo->dtstamp = self::getDateTime('dtstamp', $pzTodo->getUpdated(), Sabre_VObject_Property_DateTime::UTC);
    $todo->sequence = $pzTodo->getSequence();
    $todo->summary = $pzTodo->getTitle();
    if($description = $pzTodo->getDescription())
      $todo->description = $description;
    $todo->priority = $pzTodo->getPriority();
    if($order = $pzTodo->getOrder())
      $todo->__set('x-apple-sort-order', $order);
    if($start = $pzTodo->getFrom())
      $todo->dtstart = self::getDateTime('dtstart', $start);
    if($due = $pzTodo->getDue())
      $todo->due = self::getDateTime('due', $due);
    if($completed = $pzTodo->getCompleted())
    {
      $todo->status = 'COMPLETED';
      $todo->completed = self::getDateTime('completed', $completed, Sabre_VObject_Property_DateTime::UTC);
    }
    if($pzTodo->hasRule())
    {
      $pzRule = pz_calendar_rule::get($pzTodo);
      $this->createRuleComponent($todo, $pzRule);
    }
    $this->createAlarmComponents($todo, $pzTodo);
    $calendar->add($todo);
  }

  private function createEventComponent(Sabre_VObject_Component $calendar, pz_calendar_event $pzEvent)
  {
    $event = $this->createVEvent($pzEvent);
    if(!$pzEvent->isBooked() && $pzEvent->hasRule())
    {
      $pzRule = pz_calendar_rule::get($pzEvent);
      $this->createRuleComponent($event, $pzRule);
      foreach($pzRule->getExceptions() as $exception)
      {
        $exdate = new Sabre_VObject_Property_DateTime('exdate');
        $exdate->setDateTime($exception);
        $event->add($exdate);
      }
    }
    $calendar->add($event);
    if(!$pzEvent->isBooked() && $pzEvent->hasRule())
    {
      $exceptions = pz_calendar_rule_event::getAllExceptions($pzRule);
      foreach($exceptions as $pzRuleEvent)
      {
        $exEvent = $this->createVEvent($pzRuleEvent);
        $exEvent->uid = $event->uid;
        $exEvent->created = $event->created;
        $dateType = $pzEvent->isAllDay() ? Sabre_VObject_Property_DateTimeDateTime::DATE : Sabre_VObject_Property_DateTime::UTC;
        $exEvent->__set('recurrence-id', self::getDateTime('recurrence-id', $pzRuleEvent->getBaseFrom(), $dateType));
        $calendar->add($exEvent);
      }
    }
  }

  private function createVEvent(pz_calendar_event $pzEvent)
  {
    $event = new Sabre_VObject_Component('vevent');
    $event->uid = substr($pzEvent->getUri(), 0, -4);
    $event->created = self::getDateTime('created', $pzEvent->getCreated(), Sabre_VObject_Property_DateTime::UTC);
    $event->dtstamp = self::getDateTime('dtstamp', $pzEvent->getUpdated(), Sabre_VObject_Property_DateTime::UTC);
    $event->sequence = $pzEvent->getSequence();
    $dtend = $pzEvent->getTo();
    $dateType = Sabre_VObject_Property_DateTime::LOCALTZ;
    if($pzEvent->isAllDay())
    {
      $dtend->modify('+1 day');
      $dateType = Sabre_VObject_Property_DateTime::DATE;
    }
    $event->dtstart = self::getDateTime('dtstart', $pzEvent->getFrom(), $dateType);
    $event->dtend = self::getDateTime('dtend', $dtend, $dateType);
    $event->summary = $pzEvent->getTitle();
    if($location = $pzEvent->getLocation())
      $event->location = $location;
    if($description = $pzEvent->getDescription())
      $event->description = $description;
    if($url = $pzEvent->getUrl())
      $event->url = $url;

    foreach ($pzEvent->getReleasedClips() as $pzClip)
    {
      $clip = new Sabre_VObject_Property('attach', $pzClip->getUri());
      if ($type = $pzClip->getContentType())
        $clip['fmttype'] = $type;
      $clip['value'] = 'URI';
      $clip['filename'] = $pzClip->getFilename();
      $clip['size'] = $pzClip->getContentLength();
      $clip['managed-id'] = $pzClip->getId();
      $event->add($clip);
    }
    $organizer = $pzEvent->getUserId();
    $pzAttendees = $pzEvent->getAttendees();
    if(pz::getUser()->getId() != $organizer || count($pzAttendees) > 0)
    {
      $user = pz_user::get($organizer);
      if($user)
      {
        $event->organizer = 'mailto:'. $user->getEmail();
        $event->organizer['cn'] = $user->getName();
        $attendee = new Sabre_VObject_Property('attendee', 'mailto:'. $user->getEmail());
        $attendee['cn'] = $user->getName();
        $attendee['partstat'] = 'ACCEPTED';
        $attendee['role'] = 'CHAIR';
        $event->add($attendee);
        $userIsAttendee = false;
        foreach($pzAttendees as $pzAttendee)
        {
          $attendee = new Sabre_VObject_Property('attendee', 'mailto:'. $pzAttendee->getEmail());
          $attendee['cn'] = $pzAttendee->getName();
          $attendee['partstat'] = $pzAttendee->getStatus();
          $event->add($attendee);
        }
      }
    }
    $this->createAlarmComponents($event, $pzEvent);
    return $event;
  }

  private function createAlarmComponents(Sabre_VObject_Component $component, pz_calendar_item $item)
  {
    foreach($item->getAlarms() as $pzAlarm)
    {
      $alarm = new Sabre_VObject_Component('valarm');
      $alarm->__set('x-wr-alarmuid', $pzAlarm->getUid());
      $alarm->action = $pzAlarm->getAction();
      if($pzAlarm->triggerIsInterval())
      {
        $alarm->trigger = $pzAlarm->getTriggerString();
      }
      else
      {
        $alarm->trigger = self::getDateTime('trigger', $pzAlarm->getTrigger(), Sabre_VObject_Property_DateTime::UTC);
        $alarm->trigger['value'] = 'DATE-TIME';
      }
      if($description = $pzAlarm->getDescription())
        $alarm->description = $description;
      if($summary = $pzAlarm->getSummary())
        $alarm->summary = $summary;
      if($emails = $pzAlarm->getEmails())
      {
        foreach($emails as $email)
        {
          $alarm->add(new Sabre_VObject_Property('attendee', 'mailto:'. $email));
        }
      }
      if($attachment = $pzAlarm->getAttachment())
        $alarm->attachment = Sabre_VObject_Reader::read($attachment);
      if($location = $pzAlarm->getLocation())
        $alarm->location = $location;
      if($structuredLocation = $pzAlarm->getStructuredLocation())
        $alarm->__set('x-apple-structured-location', Sabre_VObject_Reader::read($structuredLocation));
      if($proximity = $pzAlarm->getProximity())
        $alarm->__set('x-apple-proximity', $proximity);
      if($acknowledged = $pzAlarm->getAckknowledged())
        $alarm->acknowledged = self::getDateTime('acknowledged', $acknowledged, Sabre_VObject_Property_DateTime::UTC);
      if($pzAlarm->getRelatedId() && $related = $pzAlarm->getRelated())
        $alarm->__set('related-to', $related->getUid());
      $component->add($alarm);
    }
  }

  private function createRuleComponent(Sabre_VObject_Component $component, pz_calendar_rule $pzRule)
  {
    $rule = 'FREQ='. $pzRule->getFrequence();
    if(($interval = $pzRule->getInterval()) > 1)
      $rule .= ';INTERVAL='. $interval;
    if($count = $pzRule->getCount())
      $rule .= ';COUNT='. $count;
    if($end = $pzRule->getEnd())
      $rule .= ';UNTIL='. self::getDateTime('until', $end);
    if($weekDays = $pzRule->getWeekDays())
    {
      $names = array(null, 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');
      foreach($weekDays as &$weekDay)
      $weekDay = $names[$weekDay];
      $rule .= ';BYDAY='. implode(',', $weekDays);
    }
    if($days = $pzRule->getDays())
      $rule .= ';BYMONTHDAY='. implode(',', $days);
    if($months = $pzRule->getMonths())
      $rule .= ';BYMONTH='. implode(',', $months);
    if($nth = $pzRule->getNth())
      $rule .= ';BYSETPOS='. $nth;
    $component->rrule = $rule;
  }

  /**
   * Creates a new calendar object.
   *
   * @param string $calendarId
   * @param string $objectUri
   * @param string $calendarData
   * @return void
   */
  public function createCalendarObject($calendarId, $objectUri, $calendarData)
  {
    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    $sql = rex_sql::factory();
    $sql->setQuery('
      SELECT project_id FROM '. pz_calendar_event::TABLE .' WHERE project_id = :project_id AND uri = :uri AND user_id != :user_id
      UNION
      SELECT project_id FROM '. pz_calendar_todo::TABLE .' WHERE project_id = :project_id AND uri = :uri
    ', array(':project_id' => $rawCalendarId, ':uri' => $objectUri, ':user_id' => pz::getUser()->getId()));
    if($sql->getRows() > 0)
    {
      pz::setConfig('calendar_etag_add/'. $objectUri, 1);
      self::incrementCtag($sql->getValue('project_id'));
      throw new Sabre_DAV_Exception_Forbidden('Forbidden!1');
    }

    $calendar = Sabre_VObject_Reader::read($calendarData);
    if($event = $calendar->vevent[0])
    {
      $this->createPzEvent($rawCalendarId, $jobs, $objectUri, $event);
    }
    elseif($jobs && $todo = $calendar->vtodo[0])
    {
      $this->createPzTodo($rawCalendarId, $objectUri, $todo);
    }
    else
    {
      throw new Sabre_DAV_Exception_Forbidden('Forbidden!');
    }
  }

  private function createPzEvent($projectId, $jobs, $objectUri, $event)
  {
    $pzEvent = pz_calendar_event::create();
    $this->setEventValues($projectId, $objectUri, $event, $pzEvent);
    $pzEvent->setUserId(pz::getUser()->getId());
    $pzEvent->setBooked($jobs);
    $pzEvent->save();
    if(!$jobs && isset($event->rrule))
    {
      $pzRule = pz_calendar_rule::create($pzEvent);
      $this->setRuleValues($event, $pzRule);
      $pzRule->save();

      if(count($calendar->vevent) > 1)
      {
        $events = $this->getSortedEvents($calendar->vevent);
        $count = count($events);
        for($i = 0; $i < $count; ++$i)
        {
          $event = $events[$i];
          $recurrenceId = $event->__get('recurrence-id');
          $mode = isset($recurrenceId['RANGE']) && $recurrenceId['RANGE'] == 'THISANDFUTURE' ? pz_calendar_rule_event::FUTURE : pz_calendar_rule_event::THIS;
          $recurrenceId = new Sabre_VObject_Property_DateTime('RECURRENCE-ID', $recurrenceId);
          $pzRuleEvent = pz_calendar_rule_event::create($pzRule, $recurrenceId->getDateTime()->setTimezone(self::getDateTimeZone()));
          $this->setRuleEventValues($event, $pzRuleEvent, $pzEvent);
          if(isset($event->rrule) && $mode == pz_calendar_rule_event::FUTURE)
          {
            $this->setRuleValues($event, $pzRule);
          }
          $pzRuleEvent->save($mode);
          $pzRule = $pzRuleEvent->getRule();
        }
      }
    }
    // $pzEvent->saveToHistory('create');
  }

  private function createPzTodo($projectId, $objectUri, $todo)
  {
    $pzTodo = pz_calendar_todo::create();
    $this->setTodoValues($projectId, $objectUri, $todo, $pzTodo);
    $pzTodo->setUserId(pz::getUser()->getId());
    $pzTodo->save();
    if(isset($todo->rrule))
    {
      $pzRule = pz_calendar_rule::create($pzTodo);
      $this->setRuleValues($todo, $pzRule);
      $pzRule->save();
    }
  }

  /**
   * Updates an existing calendarobject, based on it's uri.
   *
   * @param string $calendarId
   * @param string $objectUri
   * @param string $calendarData
   * @return void
   */
  public function updateCalendarObject($calendarId, $objectUri, $calendarData)
  {
    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    $calendar = Sabre_VObject_Reader::read($calendarData);
    if($event = $calendar->vevent)
    {
      $this->updatePzEvent($rawCalendarId, $jobs, $objectUri, $event);
    }
    elseif($jobs && $todo = $calendar->vtodo[0])
    {
      $this->updatePzTodo($rawCalendarId, $objectUri, $todo);
    }
    else
    {
      throw new Sabre_DAV_Exception_NotFound('Not found!');
    }
  }

  private function updatePzTodo($rawCalendarId, $objectUri, Sabre_VObject_Component $todo)
  {
    $pzTodo = pz_calendar_todo::getByProjectUri($rawCalendarId, $objectUri);
    $this->setTodoValues($rawCalendarId, $objectUri, $todo, $pzTodo);
    $pzTodo->save();
    $hasRule = $pzTodo->hasRule();
    if(isset($todo->rrule))
    {
      $pzRule = $hasRule ? pz_calendar_rule::get($pzTodo) : pz_calendar_rule::create($pzTodo);
      $this->setRuleValues($todo, $pzRule);
      $pzRule->save();
    }
    elseif($hasRule)
    {
      rex_sql::factory()->setQuery('
      	DELETE r
      	FROM '. pz_calendar_rule::TABLE .' r
      	WHERE r.todo_id = ?
      ', array($pzTodo->getId()));
    }
  }

  private function updatePzEvent($rawCalendarId, $jobs, $objectUri, Sabre_VObject_Component $event)
  {
    $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);
    $add = $event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone())->diff($pzEvent->getFrom());
    $this->setEventValues($rawCalendarId, $objectUri, $event, $pzEvent);
    $pzEvent->save();
    $hasRule = $pzEvent->hasRule();
    if(!$jobs && isset($event->rrule))
    {
      $pzRule = $hasRule ? pz_calendar_rule::get($pzEvent) : pz_calendar_rule::create($pzEvent);
      $this->setRuleValues($event, $pzRule);
      $pzRule->save();

      $eventIds = array($pzEvent->getId());
      $ruleIds = array($pzRule->getId() => true);
      if(count($event) > 1)
      {
        $events = $this->getSortedEvents($event);
        $count = count($events);
        for($i = 0; $i < $count; ++$i)
        {
          $event = $events[$i];
          $recurrenceId = $event->__get('recurrence-id');
          $mode = isset($recurrenceId['RANGE']) && $recurrenceId['RANGE'] == 'THISANDFUTURE' ? pz_calendar_rule_event::FUTURE : pz_calendar_rule_event::THIS;
          $recurrenceId = new Sabre_VObject_Property_DateTime('RECURRENCE-ID', $recurrenceId);
          $pzRuleEvent = pz_calendar_rule_event::getByBaseFrom($pzRule, $recurrenceId->getDateTime()->setTimezone(self::getDateTimeZone())->add($add));
          $pzRuleEvent->setBaseFrom($recurrenceId->getDateTime()->sub($add));
          $this->setRuleEventValues($event, $pzRuleEvent, $pzEvent);
          if(isset($event->rrule) && $mode == pz_calendar_rule_event::FUTURE)
          {
            $this->setRuleValues($event, $pzRule);
          }
          $pzRuleEvent->save($mode);
          $eventIds[] = $pzRuleEvent->getId(true);
          $pzRule = $pzRuleEvent->getRule();
          $ruleIds[$pzRule->getId()] = true;
        }
      }
      if($hasRule)
      {
        $ruleIds = array_keys($ruleIds);
        $eventIn = implode(',', array_fill(0, count($eventIds), '?'));
        $ruleIn = implode(',', array_fill(0, count($ruleIds), '?'));
        $params = array_merge($ruleIds, $eventIds);
        rex_sql::factory()->setQuery('
        	DELETE e, at, al
        	FROM '. pz_calendar_event::TABLE .' e
        	LEFT JOIN '. pz_calendar_attendee::TABLE .' at
      		ON at.event_id = e.id
        	LEFT JOIN '. pz_calendar_alarm::TABLE .' al
        	ON al.event_id = e.id
        	WHERE rule_id IN ('. $ruleIn .') AND e.id NOT IN ('. $eventIn .')
        ', $params);
      }
    }
    elseif($hasRule)
    {
      rex_sql::factory()->setQuery('
      	DELETE r, e, at, al
      	FROM '. pz_calendar_rule::TABLE .' r
      	LEFT JOIN '. pz_calendar_event::TABLE .' e
      	ON e.rule_id = r.id AND e.id != r.event_id
      	LEFT JOIN '. pz_calendar_attendee::TABLE .' at
      	ON at.event_id = e.id
      	LEFT JOIN '. pz_calendar_alarm::TABLE .' al
      	ON al.event_id = e.id
      	WHERE r.event_id = ?
      ', array($pzEvent->getId()));
    }
    $pzEvent->saveToHistory('update');
  }

  private function setTodoValues($calendarId, $objectUri, Sabre_VObject_Component $todo, pz_calendar_todo $pzTodo)
  {
    $pzTodo
      ->setUri($objectUri)
      ->setProjectId($calendarId)
      ->setTitle((string) $todo->summary)
      ->setDescription((string) $todo->description)
      ->setPriority((int) (string) $todo->priority);
    if($order = $todo->__get('x-apple-sort-order'))
      $pzTodo->setOrder((int) (string) $order);
    else
      $pzTodo->setOrder(null);
    if($start = $todo->dtstart)
      $pzTodo->setFrom($start->getDateTime()->setTimezone(self::getDateTimeZone()));
    else
      $pzTodo->setFrom(null);
    if($due = $todo->due)
      $pzTodo->setDue($due->getDateTime()->setTimezone(self::getDateTimeZone()));
    else
      $pzTodo->setDue(null);
    if($completed = $todo->completed)
      $pzTodo->setCompleted($completed->getDateTime()->setTimezone(self::getDateTimeZone()));
    else
      $pzTodo->setCompleted(null);
    $pzTodo->setAlarms($this->getAlarms($todo));
  }

  private function setEventValues($calendarId, $objectUri, Sabre_VObject_Component $event, pz_calendar_event $pzEvent)
  {
    if($event->dtend->getDateType() == Sabre_VObject_Property_DateTime::DATE)
    {
      $event->dtend->getDateTime()->modify('-1 day');
    }

    $pzEvent
      ->setUri($objectUri)
      ->setProjectId($calendarId)
      ->setSequence((string) $event->sequence)
      ->setTitle((string) $event->summary)
      ->setLocation((string) $event->location)
      ->setDescription((string) $event->description)
      ->setUrl((string) $event->url)
      ->setFrom($event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone()))
      ->setTo($event->dtend->getDateTime()->setTimezone(self::getDateTimeZone()))
      ->setAllDay($event->dtstart->getDateType() == Sabre_VObject_Property_DateTime::DATE);

    if(!$pzEvent->isBooked())
    {
      $pzEvent->setAttendees($this->getAttendees($event));
      $pzEvent->setAlarms($this->getAlarms($event));
    }
  }

  private function setRuleEventValues(Sabre_VObject_Component $event, pz_calendar_rule_event $pzRuleEvent, pz_calendar_event $pzEvent)
  {
    if($event->dtend->getDateType() == Sabre_VObject_Property_DateTime::DATE)
    {
      $event->dtend->getDateTime()->modify('-1 day');
    }
    $pzRuleEvent
      ->setSequence((string) $event->sequence)
      ->setFrom($event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone()))
      ->setTo($event->dtend->getDateTime()->setTimezone(self::getDateTimeZone()));
    if(isset($event->summary) && $pzEvent->getTitle() != (string) $event->summary)
      $pzRuleEvent->setTitle((string) $event->summary);
    if(isset($event->location) && $pzEvent->getLocation() != (string) $event->location)
      $pzRuleEvent->setLocation((string) $event->location);
    if(isset($event->description) && $pzEvent->getDescription() != (string) $event->description)
      $pzRuleEvent->setDescription((string) $event->description);
    if(isset($event->url) && $pzEvent->getUrl() != (string) $event->url)
      $pzRuleEvent->setUrl((string) $event->url);
    if($pzEvent->isAllDay() xor $event->dtstart->getDateType() == Sabre_VObject_Property_DateTime::DATE)
      $pzRuleEvent->setAllDay($event->dtstart->getDateType() == Sabre_VObject_Property_DateTime::DATE);
    $pzRuleEvent->setAttendees($this->getAttendees($event));
    $pzRuleEvent->setAlarms($this->getAlarms($event));
  }

  private function getAttendees(Sabre_VObject_Component $event)
  {
    $attendees = $event->attendee;
    $pzAttendees = array();
    if(count($attendees) > 0)
    {
      $organizer = str_ireplace('mailto:', '', strtolower($event->organizer));
      $sqlLogin = rex_sql::factory();
      $sqlLogin->prepareQuery('SELECT id, name, email FROM pz_user WHERE LOWER(login) = ? LIMIT 2');
      $sqlEmail = rex_sql::factory();
      $sqlEmail->prepareQuery('
        SELECT DISTINCT(u.id), u.name, u.email
        FROM pz_user u
        LEFT JOIN pz_address_field f1
        ON u.email = f1.value AND f1.type = "EMAIL"
        LEFT JOIN pz_address_field f2
        ON f1.address_id = f2.address_id AND f2.type = "EMAIL"
        WHERE LOWER(email) = :email OR f2.value = :email
        LIMIT 2
      ');
      //$userEmail = strtolower(pz::getUser()->getEmail());
      foreach($attendees as $attendee)
      {
        $email = str_ireplace('mailto:', '', $attendee);
        if(strtolower($email) != $organizer /*&& strtolower($email) != $userEmail*/)
        {
          $userId = '';
          $name = (string) $attendee['cn'];
          if(strpos($email, '@') === false)
          {
            $sqlLogin->execute(array(strtolower($name)));
            if($sqlLogin->getRows() != 1)
              continue;
            $name = $sqlLogin->getValue('name');
            $email = $sqlLogin->getValue('email');
            $userId = $sqlLogin->getValue('id');
          }
          else
          {
            $sqlEmail->execute(array(':email' => strtolower($email)));
            if($sqlEmail->getRows() == 1)
            {
              $userId = $sqlEmail->getValue('id');
              $name = $sqlEmail->getValue('name');
              $email = $sqlEmail->getValue('email');
            }
          }

          $pzAttendee = pz_calendar_attendee::create();
          $pzAttendee->setStatus((string) $attendee['partstat']);
          $pzAttendee->setEmail($email);
          $pzAttendee->setName($name);
          if($userId)
          {
            $pzAttendee->setUserId($userId);
          }
          $pzAttendees[] = $pzAttendee;
        }
      }
    }
    return $pzAttendees;
  }

  private function getAlarms(Sabre_VObject_Component $component)
  {
    $pzAlarms = array();
    if(count($component->valarm) > 0)
    {
      foreach($component->valarm as $alarm)
      {
        $pzAlarm = pz_calendar_alarm::create();
        if($uid = $alarm->__get('x-wr-alarmuid'))
          $pzAlarm->setUid((string) $uid);
        $pzAlarm->setAction((string) $alarm->action);
        if(isset($alarm->trigger['value']) && $alarm->trigger['value'] == 'DATE-TIME')
        {
          $trigger = new Sabre_VObject_Property_DateTime('trigger', (string) $alarm->trigger);
          $pzAlarm->setTrigger($trigger->getDateTime()->setTimezone(self::getDateTimeZone()));
        }
        else
        {
          $trigger = (string) $alarm->trigger;
          if(preg_match('/([PYM])([0-9]+)W(?:([0-9]+)D)?/', $trigger, $matches))
          {
            $days = 7 * $matches[2] + (isset($matches[3]) ? $matches[3] : 0);
            $trigger = str_replace($matches[0], $matches[1] . $days .'D', $trigger);
          }
          $dateInterval = new DateInterval(ltrim($trigger, '-'));
          $dateInterval->invert = strpos($trigger, '-') === 0;
          $pzAlarm->setTrigger($dateInterval);
        }
        if($alarm->description)
          $pzAlarm->setDescription((string) $alarm->description);
        if($alarm->summary)
          $pzAlarm->setSummary((string) $alarm->summary);
        if($alarm->attendee)
        {
          $emails = array();
          foreach($alarm->attendee as $attendee)
            $emails[] = (string) $attendee;
          $pzAlarm->setEmails($emails);
        }
        if($alarm->attach)
          $pzAlarm->setAttachment($alarm->attach->serialize());
        if($alarm->location)
          $pzAlarm->setLocation((string) $alarm->location);
        if($structuredLocation = $alarm->__get('x-apple-structured-location'))
          $pzAlarm->setStructuredLocation($structuredLocation->serialize());
        if($proximity = $alarm->__get('x-apple-proximity'))
          $pzAlarm->setProximity((string) $proximity);
        if($acknowledged = $alarm->acknowledged)
          $pzAlarm->setAcknowledged($acknowledged->getDateTime()->setTimezone(self::getDateTimeZone()));
        if(($relatedUri = $alarm->__get('related-to')) && ($related = pz_calendar_alarm::getByUid($relatedUri)))
          $pzAlarm->setRelatedId($related->getId());

        $pzAlarms[] = $pzAlarm;
      }
    }
    return $pzAlarms;
  }

  private function setRuleValues(Sabre_VObject_Component $component, pz_calendar_rule $pzRule)
  {
    parse_str(str_replace(';', '&', $component->rrule), $rule);
    $freq = self::constant($rule['FREQ']);
    $pzRule
      ->setFrequence($freq)
      ->setInterval(max(1, $rule['INTERVAL']))
      ->setCount(null)
      ->setEnd(null)
      ->setWeekDays(array())
      ->setDays(array())
      ->setMonths(array())
      ->setNth(0)
      ->setExceptions(array());
    if(isset($rule['COUNT']))
    {
    $pzRule->setCount($rule['COUNT']);
    }
    if(isset($rule['UNTIL']))
    {
      $end = new Sabre_VObject_Property_DateTime('UNTIL', $rule['UNTIL']);
      $pzRule->setEnd($end->getDateTime());
    }
    if(($freq == pz_calendar_rule::WEEKLY || isset($rule['BYSETPOS'])) && isset($rule['BYDAY']))
    {
      $byday = array_map('self::constant', explode(',', $rule['BYDAY']));
      $pzRule->setWeekDays($byday);
    }
    if(isset($rule['BYSETPOS']))
    {
      $pzRule->setNth($rule['BYSETPOS']);
    }
    elseif(isset($rule['BYDAY']) && preg_match('/((?:\+|-)?[0-9])([A-Z]{2})/', $rule['BYDAY'], $matches))
    {
      $pzRule
        ->setNth($matches[1])
        ->setWeekDay(self::constant($matches[2]));
    }
    if(isset($rule['BYMONTHDAY']))
    {
      $pzRule->setDays(explode(',', $rule['BYMONTHDAY']));
    }
    if(isset($rule['BYMONTH']))
    {
      $pzRule->setMonths(explode(',', $rule['BYMONTH']));
    }
    if(isset($component->exdate))
    {
      $exceptions = array();
      foreach($component->exdate as $exdate)
      {
        foreach($exdate->getDateTimes() as $dt)
          $exceptions[] = $dt->setTimeZone(self::getDateTimeZone());
      }
      $pzRule->setExceptions($exceptions);
    }
  }

  private function getSortedEvents(Traversable $events)
  {
    $events = iterator_to_array($events);
    unset($events[0]);
    usort($events,
      function($a, $b)
      {
        return $a->dtstart->getDateTime() < $b->dtstart->getDateTime() ? -1 : 1;
      }
    );
    return $events;
  }

  /**
   * Deletes an existing calendar object.
   *
   * @param string $calendarId
   * @param string $objectUri
   * @return void
   */
  public function deleteCalendarObject($calendarId, $objectUri)
  {
    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    $sql = rex_sql::factory();

    $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);

    $sql->setQuery('
      DELETE b, r, e, at, al
      FROM '. pz_calendar_event::TABLE .' b
      LEFT JOIN '. pz_calendar_rule::TABLE .' r
      ON b.id = r.event_id
      LEFT JOIN '. pz_calendar_event::TABLE .' e
      ON e.rule_id = r.id
      LEFT JOIN '. pz_calendar_attendee::TABLE .' at
      ON at.event_id = e.id OR at.event_id = b.id
      LEFT JOIN '. pz_calendar_alarm::TABLE .' al
      ON al.event_id = e.id OR al.event_id = b.id
      WHERE b.project_id = ? AND b.uri = ? AND b.user_id = ?
    ', array($rawCalendarId, $objectUri, pz::getUser()->getId()));

    if($pzEvent)
      $pzEvent->saveToHistory('delete');

    if($jobs)
    {
      $sql->setQuery('
        DELETE b, al, r
        FROM '. pz_calendar_todo::TABLE .' b
        LEFT JOIN '. pz_calendar_alarm::TABLE .' al
        ON al.todo_id = b.id
        LEFT JOIN '. pz_calendar_rule::TABLE .' r
        ON r.todo_id = b.id
        WHERE b.project_id = ? AND b.uri = ?
      ', array($rawCalendarId, $objectUri));
    }
    self::incrementCtag($rawCalendarId);
  }

  static public function import($calendarId, $data)
  {
    $calendar = Sabre_VObject_Reader::read(str_replace('W. Europe Standard Time', 'Europe/London', $data));
    foreach($calendar->vevent as $event)
    {
      if(isset($event->organizer))
      {
        $organizer = str_replace('mailto:', '', strtolower($event->organizer));
        if($organizer != strtolower(pz::getUser()->getEmail()))
        {
          $attendee = new Sabre_VObject_Property('attendee', (string) $event->organizer);
          $attendee['cn'] = (string) $event->organizer['cn'];
          $attendee['partstat'] = 'ACCEPTED';
          $event->add($attendee);
          unset($event->organizer);
        }
      }
      unset($event->valarm);
    }
    $data = $calendar->serialize();

    $backend = new self;
    if(preg_match('/^UID:(.*)\s?$/Umi', $data, $matches))
    {
      $uri = $matches[1] .'.ics';
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT id FROM pz_calendar_event WHERE uri = ?', array($uri));
      if($sql->getRows() == 0)
      {
        $backend->createCalendarObject($calendarId, $uri, $data);
      }
      else
      {
        $backend->updateCalendarObject($calendarId, $uri, $data);
      }
    }
    else
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT UPPER(UUID()) as uid');
      $backend->createCalendarObject($calendarId, $sql->getValue('uid') .'.ics', $data);
    }
  }

  static public function export(pz_calendar_event $event)
  {
    $backend = new self;
    return $backend->getCalendarObjectData($event);
  }

  static private function splitCalendarId($calendarId)
  {
    $jobs = false;
    $todos = false;
    $parts = array_pad(explode('_', $calendarId, 2), 2, null);
    return array($parts[0], $parts[1] == 'jobs');
  }

  static public function incrementCtag($calendarId)
  {
    $ctags = pz::getConfig('calendar_ctag', array());
    $ctags[$calendarId] = isset($ctags[$calendarId]) ? ($ctags[$calendarId] + 1) : 1;
    pz::setConfig('calendar_ctag', $ctags);
  }

  static private function constant($constant)
  {
    return constant('pz_calendar_rule::'. $constant);
  }

  static private function getDateTime($name, DateTime $dateTime, $dateType = Sabre_VObject_Property_DateTime::LOCALTZ)
  {
    $dt = new Sabre_VObject_Property_DateTime($name);
    $dt->setDateTime($dateTime, $dateType);
    return $dt;
  }

  static private function getDateTimeZone()
  {
    static $timezone = null;
    return $timezone ?: $timezone = new DateTimeZone('Europe/Berlin');
  }

  static private function getTimezone()
  {
    return 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//prozer 2.0//
CALSCALE:GREGORIAN
BEGIN:VTIMEZONE
TZID:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:CEST
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:CET
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
END:VCALENDAR';
  }

  public function createCalendar($principalUri,$calendarUri, array $properties)
  {
    return false;
  }

  public function updateCalendar($calendarId, array $properties)
  {
    return false;
  }

  public function deleteCalendar($calendarId)
  {
    list($calendarId, $jobs, $todos) = self::splitCalendarId($calendarId);

    $sql = rex_sql::factory()
      ->setTable('pz_project_user')
      ->setWhere(array('project_id' => $calendarId, 'user_id' => pz::getUser()->getId()));
    if($jobs)
      $sql->setValue('caldav_jobs', 0);
    else
      $sql->setValue('caldav', 0);
    $sql->update();
  }
}
