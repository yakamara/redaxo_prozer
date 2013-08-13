<?php

abstract class pz_calendar_item extends pz_calendar_element
{
  protected $uri;

  protected $project_id;

  protected $project_sub_id;

  protected $title;

  protected $description;

  /**
   * @var DateTime
   */
  protected $from;

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

  protected $rule_id;

  protected $alarms;

  public function getUri()
  {
    return $this->getValue('uri');
  }

  public function getProjectId()
  {
    return intval($this->getValue('project_id'));
  }

  public function getProject()
  {
    return pz_project::get($this->getValue('project_id'));
  }

  public function getProjectSubId()
  {
    return intval($this->getValue('project_sub_id'));
  }

  public function getTitle()
  {
    return $this->makeSingleLine($this->getValue('title'));
  }

  public function getDescription()
  {
    return $this->checkMultiLine($this->getValue('description'));
  }

  /**
   * @return DateTime
   */
  public function getFrom()
  {
    if($this->from)
      return clone $this->from;
    return null;
  }

  public function setUri($uri)
  {
    return $this->setValue('uri', $uri);
  }

  public function setProjectId($project_id)
  {
    return $this->setValue('project_id', $project_id);
  }

  public function setProjectSubId($project_sub_id)
  {
    if(!$this->getProject()->hasProjectSubId($project_sub_id))
      $project_sub_id = 0;
    return $this->setValue('project_sub_id', $project_sub_id);
  }

  public function setTitle($title)
  {
    return $this->setValue('title', $title);
  }

  public function setDescription($description)
  {
    return $this->setValue('description', $description);
  }

  public function setFrom(DateTime $from = null)
  {
    return $this->setValue('from', $from);
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

  public function getUser()
  {
    return pz_user::get($this->getValue('user_id'));
  }

  public function setUserId($user)
  {
    return $this->setValue('user_id', $user);
  }

  public function hasRule()
  {
    return (boolean) $this->rule_id;
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

  public function getAlarms()
  {
    if($this->alarms === null)
    {
      $this->alarms = pz_calendar_alarm::getAll($this);
    }
    return $this->alarms;
  }

  public function setAlarms(array $alarms)
  {
    return $this->setValue('alarms', $alarms);
  }

  public function makeSingleLine($value) {
    return str_replace(array("\n","\r"),array(" ",""),$value);
  }

  public function checkMultiLine($value) {
    return str_replace(array("\r"),array(""),$value);
  }

}
