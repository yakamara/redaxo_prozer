<?php

class pz_screen_controller extends pz_controller
{

	static $controller = array('login', 'emails', 'projects', 'project', 'calendars', 'addresses', 'clipboard', 'tools', 'admin');
	static $controll = NULL;

	public function controller($function) 
	{

 		$pz_login = new pz_login;
    $login = rex_request('login','string');
    $psw = rex_request('psw','string');
    if($login != '' && $psw != '')  {
        $pz_login->setLogin($login, $psw);
    }

    if(rex_request('logout','string') == 1)
    {
    	$pz_login->setLogout(true);
    }
		$pz_login->checkLogin();

		$controller = array();
		foreach(self::$controller as $controll)
		{
			$class = 'pz_'.$controll.'_controller_'.pz::$mediaview;
			if(class_exists($class)) 
			{
				$controller[$controll] = new $class;
				if(!$controller[$controll]->checkPerm()) 
				{
					unset($controller[$controll]);
				}
			}else 
			{
				pz::debug("class does not exist: $controll");
			}
		}

		static::$controller = $controller;

		$controll = rex_request('controll','string');
		if(!array_key_exists($controll,self::$controller))
		{

			if(pz::getUser() && array_key_exists(pz::getUser()->getStartpage(),self::$controller))
			{
				$controll = pz::getUser()->getStartpage(); // 'login';

			}elseif(pz::getUser())
			{
				$controll = "";
				foreach(self::$controller as $cl)
				{
					if($cl->isVisible())
					{
						$controll = $cl->getName();
						break;
					}
				}

				// $controll = "tools";

			}else 
			{
				if(rex_request("pz_login_refresh","int") == 1 && $controll != "login" && $controll != "")
					return "relogin";
				$controll = 'login';
			}
		}

		static::$controll = $controll;
		if(isset($controller[$controll])) 
		{
			return $controller[$controll]->controller($function);
		}
		return '';

	}

}