<?php

class pz_controller {

	var $visible = TRUE;
	var $name = "undefined";
	// var $controll = "";
	var $navigation = array();
	var $function = "";

	function getUrl() {
		return pz::url('',$this->name);
	}

	function isVisible() {
		return $this->visible;
	}

	function getName() {

		return rex_i18n::translate($this->name);
	}

	function checkPerm() 
	{
		if(pz::getUser()) {
			return TRUE;
		}
		return FALSE;
	}

	public function controller($func) {
		return "Controller";
	}

}