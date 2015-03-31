<?php

class rex_xform_pz_reminder_screen extends rex_xform_abstract
{
    public function enterObject()
    {
        $id_counter = 0;

        $class = $this->getHTMLClass();
        $classes = $class;

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $ul_classes = $classes.' js-save-dropdown-value';

        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

        // ---------------------------------------------------------------- Erinnerung auswaehlen
        $id_counter++; // Id Counter, da mehrere Ids benoetigt werden

        $tpl_selected_text = 'ohne';
        $tpl_selected_value = '';

        $tpl_entries = [];

        $tpl_entries[0]['title'] = 'Popup';
        $tpl_entries[0]['attributes']['rel'] = 'popup';

        $tpl_entries[1]['title'] = 'Popup mit Ton';
        $tpl_entries[1]['attributes']['rel'] = 'popup_mit_ton';

        $tpl_entries[2]['title'] = 'E-Mail';
        $tpl_entries[2]['attributes']['rel'] = 'email';

        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId().'-'. $id_counter++ . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $reminder_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

        // XForm Fragment aufbauen
        $before = '';
        $after = '';
        $label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . pz_i18n::translate('Erinnerung') . '</label>';
        $field = $reminder_dropdown;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $class, false);

        $reminder_fragment = $this->params['fragment'];
        $reminder_fragment = $f->parse($reminder_fragment);

        // ---------------------------------------------------------------- ENDE Erinnerung auswaehlen


        // ---------------------------------------------------------------- wenn - Popup und Popup mit Ton
        // ---------------------------------------------------------------- erscheint bei allen, ausser bei "Am"
        $id_counter++;

        $tpl_selected_text = 'ohne';
        $tpl_selected_value = '';

        $tpl_entries = [];

        $tpl_entries[0]['title'] = 'Minuten davor';
        $tpl_entries[0]['attributes']['rel'] = 'minuten_davor';

        $tpl_entries[1]['title'] = 'Stunden davor';
        $tpl_entries[1]['attributes']['rel'] = 'stunden_davor';

        $tpl_entries[2]['title'] = 'Tage davor';
        $tpl_entries[2]['attributes']['rel'] = 'tage_davor';

        $tpl_entries[3]['title'] = 'Minuten danach';
        $tpl_entries[3]['attributes']['rel'] = 'minuten_danach';

        $tpl_entries[4]['title'] = 'Stunden danach';
        $tpl_entries[4]['attributes']['rel'] = 'stunden_danach';

        $tpl_entries[5]['title'] = 'Tage danach';
        $tpl_entries[5]['attributes']['rel'] = 'tage_danach';

        $tpl_entries[6]['title'] = 'Am';
        $tpl_entries[6]['attributes']['rel'] = 'am';

        $fragment_ul_classes = $ul_classes.' w5 w6';
        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $fragment_ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId() . '-'. $id_counter .'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $popup_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

        // XForm Fragment aufbauen
        $before = '';
        $after = '';
        $label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . pz_i18n::translate('Zeit') . '</label>';
        $field = '<input type="number" value="" name="" min="1" class="xform-number" />';
        $field .= $popup_dropdown;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' data-indent xform-number fnumber1-sl1';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $popup_fragment = $this->params['fragment'];
        $popup_fragment = $f->parse($popup_fragment);

        // ---------------------------------------------------------------- ENDE wenn - Popup und Popup mit Ton


        // ---------------------------------------------------------------- wenn - bei Popup und Popup mit Ton "Am" gewaehlt wurde
        // ---------------------------------------------------------------- erscheint nur, wenn "Am" gewaehlt wurde
        $id_counter++;

        $tpl_selected_text = 'Am';
        $tpl_selected_value = 'am';

        $tpl_entries = [];

        $tpl_entries[0]['title'] = 'Minuten davor';
        $tpl_entries[0]['attributes']['rel'] = 'minuten_davor';

        $tpl_entries[1]['title'] = 'Stunden davor';
        $tpl_entries[1]['attributes']['rel'] = 'stunden_davor';

        $tpl_entries[2]['title'] = 'Tage davor';
        $tpl_entries[2]['attributes']['rel'] = 'tage_davor';

        $tpl_entries[3]['title'] = 'Minuten danach';
        $tpl_entries[3]['attributes']['rel'] = 'minuten_danach';

        $tpl_entries[4]['title'] = 'Stunden danach';
        $tpl_entries[4]['attributes']['rel'] = 'stunden_danach';

        $tpl_entries[5]['title'] = 'Tage danach';
        $tpl_entries[5]['attributes']['rel'] = 'tage_danach';

        $tpl_entries[6]['title'] = 'Am';
        $tpl_entries[6]['attributes']['rel'] = 'am';

        $fragment_ul_classes = $ul_classes.' w5 w6';
        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $fragment_ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId() . '-'. $id_counter .'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $popup_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

        $pzcal = new pz_calendar_screen();
        $calendar = $pzcal->getXFormView();

        // XForm Fragment aufbauen
        $before = '';
        $after = '';
        $label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . pz_i18n::translate('Zeit') . '</label>';
        $field = $popup_dropdown;
        $field .= $calendar;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' data-indent xform-date sl1-fdate1-sl3';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $popup_fragment_at = $this->params['fragment'];
        $popup_fragment_at = $f->parse($popup_fragment_at);

        // ---------------------------------------------------------------- ENDE wenn - bei Popup und Popup mit Ton "Am" gewaehlt wurde


        // ---------------------------------------------------------------- Ton auswaehlen
        $id_counter++;

        $tpl_selected_text = 'Der Schrei';
        $tpl_selected_value = 'schrei';

        $tpl_entries = [];
        $tpl_entries[0]['title'] = 'Der Schrei';
        $tpl_entries[0]['attributes']['rel'] = 'schrei';

        $tpl_entries[1]['title'] = 'Beethoven';
        $tpl_entries[1]['attributes']['rel'] = 'beethoven';

        $tpl_entries[2]['title'] = 'UggaChuckka';
        $tpl_entries[2]['attributes']['rel'] = 'uggachuckka';

        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId().'-'. $id_counter++ . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $sound_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

        // XForm Fragment aufbauen
        $before = '';
        $after = '';
        $label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . pz_i18n::translate('Ton') . '</label>';
        $field = $sound_dropdown;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' data-indent';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $sound_fragment = $this->params['fragment'];
        $sound_fragment = $f->parse($sound_fragment);

        // ---------------------------------------------------------------- ENDE Ton auswaehlen

        // ---------------------------------------------------------------- E-Mail auswaehlen
        $id_counter++;

        $tpl_selected_text = 'jan@';
        $tpl_selected_value = 'jan@';

        $tpl_entries = [];
        $tpl_entries[0]['title'] = 'jan@';
        $tpl_entries[0]['attributes']['rel'] = 'jan@';

        $tpl_entries[1]['title'] = 'kai@';
        $tpl_entries[1]['attributes']['rel'] = 'kai@';

        $tpl_entries[2]['title'] = 'ralph@';
        $tpl_entries[2]['attributes']['rel'] = 'ralph@';

        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId().'-'. $id_counter++ . '" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $email_dropdown = $f->parse('pz_screen_select_dropdown.tpl');

        // XForm Fragment aufbauen
        $before = '';
        $after = '';
        $label = '<label'.$classes.' for="' . $this->getFieldId() . '-'. $id_counter .'">' . pz_i18n::translate('E-Mail') . '</label>';
        $field = $email_dropdown;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' data-indent';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $email_fragment = $this->params['fragment'];
        $email_fragment = $f->parse($email_fragment);

        // ---------------------------------------------------------------- ENDE Ton auswaehlen


        $this->params['form_output'][$this->getId()] = $reminder_fragment.$popup_fragment.$popup_fragment_at.$sound_fragment.$email_fragment;

        $this->params['value_pool']['email'][$this->getElement(1)] = $this->getValue();
        if ($this->getElement(4) != 'no_db') {
            $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
        }
    }

    public function getDescription()
    {
        return 'select -> Beispiel: select|gender|Geschlecht *|Frau=w,Herr=m|[no_db]|defaultwert|multiple=1';
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'pz_screen_select',
            'values' => [
                ['type' => 'name',   'label' => 'Feld'],
                ['type' => 'text',    'label' => 'Bezeichnung'],
                ['type' => 'text',    'label' => 'Selektdefinition, kommasepariert',   'example' => 'w=Frau,m=Herr'],
                ['type' => 'no_db',   'label' => 'Datenbank', 'default' => 1],
                ['type' => 'text',    'label' => 'Defaultwert'],
                ['type' => 'boolean', 'label' => 'Mehrere Felder möglich'],
                ['type' => 'text',    'label' => 'Höhe der Auswahlbox'],
            ],
            'description' => 'Ein Selektfeld mit festen Definitionen',
            'dbtype' => 'text',
        ];
    }
}
