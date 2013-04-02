<?php

class pz_screen 
{

	/*
	static function initPage($content) {
		
		$f = new rex_fragment();
		$f->setVar('content',$content);
		return $f->parse('pz_screen_page.tpl');
	}
	*/

	static function getPageTitle() 
	{
	  $package = rex_package::get("prozer");

	  $page_title = pz::getConfig("page_title");
	  if($page_title != "") {
	    return $page_title.' - PROZER '.$package->getVersion();
	  }
		return 'PROZER '.$package->getVersion();
	}

	static function getHeader($p = array()) 
	{
		$fragment = new rex_fragment();
		$fragment->setVar('navigation', self::getMainNavigation($p), false);
		if(pz::getUser())
		{
			$fragment->setVar('user', pz::getUser()->getName($p), false);

			$users = array();
			if(pz::getUser()->getId() != pz::getLoginUser()->getId())
				$users[] = array('name' => pz::getLoginUser()->getName(), 'link' => '/screen/?pz_set_user='.pz::getLoginUser()->getId());
			
			foreach(pz::getLoginUser()->getGivenUserPerms() as $user_perm)
			{
				if(pz::getUser()->getId() != $user_perm->getFromUser()->getId())
					$users[] = array('name' => $user_perm->getFromUser()->getName(), 'link' => '/screen/?pz_set_user='.$user_perm->getFromUser()->getId());
			}
			
			$fragment->setVar('user_navigation', $users);
			
		}else
		{
			$fragment->setVar('user', "");
		}
		return $fragment->parse('pz_screen_header.tpl');
	}

	static function getMainNavigation($p = array()) 
	{
	
		$first = " first";
		$temp_k = "";
		$items = array();
		foreach(pz_screen_controller::$controller as $k => $controll) 
		{
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
		return $f->parse('pz_screen_main_navigation.tpl');
	}

	static function getNavigation($p, $navigation = array(), $function = "", $name = "", $flyout = "") {
		
		if($flyout == "" && (pz::getUser()->isMe() || pz::getUser()->getUserPerm()->hasProjectsPerm()))
    {
  		$projects_screen = new pz_projects_screen(pz::getUser()->getMyProjects());
  		$flyout = $projects_screen->getProjectsFlyout($p);
	  }	
		
		$temp_k = "";
		$items = array();
		foreach($navigation as $k) {
			$active = "";
			if($function == $k) $active = " active";
			$items[$k] = array();
			$items[$k]["classes"] = 'subnavi-'.$k.$active;
			$items[$k]["name"] = rex_i18n::msg("page_".$name."_".$k);
			$items[$k]["url"] = pz::url('screen',$name, $k, array());
			$first = "";
			$temp_k = $k;
		}
		if($temp_k != "") $items[$temp_k]["classes"] = 'subnavi-'.$k." last";
		$f = new rex_fragment();
		$f->items = $items;
		$f->item_active = $function;
		$f->flyout = $flyout;

		return $f->parse('pz_screen_main_sub_navigation.tpl');
	
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

	static public function getTooltipView($html,$tooltip)
	{
		return '<div class="tooltip">'.$html.'<span class="tooltip"><span class="inner">'.$tooltip.'</span></span></div>';	
	}

  static public function prepareOutput($text, $specialchars = TRUE) 
  {
		$text = pz_screen::setLinks($text);
		if($specialchars)
			$text = htmlspecialchars($text);
		$text = pz_screen::replaceLinks($text);
		return $text;
	}
  
	static public function setLinks($text) 
	{
		$urlsuch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\^\$!`\"'\|\[\]\{\}<>]*)/si";
		$urlsuch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\^\$!`\"'\|\[\]\{\}<>]*)/si";
		
		$urlreplace[]="\\1[URL]\\2\\4[/URL]";
		$urlreplace[]="[URL]\\1\\3[/URL]";

		$text = preg_replace($urlsuch, $urlreplace, $text);

		$emailsuch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
		$emailsuch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
		$emailreplace[]="\\1[EMAIL]\\2[/EMAIL]";
		$emailreplace[]="[EMAIL]\\0[/EMAIL]";
		if (strpos($text, "@")) {
			$text = preg_replace($emailsuch, $emailreplace, $text);
		}

		return $text;
	}

	static function replaceLinks($text) 
	{
		$text = preg_replace("/\[URL\]www.(.*?)\[\/URL\]/si", "<a target=\"_blank\" href=\"http://www.\\1\">www.\\1</a>", $text);
		$text = preg_replace("/\[URL\](.*?)\[\/URL\]/si", "<a target=\"_blank\" href=\"\\1\">\\1</a>", $text);
		$text = preg_replace("/\[EMAIL\](.*?)\[\/EMAIL\]/si", "<a href=\"/screen/emails/create/?to=\\1\">\\1</a>", $text); 
		return $text;
	}

}