<?php

class pz_calendars_controller extends pz_controller {

	
	function checkPerm() 
	{
		if(pz::getUser() && pz::getUser()->isMe()) return TRUE;
		if(pz::getUser() && pz::getUser()->getUserPerm()->hasCalendarReadPerm()) return TRUE;
		else return FALSE;
	}
	
}