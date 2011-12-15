<?php

class pz_cronjob_controller extends pz_controller{

	static $controller = array('emails', 'projects', 'project');

	function controller() {
		$return = "";
		$controll = rex_request('controll','string');

		if(in_array($controll,self::$controller))
		{
			$class = 'pz_'.$controll.'_controller_'.pz::$mediaview;
			if(class_exists($class)) {
				$controller = new $class;
				$return .= $controller->controller();
			}
		}else
		{
			foreach(self::$controller as $controll)
			{			
				$class = 'pz_'.$controll.'_controller_'.pz::$mediaview;
				if(class_exists($class)) {
					$controller = new $class;
					$return .= $controller->controller();
				}
			}
			
		}

		return $return;
	
	}

}