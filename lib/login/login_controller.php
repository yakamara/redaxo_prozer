<?php


class pz_login_controller extends pz_controller{

	var $visible = FALSE;
	var $name = "login";

	function checkPerm() 
	{
		if(!pz::getUser()) return TRUE;
		else return FALSE;
	}
	



}