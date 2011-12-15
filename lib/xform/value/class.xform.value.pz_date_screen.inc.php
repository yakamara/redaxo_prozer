<?php

class rex_xform_value_pz_date_screen extends rex_xform_value_abstract
{

	function enterObject()
	{
    
		if (!$this->params['send'] && $this->getValue() == '' && $this->getElement(5) != '')
		{
			$this->setValue($this->getElement(5));
		}

		if(!is_array($this->getValue()))
		{
			$this->setValue(explode(",",$this->getValue()));
		}
		
		$values = $this->getValue();




		$class = $this->getHTMLClass();
		$classes = $class;
		
		if (isset($this->params['warning'][$this->getId()]))
		{
			$classes .= ' '.$this->params['warning'][$this->getId()];
		}
		
		$ul_classes = $classes.' js-save-dropdown-value';
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
		
		
		

    // ---------------------------------------------------------------- 
		$id_counter = 0; // Id Counter, da mehrere Ids benoetigt werden
    $id_counter++;
		
		
		
    // ---------------------------------------------------------------- Kalender
    $pzcal = new pz_calendar_screen();
    $calendar = $pzcal->getXFormView();
    		
		
    // ---------------------------------------------------------------- Form Element
    $before = '';
    $after = '';
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . rex_i18n::translate($this->getElement(2)) . '</label>' : '';
		$field = $calendar;
		$extra = '';
    $html_id = $this->getHTMLId();
    $name = $this->getName();
		$fragment_class = $class.' xform-date';
     
    
    
		$f = new rex_fragment();
		$f->setVar('before', $before, false);
		$f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('extra', $extra, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $fragment_class, false);
		
		$fragment = $this->params['fragment'];
		$this->params["form_output"][$this->getId()] = $f->parse($fragment);
		
		

		$this->params["value_pool"]["email"][$this->getElement(1)] = $this->getValue();
		if ($this->getElement(4) != "no_db") $this->params["value_pool"]["sql"][$this->getElement(1)] = $this->getValue();
		
		
	}

	function getDescription()
	{
		return "select -> Beispiel: select|gender|Geschlecht *|Frau=w,Herr=m|[no_db]|defaultwert|multiple=1";
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
				array( 'type' => 'boolean', 'label' => 'Mehrere Felder möglich'),
				array( 'type' => 'text',    'label' => 'Höhe der Auswahlbox'),
				),
            'description' => 'Ein Selektfeld mit festen Definitionen',
            'dbtype' => 'text'
            );

	}
}

?>