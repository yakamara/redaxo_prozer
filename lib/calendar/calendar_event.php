<?php

class pz_calendar_event extends pz_calendar_item
{
  const TABLE = 'pz_calendar_event';

  protected $clip_ids;

  protected $location;

  protected $url;

  protected $booked;

  /**
   * @var DateTime
   */
  protected $to;

  protected $allday;

  protected $attendees;

  protected function __construct(array $params = array())
  {
    if (isset($params['e.id']))
      $this->id = $params['e.id'];
    if (isset($params['e.uri']))
      $this->uri = $params['e.uri'];
    if (isset($params['e.project_id']))
      $this->project_id = $params['e.project_id'];
    if (isset($params['e.project_sub_id']))
      $this->project_sub_id = $params['e.project_sub_id'];
    if (isset($params['e.clip_ids']))
      $this->clip_ids = $params['e.clip_ids'];
    if (isset($params['e.title']))
      $this->title = $this->makeSingleLine($params['e.title']);
    if (isset($params['e.description']))
      $this->description = $params['e.description'];
    if (isset($params['e.url']))
      $this->url = $this->makeSingleLine($params['e.url']);
    if (isset($params['e.location']))
      $this->location = $this->makeSingleLine($params['e.location']);
    if (isset($params['e.booked']))
      $this->booked = (boolean) $params['e.booked'];
    if (isset($params['e.from']))
      $this->from = new DateTime($params['e.from']);
    if (isset($params['e.to']))
      $this->to = new DateTime($params['e.to']);
    if (isset($params['e.allday']))
      $this->allday = (boolean) $params['e.allday'];
    if (isset($params['e.rule_id']))
      $this->rule_id = $params['e.rule_id'];
    if (isset($params['e.created']))
      $this->created = new DateTime($params['e.created']);;
    if (isset($params['e.updated']))
      $this->updated = new DateTime($params['e.updated']);
    if (isset($params['e.sequence']))
      $this->sequence = $params['e.sequence'];
    if (isset($params['e.user_id']))
      $this->user_id = $params['e.user_id'];
  }

  // ---------------------------

  public function getClipIds()
  {
    if ($this->getValue('clip_ids') != '')
      return explode(',', $this->getValue('clip_ids'));
    return array();
  }

  public function getClips()
  {
    $clips = array();
    foreach ($this->getClipIds() as $c_id) {
      if (($clip = pz_clip::get($c_id)))
        $clips[$c_id] = $clip;
    }
    return $clips;
  }

  public function getReleasedClips()
  {
    $released_clips = array();
    foreach ($this->getClips() as $c) {
      if ($c->isReleased())
        $released_clips[] = $c;
    }
    return $released_clips;
  }

  public function hasClips()
  {
    if (count($this->getClips()) > 0)
      return true;
    return false;
  }

  public function getEventsByClip($clip)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . self::TABLE . ' e WHERE FIND_IN_SET( ? , e.clip_ids )', array($clip->getId()));

    $events = array();
    foreach ($sql as $row) {
      $events[] = new self($row->getRow());
    }
    return $events;
  }

  // ---------------------------


  public function getLocation()
  {
    return $this->makeSingleLine($this->getValue('location'));
  }

  public function getUrl()
  {
    return $this->makeSingleLine($this->getValue('url'));
  }

  public function setClipIds($clip_ids)
  {
    return $this->setValue('clip_ids', $clip_ids);
  }

  public function setLocation($location)
  {
    return $this->setValue('location', $this->makeSingleLine($location));
  }

  public function setUrl($url)
  {
    return $this->setValue('url', $this->makeSingleLine($url));
  }


  // ---------------------------

  public function isJob()
  {
    return $this->getValue('booked');
  }

  public function isBooked()
  {
    return $this->isJob();
  }

  public function setBooked($booked = 1)
  {
    return $this->setJob($booked);
  }

  public function setJob($booked = 1)
  {
    return $this->setValue('booked', $booked);
  }


  // ---------------------------

  /**
   * @return DateTime
   */
  public function getTo()
  {
    return clone $this->to;
  }

  /**
   * @return DateInterval
   */
  public function getDuration()
  {
    return $this->from->diff($this->to);
  }

  public function isAllDay()
  {
    return $this->getValue('allday');
  }

  public function setTo(DateTime $to)
  {
    return $this->setValue('to', $to);
  }

  public function setAllDay($allday)
  {
    return $this->setValue('allday', $allday);
  }

  public function isDay(DateTime $day)
  {
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($this->getFrom(), $interval ,$this->getTo());
    foreach($daterange as $date){
      if($date->format("Ymd") == $day->format("Ymd")) return true;
    }
    return false;
  }

  // ---------------------------

  public function getAttendees()
  {
    if ($this->attendees === null) {
      $this->attendees = pz_calendar_attendee::getAll($this);
    }
    return $this->attendees;
  }

  public function setAttendees(array $attendees)
  {
    return $this->setValue('attendees', $attendees);
  }

  // ---------------------------

  public function isRuleEvent()
  {
    return $this instanceof pz_calendar_rule_event;
  }

  public function compareTo(self $other)
  {
    $return = 0;

    if ($this->from == $other->from) {
      $return = 0;
    } elseif (($this->allday xor $other->allday) && $this->from->format('dmY') == $other->from->format('dmY')) {
      $return = $this->allday ? -1 : 1;
    } else {
      $return = $this->from < $other->from ? -1 : 1;
    }
    return $return;

  }

  // ---------------------------

  public function save()
  {
    $sql = rex_sql::factory()
      ->setTable(self::TABLE);
    $ignore = array('attendees', 'alarms');
    foreach (array_keys($this->changed) as $key) {
      if ($this->allday && $key == 'from') {
        $this->from->setTime(0, 0, 0);
      } elseif ($this->allday && $key == 'to') {
        $this->to->setTime(23, 59, 59);
      }
      if (!in_array($key, $ignore))
        $sql->setValue($key, self::sqlValue($this->$key));
    }
    $sql->setValue('vt', $this->getTitle() . ' ' . $this->getDescription() . ' ' . $this->getLocation());
    if (!$this->hasChanged('updated')) {
      $sql->setRawValue('updated', 'NOW()');
    }
    if ($this->new) {
      if (!$this->hasChanged('user_id')) {
        $sql->setValue('user_id', pz::getUser()->getId());
      }
      if (!$this->hasChanged('created')) {
        $sql->setRawValue('created', 'NOW()');
      }
      if (!$this->hasChanged('uri')) {
        $sql->setRawValue('uri', 'CONCAT(UPPER(UUID()), ".ics")');
      }
      if (!$this->hasChanged('booked')) {
        $sql->setValue('booked', 0);
      }
      $sql->insert();
      $this->id = $sql->getLastId();
    } else {
      if (!$this->hasChanged('sequence')) {
        $sql->setRawValue('sequence', 'sequence + 1');
      }
      $sql->setWhere(array('id' => $this->id))
        ->update();
    }
    if (!$this->booked && $this->hasChanged('attendees')) {
      pz_calendar_attendee::saveAll($this);
    }
    if (!$this->booked && $this->hasChanged('alarms')) {
      pz_calendar_alarm::saveAll($this);
    }

    if ($this->new) {
      $event = self::get($this->id);
      $event->saveToHistory('create');
    } else {
      $event = self::get($this->id);
      $event->saveToHistory('update');
    }

    pz_sabre_caldav_backend::incrementCtag($this->project_id);

    $this->changed = array();
    $this->new = false;
  }

  public function delete()
  {
    $this->saveToHistory('delete');
    self::_delete('e.id = ?', array($this->id));
    pz_sabre_caldav_backend::incrementCtag($this->project_id);
  }

  public function saveToHistory($mode = 'update')
  {
    $data = array();
    if ($mode != 'delete') {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', array($this->id));
      $arr = $sql->getArray();
      $data = $arr[0];
      $sql->setQuery('SELECT * FROM ' . pz_calendar_attendee::TABLE . ' WHERE event_id = ?', array($this->id));
      $data['attendees'] = $sql->getArray();
      $sql->setQuery('SELECT * FROM ' . pz_calendar_alarm::TABLE . ' WHERE event_id = ?', array($this->id));
      $data['alarms'] = $sql->getArray();
      if ($data['rule_id']) {
        $sql->setQuery('SELECT * FROM ' . pz_calendar_rule::TABLE . ' WHERE id = ?', array($data['rule_id']));
        $arr = $sql->getArray();
        $data['rule'] = $arr[0];
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE rule_id = ? AND id != ?', array($data['rule_id'], $this->id));
        $data['rule']['exception_events'] = $sql->getArray();
      }
    }
    rex_sql::factory()
      ->setTable('pz_history')
      ->setValue('control', 'calendar_event')
      ->setValue('project_id', $this->getProjectId())
      ->setValue('data_id', $this->id)
      ->setValue('user_id', pz::getUser()->getId())
      ->setValue('data', json_encode($data))
      ->setValue('mode', $mode)
      ->setRawValue('stamp', 'NOW()')
      ->insert();
  }

  static protected function _delete($where, array $params)
  {
    rex_sql::factory()->setQuery('
      DELETE e, at, al
      FROM ' . self::TABLE . ' e
      LEFT JOIN ' . pz_calendar_attendee::TABLE . ' at
      ON at.event_id = e.id
      LEFT JOIN ' . pz_calendar_alarm::TABLE . ' al
      ON al.event_id = e.id
      WHERE ' . $where . '
    ', $params);
  }

  static public function create()
  {
    $event = new self;
    $event->new = true;
    return $event;
  }

  // ---------------------------

  static public function get($id)
  {
    if (strpos($id, '_') !== false) {
      return pz_calendar_rule_event::get($id);
    }

    static $sql = null;
    if (!$sql) {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
        SELECT *
        FROM ' . self::TABLE . ' e
        WHERE id = ?
      ');
    }
    $sql->execute(array($id));
    if ($sql->getRows() == 0) {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getByProjectUri($project, $uri, $job = false)
  {
    static $sql = null;
    if (!$sql) {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
        SELECT *
        FROM ' . self::TABLE . ' e
        WHERE project_id = ? AND uri = ? AND booked = ?
      ');
    }
    $sql->execute(array($project, $uri, intval($job)));
    if ($sql->getRows() == 0) {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getAll(
      array $projects,
      DateTime $from = null,
      DateTime $to = null,
      $onlyJobs = false,
      $users = null,
      $order = array(),
      $fulltext = ''
    )
  {
    if (empty($projects))
      return array();

    if (!$users)
      $users = array();

    if (!is_array($users))
      $users = array($users);

    if (count($order) == 0) {
      $order['from'] = 'asc';
      $order['allday'] = 'desc';
    }

    $orderby = array();
    foreach ($order as $o => $s) {
      $orderby[] = '`' . $o . '` ' . $s;
    }

    $events = array();

    $params = $projects;
    $wInClause = implode(',', array_pad(array(), count($projects), '?'));

    /*
    $wFromTo = '';
    if($from)
    {
      $from->setTime(0, 0);
      $to = $to ?: clone $from;
      $to->setTime(23, 59, 59);
      $params[] = $from->format(self::DATETIME);
      $params[] = $to->format(self::DATETIME);
      $wFromTo = ' AND `to` >= ? AND `from` <= ?';
    }
    */

    $wFromTo = '';
    if ($from) {
      $from->setTime(0, 0);
      $params[] = $from->format(self::DATETIME);
      $wFromTo .= ' AND `to` >= ?';
    }
    if ($to) {
      $to->setTime(23, 59, 59);
      $params[] = $to->format(self::DATETIME);
      $wFromTo .= ' AND `from` <= ?';
    }

    $wUsers = '';
    if (count($users) > 0) {
      $wUsers = ' AND ( user_id IN (' . implode(',', $users) . ') )';
    }

    // alle termine
    $wJobs = '';
    if ($onlyJobs) {
      $wJobs = ' AND ( booked = 1)';
    } else {
      $wJobs = ' AND ( booked <> 1)';
    }

    $wFulltext = '';
    if ($fulltext != '') {
      $params[] = '%' . $fulltext . '%';
      $wFulltext = ' AND vt LIKE ? ';
    }

    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $sql->setQuery('
      SELECT *
      FROM ' . self::TABLE . ' e
      WHERE (rule_id = 0 || rule_id IS NULL) AND project_id IN (' . $wInClause . ')' . $wFromTo . $wUsers . $wJobs . $wFulltext . '
      ORDER BY ' . implode(',', $orderby) . '
    ', $params);

    foreach ($sql as $row) {
      $events[$row->getValue('id')] = new self($row->getRow());
    }

    if (!$onlyJobs && $from) {

      $rules = pz_calendar_rule::getAll($projects);
      foreach ($rules as $rule) {

        // $events = array_merge($events, pz_calendar_rule_event::getAll($rule, $from, $to));
        $rule_events = pz_calendar_rule_event::getAllRuleEvents($rule, $from, $to);
        $events = $events + $rule_events;

      }

      // self::sort($events);
    }

    return $events;
  }

  static public function getAttendeeEvents(DateTime $from = null, DateTime $to = null, $user = null, $ignore = array(pz_calendar_attendee::DECLINED) )
  {
    if (!$user)
      $user = pz::getUser();

    $emails = $user->getEmails();
    if (count($emails) == 0)
      return array();

    $events = array();
    $params = array();

    $wFromTo = '';
    if ($from) {
      $from->setTime(0, 0);
      $params[] = $from->format(self::DATETIME);
      $wFromTo .= ' AND `to` >= ?';
    }
    if ($to) {
      $to->setTime(23, 59, 59);
      $params[] = $to->format(self::DATETIME);
      $wFromTo .= ' AND `from` <= ?';
    }

    $params = array_merge($params, $emails);
    $wEmails = implode(',', array_pad(array(), count($emails), '?'));

    $params = array_merge($params, $ignore);

    $wIgnore = '';
    if (count($ignore) > 0) {
      $wIgnore = 'NOT IN (' . implode(',', array_pad(array(), count($ignore), '?')) . ')';
    }

    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $sql->setQuery('
      SELECT *
      FROM ' . self::TABLE . ' e
      LEFT JOIN ' . pz_calendar_attendee::TABLE . ' a
      ON e.id = a.event_id
      WHERE (rule_id = 0 || rule_id IS NULL)' . $wFromTo . ' AND a.email IN (' . $wEmails . ') AND a.status ' . $wIgnore . '
      ORDER BY `from` ASC, allday DESC
    ', $params);

    foreach ($sql as $row) {
      $events[$row->getValue('e.id')] = new self($row->getRow());
    }

    return $events;
  }


  static public function getJobTime(array $projects, $user_id, DateTime $from = null, DateTime $to = null)
  {
    $params = $projects;
    $wInClause = implode(',', array_pad(array(), count($projects), '?'));

    $wFromTo = '';
    if ($from) {
      $from->setTime(0, 0);
      $to = $to ?: clone $from;
      $to->setTime(23, 59, 59);
      $params[] = $from->format(self::DATETIME);
      $params[] = $to->format(self::DATETIME);
      $wFromTo = ' AND `to` >= ? AND `from` <= ?';
    }
    $params[] = $user_id;

    $sql = rex_sql::factory();
    $sql->setQuery('
      SELECT TIME_FORMAT(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(`to`) - UNIX_TIMESTAMP(`from`))), "PT%HH%iM%sS") AS time
      FROM ' . self::TABLE . '
      WHERE booked = 1 AND project_id IN (' . $wInClause . ')' . $wFromTo . ' AND user_id = ?
    ', $params);
    if ($sql->getRows() != 1)
      return null;
    return new DateInterval($sql->getValue('time'));
  }

  /**
   * @return array[pz_calendar_event]
   */
  static public function getAllBase($project, $jobs = false)
  {
    $params = array($project, intval($jobs));
    if ($jobs) {
      $add = ' AND user_id = ?';
      $params[] = pz::getUser()->getId();
    } else {
      $add = ' AND (user_id = ? OR (SELECT count(id) FROM pz_calendar_attendee a WHERE a.event_id = e.id) = 0 OR (SELECT count(id) FROM pz_calendar_attendee a WHERE a.event_id = e.id AND a.user_id = ?) > 0)';
      $params[] = pz::getUser()->getId();
      $params[] = pz::getUser()->getId();
    }
    $sql = rex_sql::factory();
    $sql->setQuery('
      SELECT *
      FROM ' . self::TABLE . ' e
      WHERE project_id = ? AND uri != "" AND booked = ?' . $add . '
    ', $params);

    $events = array();
    foreach ($sql as $row) {
      $events[] = new self($row->getRow());
    }
    return $events;
  }

  // ---------------------------
  
  static function resetProjectSubs($project_sub_id)
  {
    $s = rex_sql::factory();
    $s->setQuery('update ' . self::TABLE . ' set project_sub_id = 0 where project_sub_id = ?', array($project_sub_id) );
  }

  public function copy2Job()
  {

    $event = self::create();
    $event->setTitle($this->getTitle());
    $event->setProjectId($this->getProjectId());
    $event->setLocation($this->getLocation());
    $event->setBooked(1);
    $event->setAllDay(0);
    $event->setFrom($this->getFrom());
    $event->setTo($this->getTo());
    $event->setDescription($this->getDescription());
    $event->setUrl($this->getUrl());
    $event->setUserId(pz::getUser()->getId());
    $event->save();

    return $event;
  }

  static public function sort(array &$events)
  {
    usort($events,
      function (self $a, self $b) {
        return $a->compareTo($b);
      }
    );
  }
}
