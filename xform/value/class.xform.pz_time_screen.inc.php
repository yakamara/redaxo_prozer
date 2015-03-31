<?php

class rex_xform_pz_time_screen extends rex_xform_abstract
{
    public function enterObject()
    {
        $hour_start = 7;
        $hour_end = 23;
        $minute_step = 15;

        if (!$this->params['send'] && $this->getValue() == '' && $this->getElement(5) != '') {
            $this->setValue($this->getElement(5));
        }

        if (!is_array($this->getValue())) {
            $this->setValue(explode(',', $this->getValue()));
        }

        $values = $this->getValue();

        $class = $this->getHTMLClass();
        $classes = $class;

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $ul_classes = $classes.' js-save-dropdown-value';

        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

        // ----------------------------------------------------------------
        $id_counter = 0; // Id Counter, da mehrere Ids benoetigt werden
        $id_counter++;

        // ---------------------------------------------------------------- Stunden
        $tpl_entries = [];

        $tpl_selected_text = '';
        $tpl_selected_value = '';

        for ($i = $hour_start; $i <= $hour_end; $i++) {
            for ($j = 0; $j <= 59; $j += $minute_step) {
                $h = ($i <= 9) ? '0'.$i : $i;
                $m = ($j <= 9) ? '0'.$j : $j;

                $hm = $h.':'.$m;

                if ($i == $hour_start) {
                    $tpl_selected_text = $hm;
                    $tpl_selected_value = $hm;
                }

                $tpl_entries[$hm]['title'] = $hm;
                $tpl_entries[$hm]['attributes']['rel'] = $hm;
            }
        }

        $fragment_ul_classes = $ul_classes;
        // Dropdown aufbauen
        $f = new pz_fragment();
        $f->setVar('class_ul', $fragment_ul_classes, false);
        $f->setVar('class_selected', $this->getFieldId() .'-'. $id_counter .'-selected', false);
        $f->setVar('selected', $tpl_selected_text, false);
        $f->setVar('entries', $tpl_entries, false);
        $f->setVar('extra', '<input id="' . $this->getFieldId() . '-'. $id_counter .'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($tpl_selected_value)).'" />', false);
        $hours = $f->parse('pz_screen_select_dropdown.tpl');

        // ---------------------------------------------------------------- Stunden Butler
        $tpl_entries = [];
        // Stunden
        for ($i = $hour_start; $i <= $hour_end; $i++) {
            $h = ($i <= 9) ? '0'.$i : $i;

            $tpl_entries[$h]['name'] = $h;
            $tpl_entries[$h]['attributes']['class'] = 'bt11';
        }
        // Zusatz
        for ($j = 0; $j <= 59; $j += $minute_step) {
            $m = ($j <= 9) ? '0'.$j : $j;
            $m = ':'.$m;

            $tpl_entries[$m]['name'] = $m;
            $tpl_entries[$m]['attributes']['class'] = 'bt11';
        }

        $f = new pz_fragment();
        $f->setVar('entries', $tpl_entries, false);
        $butler = $f->parse('pz_screen_navi_butler.tpl');

        // ---------------------------------------------------------------- Form Element
        $before = '';
        $after = '';
        $label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
        $field = $hours.$butler;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' xform-time sl1-navi-butler';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $fragment = $this->params['fragment'];
        $this->params['form_output'][$this->getId()] = $f->parse($fragment);

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
