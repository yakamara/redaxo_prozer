<?php

class pz_api_controller extends pz_controller
{

	static $controller = array('emails', 'addresses', 'calendar_event', 'admin');
	static $controll = NULL;

	public function controller($function) 
	{
		
		$login = rex_request('login','string','aa');
		$apikey = rex_request('apitoken','string',-1);
		
   	$pz_login = new pz_login;
   	$pz_login->setSysID('pz_api_'. rex::getProperty('instname'));
   	$pz_login->setLoginquery('SELECT * FROM pz_user WHERE status=1 AND login = :login AND digest = :password');
		$pz_login->setLogin($login, $apikey);

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
			// TODO: show possibilities.
			// - Function list

		}

		static::$controll = $controll;
		pz::debug("controll: $controll");

		if(isset($controller[$controll])) 
		{
			$return = $controller[$controll]->controller($function);

		}else 
		{
			$return = 'failed';

		}

  	return $return;
		
	}

}