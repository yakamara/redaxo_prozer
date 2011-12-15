<?php

class pz_screen_controller extends pz_controller{

	static $controller = array('login', 'emails', 'projects', 'project', 'calendars', 'addresses', 'clipboard', 'users', 'tools', 'profile', 'search');
	static $controll = NULL;

	public function controller($function) {
	
		// ---------- screen
		// screen controller noch bauen

   		$pz_login = new pz_login;
  		if($login = rex_request('login','string') != '' && $psw = rex_request('psw','string') != '') {
  			$pz_login->setLogin($login, $psw);
  		}
  		if(rex_request('logout','string') == 1) {
  			$pz_login->setLogout(true);
  		}
  		$pz_login->checkLogin();

		$controller = array();
		foreach(self::$controller as $controll) {
			$class = 'pz_'.$controll.'_controller_'.pz::$mediaview;
			if(class_exists($class)) {

				$controller[$controll] = new $class;
				if(!$controller[$controll]->checkPerm()) {
					unset($controller[$controll]);
				}

			}else {
				pz::debug("class does not exist: $controll");

			}
		}
		static::$controller = $controller;

		$controll = rex_request('controll','string');
		if(!array_key_exists($controll,self::$controller))
		{
			if(pz::getUser()) $controll = pz::getUser()->getStartpage(); // 'login';
			else {
			
				if(rex_request("pz_login_refresh","int") == 1 && $controll != "login" && $controll != "")
					return "relogin";
				$controll = 'login';
			}
		}

		static::$controll = $controll;
		pz::debug("controll: $controll");

		if(isset($controller[$controll])) {
			return $controller[$controll]->controller($function);

		}else {
			return '';
		}
		
		return '';
		
	}

}