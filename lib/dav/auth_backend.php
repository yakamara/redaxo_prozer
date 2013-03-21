<?php

class pz_sabre_auth_backend extends Sabre_DAV_Auth_Backend_AbstractBasic
{
  /**
   * Validates a username and password
   *
   * This method should return true or false depending on if login
   * succeeded.
   *
   * @param string $username
   * @param string $password
   * @return bool
   */
  protected function validateUserPass($username, $password)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', array($username));

    if($sql->getRows() == 1 && rex_login::passwordVerify($password, $sql->getValue('password')))
    {
      pz::setUser(new pz_user($sql));
      return true;
    }
    return false;
  }
}