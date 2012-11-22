<?php

class pz_addresses_controller extends pz_controller {

	function checkPerm() 
	{
		if(pz::getUser()) return TRUE;
		else return FALSE;
	}

}