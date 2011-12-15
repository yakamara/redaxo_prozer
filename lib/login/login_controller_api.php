<?php


class pz_login_controller_api extends pz_login_controller{


	function controller($function) {
	
		switch($function) {

			case("send"):
				$user = rex_request("login_name","string","");
				$psw = rex_request("login_password","string","");
				$stay = rex_request("login_stay","string",""); 
				
				$login = new pz_login();
				$login->setLogin($user, $psw);
				if($stay == 1) { 
					$login->setStayLoggedIn(true);
				}
				
				if($login->checkLogin())
				{
				  return pz_api::send(1);
				}
				return pz_api::send(0);

			case("status"):
				$login = new pz_login();
				if($login->checkLogin())
				{
				  return pz_api::send(1);
				}
				return pz_api::send(0);

			case("logout"):
			
				// TODO: logout
				return pz_api::send(1);

			default:
				return pz_api::send("");
				break;
		}


	}

}