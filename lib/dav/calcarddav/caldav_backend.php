<?php

use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Plugin;
use Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\VObject\Component;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\Reader;

class pz_sabre_caldav_backend extends AbstractBackend
{
    private $calendars = [];

    private $objects = [];

    private $items = [];

    public function __construct()
    {
    }

    public function getCalendarsForUser($principalUri)
    {
        if (isset($this->calendars[$principalUri])) {
            return $this->calendars[$principalUri];
        }

        $calendars = [];

        $ctags = pz::getConfig('calendar_ctag', []);

        $normal = pz::getUser()->getCalDavProjects();
        $jobs   = pz::getUser()->getCalDavJobsProjects();
        $projects = [];
        foreach ([$normal, $jobs] as $type => $array) {
            foreach ($array as $project) {
                $projects[] = [$type, $project];
            }
        }
        usort($projects, function ($a, $b) {
            list($typeA, $projectA) = $a;
            list($typeB, $projectB) = $b;
            $nameA = str_replace(['ä', 'ö', 'ü', 'ß'], ['a', 'o', 'u', 's'], mb_strtolower($projectA->getName()));
            $nameB = str_replace(['ä', 'ö', 'ü', 'ß'], ['a', 'o', 'u', 's'], mb_strtolower($projectB->getName()));
            $cmp = strcasecmp($nameA, $nameB);
            if ($cmp == 0) {
                return $typeA <= $typeB ? -1 : 1;
            }
            return $cmp;
        });

        foreach ($projects as $i => $t_p) {
            /** @var pz_project $project */
            list($type, $project) = $t_p;
            switch ($type) {
                case 1:
                    $id = $project->getId() . '_jobs';
                    $nameAdd = pz_i18n::msg('jobs');
                    $supported = ['VEVENT', 'VTODO'];
                    break;
                /*case 2:
                    $id = $project->getId() .'_todos';
                    $nameAdd = pz_i18n::msg('todos');
                    break;*/
                default:
                    $id = $project->getId() . '_events';
                    $nameAdd = pz_i18n::msg('events');
                    $supported = ['VEVENT'];
            }
            $label = $project->getLabel();
            $color = '#FFFFFF';
            if ($label) {
                $color = $type == 1 ? $label->getBorder() : $label->getColor();
            }
            $calendars[] = [
                'id' => $id,
                'uri' => $id,
                'principaluri' => $principalUri,
                '{' . Plugin::NS_CALENDARSERVER . '}getctag' => isset($ctags[$project->getId()]) ? $ctags[$project->getId()] : 0,
                '{' . Plugin::NS_CALDAV . '}supported-calendar-component-set' => new SupportedCalendarComponentSet($supported),
                '{DAV:}displayname' => '[' . $nameAdd . '] ' . $project->getName(),
                '{' . Plugin::NS_CALDAV . '}calendar-description' => $project->getDescription(),
                '{' . Plugin::NS_CALDAV . '}calendar-timezone' => self::getTimezone(),
                '{http://apple.com/ns/ical/}calendar-order' => $i,
                '{http://apple.com/ns/ical/}calendar-color' => $color,
            ];
        }

        $this->calendars[$principalUri] = $calendars;

        return $calendars;
    }

    public function getCalendarObjects($calendarId)
    {
        list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

        $objects = [];

        $items = pz_calendar_event::getAllBase($rawCalendarId, $jobs);
        foreach ($items as $item) {
            $objects[] = $this->getCalendarObjectMeta($calendarId, $item);
            $this->items[$calendarId][$item->getUri()] = $item;
        }
        if ($jobs) {
            $items = pz_calendar_todo::getAllBase($rawCalendarId);
            foreach ($items as $item) {
                $objects[] = $this->getCalendarObjectMeta($calendarId, $item);
                $this->items[$calendarId][$item->getUri()] = $item;
            }
        }

        return $objects;
    }

    public function getCalendarObject($calendarId, $objectUri)
    {
        if (isset($this->objects[$calendarId][$objectUri]['calendardata'])) {
            return $this->objects[$calendarId][$objectUri];
        }

        list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

        if (!isset($this->items[$calendarId][$objectUri])) {
            $this->items[$calendarId][$objectUri] = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);
            if (!$this->items[$calendarId][$objectUri]) {
                $this->items[$calendarId][$objectUri] = pz_calendar_todo::getByProjectUri($rawCalendarId, $objectUri);
            }
        }
        $item = $this->items[$calendarId][$objectUri];
        if (!$item) {
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
        if (isset($this->objects[$calendarId][$uri])) {
            return $this->objects[$calendarId][$uri];
        }
        $timestamp = $item->getUpdated()->getTimestamp();
        $etag = (string) $timestamp;
        if (pz::hasConfig('calendar_etag_add/' . $uri)) {
            $etag .= '1';
            pz::removeConfig('calendar_etag_add/' . $uri);
        }
        $this->objects[$calendarId][$uri] = [
            'id' => $item->getId(),
            'uri' => $uri,
            'lastmodified' => $timestamp,
            'calendarid' => $calendarId,
            'etag' => '"' . $etag . '"',
        ];
        return $this->objects[$calendarId][$uri];
    }

    private function getCalendarObjectData(pz_calendar_item $item)
    {
        $calendar = new Component\VCalendar();

        if ($item instanceof pz_calendar_todo) {
            $this->createTodoComponent($calendar, $item);
        } else {
            $this->createEventComponent($calendar, $item);
        }

        return $calendar->serialize();
    }

    private function createTodoComponent(Component\VCalendar $calendar, pz_calendar_todo $pzTodo)
    {
        $todo = $calendar->createComponent('vtodo');
        $todo->uid = substr($pzTodo->getUri(), 0, -4);
        $todo->created = $pzTodo->getCreated()->setTimezone(new DateTimeZone('UTC'));
        $todo->dtstamp = $pzTodo->getUpdated()->setTimezone(new DateTimeZone('UTC'));
        $todo->sequence = $pzTodo->getSequence();
        $todo->summary = $pzTodo->getTitle();
        if ($description = $pzTodo->getDescription()) {
            $todo->description = $description;
        }
        $todo->priority = $pzTodo->getPriority();
        if ($order = $pzTodo->getOrder()) {
            $todo->__set('x-apple-sort-order', $order);
        }
        if ($start = $pzTodo->getFrom()) {
            $todo->dtstart = $start;
        }
        if ($due = $pzTodo->getDue()) {
            $todo->due = $due;
        }
        if ($completed = $pzTodo->getCompleted()) {
            $todo->status = 'COMPLETED';
            $todo->completed = $completed->setTimezone(new DateTimeZone('UTC'));
        }
        if ($pzTodo->hasRule()) {
            $pzRule = pz_calendar_rule::get($pzTodo);
            $this->createRuleComponent($todo, $pzRule);
        }
        $this->createAlarmComponents($calendar, $todo, $pzTodo);
        $calendar->add($todo);
    }

    private function createEventComponent(Component\VCalendar $calendar, pz_calendar_event $pzEvent)
    {
        $event = $this->createVEvent($calendar, $pzEvent);
        if (!$pzEvent->isBooked() && $pzEvent->hasRule()) {
            $pzRule = pz_calendar_rule::get($pzEvent);
            $this->createRuleComponent($event, $pzRule);
            foreach ($pzRule->getExceptions() as $exception) {
                $event->add('exdate', $exception);
            }
        }
        $calendar->add($event);
        if (!$pzEvent->isBooked() && $pzEvent->hasRule()) {
            $exceptions = pz_calendar_rule_event::getAllExceptions($pzRule);
            foreach ($exceptions as $pzRuleEvent) {
                $exEvent = $this->createVEvent($calendar, $pzRuleEvent);
                $exEvent->uid = $event->uid;
                $exEvent->created = $event->created;
                if ($pzEvent->isAllDay()) {
                    $exEvent->add('recurrence-id', $pzRuleEvent->getBaseFrom(), ['value' => 'DATE']);
                } else {
                    $exEvent->__set('recurrence-id', $pzRuleEvent->getBaseFrom()->setTimezone(new DateTimeZone('UTC')));
                }
                $calendar->add($exEvent);
            }
        }
    }

    private function createVEvent(Component\VCalendar $calendar, pz_calendar_event $pzEvent)
    {
        $event = $calendar->createComponent('vevent');
        $event->uid = substr($pzEvent->getUri(), 0, -4);
        $event->created = $pzEvent->getCreated()->setTimezone(new DateTimeZone('UTC'));
        $event->dtstamp = $pzEvent->getUpdated()->setTimezone(new DateTimeZone('UTC'));
        $event->sequence = $pzEvent->getSequence();
        $dtend = $pzEvent->getTo();
        if ($pzEvent->isAllDay()) {
            $dtend->modify('+1 day');
        }
        $event->dtstart = $pzEvent->getFrom();
        $event->dtend = $dtend;
        if ($pzEvent->isAllDay()) {
            $event->dtstart['VALUE'] = 'date';
            $event->dtend['VALUE'] = 'date';
        }
        $event->summary = $pzEvent->getTitle();
        if ($location = $pzEvent->getLocation()) {
            $event->location = $location;
        }
        if ($description = $pzEvent->getDescription()) {
            $event->description = $description;
        }
        if ($url = $pzEvent->getUrl()) {
            $event->url = $url;
        }

        foreach ($pzEvent->getReleasedClips() as $pzClip) {
            $clip = $calendar->createProperty('attach', $pzClip->getUri());
            if ($type = $pzClip->getContentType()) {
                $clip['fmttype'] = $type;
            }
            $clip['value'] = 'URI';
            $clip['filename'] = $pzClip->getFilename();
            $clip['size'] = $pzClip->getContentLength();
            $clip['managed-id'] = $pzClip->getId();
            $event->add($clip);
        }
        $pzAttendees = $pzEvent->getAttendees();
        $hasOrganizer = false;
        foreach ($pzAttendees as $pzAttendee) {
            $attendee = $calendar->createProperty('attendee', 'mailto:' . $pzAttendee->getEmail());
            $attendee['cn'] = $pzAttendee->getName();
            $attendee['partstat'] = $pzAttendee->getStatus();
            $attendee['role'] = $pzAttendee->getRole();
            $event->add($attendee);

            if (!$hasOrganizer && pz_calendar_attendee::ROLE_CHAIR === $pzAttendee->getRole()) {
                $hasOrganizer = true;
                $event->organizer = 'mailto:' . $pzAttendee->getEmail();
                $event->organizer['cn'] = $pzAttendee->getName();
            }
        }
        if (!$hasOrganizer) {
            $user = null;
            if (pz::getUser()->getId() != $pzEvent->getUserId()) {
                $user = pz_user::get($pzEvent->getUserId());
            } elseif (count($pzAttendees)) {
                $user = pz::getUser();
            }
            if ($user) {
                $event->organizer = 'mailto:' . $user->getEmail();
                $event->organizer['cn'] = $user->getName();
                $attendee = $calendar->createProperty('attendee', 'mailto:' . $user->getEmail());
                $attendee['cn'] = $user->getName();
                $attendee['partstat'] = 'ACCEPTED';
                $attendee['role'] = 'CHAIR';
            }
        }

        $this->createAlarmComponents($calendar, $event, $pzEvent);
        return $event;
    }

    private function createAlarmComponents(Component\VCalendar $calendar, Component $component, pz_calendar_item $item)
    {
        foreach ($item->getAlarms() as $pzAlarm) {
            $alarm = $calendar->createComponent('valarm');
            $alarm->__set('x-wr-alarmuid', $pzAlarm->getUid());
            $alarm->action = $pzAlarm->getAction();
            if ($pzAlarm->triggerIsInterval()) {
                $alarm->trigger = $pzAlarm->getTriggerString();
            } else {
                $alarm->trigger = $pzAlarm->getTrigger()->setTimezone(new DateTimeZone('UTC'))->format('Ymd\\THis\\Z');
                $alarm->trigger['value'] = 'DATE-TIME';
            }
            if ($description = $pzAlarm->getDescription()) {
                $alarm->description = $description;
            }
            if ($summary = $pzAlarm->getSummary()) {
                $alarm->summary = $summary;
            }
            if ($emails = $pzAlarm->getEmails()) {
                foreach ($emails as $email) {
                    $alarm->add($calendar->createProperty('attendee', 'mailto:' . $email));
                }
            }
            if ($attachment = $pzAlarm->getAttachment()) {
                $alarm->attachment = pz_sabre_single_line_parser::parseSingeLine($calendar, $attachment);
            }
            if ($location = $pzAlarm->getLocation()) {
                $alarm->location = $location;
            }
            if ($structuredLocation = $pzAlarm->getStructuredLocation()) {
                $alarm->__set('x-apple-structured-location', pz_sabre_single_line_parser::parseSingeLine($calendar, $structuredLocation));
            }
            if ($proximity = $pzAlarm->getProximity()) {
                $alarm->__set('x-apple-proximity', $proximity);
            }
            if ($pzAlarm->isDefault()) {
                $alarm->__set('X-APPLE-DEFAULT-ALARM', 'TRUE');
            }
            if ($acknowledged = $pzAlarm->getAckknowledged()) {
                $alarm->acknowledged = $acknowledged->setTimezone(new DateTimeZone('UTC'));
            }
            if ($pzAlarm->getRelatedId() && $related = $pzAlarm->getRelated()) {
                $alarm->__set('related-to', $related->getUid());
            }
            $component->add($alarm);
        }
    }

    private function createRuleComponent(Component $component, pz_calendar_rule $pzRule)
    {
        $rule = 'FREQ=' . $pzRule->getFrequence();
        if (($interval = $pzRule->getInterval()) > 1) {
            $rule .= ';INTERVAL=' . $interval;
        }
        if ($count = $pzRule->getCount()) {
            $rule .= ';COUNT=' . $count;
        }
        if ($end = $pzRule->getEnd()) {
            $rule .= ';UNTIL=' . $end->format('Ymd\\THis');
        }
        if ($weekDays = $pzRule->getWeekDays()) {
            $names = [null, 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
            foreach ($weekDays as &$weekDay) {
                $weekDay = $names[$weekDay];
            }
            $rule .= ';BYDAY=' . implode(',', $weekDays);
        }
        if ($days = $pzRule->getDays()) {
            $rule .= ';BYMONTHDAY=' . implode(',', $days);
        }
        if ($months = $pzRule->getMonths()) {
            $rule .= ';BYMONTH=' . implode(',', $months);
        }
        if ($nth = $pzRule->getNth()) {
            $rule .= ';BYSETPOS=' . $nth;
        }
        $component->rrule = $rule;
    }

    /**
     * Creates a new calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

        $sql = pz_sql::factory();
        $sql->setQuery('
            SELECT project_id FROM ' . pz_calendar_event::TABLE . ' WHERE project_id = :project_id AND uri = :uri AND user_id != :user_id
            UNION
            SELECT project_id FROM ' . pz_calendar_todo::TABLE . ' WHERE project_id = :project_id AND uri = :uri
        ', [':project_id' => $rawCalendarId, ':uri' => $objectUri, ':user_id' => pz::getUser()->getId()]);
        if ($sql->getRows() > 0) {
            pz::setConfig('calendar_etag_add/' . $objectUri, 1);
            self::incrementCtag($sql->getValue('project_id'));
            throw new Forbidden('Forbidden!');
        }

        $calendar = Reader::read($calendarData);
        if ($event = $calendar->vevent[0]) {
            $this->createPzEvent($rawCalendarId, $jobs, $objectUri, $event);
        } elseif ($jobs && $todo = $calendar->vtodo[0]) {
            $this->createPzTodo($rawCalendarId, $objectUri, $todo);
        } else {
            throw new Forbidden('Forbidden!');
        }
    }

    private function createPzEvent($projectId, $jobs, $objectUri, $event, $save = true)
    {
        $pzEvent = pz_calendar_event::create();
        $this->setEventValues($projectId, $objectUri, $event, $pzEvent);
        $pzEvent->setUserId(pz::getUser()->getId());
        $pzEvent->setBooked($jobs);
        if ($save) {
            $pzEvent->save();
        }
        if (!$jobs && isset($event->rrule)) {
            $pzRule = pz_calendar_rule::create($pzEvent);
            $this->setRuleValues($event, $pzRule);
            if ($save) {
                $pzRule->save();
            }

            if (count($event) > 1) {
                $events = $this->getSortedEvents($event);
                $count = count($events);
                for ($i = 0; $i < $count; ++$i) {
                    $event = $events[$i];
                    $recurrenceId = $event->__get('recurrence-id');
                    $mode = isset($recurrenceId['RANGE']) && $recurrenceId['RANGE'] == 'THISANDFUTURE' ? pz_calendar_rule_event::FUTURE : pz_calendar_rule_event::THIS;
                    $pzRuleEvent = pz_calendar_rule_event::create($pzRule, $recurrenceId->getDateTime()->setTimezone(self::getDateTimeZone()));
                    $this->setRuleEventValues($event, $pzRuleEvent, $pzEvent);
                    if (isset($event->rrule) && $mode == pz_calendar_rule_event::FUTURE) {
                        $this->setRuleValues($event, $pzRule);
                    }
                    if ($save) {
                        $pzRuleEvent->save($mode);
                    }
                    $pzRule = $pzRuleEvent->getRule();
                }
            }
        }
        // $pzEvent->saveToHistory('create');
        return $pzEvent;
    }

    private function createPzTodo($projectId, $objectUri, $todo)
    {
        $pzTodo = pz_calendar_todo::create();
        $this->setTodoValues($projectId, $objectUri, $todo, $pzTodo);
        $pzTodo->setUserId(pz::getUser()->getId());
        $pzTodo->save();
        if (isset($todo->rrule)) {
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
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

        $calendar = Reader::read($calendarData);
        if ($event = $calendar->vevent) {
            $this->updatePzEvent($rawCalendarId, $jobs, $objectUri, $event);
        } elseif ($jobs && $todo = $calendar->vtodo[0]) {
            $this->updatePzTodo($rawCalendarId, $objectUri, $todo);
        } else {
            throw new NotFound('Not found!');
        }
    }

    private function updatePzTodo($rawCalendarId, $objectUri, Component $todo)
    {
        $pzTodo = pz_calendar_todo::getByProjectUri($rawCalendarId, $objectUri);
        $this->setTodoValues($rawCalendarId, $objectUri, $todo, $pzTodo);
        $pzTodo->save();
        $hasRule = $pzTodo->hasRule();
        if (isset($todo->rrule)) {
            $pzRule = $hasRule ? pz_calendar_rule::get($pzTodo) : pz_calendar_rule::create($pzTodo);
            $this->setRuleValues($todo, $pzRule);
            $pzRule->save();
        } elseif ($hasRule) {
            pz_sql::factory()->setQuery('
                DELETE r
                FROM ' . pz_calendar_rule::TABLE . ' r
                WHERE r.todo_id = ?
            ', [$pzTodo->getId()]);
        }
    }

    private function updatePzEvent($rawCalendarId, $jobs, $objectUri, Component $event, $checkOrganizer = true)
    {
        $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);

        if (
            !$pzEvent->isBooked() && $checkOrganizer &&
            ($organizer = $this->getEmail($event->organizer)) &&
            !in_array($organizer, $pzEvent->getUser()->getEmails())
        ) {
            $pzAttendees = $pzEvent->getAttendees();
            $pzAttendee = null;
            foreach ($pzAttendees as $a) {
                if ($a->getUserId() == pz::getUser()->getId()) {
                    $pzAttendee = $a;
                    break;
                }
            }
            if ($pzAttendee) {
                $emails = pz::getUser()->getEmails();
                foreach ($event->attendee as $attendee) {
                    $email = $this->getEmail($attendee);
                    if (in_array($email, $emails)) {
                        $pzAttendee->setStatus((string) $attendee['partstat'] ?: 'NEEDS-ACTION');
                        $pzAttendee->setEmail($email);
                        $pzAttendee->setName($attendee['cn']);
                    }
                }

                $pzEvent->setAttendees($pzAttendees);
            }

            $pzEvent->setAlarms($this->getAlarms($event));
            $pzEvent->save();
            $pzEvent->saveToHistory('update');
            return;
        }

        $add = $event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone())->diff($pzEvent->getFrom());
        $this->setEventValues($rawCalendarId, $objectUri, $event, $pzEvent);
        $pzEvent->save();
        $hasRule = $pzEvent->hasRule();
        if (!$jobs && isset($event->rrule)) {
            $pzRule = $hasRule ? pz_calendar_rule::get($pzEvent) : pz_calendar_rule::create($pzEvent);
            $this->setRuleValues($event, $pzRule);
            $pzRule->save();

            $eventIds = [$pzEvent->getId()];
            $ruleIds = [$pzRule->getId() => true];
            if (count($event) > 1) {
                $events = $this->getSortedEvents($event);
                $count = count($events);
                for ($i = 0; $i < $count; ++$i) {
                    $event = $events[$i];
                    $recurrenceId = $event->__get('recurrence-id');
                    $mode = isset($recurrenceId['RANGE']) && $recurrenceId['RANGE'] == 'THISANDFUTURE' ? pz_calendar_rule_event::FUTURE : pz_calendar_rule_event::THIS;
                    $pzRuleEvent = pz_calendar_rule_event::getByBaseFrom($pzRule, $recurrenceId->getDateTime()->setTimezone(self::getDateTimeZone())->add($add));
                    $pzRuleEvent->setBaseFrom($recurrenceId->getDateTime()->sub($add));
                    $this->setRuleEventValues($event, $pzRuleEvent, $pzEvent);
                    if (isset($event->rrule) && $mode == pz_calendar_rule_event::FUTURE) {
                        $this->setRuleValues($event, $pzRule);
                    }
                    $pzRuleEvent->save($mode);
                    $eventIds[] = $pzRuleEvent->getId(true);
                    $pzRule = $pzRuleEvent->getRule();
                    $ruleIds[$pzRule->getId()] = true;
                }
            }
            if ($hasRule) {
                $ruleIds = array_keys($ruleIds);
                $eventIn = implode(',', array_fill(0, count($eventIds), '?'));
                $ruleIn = implode(',', array_fill(0, count($ruleIds), '?'));
                $params = array_merge($ruleIds, $eventIds);
                pz_sql::factory()->setQuery('
                    DELETE e, at, al
                    FROM ' . pz_calendar_event::TABLE . ' e
                    LEFT JOIN ' . pz_calendar_attendee::TABLE . ' at
                    ON at.event_id = e.id
                    LEFT JOIN ' . pz_calendar_alarm::TABLE . ' al
                    ON al.event_id = e.id
                    WHERE rule_id IN (' . $ruleIn . ') AND e.id NOT IN (' . $eventIn . ')
                ', $params);
            }
        } elseif ($hasRule) {
            pz_sql::factory()->setQuery('
                DELETE r, e, at, al
                FROM ' . pz_calendar_rule::TABLE . ' r
                LEFT JOIN ' . pz_calendar_event::TABLE . ' e
                ON e.rule_id = r.id AND e.id != r.event_id
                LEFT JOIN ' . pz_calendar_attendee::TABLE . ' at
                ON at.event_id = e.id
                LEFT JOIN ' . pz_calendar_alarm::TABLE . ' al
                ON al.event_id = e.id
                WHERE r.event_id = ?
            ', [$pzEvent->getId()]);
        }
        $pzEvent->saveToHistory('update');
    }

    private function setTodoValues($calendarId, $objectUri, Component $todo, pz_calendar_todo $pzTodo)
    {
        $pzTodo
            ->setUri($objectUri)
            ->setProjectId($calendarId)
            ->setTitle((string) $todo->summary)
            ->setDescription((string) $todo->description)
            ->setPriority((int) (string) $todo->priority);
        if ($order = $todo->__get('x-apple-sort-order')) {
            $pzTodo->setOrder((int) (string) $order);
        } else {
            $pzTodo->setOrder(null);
        }
        if ($start = $todo->dtstart) {
            $pzTodo->setFrom($start->getDateTime()->setTimezone(self::getDateTimeZone()));
        } else {
            $pzTodo->setFrom(null);
        }
        if ($due = $todo->due) {
            $pzTodo->setDue($due->getDateTime()->setTimezone(self::getDateTimeZone()));
        } else {
            $pzTodo->setDue(null);
        }
        if ($completed = $todo->completed) {
            $pzTodo->setCompleted($completed->getDateTime()->setTimezone(self::getDateTimeZone()));
        } else {
            $pzTodo->setCompleted(null);
        }
        $pzTodo->setAlarms($this->getAlarms($todo));
    }

    private function setEventValues($calendarId, $objectUri, Component $event, pz_calendar_event $pzEvent)
    {
        /** @var DateTime $end */
        $end = $event->dtend->getDateTime();
        if ($event->dtend->getValueType() == 'DATE') {
            $end->modify('-1 day');
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
            ->setTo($end->setTimezone(self::getDateTimeZone()))
            ->setAllDay($event->dtstart->getValueType() == 'DATE');

        if (!$pzEvent->isBooked()) {
            $pzEvent->setAttendees($this->getAttendees($event));
            $pzEvent->setAlarms($this->getAlarms($event));
        }
    }

    private function setRuleEventValues(Component $event, pz_calendar_rule_event $pzRuleEvent, pz_calendar_event $pzEvent)
    {
        if ($event->dtend->getValueType() == 'DATE') {
            $event->dtend->getDateTime()->modify('-1 day');
        }
        $pzRuleEvent
            ->setSequence((string) $event->sequence)
            ->setFrom($event->dtstart->getDateTime()->setTimezone(self::getDateTimeZone()))
            ->setTo($event->dtend->getDateTime()->setTimezone(self::getDateTimeZone()));
        if (isset($event->summary) && $pzEvent->getTitle() != (string) $event->summary) {
            $pzRuleEvent->setTitle((string) $event->summary);
        }
        if (isset($event->location) && $pzEvent->getLocation() != (string) $event->location) {
            $pzRuleEvent->setLocation((string) $event->location);
        }
        if (isset($event->description) && $pzEvent->getDescription() != (string) $event->description) {
            $pzRuleEvent->setDescription((string) $event->description);
        }
        if (isset($event->url) && $pzEvent->getUrl() != (string) $event->url) {
            $pzRuleEvent->setUrl((string) $event->url);
        }
        if ($pzEvent->isAllDay() xor $event->dtstart->getValueType() == 'DATE') {
            $pzRuleEvent->setAllDay($event->dtstart->getValueType() == 'DATE');
        }
        $pzRuleEvent->setAttendees($this->getAttendees($event));
        $pzRuleEvent->setAlarms($this->getAlarms($event));
    }

    private function getAttendees(Component $event)
    {
        $attendees = $event->attendee;
        $pzAttendees = [];
        if (count($attendees) <= 0) {
            return $pzAttendees;
        }

        $userEmails = pz::getUser()->getEmails();
        $organizer = $event->organizer;
        $organizerEmail = $this->getEmail($organizer);
        $otherOrganizer = !in_array($organizerEmail, $userEmails);
        if ($otherOrganizer) {
            $pzAttendee = pz_calendar_attendee::create();
            $pzAttendee->setRole(pz_calendar_attendee::ROLE_CHAIR);
            $pzAttendee->setStatus(pz_calendar_attendee::STATUS_ACCEPTED);
            $pzAttendee->setEmail($organizerEmail);
            $pzAttendee->setName((string) $organizer['cn']);
            $pzAttendees[] = $pzAttendee;
        }

        $sqlLogin = pz_sql::factory();
        $sqlLogin->prepareQuery('SELECT id, name, email FROM pz_user WHERE LOWER(login) = ? LIMIT 2');
        $sqlEmail = pz_sql::factory();
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
        foreach ($attendees as $attendee) {
            $email = $this->getEmail($attendee);
            if ($email == $organizerEmail || !$otherOrganizer && in_array($email, $userEmails)) {
                continue;
            }

            $userId = '';
            $name = (string) $attendee['cn'];
            if (strpos($email, '@') === false) {
                $sqlLogin->execute([strtolower($name)]);
                if ($sqlLogin->getRows() != 1) {
                    continue;
                }
                $name = $sqlLogin->getValue('name');
                $email = $sqlLogin->getValue('email');
                $userId = $sqlLogin->getValue('id');
            } else {
                $sqlEmail->execute([':email' => strtolower($email)]);
                if ($sqlEmail->getRows() == 1) {
                    $userId = $sqlEmail->getValue('id');
                    $name = $sqlEmail->getValue('name');
                    $email = $sqlEmail->getValue('email');
                }
            }

            $pzAttendee = pz_calendar_attendee::create();
            $pzAttendee->setStatus((string) $attendee['partstat'] ?: 'NEEDS-ACTION');
            $pzAttendee->setEmail($email);
            $pzAttendee->setName($name);
            $pzAttendee->setRole((string) $attendee['role']);
            if ($userId) {
                $pzAttendee->setUserId($userId);
            }
            $pzAttendees[] = $pzAttendee;
        }

        return $pzAttendees;
    }

    private function getEmail($property)
    {
        if (!$property) {
            return null;
        }
        if ($email = (string) $property['EMAIL']) {
            return strtolower($email);
        }

        return str_replace('mailto:', '', strtolower($property));
    }

    private function getAlarms(Component $component)
    {
        $pzAlarms = [];
        if (count($component->valarm) > 0) {
            foreach ($component->valarm as $alarm) {
                $pzAlarm = pz_calendar_alarm::create();
                if ($uid = $alarm->__get('x-wr-alarmuid')) {
                    $pzAlarm->setUid((string) $uid);
                }
                $pzAlarm->setAction((string) $alarm->action);
                if (isset($alarm->trigger['value']) && $alarm->trigger['value'] == 'DATE-TIME') {
                    $pzAlarm->setTrigger($alarm->trigger->getDateTime()->setTimezone(self::getDateTimeZone()));
                } else {
                    $trigger = (string) $alarm->trigger;
                    if (preg_match('/([PYM])([0-9]+)W(?:([0-9]+)D)?/', $trigger, $matches)) {
                        $days = 7 * $matches[2] + (isset($matches[3]) ? $matches[3] : 0);
                        $trigger = str_replace($matches[0], $matches[1] . $days . 'D', $trigger);
                    }
                    $dateInterval = new DateInterval(ltrim($trigger, '-'));
                    $dateInterval->invert = strpos($trigger, '-') === 0;
                    $pzAlarm->setTrigger($dateInterval);
                }
                if ($alarm->description) {
                    $pzAlarm->setDescription((string) $alarm->description);
                }
                if ($alarm->summary) {
                    $pzAlarm->setSummary((string) $alarm->summary);
                }
                if ($alarm->attendee) {
                    $emails = [];
                    foreach ($alarm->attendee as $attendee) {
                        $emails[] = (string) $attendee;
                    }
                    $pzAlarm->setEmails($emails);
                }
                if ($alarm->attach) {
                    $pzAlarm->setAttachment($alarm->attach->serialize());
                }
                if ($alarm->location) {
                    $pzAlarm->setLocation((string) $alarm->location);
                }
                if ($structuredLocation = $alarm->__get('x-apple-structured-location')) {
                    $pzAlarm->setStructuredLocation($structuredLocation->serialize());
                }
                if ($proximity = $alarm->__get('x-apple-proximity')) {
                    $pzAlarm->setProximity((string) $proximity);
                }
                if ($proximity = $alarm->__get('X-APPLE-DEFAULT-ALARM')) {
                    $pzAlarm->setDefault(true);
                }
                if ($acknowledged = $alarm->acknowledged) {
                    $pzAlarm->setAcknowledged($acknowledged->getDateTime()->setTimezone(self::getDateTimeZone()));
                }
                if (($relatedUri = $alarm->__get('related-to')) && ($related = pz_calendar_alarm::getByUid($relatedUri))) {
                    $pzAlarm->setRelatedId($related->getId());
                }

                $pzAlarms[] = $pzAlarm;
            }
        }
        return $pzAlarms;
    }

    private function setRuleValues(Component $component, pz_calendar_rule $pzRule)
    {
        parse_str(str_replace(';', '&', $component->rrule), $rule);
        $freq = self::constant($rule['FREQ']);
        $pzRule
            ->setFrequence($freq)
            ->setInterval(max(1, $rule['INTERVAL']))
            ->setCount(null)
            ->setEnd(null)
            ->setWeekDays([])
            ->setDays([])
            ->setMonths([])
            ->setNth(0)
            ->setExceptions([]);
        if (isset($rule['COUNT'])) {
            $pzRule->setCount($rule['COUNT']);
        }
        if (isset($rule['UNTIL'])) {
            $pzRule->setEnd(\Sabre\VObject\DateTimeParser::parseDateTime($rule['UNTIL']));
        }
        if (($freq == pz_calendar_rule::WEEKLY || isset($rule['BYSETPOS'])) && isset($rule['BYDAY'])) {
            $byday = array_map('self::constant', explode(',', $rule['BYDAY']));
            $pzRule->setWeekDays($byday);
        }
        if (isset($rule['BYSETPOS'])) {
            $pzRule->setNth($rule['BYSETPOS']);
        } elseif (isset($rule['BYDAY']) && preg_match('/((?:\+|-)?[0-9])([A-Z]{2})/', $rule['BYDAY'], $matches)) {
            $pzRule
                ->setNth($matches[1])
                ->setWeekDay(self::constant($matches[2]));
        }
        if (isset($rule['BYMONTHDAY'])) {
            $pzRule->setDays(explode(',', $rule['BYMONTHDAY']));
        }
        if (isset($rule['BYMONTH'])) {
            $pzRule->setMonths(explode(',', $rule['BYMONTH']));
        }
        if (isset($component->exdate)) {
            $exceptions = [];
            foreach ($component->exdate as $exdate) {
                foreach ($exdate->getDateTimes() as $dt) {
                    $exceptions[] = $dt->setTimeZone(self::getDateTimeZone());
                }
            }
            $pzRule->setExceptions($exceptions);
        }
    }

    private function getSortedEvents(Traversable $events)
    {
        $events = iterator_to_array($events);
        unset($events[0]);
        usort($events,
            function ($a, $b) {
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
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        list($rawCalendarId, $jobs) = self::splitCalendarId($calendarId);

        $sql = pz_sql::factory();

        $pzEvent = pz_calendar_event::getByProjectUri($rawCalendarId, $objectUri, $jobs);

        $sql->setQuery('
            DELETE b, r, e, at, al
            FROM ' . pz_calendar_event::TABLE . ' b
            LEFT JOIN ' . pz_calendar_rule::TABLE . ' r
            ON b.id = r.event_id
            LEFT JOIN ' . pz_calendar_event::TABLE . ' e
            ON e.rule_id = r.id
            LEFT JOIN ' . pz_calendar_attendee::TABLE . ' at
            ON at.event_id = e.id OR at.event_id = b.id
            LEFT JOIN ' . pz_calendar_alarm::TABLE . ' al
            ON al.event_id = e.id OR al.event_id = b.id
            WHERE b.project_id = ? AND b.uri = ? AND b.user_id = ?
        ', [$rawCalendarId, $objectUri, pz::getUser()->getId()]);

        if ($pzEvent) {
            $pzEvent->saveToHistory('delete');
        }

        if ($jobs) {
            $sql->setQuery('
                DELETE b, al, r
                FROM ' . pz_calendar_todo::TABLE . ' b
                LEFT JOIN ' . pz_calendar_alarm::TABLE . ' al
                ON al.todo_id = b.id
                LEFT JOIN ' . pz_calendar_rule::TABLE . ' r
                ON r.todo_id = b.id
                WHERE b.project_id = ? AND b.uri = ?
            ', [$rawCalendarId, $objectUri]);
        }
        self::incrementCtag($rawCalendarId);
    }

    public static function getImportStatus($data)
    {
        $status = ['importable' => true, 'uri' => null, 'method' => null, 'event' => null];

        if (!preg_match('/^UID:(.*)\s?$/Umi', $data, $matches)) {
            return $status;
        }

        $status['uri'] = $uri = $matches[1] . '.ics';
        $status['event'] = $event = pz_calendar_event::getByUri($uri);

        if (preg_match('/^METHOD:(.*)\s?$/Umi', $data, $matches)) {
            $status['method'] = $matches[1];
        }

        if ($event && $event->getUserId() != pz::getUser()->getId()) {
            $status['importable'] = false;
        } elseif ($event && 'CANCEL' !== $status['method'] && preg_match('/^DTSTAMP:(.*)\s?$/Umi', $data, $matches)) {
            $datetime = DateTimeParser::parseDateTime($matches[1], self::getDateTimeZone());
            if ($datetime && $datetime < $event->getUpdated()) {
                $status['importable'] = false;
            }
        }

        return $status;
    }

    public static function getPreview($data)
    {
        $calendar = Reader::read($data);
        $backend = new self();
        $event = $backend->createPzEvent(null, false, null, $calendar->vevent, false);
        $event->setUserId(null);

        return $event;
    }

    public static function import($data, $projectId = null)
    {
        $status = self::getImportStatus($data);

        if (!$status['importable']) {
            throw new RuntimeException('Event is not importable');
        }

        if (!$status['event'] && !$projectId) {
            throw new RuntimeException('Missing project id for new event');
        }

        $calendar = Reader::read($data);
        foreach ($calendar->vevent as $event) {
            unset($event->valarm);
        }
        $data = $calendar->serialize();

        $backend = new self();
        if (!$status['uri']) {
            $sql = pz_sql::factory();
            $sql->setQuery('SELECT UPPER(UUID()) as uid');
            $backend->createCalendarObject($projectId.'_events', $sql->getValue('uid') . '.ics', $data);
        } elseif (!$status['event']) {
            $backend->createCalendarObject($projectId.'_events', $status['uri'], $data);
        } elseif ('CANCEL' === $status['method']) {
            $backend->deleteCalendarObject($status['event']->getProjectId().'_events', $status['uri']);
        } else {
            $backend->updatePzEvent($status['event']->getProjectId(), false, $status['uri'], $calendar->vevent, false);
        }
    }

    public static function export(pz_calendar_event $event)
    {
        $backend = new self();
        return $backend->getCalendarObjectData($event);
    }

    private static function splitCalendarId($calendarId)
    {
        $parts = array_pad(explode('_', $calendarId, 2), 2, null);
        return [$parts[0], $parts[1] == 'jobs'];
    }

    public static function incrementCtag($calendarId)
    {
        $ctags = pz::getConfig('calendar_ctag', []);
        $ctags[$calendarId] = isset($ctags[$calendarId]) ? ($ctags[$calendarId] + 1) : 1;
        pz::setConfig('calendar_ctag', $ctags);
    }

    private static function constant($constant)
    {
        return constant('pz_calendar_rule::' . $constant);
    }

    private static function getDateTimeZone()
    {
        static $timezone = null;
        return $timezone ?: $timezone = new DateTimeZone('Europe/Berlin');
    }

    private static function getTimezone()
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

    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        return false;
    }

    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch)
    {
        return false;
    }

    public function deleteCalendar($calendarId)
    {
        list($calendarId, $jobs, $todos) = self::splitCalendarId($calendarId);

        $sql = pz_sql::factory()
            ->setTable('pz_project_user')
            ->setWhere(['project_id' => $calendarId, 'user_id' => pz::getUser()->getId()]);
        if ($jobs) {
            $sql->setValue('caldav_jobs', 0);
        } else {
            $sql->setValue('caldav', 0);
        }
        $sql->update();
    }
}
