<?php

class pz_calendar_alarm extends pz_calendar_element
{
  const TABLE = 'pz_calendar_alarm';

  protected $uid;

  protected $user_id;

  protected $action;

  /**
   * @var DateTime|DateInterval
   */
  protected $trigger;

  protected $description;

  protected $summary;

  protected $emails;

  protected $attachment;

  /**
   * @var DateInterval
   */
  protected $before;

  protected function __construct(array $params = array())
  {
    if(isset($params['a.id']))
      $this->id = $params['a.id'];
    if(isset($params['a.uid']))
      $this->uid = $params['a.uid'];
    if(isset($params['a.action']))
      $this->action = $params['a.action'];
    if(isset($params['a.trigger']))
    {
      if(strpos($params['a.trigger'], 'P') === false)
      {
        $this->trigger = new DateTime($params['a.trigger']);
      }
      else
      {
        $this->trigger = new DateInterval(ltrim($params['a.trigger'], '-'));
        $this->trigger->invert = strpos($params['a.trigger'], '-') === 0;
      }
    }
    if(isset($params['a.description']))
      $this->description = $params['a.description'];
    if(isset($params['a.summary']))
      $this->summary = $params['a.summary'];
    if(isset($params['a.emails']))
      $this->emails = explode(',', $params['a.emails']);
    if(isset($params['a.attachment']))
      $this->attachment = $params['a.attachment'];
  }

  public function getUid()
  {
    return $this->uid;
  }

  public function getAction()
  {
    return $this->action;
  }

  /**
  * @return DateTime|DateInterval
  */
  public function getTrigger()
  {
    return clone $this->trigger;
  }

  public function getTriggerString()
  {
    if($this->triggerIsInterval())
    {
      $format = ($this->trigger->invert ? '-P' : 'P')
        . ($this->trigger->y ? '%yY' : '')
        . ($this->trigger->m ? '%mM' : '')
        . ($this->trigger->d ? '%dD' : '') .'T'
        . ($this->trigger->h ? '%hH' : '')
        . ($this->trigger->i ? '%iM' : '')
        . ($this->trigger->s ? '%sS' : '');
      return $this->trigger->format(rtrim($format, 'T'));
    }
    else
    {
      return $this->trigger->format(self::DATETIME);
    }
  }

  public function triggerIsInterval()
  {
    return $this->trigger instanceof DateInterval;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function getSummary()
  {
    return $this->summary;
  }

  public function getEmails()
  {
    return $this->emails;
  }

  public function getAttachment()
  {
    return $this->attachment;
  }

  public function setUid($uid)
  {
    return $this->setValue('uid', $uid);
  }

  public function setAction($action)
  {
    return $this->setValue('action', $action);
  }

  public function setTrigger($trigger)
  {
    return $this->setValue('trigger', $trigger);
  }

  public function setDescription($description)
  {
    return $this->setValue('description', $description);
  }

  public function setSummary($summary)
  {
    return $this->setValue('summary', $summary);
  }

  public function setEmails(array $emails)
  {
    return $this->setValue('emails', $emails);
  }

  public function setAttachment($attachment)
  {
    return $this->setValue('attachment', $attachment);
  }

  static public function create()
  {
    $alarm = new self;
    $alarm->new = true;
    return $alarm;
  }

  static public function saveAll(pz_calendar_event $event)
  {
    $alarms = $event->getAlarms();
    $id = $event->getId(true);
    $and = '';
    if(count($alarms) > 0)
    {
      $values = '';
      $params = array();
      $time = time();
      $i = 0;
      foreach($alarms as $alarm)
      {
        if($alarm->uid)
        {
          $values .= '(?,?,?,?,?,?,?,?,?,?),';
          $params[] = $alarm->uid;
        }
        else
        {
          $values .= '(CONCAT(UPPER(UUID()),"'. $i++ .'"),?,?,?,?,?,?,?,?,?),';
        }
        $emails = is_array($alarm->emails) && count($alarm->emails) > 0 ? implode(',', $alarm->emails) : null;
        array_push($params, $id, pz::getUser()->getId(), $alarm->action, $alarm->getTriggerString(), $alarm->description, $alarm->summary, $emails, $alarm->attachment, $time);
      }
      rex_sql::factory()->setQuery('
        INSERT INTO '. self::TABLE .' (uid, event_id, user_id, `action`, `trigger`, description, summary, emails, attachment, timestamp)
        VALUES '. rtrim($values, ',') .'
        ON DUPLICATE KEY UPDATE event_id = VALUES(event_id), user_id = VALUES(user_id), `action` = VALUES(`action`), `trigger` = VALUES(`trigger`), description = VALUES(description), summary = VALUES(summary), emails = VALUES(emails), attachment = VALUES(attachment), timestamp = VALUES(timestamp)
      ', $params);
      $and = ' AND timestamp < '. $time;
    }
    rex_sql::factory()->setQuery('
      DELETE FROM '. self::TABLE .'
      WHERE event_id = ? AND user_id = ?'. $and .'
    ', array($id, pz::getUser()->getId()));
  }

  static public function get($id)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' a
      	WHERE id = ?
      ');
    }
    $sql->execute(array($id));
    return new self($sql->getRow());
  }

  static public function getAll(pz_calendar_event $event)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' a
      	WHERE event_id = ? AND user_id = ?
      ');
    }
    $sql->execute(array($event->getId(true), pz::getUser()->getId()));
    $alarms = array();
    foreach($sql as $row)
    {
      $alarms[] = new self($row->getRow());
    }
    return $alarms;
  }
}