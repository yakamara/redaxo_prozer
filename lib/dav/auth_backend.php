<?php

class pz_sabre_auth_backend extends Sabre_DAV_Auth_Backend_AbstractDigest
{
  private $sql;

	/**
   * Returns the digest hash for a user.
   *
   * @param string $realm
   * @param string $username
   * @return string|null
   */
  public function getDigestHash($realm,$username)
  {
    $this->sql = rex_sql::factory();
    $this->sql->setQuery('SELECT * FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', array($username));

    if($this->sql->getRows() != 1)
    {
      throw new Sabre_DAV_Exception_NotAuthenticated('User doesn\'t exist!');
    }

    return $this->sql->getValue('digest');
  }

  /**
   * Authenticates the user based on the current request.
   *
   * If authentication is succesful, true must be returned.
   * If authentication fails, an exception must be thrown.
   *
   * @throws Sabre_DAV_Exception_NotAuthenticated
   * @return bool
   */
  public function authenticate(Sabre_DAV_Server $server,$realm)
  {
    parent::authenticate($server, $realm);

    if($this->sql)
    {
      pz::setUser(new pz_user($this->sql));
      return true;
    }
  }
}