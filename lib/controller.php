<?php

class pz_controller 
{

	var $visible = TRUE;
	var $name = "undefined";
	// var $controll = "";
	var $navigation = array();
	var $function = "";

	function getUrl() 
	{
		return pz::url('',$this->name);
	}

	function isVisible() 
	{
		return $this->visible;
	}

	function getName() 
	{
		return pz_i18n::translate($this->name);
	}

	function checkPerm() 
	{
		if(pz::getUser() && pz::getUser()->isMe()) return TRUE;
		else return FALSE;
	}

	public function controller($func) 
	{
		return "Controller";
	}

}