<?php

class rex_xform_value_pz_select_screen extends rex_xform_value_abstract
{

	function enterObject()
	{
	
	  $multiple = FALSE;
		if($this->getElement(6) == 1)
		  $multiple = TRUE;

    $empty_name = rex_i18n::translate($this->getElement(7));
	  $size = 1;

		$SEL = new rex_select();
		$SEL->setId($this->getFieldId());
		
		if($multiple)
		{
			if($size == 1)
			{
				$size = 5;
			}
			$SEL->setName($this->getFieldName()."[]");
			$SEL->setSize($size);
			$SEL->setMultiple(1);
		}else
		{
			$SEL->setName($this->getFieldName());
			$SEL->setSize(1);
		}
    
  	if(is_array($this->getElement(3)))
  	{
  	  $options = $this->getElement(3);
  	}else
  	{
  		$options = array();
  		foreach (explode(',', $this->getElement(3)) as $option)
  		{
  			$params = explode('=', $option);
  			$value = $params[0];
  			if (isset ($params[1])) {
  				$text = $params[1];
  			}else {
  				$text = $params[0];
  			}
  			$options[] = array('label'=>$value,'id'=>$text);
  		}
  	}

    if($empty_name != "" && !$multiple) {
			$SEL->addOption("", "");
		}

    $SEL->setAttribute("data-placeholder",$empty_name);


		foreach($options as $e) {
			$SEL->addOption(rex_i18n::translate($e["label"],null,false), $e["id"]);
		}    	


		if (!$this->params['send'] && $this->getValue() == '' && $this->getElement(5) != '')
		{
			$this->setValue($this->getElement(5));
		}

		if(!is_array($this->getValue()))
		{
			$this->setValue(explode(",",$this->getValue()));
		}

		foreach($this->getValue() as $v)
		{
			$SEL->setSelected($v);
		}

		$this->setValue(implode(",",$this->getValue()));

		$class = $this->getHTMLClass();
		$classes = $class;
		
		if (isset($this->params['warning'][$this->getId()]))
		{
			$classes .= ' '.$this->params['warning'][$this->getId()];
		}
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
		
		$SEL->setStyle($classes);
		
		
		
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . rex_i18n::translate($this->getElement(2)) . '</label>' : '';
		$field = $SEL->get();
		$html_id = $this->getHTMLId();
		$name = $this->getName();
    
		$f = new rex_fragment();
		$f->setVar('before', "", false);
		$f->setVar('after', "", false);
		$f->setVar('extra', "", false);
		
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $class, false);
		
		$fragment = $this->params['fragment'];
		$this->params["form_output"][$this->getId()] = $f->parse($fragment);

    $chosen_options = array();
    $chosen_options[] = 'allow_single_deselect:true';
    $chosen_options[] = 'no_results_text: "'.rex_i18n::msg("no_results_text").'"';

    if(count($options)<10) {
      $chosen_options[] = '"disable_search":true';
    }

    $this->params["form_output"][$this->getId()] .= '<script>
    $(document).ready(function()
    {
      $("#'.$html_id.' select").chosen({'.implode(",",$chosen_options).'});
    });
    </script>';

		$this->params["value_pool"]["email"][$this->getElement(1)] = $this->getValue();
		if ($this->getElement(4) != "no_db") $this->params["value_pool"]["sql"][$this->getElement(1)] = $this->getValue();
	
  	return;
		
	}

	function getDescription()
	{
		return "select -> Beispiel: pz_select_screen|gender|Geschlecht *|Frau=w,Herr=m|[no_db]|defaultwert|1/0 Leeroption|Leeroptionstext";
	}

	function getDefinitions()
	{
		return array(
            'type' => 'value',
            'name' => 'pz_screen_select',
            'values' => array(
      				array( 'type' => 'name',   'label' => 'Feld' ),
      				array( 'type' => 'text',    'label' => 'Bezeichnung'),
      				array( 'type' => 'text',    'label' => 'Selektdefinition, kommasepariert',   'example' => 'w=Frau,m=Herr'),
      				array( 'type' => 'no_db',   'label' => 'Datenbank', 'default' => 1),
      				array( 'type' => 'text',    'label' => 'Defaultwert'),
              array( 'type' => 'boolean',		'label' => 'Leeroption'),
              array( 'type' => 'text',		'label' => 'Text bei Leeroption (Bitte auswählen)'),
      				),
            'description' => 'Ein Selektfeld mit festen Definitionen',
            'dbtype' => 'text'
            );

	}
}

?>