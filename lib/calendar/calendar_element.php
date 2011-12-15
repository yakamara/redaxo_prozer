<?php

abstract class pz_calendar_element
{
  const
    DATE = 'Y-m-d',
    TIME = 'H:i:s',
    DATETIME = 'Y-m-d H:i:s';

  protected $id;

  protected $changed = array();

  protected $new = false;

  public function getId()
  {
    return $this->id;
  }

  public function abort()
  {
    foreach($this->changed as $key => $original)
    {
      $this->$key = $original;
    }
    $this->changed = array();
  }

  protected function getValue($key)
  {
    return $this->$key;
  }

  protected function setValue($key, $value)
  {
    if(!$this->hasChanged($key))
    {
      $this->changed[$key] = $this->$key;
    }
    $this->$key = $value;
    return $this;
  }

  protected function hasChanged($key)
  {
    return array_key_exists($key, $this->changed);
  }

  static protected function factory($class, $param1 = null, $param2 = null, $param3 = null)
  {
    return new $class($param1, $param2, $param3);
  }

  static protected function sqlValue($value)
  {
    if(is_array($value))
    {
      return implode(',', array_map(__METHOD__, $value));
    }
    if($value instanceof self)
    {
      return $value->getId();
    }
    if($value instanceof DateTime || $value instanceof DateInterval)
    {
      return $value->format(self::DATETIME);
    }
    return $value;
  }
}