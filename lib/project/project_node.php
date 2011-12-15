<?php

abstract class pz_project_node extends pz_model
{
  public function __construct(array $vars)
  {
    $this->setVars($vars);
  }

  static public function factory(array $vars)
  {
    $class = $vars['is_directory'] ? 'pz_project_directory' : 'pz_project_file';
    return new $class($vars);
  }

  static public function get($id)
  {
    $sql = rex_sql::factory();
    $array = $sql->getArray('SELECT * FROM pz_project_file WHERE id = ? LIMIT 2', array($id));
    if(count($array) != 1)
      return null;
    return static::factory($array[0]);
  }

  public function isDirectory()
  {
    return $this instanceof pz_project_directory;
  }

  public function getId()
  {
    return $this->vars['id'];
  }

  public function getName()
  {
    return $this->vars['name'];
  }

  public function getProjectId()
  {
    return $this->vars['project_id'];
  }

  public function getParentId()
  {
    return $this->vars['parent_id'];
  }

  public function getParent()
  {
    return self::get($this->getParentId());
  }

  public function getPath()
  {
    $path = '/';
    $current = $this;
    while($current->getParentId() != 0)
    {
      $current = $this->getParent();
      $path = '/'. $current->getName() . $path;
    }
    return $path;
  }

  public function setName($name)
  {
    $this->moveTo($this->getParent(), $name);
  }

  public function moveTo(pz_project_directory $destination, $name = null)
  {
    if($destination->getProjectId() != $this->getProjectId())
      throw new rex_exception('The destination must be in the same project!');

    $sql = rex_sql::factory();
    $name = $name ?: $this->getName();
    $sql->setQuery('SELECT id FROM pz_project_file WHERE project_id = ? AND name = ? AND parent_id = ?', array($this->getProjectId(), $name, $destination->getId()));
    if($sql->getRows() > 0)
      throw new rex_exception('Destination path already exists');

    $sql->setQuery('UPDATE pz_project_file SET name = ?, parent_id = ?, updated = NOW(), updated_user_id = ? WHERE id = ?', array($name, $destination->getId(), pz::getUser()->getId(), $this->getId()));

    $this->vars['parent_id'] = $destination->getId();
    $this->vars['name'] = $name;
  }

  public function delete()
  {
    static $sql;
    if(!$sql)
    {
      $sql = rex_sql::factory();
      $sql->prepareQuery('DELETE FROM pz_project_file WHERE id = ?');
    }
    $sql->execute(array($this->getId()));
  }

  public function getLastModified()
  {
    return strtotime($this->getVar('updated'));
  }
}