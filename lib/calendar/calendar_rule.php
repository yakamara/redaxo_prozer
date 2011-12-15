<?php

class pz_calendar_rule extends pz_calendar_element
{
  const
    TABLE = 'pz_calendar_rule',
    DAILY = 'DAILY',
    WEEKLY = 'WEEKLY',
    MONTHLY = 'MONTHLY',
    YEARLY = 'YEARLY',
    NEXT = 0,
    PREVIOUS = 'prev',
    LAST = -1,
    DAY = null,
    MO = 1,
    TU = 2,
    WE = 3,
    TH = 4,
    FR = 5,
    SA = 6,
    SU = 7,
    WEEKDAY = 8,
    WEEKEND = 9;

  static private $weekdayArray = array(self::MO, self::TU, self::WE, self::TH, self::FR);

  static private $weekendArray = array(self::SA, self::SU);

  protected $frequence;

  protected $interval;

  protected $weekDays = array();

  protected $days = array();

  protected $months = array();

  protected $nth;

  /**
   * @var DateTime
   */
  protected $begin;

  /**
  * @var DateTime
  */
  protected $end;

  protected $count;

  protected $exceptions = array();

  /**
   * @var pz_calendar_event
   */
  protected $base;

  protected function __construct(pz_calendar_event $base, array $params = array())
  {
    $this->base = $base;
    $this->begin = $base->getFrom();
    if(isset($params['r.id']))
      $this->id = $params['r.id'];
    if(isset($params['r.frequence']))
      $this->frequence = $params['r.frequence'];
    if(isset($params['r.interval']))
      $this->interval = $params['r.interval'];
    if(isset($params['r.weekdays']) && $params['r.weekdays'])
      $this->weekDays = explode(',', $params['r.weekdays']);
    if(isset($params['r.days']) && $params['r.days'])
      $this->days = explode(',', $params['r.days']);
    if(isset($params['r.months']) && $params['r.months'])
      $this->months = explode(',', $params['r.months']);
    if(isset($params['r.nth']))
      $this->nth = $params['r.nth'];
    if(isset($params['r.end']))
      $this->end = new DateTime($params['r.end'] .'23:59:59');
    if(isset($params['r.count']))
      $this->count = $params['r.count'];
    if(isset($params['r.exceptions']) && $params['r.exceptions'])
      $this->exceptions = array_map(array(new ReflectionClass('DateTime'), 'newInstance'), explode(',', $params['r.exceptions']));
  }

  public function getFrequence()
  {
    return $this->frequence;
  }

  public function getInterval()
  {
    return $this->interval;
  }

  public function getWeekDays()
  {
    return $this->weekDays;
  }

  public function getWeekDay()
  {
    if($this->weekDays == self::$weekdayArray)
    {
      return self::WEEKDAY;
    }
    if($this->weekDays == self::$weekendArray)
    {
      return self::WEEKEND;
    }
    return isset($this->weekDays[0]) ? $this->weekDays[0] : null;
  }

  public function getDays()
  {
    return $this->days;
  }

  public function getMonths()
  {
    return $this->months;
  }

  public function getNth()
  {
    return $this->nth;
  }

  /**
  * @return DateTime
  */
  public function getBegin()
  {
    return clone $this->begin;
  }

  /**
  * @return DateTime
  */
  public function getEnd()
  {
    return $this->end ? clone $this->end : null;
  }

  public function getCount()
  {
    return $this->count;
  }

  public function getExceptions()
  {
    return $this->exceptions;
  }

  /**
   * @return pz_calendar_event
   */
  public function getBase()
  {
    return $this->base;
  }

  public function getDescription()
  {
    return 'Description';
  }

  public function setFrequence($frequence)
  {
    return $this->setValue('frequence', $frequence);
  }

  public function setInterval($interval)
  {
    return $this->setValue('interval', $interval);
  }

  public function setWeekDays(array $weekdays)
  {
    sort($weekdays);
    return $this->setValue('weekDays', $weekdays);
  }

  public function setWeekDay($weekDay)
  {
    switch($weekDay)
    {
      case self::WEEKDAY: $weekDays = self::$weekdayArray; break;
      case self::WEEKEND: $weekDays = self::$weekendArray; break;
      case null: $weekDays = array(); break;
      default: $weekDays = array($weekDay);
    }
    return $this->setWeekDays($weekDays);
  }

  public function setDays(array $days)
  {
    $this->setWeekDay(0);
    $this->setNth(0);
    sort($days);
    return $this->setValue('days', $days);
  }

  public function setMonths(array $months)
  {
    $this->setWeekDay(0);
    $this->setNth(0);
    sort($months);
    return $this->setValue('months', $months);
  }

  public function setNth($nth)
  {
    return $this->setValue('nth', $nth);
  }

  public function setBegin(DateTime $begin)
  {
    return $this->setValue('begin', $begin);
  }

  public function setEnd(DateTime $end = null)
  {
    return $this->setValue('end', $end);
  }

  public function setCount($count)
  {
    return $this->setValue('count', $count);
  }

  public function setExceptions(array $exceptions)
  {
    sort($exceptions);
    return $this->setValue('exceptions', $exceptions);
  }

  public function addException(DateTime $time)
  {
    $exceptions = $this->exceptions;
    $exceptions[] = $time;
    return $this->setExceptions($exceptions);
  }

  public function save()
  {
    $base = $this->getBase();
    $sql = rex_sql::factory()
      ->setTable(self::TABLE);
    foreach(array_keys($this->changed) as $key)
    {
      if($key != 'begin')
      {
        $sql->setValue(strtolower($key), self::sqlValue($this->$key));
      }
    }
    if($this->new)
    {
      if(!$base->getId())
      {
        $base->save();
      }
      $sql->setValue('event_id', $base->getId())
        ->insert();
      $this->id = $sql->getLastId();
    }
    elseif(!empty($this->changed))
    {
      $sql->setWhere(array('id' => $this->id))
        ->update();
    }
    $sql->flushValues()
      ->setTable(pz_calendar_event::TABLE)
      ->setWhere(array('id' => $base->getId()))
      ->setValue('rule_id', $this->id)
      ->setRawValue('updated', 'NOW()');
    if(array_key_exists('begin', $this->changed))
    {
      $from = $this->changed['begin']->modify($this->begin->format(self::TIME));
      $to = clone $from;
      $to->add($base->getDuration());
      $sql->setValue('from', $from->format(self::DATETIME))
        ->setValue('to', $to->format(self::DATETIME));
    }
    $sql->update();
    $this->changed = array();
    $this->new = false;
  }

  public function delete()
  {
    rex_sql::factory()
      ->setTable(self::TABLE)
      ->setWhere(array('id' => $this->id))
      ->delete();
  }

  static public function create(pz_calendar_event $base)
  {
    $rule = new self($base);
    $rule->new = true;
    return $rule;
  }

  static public function get(pz_calendar_event $base)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
    		SELECT *
        FROM '. self::TABLE .' r
        WHERE event_id = ?
      ');
    }

    $sql->execute(array($base->getId()));
    return new self($base, $sql->getRow());
  }

  static public function getAll(array $projects)
  {
    $rules = array();

    $params = $projects;
    $params[] = pz::getUser()->getId();
    $inClause = implode(',', array_pad(array(), count($projects), '?'));
    $sql = rex_sql::factory();
    $sql->setQuery('
  		SELECT *
      FROM '. self::TABLE .' r
      LEFT JOIN '. pz_calendar_event::TABLE .' e
      ON event_id = e.id
      WHERE project_id in ('. $inClause .') AND (booked = 0 OR user_id = ?)
    ', $params);
    foreach($sql as $row)
    {
      $params = $row->getRow();
      $rules[] = new self(parent::factory('pz_calendar_event', $params), $params);
    }

    return $rules;
  }
}