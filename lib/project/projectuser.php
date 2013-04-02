<?php

class pz_projectuser extends pz_model
{

  public $vars = array();
  private $isProjectuser = false;
  public $project = null;
  public $user = null;

  function __construct($vars = array(), $user, $project)
  {
    if (count($vars) > 5) {
      $this->setVars($vars);
      $this->user = $user;
      $this->project = $project;
      return true;
    }
    return false;
  }

  static function get($user, $project)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $projectusers = $s->getArray('select * from pz_project_user as pu where pu.project_id= ?  and pu.user_id= ?', array($project->getId(), $user->getId()));

    if (count($projectusers) == 1) {
      $projectuser = current($projectusers);
      return new self($projectuser, $user, $project);
    }
    return false;
  }

  public function setEmails($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('emails', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setCalendarEvents($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('calendar', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setCalendarJobs($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('calendar_jobs', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setCalDavEvents($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('caldav', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setCalDavJobs($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('caldav_jobs', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setFiles($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('files', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setWebDav($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('webdav', $status);
    $s->update();

    $this->update();

    return $status;
  }

  public function setAdmin($status = 1)
  {
    $s = rex_sql::factory();
    // $s->debugsql = 1;
    $s->setTable('pz_project_user');
    $s->setWhere(array('id' => $this->getId()));
    $s->setValue('admin', $status);
    $s->update();

    $this->update();

    return $status;
  }


  public function getId()
  {
    return $this->getVar('id');
  }

  public function getUser()
  {
    return $this->user;
  }

  public function getProject()
  {
    return $this->project;
  }

  public function hasCalendarEvents()
  {
    if ($this->vars['calendar'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  public function hasCalendarJobs()
  {
    if ($this->vars['calendar_jobs'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  public function hasCalDavEvents()
  {
    if ($this->vars['caldav'] == 1 && $this->hasCalendarEvents()) {
      return true;
    }
    return false;
  }

  public function hasCalDavJobs()
  {
    if ($this->vars['caldav_jobs'] == 1 && $this->hasCalendarJobs()) {
      return true;
    }
    return false;
  }

  public function hasWebDav()
  {
    // TODO;
    // - WebDav ist nicht mehr Projektbezogen

    if ($this->user->hasPerm('webdav'))
      return true;
    if ($this->user->isAdmin())
      return true;

    return false;
  }

  public function hasFiles()
  {
    if ($this->vars['files'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  public function hasEmails()
  {
    if ($this->vars['emails'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  public function hasWiki()
  {
    if ($this->vars['wiki'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  public function isAdmin()
  {
    if ($this->vars['admin'] == 1 || $this->vars['admin'] == 1) {
      return true;
    }
    return false;
  }

  // ---------------------

  public function update()
  {
    $this->saveToHistory('update');
  }

  public function create()
  {
    if ($this->hasCalendarEvents() || $this->hasCalendarJobs()) {
      $s = rex_sql::factory();
      $s->setTable('pz_project_user');
      $s->setWhere(array('id' => $this->getId()));
      if($this->hasCalendarEvents())
        $s->setValue('caldav', 1);
      if($this->hasCalendarJobs())
        $s->setValue('caldav_jobs', 1);
      $s->update();
    }
    $this->saveToHistory('update');
  }

  public function delete()
  {
    $this->saveToHistory('update');

    $a = rex_sql::factory();
    // $a->debugsql = 1;
    $a->setTable('pz_project_user');
    $a->setWhere(
      array(
        'id' => $this->getId(),
        'project_id' => $this->project->getId()
      )
      );

    $a->delete();
    return true;
  }

  public function saveToHistory($mode = 'update')
	{
	  $sql = rex_sql::factory();
	  $sql->setTable('pz_history')
	    ->setValue('control', 'projectuser')
	    ->setValue('project_id', $this->project->getId())
	    ->setValue('data_id', $this->getId())
	    ->setValue('user_id', pz::getUser()->getId())
	    ->setRawValue('stamp', 'NOW()')
	    ->setValue('mode', $mode);
	    
	  if($mode != 'delete')
	  {
	    $data = $this->getVars();
	    $sql->setValue('data', json_encode($data));
	  }
	  
	  $sql->insert();
	}



}
