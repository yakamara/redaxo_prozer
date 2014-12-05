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

    protected $description = '';

    protected $summary = '';

    protected $emails = '';

    protected $attachment = '';

    protected $location;

    protected $structured_location;

    protected $proximity;

    /**
     * @var DateTime
     */
    protected $acknowledged;

    protected $related_id;

    protected function __construct(array $params = [])
    {
        if (isset($params['a.id'])) {
            $this->id = $params['a.id'];
        }
        if (isset($params['a.uid'])) {
            $this->uid = $params['a.uid'];
        }
        if (isset($params['a.action'])) {
            $this->action = $params['a.action'];
        }
        if (isset($params['a.trigger'])) {
            if (strpos($params['a.trigger'], 'P') === false) {
                $this->trigger = new DateTime($params['a.trigger']);
            } else {
                $this->trigger = new DateInterval(ltrim($params['a.trigger'], '-'));
                $this->trigger->invert = strpos($params['a.trigger'], '-') === 0;
            }
        }
        if (isset($params['a.description'])) {
            $this->description = $params['a.description'];
        }
        if (isset($params['a.summary'])) {
            $this->summary = $params['a.summary'];
        }
        if (isset($params['a.emails'])) {
            $this->emails = array_filter(explode(',', $params['a.emails']));
        }
        if (isset($params['a.attachment'])) {
            $this->attachment = $params['a.attachment'];
        }
        if (isset($params['a.location'])) {
            $this->location = $params['a.location'];
        }
        if (isset($params['a.structured_location'])) {
            $this->structured_location = $params['a.structured_location'];
        }
        if (isset($params['a.proximity'])) {
            $this->proximity = $params['a.proximity'];
        }
        if (isset($params['a.acknowledged'])) {
            $this->acknowledged = new DateTime($params['a.acknowledged']);
        }
        if (isset($params['a.related_id'])) {
            $this->related_id = $params['a.related_id'];
        }
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
        if ($this->triggerIsInterval()) {
            $format = ($this->trigger->invert ? '-P' : 'P')
                . ($this->trigger->y ? '%yY' : '')
                . ($this->trigger->m ? '%mM' : '')
                . ($this->trigger->d ? '%dD' : '') . 'T'
                . ($this->trigger->h ? '%hH' : '')
                . ($this->trigger->i ? '%iM' : '')
                . ($this->trigger->s ? '%sS' : '');
            if (substr($format, -2) == 'PT') {
                $format .= '0S';
            }
            return $this->trigger->format(rtrim($format, 'T'));
        } else {
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

    public function getLocation()
    {
        return $this->location;
    }

    public function getStructuredLocation()
    {
        return $this->structured_location;
    }

    public function getProximity()
    {
        return $this->proximity;
    }

    /**
     * @return DateTime
     */
    public function getAckknowledged()
    {
        if ($this->acknowledged) {
            return clone $this->acknowledged;
        }
        return null;
    }

    public function getRelatedId()
    {
        return $this->related_id;
    }

    /**
     * @return pz_calendar_alarm
     */
    public function getRelated()
    {
        return self::get($this->related_id);
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

    public function setLocation($location)
    {
        return $this->setValue('location', $location);
    }

    public function setStructuredLocation($structured_location)
    {
        return $this->setValue('structured_location', $structured_location);
    }

    public function setProximity($proximity)
    {
        return $this->setValue('proximity', $proximity);
    }

    public function setAcknowledged(DateTime $acknowledged = null)
    {
        return $this->setValue('acknowledged', $acknowledged);
    }

    public function setRelatedId($related_id)
    {
        return $this->setValue('related_id', $related_id);
    }

    public static function create()
    {
        $alarm = new self;
        $alarm->new = true;
        return $alarm;
    }

    public static function saveAll(pz_calendar_item $item)
    {
        $column = $item instanceof pz_calendar_todo ? 'todo_id' : 'event_id';
        $alarms = $item->getAlarms();
        $id = $item->getId(true);
        $and = '';
        if (count($alarms) > 0) {
            $values = '';
            $params = [];
            $time = time();
            $i = 0;
            foreach ($alarms as $alarm) {
                if ($alarm->uid) {
                    $values .= '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?),';
                    $params[] = $alarm->uid;
                } else {
                    $values .= '(CONCAT(UPPER(UUID()),"' . $i++ . '"),?,?,?,?,?,?,?,?,?,?,?,?,?,?),';
                }
                $emails = is_array($alarm->emails) && count($alarm->emails) > 0 ? implode(',', $alarm->emails) : '';
                array_push($params, $id, pz::getUser()->getId(), $alarm->action, $alarm->getTriggerString(), $alarm->description, $alarm->summary, $emails, $alarm->attachment, $alarm->location, $alarm->structured_location, $alarm->proximity, self::sqlValue($alarm->acknowledged), $alarm->related_id, $time);
            }
            pz_sql::factory()->setQuery('
                INSERT INTO ' . self::TABLE . ' (uid, `' . $column . '`, user_id, `action`, `trigger`, description, summary, emails, attachment, location, structured_location, proximity, acknowledged, related_id, timestamp)
                VALUES ' . rtrim($values, ',') . '
                ON DUPLICATE KEY UPDATE `' . $column . '` = VALUES(`' . $column . '`), user_id = VALUES(user_id), `action` = VALUES(`action`), `trigger` = VALUES(`trigger`), description = VALUES(description), summary = VALUES(summary), emails = VALUES(emails), attachment = VALUES(attachment), location = VALUES(location), structured_location = VALUES(structured_location), proximity = VALUES(proximity), acknowledged = VALUES(acknowledged), related_id = VALUES(related_id), timestamp = VALUES(timestamp)
            ', $params);
            $and = ' AND timestamp < ' . $time;
        }
        pz_sql::factory()->setQuery('
            DELETE FROM ' . self::TABLE . '
            WHERE `' . $column . '` = ? AND user_id = ?' . $and . '
        ', [$id, pz::getUser()->getId()]);
    }

    public static function get($id)
    {
        static $sql = null;
        if (!$sql) {
            $sql = pz_sql::factory();
            $sql->prepareQuery('
                SELECT *
                FROM ' . self::TABLE . ' a
                WHERE id = ?
            ');
        }
        $sql->execute([$id]);
        return new self($sql->getRow());
    }

    public static function getByUid($uid)
    {
        static $sql = null;
        if (!$sql) {
            $sql = pz_sql::factory();
            $sql->prepareQuery('
                SELECT *
                FROM ' . self::TABLE . ' a
                WHERE uid = ?
            ');
        }
        $sql->execute([$uid]);
        return new self($sql->getRow());
    }

    public static function getAll(pz_calendar_item $item)
    {
        $column = $item instanceof pz_calendar_todo ? 'todo_id' : 'event_id';
        $sql = pz_sql::factory();
        $sql->prepareQuery('
            SELECT *
            FROM ' . self::TABLE . ' a
            WHERE `' . $column . '` = ? AND user_id = ?
        ');
        $sql->execute([$item->getId(true), pz::getUser()->getId()]);
        $alarms = [];
        foreach ($sql as $row) {
            $alarms[] = new self($row->getRow());
        }
        return $alarms;
    }
}
