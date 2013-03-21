<?php

class pz_login extends rex_login
{
  private $stayLoggedIn = false;

  public function __construct()
  {
    parent::__construct();

    $this->setSystemId('pz_'. rex::getProperty('instname'));
    $this->setSessionDuration(rex::getProperty('session_duration'));
    $this->setIdColumn('id');
    $qry = 'SELECT * FROM pz_user WHERE status=1';
    $this->setUserQuery($qry .' AND id = :id');
    $this->setLoginQuery($qry .' AND login = :login');
  }

  public function setStayLoggedIn($stayLoggedIn = false)
  {
    $this->stayLoggedIn = $stayLoggedIn;
  }

  public function checkLogin()
  {
    $sql = rex_sql::factory();
    $userId = $this->getSessionVar('UID');
    $cookiename = 'pz_user_'. sha1($this->systemId.rex::getProperty('instname'));

    if($cookiekey = rex_cookie($cookiename, 'string'))
    {
      if(!$userId)
      {
        $sql->setQuery('SELECT id FROM pz_user WHERE cookiekey = ? LIMIT 1', array($cookiekey));
        if($sql->getRows() == 1)
        {
          $this->setSessionVar('UID', $sql->getValue('id'));
          setcookie($cookiename, $cookiekey, time() + 60*60*24*365, '/');
        }else
        {
          setcookie($cookiename, '', time() - 3600, '/');
        }
      }
      $this->setSessionVar('STAMP', time());
    }

    $check = parent::checkLogin();

    if($check)
    {
      // gelungenen versuch speichern | login_tries = 0
      if($this->userLogin != '')
      {
        $this->regenerateSessionId();
        $params = array();
        $add = '';
        if($this->stayLoggedIn)
        {
          $cookiekey = $this->user->getValue('cookiekey') ?: sha1($this->systemId . time() . $this->userLogin);
          $add = 'cookiekey = ?, ';
          $params[] = $cookiekey;
          setcookie($cookiename, $cookiekey, time() + 60*60*24*365, '/');
        }

        array_push($params, time(), pz::getDateTime()->format("Y-m-d H:i:s"), session_id(), $this->userLogin);
        $sql->setQuery('UPDATE pz_user SET '. $add .'login_tries=0, lasttrydate=?, last_login=?, session_id=? WHERE login=? LIMIT 1', $params);
      }
      pz::setUser(new pz_user($this->user), $this);

    }else
    {
      // fehlversuch speichern | login_tries++
      if($this->userLogin != '')
      {
        $sql->setQuery('UPDATE pz_user SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', array(time(), $this->userLogin));
      }
    }

    if ($this->isLoggedOut() && $userId != '')
    {
      $sql->setQuery('UPDATE pz_user SET session_id="", cookiekey="" WHERE id=? LIMIT 1', array($userId));
      setcookie($cookiename, '', time() - 3600, '/');
    }

    return $check;
  }

  public function setUser($user)
  {
    $this->user = $user;
  }
}