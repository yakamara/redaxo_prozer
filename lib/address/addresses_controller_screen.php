<?php

class pz_addresses_controller_screen extends pz_addresses_controller {

	var $name = "addresses";
	var $function = "";
	var $functions = array("my", "all", "addresses" ); // "export", 
	var $function_default = "all";
	var $navigation = array("all", "my" ); // "export"

	function controller($function) {
	
		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();

		$p["mediaview"] = "screen";
		$p["controll"] = "addresses";
		$p["function"] = $this->function;
		
		switch($this->function)
		{
			case("all"):
				return $this->getAddressesPage($p);
			case("my"):
				return $this->getMyAddressesPage($p);
			case("address"):
				return $this->getAddress($p);
			case("addresses"):
				return $this->getAddresses($p);
				break;
			default:
				return '';
				
		}
	}

	// ------------------------------------------------------------------- Views

	function getNavigation($p = array())
	{
		
		return pz_screen::getNavigation(
			$p,
			$this->navigation, 
			$this->function, 
			$this->name
		);

	}


	// --------------------------------------------------- Formular Views

	public function getAddresses() {
	
		$fulltext = rex_request("search_name","string");
		$mode = rex_request("mode","string","");
		$format = rex_request("format","string","json");

		$r_addresses = array();
		switch($mode)
		{
		
			case("get_user_emails"):
				$filter = array();
				$filter[] = array('field'=>'status', 'value'=>1);
				$filter[] = array('field'=>'name', 'type' => 'like', 'value'=>'%'.$fulltext.'%');
				// $fulltext
				// status = 1
				$users = pz::getUsers($filter); 
				foreach($users as $user) 
				{
					$r_addresses[] = array(
						"id" => $user->getId(),
						"label" => $user->getName()." [".$user->getEmail()."]",
						"value" => $user->getEmail()
					);
				}
				break;
		
			case("get_emails"):

				$addresses = pz_address::getAllByFulltext($fulltext);

				foreach($addresses as $address) 
				{
					foreach($address->getFields() as $field) 
					{
						if( $field->getVar("type") == "EMAIL") 
						{
							$r_addresses[] = array(
								"id" => $field->getVar("value"),
								"label" => $address->getFullname()." - ".$field->getVar("value")." [".$field->getVar("label")."]",
								"value" => $field->getVar("value")
							);
						}
					}
				}
				break;
			default:
		}

		if($format == "json") 
			return json_encode($r_addresses); 
		
		return "";

	}



	public function getAddress() 
	{
		// TODO
		
		$address_id = rex_request("address_id","int",0);
		if($address_id < 1) {
			return FALSE;
		}
				
		if(!($address = pz_address::get($address_id))) {
			return FALSE;
		}
		
		$mode = rex_request("mode","string","");
		switch($mode)
		{
			case("vcard"):
				// TODO:
				return FALSE;
		}

	}




	


	// ------------------------------------------------------- page views

	function getMyAddressesPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("all_projects");
		
		$s1_content = "";
		$s2_content = "";

		$fulltext = rex_request("search_name","string");
		$mode = rex_request("mode","string");
		switch($mode)
		{
			/*
			case("upload_photo"):
				// TODO
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {

				}				
				return "PHOTO";
			*/
				/*
			case("view_address"):
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {
					$r = new pz_address_screen($address);
					return $r->getDetailView($p);
				}
				return "";
				*/

			case("delete_address"):
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {
					$r = new pz_address_screen($address);
					$return = $r->getDeleteForm($p);
					$address->delete();
					return $return;
				}
				
			case("edit_address"):
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {
					$r = new pz_address_screen($address);
					return $r->getEditForm($p);
				}
				return "";
			case("add_address"):
				return pz_address_screen::getAddForm($p);
				break;
			case("list"):
				$addresses = pz::getUser()->getAddresses($fulltext);
				return pz_address_screen::getTableListView(
							$addresses,
							array_merge( $p, array("linkvars" => array("mode" =>"list", "search_name" => $fulltext) ) )
						);
				break;
			case(""):
				$s1_content .= pz_address_screen::getAddressesSearchForm($p);
				$addresses = pz::getUser()->getAddresses($fulltext);
				$s2_content .= pz_address_screen::getTableListView(
							$addresses,
							array_merge( $p, array("linkvars" => array("mode" =>"list", "search_name" => $fulltext) ) )
						);
				$form = pz_address_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$s1_content .= $form;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
		
	}	

	function getAddressesPage($p = array()) 
	{
		$p["title"] = rex_i18n::msg("all_projects");
		
		$s1_content = "";
		$s2_content = "";

		$fulltext = rex_request("search_name","string");
		$mode = rex_request("mode","string");
		switch($mode)
		{

			case("delete_address"):
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {
					$r = new pz_address_screen($address);
					$return = $r->getDeleteForm($p);
					$address->delete();
					return $return;
				}
				
			case("edit_address"):
				$address_id = rex_request("address_id","int");
				if($address = pz_address::get($address_id)) {
					$r = new pz_address_screen($address);
					return $r->getEditForm($p);
				}
				return "";
			case("add_address"):
				return pz_address_screen::getAddForm($p);
				break;
			case("list"):
				$addresses = pz_address::getAllByFulltext($fulltext);
				return pz_address_screen::getTableListView($addresses,array_merge(
						$p,
						array("linkvars" => array("mode" =>"list","search_name" => rex_request("search_name","string")) )
						));
				break;
			case(""):
				$s1_content .= pz_address_screen::getAddressesSearchForm($p);
				$addresses = pz_address::getAllByFulltext($fulltext);
				$s2_content .= pz_address_screen::getTableListView(
							$addresses,
							array_merge( $p, array("linkvars" => array("mode" =>"list") ) )
						);
				$form = pz_address_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$s1_content .= $form;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');
		
	}


}