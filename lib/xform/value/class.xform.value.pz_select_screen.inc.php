<?php

class rex_xform_value_pz_select_screen extends rex_xform_value_abstract
{

	function enterObject()
	{
	
		if (!$this->params['send'] && $this->getValue() == '' && $this->getElement(5) != '') {
			$this->setValue($this->getElement(5));
		}

		if(!is_array($this->getValue())) {
			$this->setValue(explode(",",$this->getValue()));
		}
		
		$values = $this->getValue();

		$tpl_entries = array();
		$tpl_selected_text = '';
		$tpl_selected_value = '';
    
    
		$i = 0;
    
		// mit --- keine auswahl ---
		if ($this->getElement(6) == 1)
		{
			$i++;
			$tpl_entries[$i]['title'] = (rex_i18n::translate($this->getElement(7)));
			$tpl_entries[$i]['attributes']['rel'] = '';
			$tpl_entries[$i]['attributes']['onclick'] = 'pz_save_dropdown_value(\''.$this->getFieldId().'-\')';
			$tpl_entries[$i]['attributes']['id'] = $this->getFieldId().'-';
			
			if (count($values) == 1 && ($values[0] == '' || $values[0] == '0'))
			{
				$tpl_entries[$i]['class'] = 'active';
				$tpl_selected_text = (rex_i18n::translate($this->getElement(7)));
				$tpl_selected_value = '';
			}
		}
    
		$options = explode(',', $this->getElement(3));
		foreach ($options as $option)
		{
		  $i++;
			$params = explode('=', $option);
			$text = $params[0];
			if (isset ($params[1]))
			{
				$value = $params[1];
			}else
			{
				$value = $params[0];
			}
			
			$tpl_entries[$i]['title'] = (rex_i18n::translate($text));
			$tpl_entries[$i]['attributes']['rel'] = $value;
			$tpl_entries[$i]['attributes']['onclick'] = 'pz_save_dropdown_value(\''.$this->getFieldId().'-'.$value.'\')';
			$tpl_entries[$i]['attributes']['id'] = $this->getFieldId().'-'.$value;
			

			if ($i == 1 && count($values) == 1 && ($values[0] == '' || $values[0] == '0'))
			{
				$tpl_selected_text = ($tpl_entries[1]['title']);
				$tpl_selected_value = $tpl_entries[1]['attributes']['rel'];
				$tpl_entries[1]['class'] = 'active';
			}else
			{
				foreach($values as $v)
				{
					if ($value == $v)
					{
						$tpl_selected_text = htmlspecialchars($text);
						$tpl_selected_value = $value;
						$tpl_entries[$i]['class'] = 'active';
						break;
					}
				}
			}
      
		}

		$this->setValue(implode(",",$this->getValue()));

		$class = $this->getHTMLClass();
		$classes = $class;
		
		if (isset($this->params['warning'][$this->getId()]))
		{
			$classes .= ' '.$this->params['warning'][$this->getId()];
		}
		
		$ul_classes = $classes.' js-save-dropdown-value';
		
		
		
		$class_selected = $this->getFieldId() .'-selected';
		
		// Wenn disabled, dann keine Eintraege uebergeben
		if($this->getElement('disabled'))
		{
			$tpl_entries = array();
			$class_selected .= ' disabled';
		}

		$f = new rex_fragment();
		$f->setVar('class_ul', $ul_classes, false);
		$f->setVar('class_selected', $class_selected, false);
		$f->setVar('selected', $tpl_selected_text, false);
		$f->setVar('entries', $tpl_entries, false);
		$f->setVar('extra', '<input id="' . $this->getFieldId() . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
		$dropdown = $f->parse('pz_screen_select_dropdown');

		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
		
		$before = '';
		$after = '';
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . rex_i18n::translate($this->getElement(2)) . '</label>' : '';
		$field = $dropdown;
		$extra = '';
		$html_id = $this->getHTMLId();
		$name = $this->getName();
    
		$f = new rex_fragment();
		$f->setVar('before', $before, false);
		$f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('extra', $extra, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $class, false);
		
		$fragment = $this->params['fragment'];
		
		$output = $f->parse($fragment);
		$output .= '<script>pz_screen_select_event("ul.sl1 li.selected.'.$this->getFieldId().'-selected");</script>';
		
		$this->params["form_output"][$this->getId()] = $output;

		$this->params["value_pool"]["email"][$this->getElement(1)] = $this->getValue();
		if ($this->getElement(4) != "no_db") $this->params["value_pool"]["sql"][$this->getElement(1)] = $this->getValue();
		
		
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