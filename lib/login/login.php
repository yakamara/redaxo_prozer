<?php

class pz_login
{
    private $stayLoggedIn = false;

    protected $DB = 1;
    protected $sessionDuration;
    protected $loginQuery;
    protected $userQuery;
    protected $systemId = 'default';
    protected $userLogin;
    protected $userPassword;
    protected $logout = false;
    protected $idColumn = 'id';
    protected $passwordColumn = 'password';
    protected $cache = false;
    protected $loginStatus = 0; // 0 = noch checken, 1 = ok, -1 = not ok
    protected $message = '';
    protected $user;

    /**
     * Setzt, ob die Ergebnisse der Login-Abfrage
     * pro Seitenaufruf gecached werden sollen.
     */
    public function setCache($status = true)
    {
        $this->cache = $status;
    }

    /**
     * Setzt die Id der zu verwendenden SQL Connection.
     */
    public function setSqlDb($DB)
    {
        $this->DB = $DB;
    }

    /**
     * Setzt eine eindeutige System Id, damit mehrere
     * Sessions auf der gleichen Domain unterschieden werden können.
     */
    public function setSystemId($system_id)
    {
        $this->systemId = $system_id;
    }

    /**
     * Setzt das Session Timeout.
     */
    public function setSessionDuration($sessionDuration)
    {
        $this->sessionDuration = $sessionDuration;
    }

    /**
     * Setzt den Login und das Password.
     */
    public function setLogin($login, $password, $isPreHashed = false)
    {
        $this->userLogin = $login;
        $this->userPassword = $isPreHashed ? $password : sha1($password);
    }

    /**
     * Markiert die aktuelle Session als ausgeloggt.
     */
    public function setLogout($logout)
    {
        $this->logout = $logout;
    }

    /**
     * Prüft, ob die aktuelle Session ausgeloggt ist.
     */
    public function isLoggedOut()
    {
        return $this->logout;
    }

    /**
     * Setzt den UserQuery.
     *
     * Dieser wird benutzt, um einen bereits eingeloggten User
     * im Verlauf seines Aufenthaltes auf der Webseite zu verifizieren
     */
    public function setUserQuery($user_query)
    {
        $this->userQuery = $user_query;
    }

    /**
     * Setzt den LoginQuery.
     *
     * Dieser wird benutzt, um den eigentlichne Loginvorgang durchzuführen.
     * Hier wird das eingegebene Password und der Login eingesetzt.
     */
    public function setLoginQuery($login_query)
    {
        $this->loginQuery = $login_query;
    }

    /**
     * Setzt den Namen der Spalte, der die User-Id enthält.
     */
    public function setIdColumn($idColumn)
    {
        $this->idColumn = $idColumn;
    }

    /**
     * Sets the password column.
     *
     * @param string $passwordColumn
     */
    public function setPasswordColumn($passwordColumn)
    {
        $this->passwordColumn = $passwordColumn;
    }

    /**
     * Setzt einen Meldungstext.
     */
    protected function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Gibt einen Benutzer-Spezifischen Wert zurück.
     */
    public function getValue($value, $default = null)
    {
        if ($this->user) {
            return $this->user->getValue($value);
        }

        return $default;
    }

    /**
     * Setzte eine Session-Variable.
     */
    public function setSessionVar($varname, $value)
    {
        $_SESSION[pz::getProperty('instname')][$this->systemId][$varname] = $value;
    }

    /**
     * Gibt den Wert einer Session-Variable zurück.
     */
    public function getSessionVar($varname, $default = '')
    {
        if (isset($_SESSION[pz::getProperty('instname')][$this->systemId][$varname])) {
            return $_SESSION[pz::getProperty('instname')][$this->systemId][$varname];
        }

        return $default;
    }

    /*
     * Session fixation
    */
    public function regenerateSessionId()
    {
        session_regenerate_id(true);
    }

    /**
     * starts a http-session if not already started.
     */
    public static function startSession()
    {
        if (session_id() == '') {
            if (!@session_start()) {
                $error = error_get_last();
                if ($error) {
                    rex_error_handler::handleError($error['type'], $error['message'], $error['file'], $error['line']);
                } else {
                    throw new rex_exception('Unable to start session!');
                }
            }
        }
    }

    /**
     * Verschlüsselt den übergebnen String.
     */
    public static function passwordHash($password, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);
        return $password;
        // return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function passwordVerify($password, $hash, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);

        if ($password == $hash) {
            return true;
        } else {
            return false;
        }
        // return password_verify($password, $hash);
    }

    public static function passwordNeedsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    public function __construct()
    {
        self::startSession();

        $this->setSystemId('pz_'. pz::getProperty('instname'));
        $this->setSessionDuration(pz::getProperty('session_duration'));
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
        $cookiename = 'pz_user_'. sha1($this->systemId.pz::getProperty('instname'));

        if ($cookiekey = rex_cookie($cookiename, 'string')) {
            if (!$userId) {
                $sql->setQuery('SELECT id FROM pz_user WHERE cookiekey = ? LIMIT 1', [$cookiekey]);
                if ($sql->getRows() == 1) {
                    $this->setSessionVar('UID', $sql->getValue('id'));
                    setcookie($cookiename, $cookiekey, time() + 60 * 60 * 24 * 365, '/');
                } else {
                    setcookie($cookiename, '', time() - 3600, '/');
                }
            }
            $this->setSessionVar('STAMP', time());
        }

        $check = self::_checkLogin();

        if ($check) {
            // gelungenen versuch speichern | login_tries = 0
            if ($this->userLogin != '') {
                $this->regenerateSessionId();
                $params = [];
                $add = '';
                if ($this->stayLoggedIn) {
                    $cookiekey = $this->user->getValue('cookiekey') ?: sha1($this->systemId . time() . $this->userLogin);
                    $add = 'cookiekey = ?, ';
                    $params[] = $cookiekey;
                    setcookie($cookiename, $cookiekey, time() + 60 * 60 * 24 * 365, '/');
                }

                array_push($params, time(), pz::getDateTime()->format('Y-m-d H:i:s'), session_id(), $this->userLogin);
                $sql->setQuery('UPDATE pz_user SET '. $add .'login_tries=0, lasttrydate=?, last_login=?, session_id=? WHERE login=? LIMIT 1', $params);
            }
            pz::setUser(new pz_user($this->user), $this);
        } else {
            // fehlversuch speichern | login_tries++
            if ($this->userLogin != '') {
                $sql->setQuery('UPDATE pz_user SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', [time(), $this->userLogin]);
            }
        }

        if ($this->isLoggedOut() && $userId != '') {
            $sql->setQuery('UPDATE pz_user SET session_id="", cookiekey="" WHERE id=? LIMIT 1', [$userId]);
            setcookie($cookiename, '', time() - 3600, '/');
        }

        return $check;
    }

    /**
     * Prüft die mit setLogin() und setPassword() gesetzten Werte
     * anhand des LoginQueries/UserQueries und gibt den Status zurück.
     *
     * Gibt true zurück bei erfolg, sonst false
     */
    public function _checkLogin()
    {
        // wenn logout dann header schreiben und auf error seite verweisen
        // message schreiben

        $ok = false;

        if (!$this->logout) {
            // LoginStatus: 0 = noch checken, 1 = ok, -1 = not ok

            // checkLogin schonmal ausgeführt ? gecachte ausgabe erlaubt ?
            if ($this->cache) {
                if ($this->loginStatus > 0) {
                    return true;
                } elseif ($this->loginStatus < 0) {
                    return false;
                }
            }

            if ($this->userLogin != '') {
                // wenn login daten eingegeben dann checken
                // auf error seite verweisen und message schreiben

                $this->user = rex_sql::factory($this->DB);
                $this->user->debugsql = 1;
                $this->user->setQuery($this->loginQuery, [':login' => $this->userLogin]);
                if ($this->user->getRows() == 1 && self::passwordVerify($this->userPassword, $this->user->getValue($this->passwordColumn), true)) {
                    $ok = true;
                    $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                    $this->regenerateSessionId();
                } else {
                    $this->message = pz_i18n::msg('login_error');
                    $this->setSessionVar('UID', '');
                }
            } elseif ($this->getSessionVar('UID') != '') {
                // wenn kein login und kein logout dann nach sessiontime checken
                // message schreiben und falls falsch auf error verweisen

                $this->user = rex_sql::factory($this->DB);

                $this->user->setQuery($this->userQuery, [':id' => $this->getSessionVar('UID')]);
                if ($this->user->getRows() == 1) {
                    if (($this->getSessionVar('STAMP') + $this->sessionDuration) > time()) {
                        $ok = true;
                        $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                    } else {
                        $this->message = pz_i18n::msg('login_session_expired');
                    }
                } else {
                    $this->message = pz_i18n::msg('login_user_not_found');
                }
            } else {
                $ok = false;
            }
        } else {
            $this->message = pz_i18n::msg('login_logged_out');
            $this->setSessionVar('UID', '');
        }

        if ($ok) {
            // wenn alles ok dann REX[UID][system_id] schreiben
            $this->setSessionVar('STAMP', time());
        } else {
            // wenn nicht, dann UID loeschen und error seite
            $this->setSessionVar('STAMP', '');
            $this->setSessionVar('UID', '');
        }

        if ($ok) {
            $this->loginStatus = 1;
        } else {
            $this->loginStatus = -1;
        }

        return $ok;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}
