<?php

class pz_clipboard
{
    public static $clipboards = [];

    public static function get($user_id = 0)
    {
        if (isset(self::$clipboards[$user_id])) {
            return self::$clipboards[$user_id];
        }

        $user = pz_user::get($user_id);
        if ($user === null) {
            return false;
        }

        self::$clipboards[$user_id] = new self();
        self::$clipboards[$user_id]->user = $user;

        return self::$clipboards[$user_id];
    }

    public function getClips($filter = [])
    {
        $filter[] = ['field' => 'user_id', 'type' => '=', 'value' => $this->user->getId()];

        $return = pz::getFilter($filter);

        $sql = rex_sql::factory();
        // $sql->debugsql = 1;
        $clips_array = $sql->getArray('SELECT c.* FROM pz_clipboard as c '.$return['where_sql'].' ORDER BY c.id desc', $return['params']);

        $clips = [];
        foreach ($clips_array as $clip) {
            $clips[] = new pz_clip($clip);
        }
        return $clips;
    }

    public function getMyClips($filter = [])
    {
        return $this->getClips($filter);
    }

    public function getUnreleasedClips($filter = [])
    {
        $d = pz::getDateTime()->format('Y-m-d H:i:s');
        $or = [];
        $or[] = ['field' => 'uri', 'type' => '=', 'value' => ''];
        $or[] = ['field' => 'online_date', 'type' => '=', 'value' => '0000-00-00 00:00:00'];
        $or[] = ['field' => 'offline_date', 'type' => '=', 'value' => '0000-00-00 00:00:00'];
        $or[] = ['field' => 'offline_date', 'type' => '<', 'value' => $d];
        $orfilter = pz::getFilter($or, null, null, 'OR');

        $filter[] = ['type' => 'query', 'query' => $orfilter['query'], 'params' => $orfilter['params']];
        $filter[] = ['field' => 'open', 'type' => '=', 'value' => 0];
        return $this->getClips($filter);
    }

    public function getReleasedClips($filter = [])
    {
        $filter[] = ['field' => 'open', 'type' => '=', 'value' => 1];
        $filter[] = ['field' => 'uri', 'type' => '<>', 'value' => ''];

        $filter[] = ['field' => 'online_date', 'type' => '<>', 'value' => '0000-00-00 00:00:00'];
        $filter[] = ['field' => 'offline_date', 'type' => '<>', 'value' => '0000-00-00 00:00:00'];

        $d = pz::getDateTime()->format('Y-m-d H:i:s');
        $filter[] = ['field' => 'online_date', 'type' => '<', 'value' => $d];
        $filter[] = ['field' => 'offline_date', 'type' => '>=', 'value' => $d];

        return $this->getClips($filter);
    }
}
