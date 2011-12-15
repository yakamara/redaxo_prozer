<?php

class pz_user_role implements rex_user_role_interface
{
  private $perms = array();

  private function __construct()
  {

  }

  public function hasPerm($perm)
  {
    return in_array($perm, $this->perms);
  }

  public function getComplexPerm($user, $key)
  {
    return null;
  }

  static public function get($id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT perms FROM pz_user_role WHERE id = ?', array($id));
    if($sql->getRows() == 0)
    {
      return null;
    }
    return new self();;
  }
}