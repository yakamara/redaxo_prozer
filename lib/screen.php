<?php

class pz_screen {

	/*
	static function initPage($content) {
		
		$f = new rex_fragment();
		$f->setVar('content',$content);
		return $f->parse('pz_screen_page');
	}
	*/

	static function getHeader($p = array()) {
		$fragment = new rex_fragment();
		$fragment->setVar('navigation', self::getMainNavigation($p), false);
		if(pz::getUser())
		{
			$fragment->setVar('user', pz::getUser()->getName($p), false);
		}else
		{
			$fragment->setVar('user', "");
		}
		return $fragment->parse('pz_screen_header');
	}

	static function getMainNavigation($p = array()) {
	
		$first = " first";
		$temp_k = "";
		$items = array();
		foreach(pz_screen_controller::$controller as $k => $controll) {
			if($controll->isVisible())
			{
				$items[$k]["classes"] = $k.$first;
				$items[$k]["name"] = $controll->getName().$controll->name;
				if(method_exists($controll,'getMainFlyout'))
					$items[$k]["flyout"] = $controll->getMainFlyout();
				$items[$k]["url"] = pz::url('screen',$controll->name);
				
				if($controll->name == "emails")
					$items[$k]["span"] = pz::getUser()->countInboxEmails();
				
				if($controll->name == "calendars")
					$items[$k]["span"] = pz::getUser()->countAttendeeEvents();
				
				$first = "";
				$temp_k = $k;
			}
		}
		if($temp_k != "") $items[$temp_k]["classes"] = $k." last";
	
		$f = new rex_fragment();
		$f->items = $items;
		$f->item_active = pz_screen_controller::$controll;
		return $f->parse('pz_screen_main_navigation');
	}

	static function getNavigation($p, $navigation = array(), $function = "", $name = "", $flyout = "") {
		
		if($flyout == "")
			$flyout = pz_project_controller_screen::getProjectsFlyout($p);
		
		$first = " first";
		$temp_k = "";
		$items = array();
		foreach($navigation as $k) {
			$active = "";
			if($function == $k) $active = " active";
			$items[$k] = array();
			$items[$k]["classes"] = $k.$first.$active;
			$items[$k]["name"] = rex_i18n::msg("page_".$name."_".$k);
			$items[$k]["url"] = pz::url('screen',$name, $k, array());
			$first = "";
			$temp_k = $k;
		}
		if($temp_k != "") $items[$temp_k]["classes"] = $k." last";
		$f = new rex_fragment();
		$f->items = $items;
		$f->item_active = $function;
		$f->flyout = $flyout;

		return $f->parse('pz_screen_main_sub_navigation');
	
	}

	static public function getJSUpdateLayer($layer,$link)
	{
		return '<script language="Javascript"><!--	
		pz_loadPage("'.$layer.'","'.$link.'");
		--></script>';
	}

	static public function getJSLoadFormPage($layer,$form_id,$link)
	{
		return '<script language="Javascript"><!--	
		pz_loadFormPage("'.$layer.'","'.$form_id.'","'.$link.'");
		--></script>';
	}

}