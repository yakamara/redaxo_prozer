<?php

class pz_calendar_attendee extends pz_calendar_element
{
    const TABLE = 'pz_calendar_attendee';
    const NEEDSACTION = 'NEEDS-ACTION';
    const ACCEPTED = 'ACCEPTED';
    const TENTATIVE = 'TENTATIVE';
    const DECLINED = 'DECLINED';

    protected $user_id = '';

    protected $email = '';

    protected $name = '';

    protected $status;

    protected function __construct(array $params = [])
    {
        if (isset($params['a.id'])) {
            $this->id = $params['a.id'];
        }
        if (isset($params['a.user_id'])) {
            $this->user_id = $params['a.user_id'];
        }
        if (isset($params['a.email'])) {
            $this->email = strtolower($params['a.email']);
        }
        if (isset($params['a.name'])) {
            $this->name = $params['a.name'];
        }
        if (isset($params['a.status'])) {
            $this->status = $params['a.status'];
        }
    }

    /**
     * @return pz_calendar_event
     */
    public function getEvent()
    {
        return $this->event;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getEmail()
    {
        return $this->email;
        //return $this->getUserValue('email');
    }

    public function getName()
    {
        return $this->name;
        //return $this->getUserValue('name');
    }

    /*private function getUserValue($key)
    {
      if($this->user_id)
      {
        return pz_user::get($this->user_id)->getValue($key);
      }

      return $this->$key;
    }*/

    public function getStatus()
    {
        return $this->status;
    }

    public function setUserId($user_id)
    {
        return $this->setValue('user_id', (int) $user_id);
    }

    public function setEmail($email)
    {
        return $this->setValue('email', $email);
    }

    public function setName($name)
    {
        return $this->setValue('name', $name);
    }

    public function setStatus($status)
    {
        return $this->setValue('status', $status);
    }

    public static function create()
    {
        $attendee = new self();
        $attendee->new = true;
        return $attendee;
    }

    public static function saveAll(pz_calendar_event $event)
    {
        $attendees = $event->getAttendees();
        $id = $event->getId(true);
        $and = '';
        if (count($attendees) > 0) {
            $values = '';
            $params = [];
            $time = time();
            foreach ($attendees as $attendee) {
                if ($attendee->user_id > 0) {
                    if (($user = pz_user::get($attendee->user_id))) {
                        $values .= '(?,?,?,?,?,?),';
                        array_push($params, $id, $user->getId(), $user->getEmail(), $user->getName(), $attendee->status, $time);
                    }
                } else {
                    $values .= '(?,?,?,?,?,?),';
                    array_push($params, $id, $attendee->user_id, $attendee->email, $attendee->name, $attendee->status, $time);
                }
            }
            pz_sql::factory()->setQuery('
                INSERT INTO ' . self::TABLE . ' (event_id, user_id, email, name, status, timestamp)
                VALUES ' . rtrim($values, ',') . '
                ON DUPLICATE KEY UPDATE name = VALUES(name), status = VALUES(status), timestamp = VALUES(timestamp)
            ', $params);
            $and = ' AND timestamp < ' . $time;
        }
        pz_sql::factory()->setQuery('
            DELETE FROM ' . self::TABLE . '
            WHERE event_id = ?' . $and . '
        ', [$id]);
    }

    /**
     * @return pz_calendar_attendee
     */
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
        if ($sql->getRows() > 0) {
            return new self($sql->getRow());
        }
        return null;
    }

    public static function getAll(pz_calendar_event $event)
    {
        $attendees = [];

        static $sql = null;
        if (!$sql) {
            $sql = pz_sql::factory();
            $sql->prepareQuery('
                SELECT *
                FROM ' . self::TABLE . ' a
                WHERE event_id = ?
            ');
        }

        $sql->execute([$event->getId(true)]);
        foreach ($sql as $row) {
            $attendees[] = new self($row->getRow());
        }
        return $attendees;
    }

    public static function getStatusArray()
    {
        return [self::NEEDSACTION, self::ACCEPTED, self::TENTATIVE, self::DECLINED];
    }
}
