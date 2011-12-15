<?php

class pz_tools_controller_screen extends pz_tools_controller {

	var $name = "tools";
	var $function = "";
	var $functions = array("profile", "jobs", "users", "sms", "shared", "tracker"); // "userrole", "clipboard", 
	var $function_default = "profile";
	var $navigation = array("profile", "jobs", "users", "shared", "sms"); // "userrole", "clipboard", 

	// Profile: persönliche einstellungen (daten, zeitzone, sprache, mailsettings mit signatur, passwort, 

	function controller($function) {
	
		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();

		switch($this->function)
		{
			case("shared"): return $this->getSharedPage($p);
			case("tracker"): return $this->getTracker($p);
			case("profile"): return $this->getProfilePage($p);
			case("users"): return $this->getUsersPage($p);
			default: return $this->getProfilePage($p);
		}
	}

	// -------------------------------------------------------


	function getNavigation($p = array())
	{
		return pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name);
	}

	// ------------------------------------------------------- page views

	function getTracker() 
	{
		$return = '<script language="Javascript">';
		// $return .= 'alert(1)';
		$return .= '</script>';
		return $return;
	}

	function getProfilePage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("page_users_list");
		
		/*
			TODO:
			Eigene Userdaten
			- Default EMail Setup verknüpfen
			- Startseite definieren / Projekts, Mails .. 

			Rechte an andere User mit vergeben
			- Kalender, Mail, User, Read Write Delete
		*/
		$mode = rex_request("mode","string");
		switch($mode) {
			case("edit_user"):
				$u = pz::getUser();
				$u_screen = new pz_user_screen($u);
				return $u_screen->getMyEditForm($p);
			case("edit_password"):
				$u = pz::getUser();
				$u_screen = new pz_user_screen($u);
				return $u_screen->getMyPasswordEditForm($p);
			default:		
		}
		$u = pz::getUser();
		$u_screen = new pz_user_screen($u);

		$section_1 = $u_screen->getMyEditForm($p);
		$section_1.= $u_screen->getMyPasswordEditForm($p);
		
		$section_2 = "WebDav/ CalDav aktiveren für die einzelnen Projekte";
		$section_3 = "Userrechte an andere geben";
		
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name) , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		$f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}	

	public function getUsersPage($p = array())
	{
		$p["title"] = rex_i18n::msg("users");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "users";
		$p["layer"] = "users_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		// ----------------------- searchform		
	
		switch($mode) {
			
			case("add_user"):
				return pz_user_screen::getAddForm($p);;
		
			case("edit_user"):
				$user_id = rex_request("user_id","int");
				$u = pz_user::get($user_id);
				$u_screen = new pz_user_screen($u);
				return $u_screen->getEditForm($p);
			
		}
		
		$section_1 = pz_user_screen::getSearchForm($p);
		$section_1.= pz_user_screen::getAddForm($p);

		// ----------------------- jobliste

		$users = pz::getUsers();
		$section_2 = pz_user_screen::getTableListView(
						$users,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
		
		
		switch($mode)
		{
			case('list'):
				return $section_2;
				break;
			default:
				break;
		}
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}

	public function getSharedPage($p = array())
	{
		$p["title"] = rex_i18n::msg("users");
		$p["mediaview"] = "screen";
		$p["controll"] = "tools";
		$p["function"] = "shared";
		$p["layer"] = "shared_list";
		
		$section_1 = '';
		$section_2 = '';
	
		$mode = rex_request('mode', 'string');

		// ----------------------- searchform		
	
		switch($mode) {
			
			case("add_user"):
				return pz_user_screen::getAddForm($p);;
		
			case("edit_user"):
				$user_id = rex_request("user_id","int");
				$u = pz_user::get($user_id);
				$u_screen = new pz_user_screen($u);
				return $u_screen->getEditForm($p);
			
		}
		
		$section_1 = pz_user_screen::getSearchForm($p);
		$section_1.= pz_user_screen::getAddForm($p);

		// ----------------------- jobliste

		$users = pz::getUsers();
		$section_2 = pz_user_screen::getTableListView(
						$users,
						array_merge( $p, array("linkvars" => array( "mode" =>"list", ) ) )
					);
		
		
		switch($mode)
		{
			case('list'):
				return $section_2;
				break;
			default:
				break;
		}
	
		$p = array();
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader(), false);
		$f->setVar('function', $this->getNavigation() , false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		// $f->setVar('section_3', $section_3 , false);
		return $f->parse('pz_screen_main');
	}

}