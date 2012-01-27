<?php

class pz_login extends rex_login
{
  private $stayLoggedIn = false;

  public function __construct()
  {
    parent::__construct();

    $this->setSysID('pz_'. rex::getProperty('instname'));
    $this->setSessiontime(rex::getProperty('session_duration'));
    $this->setUserID('id');
    $qry = 'SELECT * FROM pz_user WHERE status=1';
    $this->setUserquery($qry .' AND id = :id');
    $this->setLoginquery($qry .' AND login = :login AND password = :password');
  }

  public function setStayLoggedIn($stayLoggedIn = false)
  {
    $this->stayLoggedIn = $stayLoggedIn;
  }

  public function checkLogin()
  {
    $sql = rex_sql::factory();
    $userId = $this->getSessionVar('UID');
    $cookiename = 'pz_user_'. sha1($this->system_id.rex::getProperty('instname'));

    if($cookiekey = rex_cookie($cookiename, 'string'))
    {
      if(!$userId)
      {
        $sql->setQuery('SELECT id FROM pz_user WHERE cookiekey = ? LIMIT 1', array($cookiekey));
        if($sql->getRows() == 1)
        {
          $this->setSessionVar('UID', $sql->getValue('id'));
          setcookie($cookiename, $cookiekey, time() + 60*60*24*365, '/');
        }
        else
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
      if($this->usr_login != '')
      {
        $this->sessionFixation();
        $params = array();
        $add = '';
        if($this->stayLoggedIn)
        {
          $cookiekey = $this->USER->getValue('cookiekey') ?: sha1($this->system_id . time() . $this->usr_login);
          $add = 'cookiekey = ?, ';
          $params[] = $cookiekey;
          setcookie($cookiename, $cookiekey, time() + 60*60*24*365, '/');
        }
        array_push($params, time(), session_id(), $this->usr_login);
        $sql->setQuery('UPDATE pz_user SET '. $add .'login_tries=0, lasttrydate=?, session_id=? WHERE login=? LIMIT 1', $params);
      }
      pz::setUser(new pz_user($this->USER));
    }
    else
    {
      // fehlversuch speichern | login_tries++
      if($this->usr_login != '')
      {
        $sql->setQuery('UPDATE pz_user SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', array(time(), $this->usr_login));
      }
    }

    if ($this->isLoggedOut() && $userId != '')
    {
      $sql->setQuery('UPDATE pz_user SET session_id="", cookiekey="" WHERE id=? LIMIT 1', array($userId));
      setcookie($cookiename, '', time() - 3600, '/');
    }

    return $check;
  }
}