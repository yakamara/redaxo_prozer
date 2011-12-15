<?php

class pz_project_directory extends pz_project_node
{
  public function getChildren()
  {
    $sql = rex_sql::factory();
    $params = array($this->vars['project_id'], $this->getId());
    $array = $sql->getArray('SELECT * FROM pz_project_file WHERE project_id = ? AND parent_id = ? ORDER BY name', $params);
    $children = array();
    foreach($array as $row)
    {
      $children[] = static::factory($row);
    }
    return $children;
  }

  public function getChild($name)
  {
    $sql = rex_sql::factory();
    $params = array($this->vars['project_id'], $name, $this->getId());
    $array = $sql->getArray('SELECT * FROM pz_project_file WHERE project_id = ? AND name = ? AND parent_id = ? LIMIT 2', $params);
    if(count($array) != 1)
      return null;
    return static::factory($array[0]);
  }

  public function childExists($name)
  {
    return is_object($this->getChild($name));
  }

  public function createFile($name, $data = null)
  {
    if(!$this->createNode($name, false))
      return false;

    $file = $this->getChild($name);
    $file->putContent($data);
    return true;
  }

  public function createDirectory($name)
  {
    return $this->createNode($name, true);
  }

  private function createNode($name, $is_directoy = false)
  {
    if($this->childExists($name))
      return false;

    $sql = rex_sql::factory();
    $sql->setTable('pz_project_file')
      ->setValue('name', $name)
      ->setValue('parent_id', $this->getId())
      ->setValue('is_directory', $is_directoy)
      ->setValue('project_id', $this->vars['project_id'])
      ->setRawValue('created', 'NOW()')
      ->setRawValue('updated', 'NOW()')
      ->setValue('created_user_id', pz::getUser()->getId())
      ->setValue('updated_user_id', pz::getUser()->getId())
      ->insert();
    return true;
  }

  public function delete()
  {
    foreach($this->getChildren() as $child)
      $child->delete();
    parent::delete();
  }
}

class pz_project_root_directory extends pz_project_directory
{
  public function __construct(pz_project $project)
  {
    $vars['id'] = 0;
    $vars['name'] = $project->getId() .' - '. str_replace('/', '-', $project->getName());
    $vars['parent_id'] = 0;
    $vars['project_id'] = $project->getId();
    parent::__construct($vars);
  }

  public function moveTo(pz_project_directory $destination, $name = null)
  {
    throw new rex_exception('The project root directory can not be moved!');
  }

  public function delete()
  {
    throw new rex_exception('The project root directory can not be deleted!');
  }

  public function getAllPaths()
  {
    $sql = rex_sql::factory();
    $sql->prepareQuery('SELECT id, name FROM pz_project_file WHERE project_id = '. $this->getProjectId() .' AND is_directory = 1 AND parent_id = ? ORDER BY name');
    $array = array();
    $func = function($id, $path) use($sql, &$func, &$array)
    {
      $array[] = array('id' => $id, 'label' => $path);
      $sql->execute(array($id));
      $rows = array();
      foreach($sql as $row)
      {
        $rows[] = array($row->getValue('id'), $path . $row->getValue('name') .'/');
      }
      foreach($rows as $row)
      {
        $func($row[0], $row[1]);
      }
    };
    $func(0, '/');
    return $array;
  }

}