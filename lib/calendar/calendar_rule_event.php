<?php

class pz_calendar_rule_event extends pz_calendar_event
{
  const
    THIS = 1,
    FUTURE = 2,
    ALL = 3;

  /**
  * @var pz_calendar_rule
  */
  protected $rule;

  /**
  * @var DateTime
  */
  protected $base_from;

  protected $exception = false;

  protected function __construct(pz_calendar_rule $rule, array $params = array(), $exception = false)
  {
    parent::__construct($params);
    $this->rule = $rule;
    $this->exception = $exception;
    if(isset($params['e.base_from']))
      $this->base_from = new DateTime($params['e.base_from']);
    elseif($this->from)
      $this->base_from = clone $this->from;
  }

  public function getId($real = false)
  {
    return $real ? $this->id : $this->rule->getBase()->getId() .'_'. $this->base_from->getTimestamp();
  }

  public function getUri()
  {
    return $this->rule->getBase()->getUri();
  }

  /**
   * @return pz_calendar_rule
   */
  public function getRule()
  {
    return $this->rule;
  }

  /**
  * @return DateTime
  */
  public function getBaseFrom()
  {
    return $this->base_from;
  }

  public function getAttendees()
  {
    return $this->exception ? parent::getAttendees() : $this->rule->getBase()->getAttendees();
  }

  public function isException()
  {
    return $this->exception;
  }

  protected function getValue($key)
  {
    return $this->$key === null ? $this->rule->getBase()->$key : $this->$key;
  }

  public function setBaseFrom(DateTime $baseFrom)
  {
    return $this->setValue('base_from', $baseFrom);
  }

  public function save($mode = self::THIS)
  {
    if($this->exception)
    {
      $mode = self::THIS;
    }
    elseif($mode == self::FUTURE && $this->isBase())
    {
      $mode = self::ALL;
    }
    $base = $this->rule->getBase();
    switch($mode)
    {
      case self::ALL:
        $set = '';
        $params = array();
        foreach($this->changed as $key => $original)
        {
          if($key == 'from' || $key == 'to')
          {
            $add = $original->diff($this->$key);
            $base->setValue($key, $base->$key->add($add));
            $function = $add->invert ? 'SUBTIME' : 'ADDTIME';
            if($key == 'from')
            {
              $excpt = $this->rule->getExceptions();
              foreach($excpt as $exception)
              {
                $exception->add($add);
              }
              $this->rule->setExceptions($excpt);
              $set .= 'base_from = '. $function .'(base_from, ?), ';
              $params[] = $add->format('%H:%I:%S');
            }
            $set .= '`'. $key .'` = '. $function .'(`'. $key .'`, IF(DATE_FORMAT(`'. $key .'`, "%H:%i") = ?, ?, 0)), ';
            $params[] = $original->format('H:i');
            $params[] = $add->format('%H:%I:%S');
          }
          else
          {
            $base->setValue($key, $this->$key);
          }
        }
        $this->rule->save();
        $base->save();
        $params[] = $this->rule->getId();
        $params[] = $base->getId();

        rex_sql::factory()->setQuery('
        	UPDATE '. self::TABLE .'
        	SET '. $set .'updated = NOW()
        	WHERE rule_id = ? AND id != ?
        ', $params);
        break;

      case self::FUTURE:
        $nBase = parent::create();
        foreach(array('project_id', 'title', 'location', 'description', 'url', 'from', 'to') as $key)
        {
          $nBase->setValue($key, $this->getValue($key));
        }
        $nBase->setAllDay($this->isAllDay());
        $nBase->setSequence($this->getSequence() + 1);
        if($this->hasChanged('attendees'))
        {
          $nBase->setAttendees($this->attendees);
        }
        $nRule = pz_calendar_rule::create($nBase);
        foreach(array('Frequence', 'Interval', 'Days', 'Months', 'WeekDays', 'Nth', 'End', 'Count') as $key)
        {
          $nRule->{'set'.$key}($this->rule->{'get'.$key}());
        }
        $excpt = array();
        $nExcpt = $this->rule->getExceptions();
        $this->rule->abort();
        $exceptions = $this->rule->getExceptions();
        $nExcpt = $nExcpt == $exceptions ? array() : $nExcpt;
        $add = isset($this->changed['from']) ? $this->changed['from']->diff($this->from) : new DateInterval('0');
        foreach($this->rule->getExceptions() as $exception)
        {
          if($exception < $this->from)
          {
            $excpt[] = $exception;
          }
          else
          {
            $nExcpt[] = $exception->add($add);
          }
        }
        $nRule->setExceptions($nExcpt);
        $nRule->save();
        $end = $this->getFrom();
        $end->modify('-1 day');
        $this->rule
          ->setEnd($end)
          ->setExceptions($excpt)
          ->save();

        $params = array();
        $set = '';
        foreach(array('from', 'to') as $key)
        {
          if(isset($this->changed[$key]))
          {
            $add = $this->changed[$key]->diff($this->$key);
            $function = $add->invert ? 'SUBTIME' : 'ADDTIME';
            if($key == 'from')
            {
              $set .= 'base_from = '. $function .'(base_from, ?), ';
              $params[] = $add->format('%H:%I:%S');
            }
            $set .= '`'. $key .'` = '. $function .'(`'. $key .'`, IF(DATE_FORMAT(`'. $key .'`, "%H:%i") = ?, ?, 0)), ';
            $params[] = $this->changed[$key]->format('H:i');
            $params[] = $add->format('%H:%I:%S');
          }
        }
        $params[] = $nRule->getId();
        $params[] = $this->from->format(self::DATETIME);
        $params[] = $this->rule->getId();
        $params[] = $nBase->getId();
        rex_sql::factory()->setQuery('
        	UPDATE '. self::TABLE .'
        	SET '. $set .'rule = ?, updatedate = NOW()
        	WHERE `from` >= ? AND rule_id = ? AND id != ?
        ', $params);

        $this->rule = $nRule;
        break;

      case self::THIS:
      default:
        $rule = $this->rule;
        if(!$this->exception)
        {
          $this->changed['from'] = isset($this->changed['from']) ? $this->changed['from'] : $this->from;
          $this->changed['to'] = isset($this->changed['to']) ? $this->changed['to'] : $this->to;
          $this->new = true;
          $this->setValue('base_from', $this->baseFrom);
          $this->setValue('uri', null);
          $this->setValue('user_id', null);
          $this->setValue('booked', null);
          $this->setValue('sequence', $rule->getBase()->getSequence() + 1);
          $this->setValue('rule_id', $rule->getId());
          $this->exception = true;
        }
        parent::save();
        $this->rule = $rule;
        break;
    }
    $this->changed = array();
  }

  public function delete($mode = self::THIS)
  {
    if($mode == self::FUTURE && $this->isBase())
    {
      $mode = self::ALL;
    }
    switch($mode)
    {
      case self::ALL:
        self::_delete('e.rule_id = ?', array($this->rule->getId()));
        $this->rule->delete();
        break;

      case self::FUTURE:
        $params = array(
          ':rule' => $this->rule->getId(),
          ':from' => $this->from->format(self::DATETIME)
        );
        self::_delete('e.rule_id = :rule AND `e.from` >= :from', $params);
        $end = $this->getFrom();
        $end->modify('-1 day');
        $this->rule->setEnd($end)->save();
        break;

      case self::THIS:
      default:
        if($this->exception)
        {
          parent::delete();
        }
        else
        {
          $this->rule->addException($this->getFrom())->save();
        }
        break;
    }
  }

  private function isBase()
  {
    return $this->id == $this->rule->getBase()->getId();
  }

  static public function create(pz_calendar_rule $rule = null, DateTime $baseFrom = null)
  {
    $event = new self($rule);
    $event->new = true;
    $event->setValue('base_from', $baseFrom);
    return $event;
  }

  static public function get($id)
  {
    list($id, $baseFrom) = explode('_', $id);
    $baseFrom = new DateTime('@'. $baseFrom);
    $base = parent::get($id);
    $rule = pz_calendar_rule::getRule($base);

    return self::getByBaseFrom($rule, $baseFrom);
  }

  static public function getByBaseFrom(pz_calendar_rule $rule, DateTime $baseFrom)
  {
    $base = $rule->getBase();

    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
				SELECT *
				FROM '. self::TABLE .' e
				WHERE rule_id = :rule AND `base_from` = :basefrom AND id != :id
      ');
    }
    $sql->execute(array(
      ':rule' => $rule->getId(),
      ':basefrom' => $baseFrom->format(self::DATETIME),
      ':id' => $base->getId()
    ));
    if($sql->getRows() == 0)
    {
      $to = clone $baseFrom;
      $to->add($base->getDuration());
      $params = array(
        'e.from' => $baseFrom->format(self::DATETIME),
        'e.to' => $to->format(self::DATETIME)
      );
      $exception = false;
    }
    else
    {
      $params = $sql->getRow();
      $exception = true;
    }
    return new self($rule, $params, $exception);
  }

  static public function getAll($rule, DateTime $from, DateTime $to = null)
  {
    $from->setTime(0, 0);
    $to = $to ?: clone $from;
    $to->setTime(23, 59, 59);

    $end = $rule->getEnd();
    if($end !== null && $from > $end || $to < $rule->getBegin())
    {
      return array();
    }

    $exceptionEvents = array();

    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
        SELECT *
        FROM '. self::TABLE .' e
      	WHERE rule_id = :rule AND id != :id AND `from` <= :to AND `to` >= :from
      ');
    }
    $base = $rule->getBase();
    $sql->execute(array(
      ':rule' => $rule->getId(),
      ':id' => $base->getId(),
      ':from' => $from->format(self::DATETIME),
      ':to' => $to->format(self::DATETIME)
    ));
    foreach($sql as $row)
    {
      $row = $row->getRow();
      $exceptionEvents[$row['e.base_from']] = $row;
    }

    $events = array();

    $frequence = $rule->getFrequence();
    $interval = $rule->getInterval();
    $iFrom = $base->getFrom();
    $iTo = $base->getTo();
    $dur = $base->getDuration();
    $time = array(
      'h' => (integer) $iFrom->format('H'),
      'm' => (integer) $iFrom->format('i')
    );
    $addQueue = new SplQueue();
    $callbackBefore = null;
    $callbackAfter = null;
    switch($frequence)
    {
      case pz_calendar_rule::DAILY:
        $addQueue->enqueue($interval .' day');
        break;
      case pz_calendar_rule::WEEKLY:
        $days = $rule->getWeekDays();
        $count = count($days);
        if($count > 0)
        {
          $day = $iFrom->format('N');
          $i = self::nextIndex($days, $day);
          if($days[$i] != $day)
          {
            $iFrom->modify(self::nthWeekDay(pz_calendar_rule::NEXT, $days[$i]));
            $iTo = clone $iFrom;
            $iTo->add($dur);
          }
        }
        if($count < 2)
        {
          $addQueue->enqueue($interval .' weeks');
        }
        else
        {
          for($j = 0, ++$i; $j < $count; ++$j, ++$i)
          {
            if($i == $count)
            {
              $addQueue->enqueue(($interval - 1) .' weeks '. self::nthWeekDay(pz_calendar_rule::NEXT, $days[0]));
              $i = 0;
            }
            else
            {
              $addQueue->enqueue(self::nthWeekDay(pz_calendar_rule::NEXT, $days[$i]));
            }
          }
        }
        break;
      case pz_calendar_rule::MONTHLY:
      case pz_calendar_rule::YEARLY:
        $weekDays = $rule->getWeekDays();
        $days = $rule->getDays();
        $months = $rule->getMonths();
        $nth = $rule->getNth();
        $countWeekDays = count($weekDays);
        $countDays = count($days);
        $countMonths = count($months);
        if($weekDays && $nth)
        {
          if($frequence == pz_calendar_rule::MONTHLY)
          {
            $monthInterval = $interval;
          }
          else
          {
            $monthInterval = 'this';
            $month = $nth == pz_calendar_rule::LAST ? 12 : 1;
            $callbackBefore = function(DateTime $iFrom) use ($month)
            {
              return $iFrom->setDate($iFrom->format('Y') + 1, $month, 1);
            };
          }
          if($countWeekDays > 1)
          {
            $weekDay = 0;
            $sign = $nth < 0 ? -1 : 1;
            $nth2 = $nth == pz_calendar_rule::LAST ? pz_calendar_rule::LAST : 1;
            $nth = abs($nth);
            $callbackAfter = function(DateTime $iFrom) use ($weekDays, $nth, $sign)
            {
              $last = $iFrom->format('N');
              if(in_array($last, $weekDays))
              {
                --$nth;
              }
              if($nth > 0)
              {
                $i = pz_calendar_rule_event::nextIndex($weekDays, $last);
                if($sign < 0 || $weekDays[$i] == $last)
                {
                  $i += $sign;
                }
                $count = count($weekDays);
                $days = 0;
                for($j = 0; $j < $nth; ++$j, $i += $sign)
                {
                  if($i == $count || $i == -1)
                  {
                    $i = $sign < 0 ? $count - 1 : 0;
                  }
                  if($sign * $last > $sign * $weekDays[$i])
                  {
                    $days += 7 - abs($last - $weekDays[$i]);
                  }
                  else
                  {
                    $days += abs($weekDays[$i] - $last);
                  }
                  $last = $weekDays[$i];
                }
                $iFrom->modify(($sign * $days) .' days');
              }
              return $iFrom;
            };
            $nth = $nth2;
          }
          else
          {
            $weekDay = $weekDays[0];
          }
          $addQueue->enqueue(self::nthWeekDay($nth, $weekDay) .' of '. $monthInterval .' month');
        }
        elseif($frequence == pz_calendar_rule::MONTHLY)
        {
          if($countDays > 1)
          {
            $day = intval($iFrom->format('d'));
            $i = self::nextIndex($days, $day);
            $dayQueue = new SplQueue();
            for($j = 0, ++$i; $j < $countDays; ++$j, ++$i)
            {
              if($i == $countDays)
              {
                $i = 0;
                $addQueue->enqueue($interval .' month');
              }
              else
              {
                $addQueue->enqueue(null);
              }
              $dayQueue->enqueue($days[$i]);
            }
            $callbackBefore = function(DateTime $iFrom) use ($dayQueue)
            {
              $day = $dayQueue->dequeue();
              $dayQueue->enqueue($day);
              return $iFrom->setDate($iFrom->format('Y'), $iFrom->format('m'), $day);
            };
          }
          else
          {
            $addQueue->enqueue($interval .' month');
          }
        }
        else
        {
          if($countMonths > 1)
          {
            $month = intval($iFrom->format('m'));
            $i = self::nextIndex($months, $month);
            for($j = 0, ++$i; $j < $countMonths; ++$j, ++$i)
            {
              if($i == $countMonths)
              {
                $addQueue->enqueue((12 - $months[$i - 1] + $months[0]) .' month '. ($interval - 1) .' year');
                $i = 0;
              }
              else
              {
                $addQueue->enqueue(($months[$i] - $months[$i - 1]) .' month');
              }
            }
          }
          else
          {
            $addQueue->enqueue($interval .' year');
          }
        }
        break;
    }
    $rCount = $rule->getCount();
    $exceptions = $rule->getExceptions();
    $addQueueCount = count($addQueue);
    for($i = 0; ($rCount === null || $i < $rCount) && ($end === null || $iFrom < $end) && $iFrom < $to; ++$i)
    {
      if($iTo > $from && !in_array($iFrom, $exceptions))
      {
        $iFromStr = $iFrom->format(self::DATETIME);
        if(array_key_exists($iFromStr, $exceptionEvents))
        {
          $params = $exceptionEvents[$iFromStr];
          unset($exceptionEvents[$iFromStr]);
          $exceptionFlag = true;
        }
        else
        {
          $params = array(
            'e.from' => $iFromStr,
            'e.to' => $iTo->format(self::DATETIME)
          );
          $exceptionFlag = false;
        }
        $events[] = new self($rule, $params, $exceptionFlag);
      }
      $iFrom = $callbackBefore ? $callbackBefore($iFrom) : $iFrom;
      if($addQueueCount > 0)
      {
        $add = $addQueue->dequeue();
        if(is_string($add))
        {
          $iFrom->modify($add);
        }
        $addQueue->enqueue($add);
      }
      $iFrom = $callbackAfter ? $callbackAfter($iFrom) : $iFrom;
      $iFrom->setTime($time['h'], $time['m'], 0);
      $iTo->setTimestamp($iFrom->getTimestamp())->add($dur);
    }

    if(count($exceptionEvents) > 0)
    {
      $params = array();
      $params[] = $rule->getId();
      $params[] = $rule->getBase()->getId();
      $params = array_merge($params, array_keys($exceptionEvents));
      $in = implode(',', array_fill(0, count($exceptionEvents), '?'));
      self::_delete('e.rule_id = ? AND e.id != ? AND e.base_from IN ('. $in .')', $params);
    }

    return $events;
  }

  static public function getAllExceptions(pz_calendar_rule $rule)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
        SELECT *
        FROM '. self::TABLE .' e
      	WHERE rule_id = :rule AND id != :id
      ');
    }
    $sql->execute(array(
      ':rule' => $rule->getId(),
      ':id' => $rule->getBase()->getId()
    ));
    $events = array();
    foreach($sql as $row)
    {
      $events[] = new self($rule, $row->getRow(), true);
    }
    return $events;
  }

  static private function nthWeekDay($nth, $weekDay)
  {
    static $nths = array('next', 'first', 'second', 'third', 'fourth', pz_calendar_rule::LAST => 'last', pz_calendar_rule::PREVIOUS => 'previous');
    static $weekDays = array('day', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
    return $nths[$nth] .' '. $weekDays[$weekDay];
  }

  static public function nextIndex(array $array, $item)
  {
    $count = count($array);
    for($i = 0; $i < $count && $array[$i] < $item; ++$i);
    return $i == $count ? 0 : $i;
  }
}