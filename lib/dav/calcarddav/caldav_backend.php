<?php

class pz_sabre_caldav_backend extends Sabre_CalDAV_Backend_Abstract
{
  private $calendars = array();

  private $objects = array();

  private $events = array();

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
    $jobs = pz::getUser()->getCalDavJobsProjects();
    $countNormal = count($normal);
    $countJobs = count($jobs);
    $count = $countNormal + $countJobs;
    for($order = 1, $i = 0, $j = 0, $nextJob = false; $order <= $count; ++$order)
    {
      if($j >= $countJobs || !$nextJob && $i < $countNormal && $normal[$i]->getName() <= $jobs[$j]->getName())
      {
        $project = $normal[$i];
        $id = $project->getId();
        $nameAdd = rex_i18n::msg('events');
        ++$i;
        $nextJob = $j < $countJobs && $project->getId() == $jobs[$j]->getId();
        $job = false;
      }
      else
      {
        $project = $jobs[$j];
        $id = $project->getId() .'_jobs';
        $nameAdd = rex_i18n::msg('jobs');
        ++$j;
        $nextJob = false;
        $job = true;
      }
      $label = $project->getLabel();
      $color = '#FFFFFF';
      if($label)
      {
        $color = $job ? $label->getBorder() : $label->getColor();
      }
      $calendars[] = array(
        'id' => $id,
        'uri' => 'prozer_project_'. $id,
        'principaluri' => $principalUri,
        '{'. Sabre_CalDAV_Plugin::NS_CALENDARSERVER .'}getctag' => isset($ctags[$project->getId()]) ? $ctags[$project->getId()] : 0,
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}supported-calendar-component-set' => new Sabre_CalDAV_Property_SupportedCalendarComponentSet(array('VEVENT')),
        '{DAV:}displayname' => '['. $nameAdd .'] '. $project->getName(),
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}calendar-description' => $project->getDescription(),
        '{'. Sabre_CalDAV_Plugin::NS_CALDAV .'}calendar-timezone' => self::getTimezone(),
        '{http://apple.com/ns/ical/}calendar-order' => $order,
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

    $events = pz_calendar_event::getAllBase($rawCalendarId, $jobs);
    $objects = array();
    foreach($events as $event)
    {
      $objects[] = $this->getCalendarObjectMeta($calendarId, $event);
      $this->events[$calendarId][$event->getUri()] = $event;
    }
    return $objects;
  }

  public function getCalendarObjectMeta($calendarId, pz_calendar_event $event)
  {
    $uri = $event->getUri();
    if(isset($this->objects[$calendarId][$uri]))
    {
      return $this->objects[$calendarId][$uri];
    }
    $timestamp = $event->getUpdated()->getTimestamp();
    $etag = (string) $timestamp;
    if(pz::hasConfig('calendar_etag_add/'. $uri))
    {
      $etag .= '1';
      pz::removeConfig('calendar_etag_add/'. $uri);
    }
    $this->objects[$calendarId][$uri] = array(
    	'id' => $event->getId(),
      'uri' => $uri,
      'lastmodified' => $timestamp,
      'calendarid' => $calendarId,
      'etag' => '"'. $etag .'"'
    );
    return $this->objects[$calendarId][$uri];
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

    if(!isset($this->events[$calendarId][$objectUri]))
    {
      $this->events[$calendarId][$objectUri] = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);
    }
    /* @var $pzEvent pz_calendar_event */
    $pzEvent = $this->events[$calendarId][$objectUri];
    if(!$pzEvent)
    {
      return null;
    }

    $calendar = $this->getCalendarObjectData($pzEvent);

    $this->objects[$calendarId][$objectUri] = $this->getCalendarObjectMeta($calendarId, $pzEvent);
    $this->objects[$calendarId][$objectUri]['calendardata'] = $calendar;
    return $this->objects[$calendarId][$objectUri];
  }

  public function getCalendarObjectData(pz_calendar_event $pzEvent)
  {
    $calendar = new Sabre_VObject_Component('vcalendar');
    $calendar->version = '2.0';
    $calendar->prodid = '-//prozer 2.0//';
    $calendar->calscale = 'GREGORIAN';
    $event = $this->createVEvent($pzEvent);
    if($pzEvent->hasRule())
    {
      $pzRule = pz_calendar_rule::get($pzEvent);
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
      $event->rrule = $rule;
      foreach($pzRule->getExceptions() as $exception)
      {
        $exdate = new Sabre_VObject_Element_DateTime('exdate');
        $exdate->setDateTime($exception);
        $event->add($exdate);
      }
    }
    $calendar->add($event);
    if($pzEvent->hasRule())
    {
      $exceptions = pz_calendar_rule_event::getAllExceptions($pzRule);
      foreach($exceptions as $pzRuleEvent)
      {
        $exEvent = $this->createVEvent($pzRuleEvent);
        $exEvent->uid = $event->uid;
        $exEvent->created = $event->created;
        $dateType = $pzEvent->isAllDay() ? Sabre_VObject_Element_DateTime::DATE : Sabre_VObject_Element_DateTime::UTC;
        $exEvent->__set('recurrence-id', self::getDateTime('recurrence-id', $pzRuleEvent->getBaseFrom(), $dateType));
        $calendar->add($exEvent);
      }
    }
    return $calendar->serialize();
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
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT project_id FROM '. pz_calendar_event::TABLE .' WHERE uri = ? AND user_id != ?', array($objectUri, pz::getUser()->getId()));
    if($sql->getRows() > 0)
    {
      pz::setConfig('calendar_etag_add/'. $objectUri, 1);
      self::incrementCtag($sql->getValue('project_id'));
      throw new Sabre_DAV_Exception_Forbidden('Forbidden!');
    }

    list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

    $pzEvent = pz_calendar_event::create();
    $calendar = Sabre_VObject_Reader::read($calendarData);
    $event = $calendar->vevent[0];
    $this->setEventValues($rawCalendarId, $objectUri, $event, $pzEvent);
    $pzEvent->setUserId(pz::getUser()->getId());
    $pzEvent->setBooked($jobs);
    $pzEvent->save();
    if(isset($event->rrule))
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
          $recurrenceId = new Sabre_VObject_Element_DateTime('RECURRENCE-ID', $recurrenceId);
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
    $pzEvent->saveToHistory('create');
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

    $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);
    $calendar = Sabre_VObject_Reader::read($calendarData);
    $event = $calendar->vevent[0];
    $add = $event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone())->diff($pzEvent->getFrom());
    $this->setEventValues($rawCalendarId, $objectUri, $event, $pzEvent);
    $pzEvent->save();
    $hasRule = $pzEvent->hasRule();
    if(isset($event->rrule))
    {
      $pzRule = $hasRule ? pz_calendar_rule::get($pzEvent) : pz_calendar_rule::create($pzEvent);
      $this->setRuleValues($event, $pzRule);
      $pzRule->save();

      $eventIds = array($pzEvent->getId());
      $ruleIds = array($pzRule->getId() => true);
      if(count($calendar->vevent) > 1)
      {
        $events = $this->getSortedEvents($calendar->vevent);
        $count = count($events);
        for($i = 0; $i < $count; ++$i)
        {
          $event = $events[$i];
          $recurrenceId = $event->__get('recurrence-id');
          $mode = isset($recurrenceId['RANGE']) && $recurrenceId['RANGE'] == 'THISANDFUTURE' ? pz_calendar_rule_event::FUTURE : pz_calendar_rule_event::THIS;
          $recurrenceId = new Sabre_VObject_Element_DateTime('RECURRENCE-ID', $recurrenceId);
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
    $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);

    rex_sql::factory()->setQuery('
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
    ', array($calendarId, $objectUri, pz::getUser()->getId()));

    if($pzEvent)
      $pzEvent->saveToHistory('delete');
    self::incrementCtag($calendarId);
  }

  private function createVEvent(pz_calendar_event $pzEvent)
  {
    $event = new Sabre_VObject_Component('vevent');
    $event->uid = substr($pzEvent->getUri(), 0, -4);
    $event->created = self::getDateTime('created', $pzEvent->getCreated(), Sabre_VObject_Element_DateTime::UTC);
    $event->dtstamp = self::getDateTime('dtstamp', $pzEvent->getUpdated(), Sabre_VObject_Element_DateTime::UTC);
    $event->sequence = $pzEvent->getSequence();
    $event->sequence = $pzEvent->getSequence();
    $dtend = $pzEvent->getTo();
    $dateType = Sabre_VObject_Element_DateTime::LOCALTZ;
    if($pzEvent->isAllDay())
    {
      $dtend->modify('+1 day');
      $dateType = Sabre_VObject_Element_DateTime::DATE;
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
    foreach($pzEvent->getAlarms() as $pzAlarm)
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
        $alarm->trigger = self::getDateTime('trigger', $pzAlarm->getTrigger(), Sabre_VObject_Element_DateTime::UTC);
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
      $event->add($alarm);
    }
    return $event;
  }

  private function setEventValues($calendarId, $objectUri, Sabre_VObject_Component $event, pz_calendar_event $pzEvent)
  {
    if($event->dtend->getDateType() == Sabre_VObject_Element_DateTime::DATE)
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
      ->setAllDay($event->dtstart->getDateType() == Sabre_VObject_Element_DateTime::DATE);

    $pzEvent->setAttendees($this->getAttendees($event));
    $pzEvent->setAlarms($this->getAlarms($event));
  }

  private function setRuleEventValues(Sabre_VObject_Component $event, pz_calendar_rule_event $pzRuleEvent, pz_calendar_event $pzEvent)
  {
    if($event->dtend->getDateType() == Sabre_VObject_Element_DateTime::DATE)
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
    if($pzEvent->isAllDay() xor $event->dtstart->getDateType() == Sabre_VObject_Element_DateTime::DATE)
      $pzRuleEvent->setAllDay($event->dtstart->getDateType() == Sabre_VObject_Element_DateTime::DATE);
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
      $sql = rex_sql::factory();
      $sql->prepareQuery('SELECT id FROM pz_user WHERE LOWER(email) = ? OR LOWER(login) = ? LIMIT 2');
      $userEmail = strtolower(pz::getUser()->getEmail());
      foreach($attendees as $attendee)
      {
        $email = str_ireplace('mailto:', '', $attendee);
        if(strtolower($email) != $organizer && strtolower($email) != $userEmail)
        {
          $name = (string) $attendee['cn'];
          if(strpos($email, '@') === false)
          {
            $sql->execute(array(null, strtolower($name)));
          }
          else
          {
            $sql->execute(array(strtolower($email), null));
          }

          $pzAttendee = pz_calendar_attendee::create();
          $pzAttendee->setStatus((string) $attendee['partstat']);
          if($sql->getRows() == 1)
          {
            $pzAttendee->setUserId($sql->getValue('id'));
          }
          else
          {
            $pzAttendee->setEmail($email);
            $pzAttendee->setName($name);
          }
          $pzAttendees[] = $pzAttendee;
        }
      }
    }
    return $pzAttendees;
  }

  private function getAlarms(Sabre_VObject_Component $event)
  {
    $pzAlarms = array();
    if(count($event->valarm) > 0)
    {
      foreach($event->valarm as $alarm)
      {
        $pzAlarm = pz_calendar_alarm::create();
        if($uid = $alarm->__get('x-wr-alarmuid'))
          $pzAlarm->setUid((string) $uid);
        $pzAlarm->setAction((string) $alarm->action);
        if(isset($alarm->trigger['value']) && $alarm->trigger['value'] == 'DATE-TIME')
        {
          $trigger = new Sabre_VObject_Element_DateTime('trigger', (string) $alarm->trigger);
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
        $pzAlarms[] = $pzAlarm;
      }
    }
    return $pzAlarms;
  }

  private function setRuleValues(Sabre_VObject_Component $event, pz_calendar_rule $pzRule)
  {
    parse_str(str_replace(';', '&', $event->rrule), $rule);
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
      $end = new Sabre_VObject_Element_DateTime('UNTIL', $rule['UNTIL']);
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
    if(isset($event->exdate))
    {
      $exceptions = array();
      foreach($event->exdate as $exdate)
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
    if(strpos($calendarId, '_jobs') === false)
      return array($calendarId, false);

    return array(str_replace('_jobs', '', $calendarId), true);
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

  static private function getDateTime($name, DateTime $dateTime, $dateType = Sabre_VObject_Element_DateTime::LOCALTZ)
  {
    $dt = new Sabre_VObject_Element_DateTime($name);
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
    list($calendarId, $jobs) = self::splitCalendarId($calendarId);

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