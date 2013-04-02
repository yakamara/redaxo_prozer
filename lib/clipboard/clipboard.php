<?php

class pz_clipboard
{

  static $clipboards = array();
  
  static function get( $user_id = 0 )
  {
    if(isset(self::$clipboards[$user_id]))
    return self::$clipboards[$user_id];
    
    $user = pz_user::get($user_id);
    if($user === null)
    return false;
    
    self::$clipboards[$user_id] = new self();
    self::$clipboards[$user_id]->user = $user;
    
    return self::$clipboards[$user_id];
  }

  public function getClips($filter = array())
  {
  
    $filter[] = array("field"=>"user_id", "type" => "=", "value" => $this->user->getId());
  
    $return = pz::getFilter($filter);
    
    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $clips_array = $sql->getArray('SELECT c.* FROM pz_clipboard as c '.$return['where_sql'].' ORDER BY c.id desc', $return["params"]);
    
    $clips = array();
    foreach($clips_array as $clip)
    {
      $clips[] = new pz_clip($clip);
    }
    return $clips;
  }


  public function getMyClips($filter = array())
  {
    return $this->getClips($filter);
  }


  public function getUnreleasedClips($filter = array())
  {
    $d = pz::getDateTime()->format("Y-m-d H:i:s");
    $or = array();
    $or[] = array('field'=>'uri', 'type'=>'=', 'value' => "");
    $or[] = array('field'=>'online_date', 'type'=>'=', 'value' => "0000-00-00 00:00:00");
    $or[] = array('field'=>'offline_date', 'type'=>'=', 'value' => "0000-00-00 00:00:00");
    $or[] = array('field'=>'offline_date', 'type'=>'<', 'value' => $d);
    $orfilter = pz::getFilter($or, NULL, NULL, "OR");

    $filter[] = array('type'=>'query', 'query' => $orfilter["query"], 'params' => $orfilter["params"]);
    $filter[] = array('field'=>'open', 'type'=>'=', 'value' => 0);
    return $this->getClips($filter);
  }


  public function getReleasedClips($filter = array())
  {

    $filter[] = array('field'=>'open', 'type'=>'=', 'value' => 1);
    $filter[] = array('field'=>'uri', 'type'=>'<>', 'value' => "");

    $filter[] = array('field'=>'online_date', 'type'=>'<>', 'value' => "0000-00-00 00:00:00");
    $filter[] = array('field'=>'offline_date', 'type'=>'<>', 'value' => "0000-00-00 00:00:00");

    $d = pz::getDateTime()->format("Y-m-d H:i:s");
    $filter[] = array('field'=>'online_date', 'type'=>'<', 'value' => $d);
    $filter[] = array('field'=>'offline_date', 'type'=>'>=', 'value' => $d);
    
    return $this->getClips($filter);
  }



}