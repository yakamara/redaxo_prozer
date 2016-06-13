<?php

class pz_api_controller extends pz_controller
{
    public static $controller = ['emails', 'addresses', 'calendar_event', 'admin'];
    public static $controll = null;

    public function controller($function)
    {
        $login = rex_request('login', 'string', 'aa');
        $digest = rex_request('apitoken', 'string', -1);

        $pz_login = new pz_login();
        $pz_login->setSystemId('pz_api_'. pz::getProperty('instname'));
        $pz_login->setLogin($login, $digest);
        // $pz_login->checkLogin();

        $check_query = rex_sql::factory();
        $check_query->setQuery('select * from pz_user where login = ? and digest = ?', [$login, $digest]);

        if ($check_query->getRows() == 1) {
            $pz_login->setUser($check_query);
            pz::setUser(new pz_user($pz_login->getUser()), $pz_login);
        }

        $controller = [];
        foreach (self::$controller as $controll) {
            $class = 'pz_'.$controll.'_controller_'.pz::$mediaview;
            if (class_exists($class)) {
                $controller[$controll] = new $class();
                if (!$controller[$controll]->checkPerm()) {
                    unset($controller[$controll]);
                }
            } else {
                pz::debug("class does not exist: $controll");
            }
        }
        static::$controller = $controller;

        $controll = rex_request('controll', 'string');
        if (!array_key_exists($controll, self::$controller)) {
            // TODO: show possibilities.
            // - Function list
        }

        static::$controll = $controll;
        pz::debug("controll: $controll");

        if (isset($controller[$controll])) {
            $return = $controller[$controll]->controller($function);
        } else {
            $return = 'failed';
        }

        return $return;
    }
}
