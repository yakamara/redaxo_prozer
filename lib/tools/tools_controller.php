<?php

class pz_tools_controller extends pz_controller {

	function checkPerm() 
	{
		if(pz::getUser())
			return TRUE;
		return FALSE;
	}

}