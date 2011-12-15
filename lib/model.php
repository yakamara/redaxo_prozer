<?php

class pz_model {

	public $vars = array();

	function __construct($vars = array())
	{
		if(!is_array($vars)) return FALSE;
		else $this->setVars($vars);
		return TRUE;
	}

	function setVars($vars = array())
	{
		$this->vars = $vars;
	}

	function getVars()
	{
		return $this->vars;
	}

	function setVar($key, $var = "")
	{
		$this->vars[$key] = $var;
	}

	function getVar($key, $type = false)
	{
		$return = '';
		if(isset($this->vars[$key]) && trim($this->vars[$key]) != '') {
			switch($type) {
				case("overview"):
					$return = trim($this->vars[$key]);
					$return = strip_tags($return);
					$return = htmlspecialchars($return);
					$return = substr($return, 0, 20);           // anpassen !!
					break;
				case('date'):
					$return = trim($this->vars[$key]);
					$return = strtotime($return);
					$return = date('d.m.Y', $return);
					break;
				case('time'):
					$return = trim($this->vars[$key]);
					$return = strtotime($return);
					$return = date('H:i', $return);
					break;
				case('datetime'):
					$return = trim($this->vars[$key]);
					$return = strtotime($return);
					$return = date('d.m.Y H:i', $return);
					break;
				default:
					$return = $this->vars[$key];
			}
		}
		return $return;
	}

	public function update() {
	}

	public function create() {
	}

	public function delete() {
	}

}