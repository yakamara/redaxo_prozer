<?php

class pz_calendar_event extends pz_calendar_element
{
  const TABLE = 'pz_calendar_event';

  protected $uri;

  protected $project_id;

  protected $title;

  protected $location;

  protected $description;

  protected $url;

  protected $booked;

  /**
   * @var DateTime
   */
  protected $from;

  /**
   * @var DateTime
   */
  protected $to;

  protected $allday;

  protected $rule_id;

  /**
   * @var DateTime
   */
  protected $created;

  /**
   * @var DateTime
   */
  protected $updated;

  protected $sequence = 0;

  protected $user_id;

  protected $attendees;

  protected $alarms;

  protected function __construct(array $params = array())
  {
    if(isset($params['e.id']))
      $this->id = $params['e.id'];
    if(isset($params['e.uri']))
      $this->uri = $params['e.uri'];
    if(isset($params['e.project_id']))
      $this->project_id = $params['e.project_id'];
    if(isset($params['e.title']))
      $this->title = $params['e.title'];
    if(isset($params['e.description']))
      $this->description = $params['e.description'];
    if(isset($params['e.url']))
      $this->url = $params['e.url'];
    if(isset($params['e.location']))
      $this->location = $params['e.location'];
    if(isset($params['e.booked']))
      $this->booked = (boolean) $params['e.booked'];
    if(isset($params['e.from']))
      $this->from = new DateTime($params['e.from']);
    if(isset($params['e.to']))
      $this->to = new DateTime($params['e.to']);
    if(isset($params['e.allday']))
      $this->allday = (boolean) $params['e.allday'];
    if(isset($params['e.rule_id']))
      $this->rule_id = $params['e.rule_id'];
    if(isset($params['e.created']))
      $this->created = new DateTime($params['e.created']);;
    if(isset($params['e.updated']))
      $this->updated = new DateTime($params['e.updated']);
    if(isset($params['e.sequence']))
      $this->sequence = $params['e.sequence'];
    if(isset($params['e.user_id']))
      $this->user_id = $params['e.user_id'];
  }

  public function getUri()
  {
    return $this->getValue('uri');
  }

  public function getProjectId()
  {
    return $this->getValue('project_id');
  }

  public function getTitle()
  {
    return $this->getValue('title');
  }

  public function getLocation()
  {
    return $this->getValue('location');
  }

  public function getDescription()
  {
    return $this->getValue('description');
  }

  public function getUrl()
  {
    return $this->getValue('url');
  }

  public function isBooked()
  {
    return $this->getValue('booked');
  }

  /**
   * @return DateTime
   */
  public function getFrom()
  {
    return clone $this->from;
  }

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

  public function hasRule()
  {
    return (boolean) $this->rule_id;
  }

  /**
  * @return DateTime
  */
  public function getCreated()
  {
    return clone $this->created;
  }

  /**
  * @return DateTime
  */
  public function getUpdated()
  {
    return clone $this->updated;
  }

  public function getSequence()
  {
    return $this->sequence;
  }

  public function getUserId()
  {
    return $this->getValue('user_id');
  }

  public function getAttendees()
  {
    if($this->attendees === null)
    {
      $this->attendees = pz_calendar_attendee::getAll($this);
    }
    return $this->attendees;
  }

  public function getAlarms()
  {
    if($this->alarms === null)
    {
      $this->alarms = pz_calendar_alarm::getAll($this);
    }
    return $this->alarms;
  }

  public function setUri($uri)
  {
    return $this->setValue('uri', $uri);
  }

  public function setProjectId($project)
  {
    return $this->setValue('project_id', $project);
  }

  public function setTitle($title)
  {
    return $this->setValue('title', $title);
  }

  public function setLocation($location)
  {
    return $this->setValue('location', $location);
  }

  public function setDescription($description)
  {
    return $this->setValue('description', $description);
  }

  public function setUrl($url)
  {
    return $this->setValue('url', $url);
  }

  public function setBooked($booked)
  {
    return $this->setValue('booked', $booked);
  }

  public function setFrom(DateTime $from)
  {
    return $this->setValue('from', $from);
  }

  public function setTo(DateTime $to)
  {
    return $this->setValue('to', $to);
  }

  public function setAllDay($allday)
  {
    return $this->setValue('allday', $allday);
  }

  public function setCreated(DateTime $created = null)
  {
    return $this->setValue('created', $created);
  }

  public function setUpdated(DateTime $updated = null)
  {
    return $this->setValue('updated', $updated);
  }

  public function setSequence($sequence)
  {
    return $this->setValue('sequence', $sequence);
  }

  public function setUserId($user)
  {
    return $this->setValue('user_id', $user);
  }

  public function setAttendees(array $attendees)
  {
    return $this->setValue('attendees', $attendees);
  }

  public function setAlarms(array $alarms)
  {
    return $this->setValue('alarms', $alarms);
  }

  public function compareTo(self $other)
  {
  	$return = 0;
  
    if($this->from == $other->from)
    {
      $return = 0;
    }elseif(($this->allday xor $other->allday) && $this->from->format('dmY') == $other->from->format('dmY'))
    {
      $return = $this->allday ? -1 : 1;
    }else
    {
    	$return = $this->from < $other->from ? -1 : 1;
    }
    return $return;
    
  }

  public function save()
  {
    $sql = rex_sql::factory()
      ->setTable(self::TABLE);
    $ignore = array('attendees', 'alarms');
    foreach(array_keys($this->changed) as $key)
    {
      if($this->allday && $key == 'from')
      {
        $this->from->setTime(0, 0, 0);
      }
      elseif($this->allday && $key == 'to')
      {
        $this->to->setTime(23, 59, 59);
      }
      if(!in_array($key, $ignore))
        $sql->setValue($key, self::sqlValue($this->$key));
    }
    $sql->setValue('vt', $this->getTitle() .' '. $this->getDescription() .' '.$this->getLocation());
    if(!$this->hasChanged('updated'))
    {
      $sql->setRawValue('updated', 'NOW()');
    }
    if($this->new)
    {
      if(!$this->hasChanged('user_id'))
      {
        $sql->setValue('user_id', pz::getUser()->getId());
      }
      if(!$this->hasChanged('created'))
      {
        $sql->setRawValue('created', 'NOW()');
      }
      if(!$this->hasChanged('uri'))
      {
        $sql->setRawValue('uri', 'CONCAT(UPPER(UUID()), ".ics")');
      }
      if(!$this->hasChanged('booked'))
      {
        $sql->setValue('booked', 0);
      }
      $sql->insert();
      $this->id = $sql->getLastId();
    }
    else
    {
      if(!$this->hasChanged('sequence'))
      {
        $sql->setRawValue('sequence', 'sequence + 1');
      }
      $sql->setWhere(array('id' => $this->id))
        ->update();
    }
    if($this->hasChanged('attendees'))
    {
      pz_calendar_attendee::saveAll($this);
    }
    if($this->hasChanged('alarms'))
    {
      pz_calendar_alarm::saveAll($this);
    }

    pz_sabre_caldav_backend::incrementCtag($this->project_id);

    $this->changed = array();
    $this->new = false;
  }

  public function delete()
  {
    self::_delete('e.id = ?', array($this->id));

    pz_sabre_caldav_backend::incrementCtag($this->project_id);
  }

  public function saveToHistory($mode = 'update')
  {
    $data = array();
    if($mode != 'delete')
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE id = ?', array($this->id));
      $arr = $sql->getArray();
      $data = $arr[0];
      $sql->setQuery('SELECT * FROM '. pz_calendar_attendee::TABLE .' WHERE event_id = ?', array($this->id));
      $data['attendees'] = $sql->getArray();
      $sql->setQuery('SELECT * FROM '. pz_calendar_alarm::TABLE .' WHERE event_id = ?', array($this->id));
      $data['alarms'] = $sql->getArray();
      if($data['rule_id'])
      {
        $sql->setQuery('SELECT * FROM '. pz_calendar_rule::TABLE .' WHERE id = ?', array($data['rule_id']));
        $arr = $sql->getArray();
        $data['rule'] = $arr[0];
        $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE rule_id = ? AND id != ?', array($data['rule_id'], $this->id));
        $data['rule']['exception_events'] = $sql->getArray();
      }
    }
    rex_sql::factory()
      ->setTable('pz_calendar_history')
      ->setValue('event_id', $this->id)
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
    	FROM '. self::TABLE .' e
    	LEFT JOIN '. pz_calendar_attendee::TABLE .' at
    	ON at.event_id = e.id
    	LEFT JOIN '. pz_calendar_alarm::TABLE .' al
    	ON al.event_id = e.id
    	WHERE '. $where .'
    ', $params);
  }

  static public function create()
  {
    $event = new self;
    $event->new = true;
    return $event;
  }

  static public function get($id)
  {
    if(strpos($id, '_') !== false)
    {
      return pz_calendar_rule_event::get($id);
    }

    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' e
      	WHERE id = ?
      ');
    }
    $sql->execute(array($id));
    if($sql->getRows() == 0)
    {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getByProjectUri($project, $uri, $job = false)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' e
      	WHERE project_id = ? AND uri = ? AND booked = ?
      ');
    }
    $sql->execute(array($project, $uri, intval($job)));
    if($sql->getRows() == 0)
    {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getAll(
  	array $projects, 
  	DateTime $from = null, 
  	DateTime $to = null, 
  	$onlyJobs = false, 
  	$user_id = null, 
  	$order = array(),
  	$fulltext = ''
  	)
  {
    if(empty($projects))
      return array();

	if(count($order) == 0) {
		$order["from"] = 'asc';
		$order["allday"] = 'desc';
	}

	$orderby = array();
	foreach($order as $o => $s)
	{
		$orderby[] = '`'.$o.'` '.$s;
	}

    $events = array();

    $params = $projects;
    $wInClause = implode(',', array_pad(array(), count($projects), '?'));

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

    $wJobs = '';
    if(!$onlyJobs || $user_id)
    {
      $params[] = $user_id ? $user_id : pz::getUser()->getId();
      $wJobs = $onlyJobs ? 'booked = 1 AND user_id = ?' : 'booked <> 1 OR user_id = ?';
      $wJobs = ' AND ('. $wJobs .')';
    }

	$wFulltext = '';
	if($fulltext != "")
	{
		$params[] = '%'.$fulltext.'%';
		$wFulltext = ' AND vt LIKE ? ';
	}

    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $sql->setQuery('
    	SELECT *
    	FROM '. self::TABLE .' e
    	WHERE rule_id = 0 AND project_id IN ('. $wInClause .')'. $wFromTo . $wJobs . $wFulltext .'
    	ORDER BY '.implode(",",$orderby).'
    ', $params);
    foreach($sql as $row)
    {
      $events[] = new self($row->getRow());
    }

    if(!$onlyJobs && $from)
    {
      $rules = pz_calendar_rule::getAll($projects);
      foreach($rules as $rule)
      {
        $events = array_merge($events, pz_calendar_rule_event::getAll($rule, $from, $to));
      }

      self::sort($events);
    }

    return $events;
  }

  static public function getJobTime(array $projects, $user_id, DateTime $from = null, DateTime $to = null)
  {
    $params = $projects;
    $wInClause = implode(',', array_pad(array(), count($projects), '?'));

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
    $params[] = $user_id;

    $sql = rex_sql::factory();
    $sql->setQuery('
      SELECT TIME_FORMAT(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(`to`) - UNIX_TIMESTAMP(`from`))), "PT%HH%iM%sS") AS time
      FROM '. self::TABLE .'
      WHERE rule_id = 0 AND project_id IN ('. $wInClause .')'. $wFromTo .' AND user_id = ?
    ', $params);
    if($sql->getRows() != 1)
      return null;
    return new DateInterval($sql->getValue('time'));
  }

  /**
   * @return array[pz_calendar_event]
   */
  static public function getAllBase($project, $jobs = false)
  {
    $params = array($project, intval($jobs));
    if($jobs)
    {
      $add = ' AND user_id = ?';
      $params[] = pz::getUser()->getId();
    }
    $sql = rex_sql::factory();
    $sql->setQuery('
    	SELECT *
    	FROM '. self::TABLE .' e
    	WHERE project_id = ? AND uri != "" AND booked = ?'. $add .'
    ', $params);

    $events = array();
    foreach($sql as $row)
    {
      $events[] = new self($row->getRow());
    }
    return $events;
  }

  static public function sort(array &$events)
  {
    usort($events,
      function(pz_calendar_event $a, pz_calendar_event $b)
      {
        return $a->compareTo($b);
      }
    );
  }
}