<?php

class pz_admin_controller extends pz_controller {

	function checkPerm() 
	{
		if(pz::getUser() && pz::getUser()->isAdmin())
			return TRUE;
		return FALSE;
	}

}