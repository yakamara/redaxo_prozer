<?php

class pz_emails_controller extends pz_controller {

	public $search_order_fields = array('id_new' => 'id desc', 'title' => 'subject desc');

	function checkPerm() 
	{
		if(pz::getUser()) return TRUE;
		else return FALSE;
	}

}