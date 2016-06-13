<?php

class pz_sabre_auth_backend extends Sabre\DAV\Auth\Backend\AbstractBasic
{
    protected function validateUserPass($username, $password)
    {
        $this->principalPrefix = 'principals/users/';

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', [$username]);

        if ($sql->getRows() == 1 && pz_login::passwordVerify($password, $sql->getValue('password'))) {
            pz::setUser(new pz_user($sql));
            return true;
        }
        return false;
    }
}
