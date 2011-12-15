<?php

class pz_address_field extends pz_model
{
	public $vars = array();

	function __construct($vars = array())
	{
		$this->setVars($vars);
	}
}