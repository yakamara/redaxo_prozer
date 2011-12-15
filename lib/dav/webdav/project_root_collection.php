<?php

class pz_sabre_project_root_collection extends Sabre_DAV_SimpleCollection
{
  private $childrenChecked = false;

  public function getChild($name)
  {
    $this->checkChildren();
    return parent::getChild($name);
  }

  public function getChildren()
  {
    $this->checkChildren();
    return parent::getChildren();
  }

  public function checkChildren()
  {
    if(!$this->childrenChecked && ($user = pz::getUser()))
    {
      foreach($user->getWebDavProjects() as $project)
      {
        $this->addChild(new pz_sabre_project_root_directory($project));
      }
      $this->childrenChecked = true;
    }
  }

  public function createFile($name, $data = null)
  {
    throw new Sabre_DAV_Exception('Permission denied to create file (filename ' . $name . ')');
  }

  public function createDirectory($name)
  {
    throw new Sabre_DAV_Exception('Permission denied to create directory');
  }
}