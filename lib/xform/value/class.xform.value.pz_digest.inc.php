<?php

class rex_xform_value_pz_digest extends rex_xform_value_abstract
{

  function postValueAction()
  {
  
  	$login = "";
  	$password = "";
  
    foreach($this->params["value_pool"]["sql"] as $key => $value)
    {
    	switch($key) {
    		case($this->getElement(2)):
    			$login = $value;
    			break;
    		case($this->getElement(3)):
    			$password = $value;
    			break;
    	}
    }
    
    $digest = pz_user::digest($login,$password);
    
  	$label_digest = $this->getName();
	$this->params["value_pool"]["sql"][$label_digest] = $digest;
	
    return;

  }

  function getDescription()
  {
    return "pz_digest|digest|login|password|";
  }

	function getDefinitions()
	{
		return array(
				'type' => 'value',
				'name' => 'pz_digest',
				'values' => array(
							array( 'type' => 'name',   'label' => 'Feld' ),
							array( 'type' => 'text',    'label' => 'Bezeichnung'),
							array( 'type' => 'text',    'label' => 'Defaultwert'),
							array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
							array( 'type' => 'text',    'label' => 'classes'),
						),
				'description' => 'Ein einfaches Textfeld als Eingabe',
				'dbtype' => 'text',
				'famous' => TRUE
				);

	}


}
