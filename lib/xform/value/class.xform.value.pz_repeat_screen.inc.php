<?php

class rex_xform_value_pz_repeat_screen extends rex_xform_value_abstract
{

	function enterObject()
	{
    $id_counter = 0;
    
		$class = $this->getHTMLClass();
		$classes = $class;
		
		if (isset($this->params['warning'][$this->getId()]))
		{
			$classes .= ' '.$this->params['warning'][$this->getId()];
		}
		
		$ul_classes = $classes.' js-save-dropdown-value';
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';


    // ---------------------------------------------------------------- Wiederholung auswaehlen
    $id_counter++; // Id Counter, da mehrere Ids benoetigt werden
    
    $tpl_selected_text = 'ohne';
    $tpl_selected_value = '';
    
    $tpl_entries = array();
    
    $tpl_entries[0]['title'] = 'täglich';
    $tpl_entries[0]['attributes']['rel'] = 'taeglich';
    
    $tpl_entries[1]['title'] = 'woechentlich';
    $tpl_entries[1]['attributes']['rel'] = 'woechentlich';
    
    $tpl_entries[2]['title'] = 'monatlich';
    $tpl_entries[2]['attributes']['rel'] = 'monatlich';
    
    $tpl_entries[3]['title'] = 'jährlich';
    $tpl_entries[3]['attributes']['rel'] = 'jaehrlich';
    
    $tpl_entries[4]['title'] = 'angepasst';
    $tpl_entries[4]['attributes']['rel'] = 'angepasst';
    
    // Dropdown aufbauen
    $f = new rex_fragment();
    $f->setVar('class_ul', $ul_classes, false);
    $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
    $f->setVar('selected', $tpl_selected_text, false);
    $f->setVar('entries', $tpl_entries, false);
    $f->setVar('extra', '<input id="' . $this->getFieldId().'-'. $id_counter++ . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
    $repeat_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

		
    // XForm Fragment aufbauen
    $before = '';
    $after = '';
		$label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . rex_i18n::translate('Wiederholung') . '</label>';
		$field = $repeat_dropdown;
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
		
		$repeat_fragment = $this->params['fragment'];
		$repeat_fragment = $f->parse($repeat_fragment);

    // ---------------------------------------------------------------- ENDE Wiederholung auswaehlen



    // ---------------------------------------------------------------- wenn - Wiederholung, aber nicht "angepasst"
    $id_counter++;
    
    $tpl_selected_text = 'nie';
    $tpl_selected_value = 'nie';
    
    $tpl_entries = array();
    
    $tpl_entries[0]['title'] = 'nie';
    $tpl_entries[0]['attributes']['rel'] = 'nie';
    $tpl_entries[0]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-repeat-end\')';
    
    $tpl_entries[1]['title'] = 'nach';
    $tpl_entries[1]['attributes']['rel'] = 'nach';
    $tpl_entries[1]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-repeat-end\'); show(\'#'.$this->getFieldId().'-repeat-end-after\');';
    
    $tpl_entries[2]['title'] = 'Datum';
    $tpl_entries[2]['attributes']['rel'] = 'datum';
    $tpl_entries[2]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-repeat-end\'); show(\'#'.$this->getFieldId().'-repeat-end-date\');';
    
    $fragment_ul_classes = $ul_classes.' w5';
    // Dropdown aufbauen
    $f = new rex_fragment();
    $f->setVar('class_ul', $fragment_ul_classes, false);
    $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
    $f->setVar('selected', $tpl_selected_text, false);
    $f->setVar('entries', $tpl_entries, false);
    $f->setVar('extra', '<input id="' . $this->getFieldId() . '-'. $id_counter .'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
    $dropdown = $f->parse('pz_screen_select_dropdown.tpl');

    
    $pzcal = new pz_calendar_screen();
    $calendar = $pzcal->getXFormView();
		
    // XForm Fragment aufbauen
    $before = '';
    $after = '';
		$label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . rex_i18n::translate('Ende') . '</label>';
		$field = $dropdown;
		// Auswahl = nach X Mal
		$field .= '<div id="'.$this->getFieldId().'-repeat-end-after" class="'.$this->getFieldId().'-repeat-end"><input type="number" value="" name="" max="10" min="1" class="xform-number" /><span class="fwording1">'.rex_i18n::translate('Mal').'</span></div>';
		// Auswahl = Datum
		$field .= '<div id="'.$this->getFieldId().'-repeat-end-date" class="'.$this->getFieldId().'-repeat-end">'.$calendar.'</div>';
		$extra = '<script type="text/javascript">hide(\'.'.$this->getFieldId().'-repeat-end\');</script>';
    $html_id = $this->getHTMLId();
    $name = $this->getName();
		$fragment_class = $class.' data-indent xform-number xform-date sl1-fnumber1-fwording1 sl1-fdate1-sl3';
    
		$f = new rex_fragment();
		$f->setVar('before', $before, false);
		$f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('extra', $extra, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $fragment_class, false);
		
		$repeat_end_fragment = $this->params['fragment'];
		$repeat_end_fragment = $f->parse($repeat_end_fragment);

    // ---------------------------------------------------------------- ENDE - Wiederholung, aber nicht "angepasst"



    // ---------------------------------------------------------------- wenn - Wiederholung, "angepasst"
    
    
    // Haeufigkeit
    
    $id_counter++; // Id Counter, da mehrere Ids benoetigt werden
    
    $tpl_selected_text = 'täglich';
    $tpl_selected_value = 'taeglich';
    
    $tpl_entries = array();
    
    $tpl_entries[0]['title'] = 'täglich';
    $tpl_entries[0]['attributes']['rel'] = 'taeglich';
    $tpl_entries[0]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-custom-repeat\'); show(\'#'.$this->getFieldId().'-custom-repeat-daily\');';
    
    $tpl_entries[1]['title'] = 'woechentlich';
    $tpl_entries[1]['attributes']['rel'] = 'woechentlich';
    $tpl_entries[1]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-custom-repeat\'); show(\'#'.$this->getFieldId().'-custom-repeat-weekly\');';
    
    $tpl_entries[2]['title'] = 'monatlich';
    $tpl_entries[2]['attributes']['rel'] = 'monatlich';
    $tpl_entries[2]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-custom-repeat\'); show(\'#'.$this->getFieldId().'-custom-repeat-monthly\');';
    
    $tpl_entries[3]['title'] = 'jährlich';
    $tpl_entries[3]['attributes']['rel'] = 'jaehrlich';
    $tpl_entries[3]['attributes']['onclick'] = 'hide(\'.'.$this->getFieldId().'-custom-repeat\'); show(\'#'.$this->getFieldId().'-custom-repeat-yearly\');';
    
    // Dropdown aufbauen
    $f = new rex_fragment();
    $f->setVar('class_ul', $ul_classes, false);
    $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
    $f->setVar('selected', $tpl_selected_text, false);
    $f->setVar('entries', $tpl_entries, false);
    $f->setVar('extra', '<input id="' . $this->getFieldId().'-'. $id_counter++ . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
    $custom_repeat_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

		
    // XForm Fragment aufbauen
    $before = '';
    $after = '';
		$label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . rex_i18n::translate('Häufigkeit') . '</label>';
		$field = $custom_repeat_dropdown;
		$extra = '<script type="text/javascript">
		            $(document).ready(function()
		            {
		              hide(\'.'.$this->getFieldId().'-custom-repeat\');
		            });
		          </script>';
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
		
		$custom_repeat_fragment = $this->params['fragment'];
		$custom_repeat_fragment = $f->parse($custom_repeat_fragment);
		
		
		
		// Taeglich
		
    // XForm Fragment aufbauen
    
    $wrapper = '<div id="'.$this->getFieldId().'-custom-repeat-daily" class="'.$this->getFieldId().'-custom-repeat">###</div>';
    $before = '';
    $after = '';
		$label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . rex_i18n::translate('Alle') . '</label>';
		$field = '<input type="number" value="" name="" max="10" min="1" class="xform-number" /><span class="fwording1">'.rex_i18n::translate('Tag(e)').'</span>';
		$extra = '';
    $html_id = $this->getHTMLId();
    $name = $this->getName();
		$fragment_class = $class.' data-indent xform-number fnumber1-fwording1';
    
		$f = new rex_fragment();
		$f->setVar('wrapper', $wrapper, false);
		$f->setVar('before', $before, false);
		$f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('extra', $extra, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $fragment_class, false);
		
		$custom_repeat_daily_fragment = $this->params['fragment'];
		$custom_repeat_daily_fragment = $f->parse($custom_repeat_daily_fragment);
		
		
    // ---------------------------------------------------------------- ENDE - Wiederholung, "angepasst"



		
		$this->params["form_output"][$this->getId()] = $repeat_fragment.$repeat_end_fragment.$custom_repeat_fragment.$custom_repeat_daily_fragment;
		
		

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