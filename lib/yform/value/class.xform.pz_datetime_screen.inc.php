<?php

class rex_yform_pz_datetime_screen extends rex_yform_abstract
{
    public function preValidateAction()
    {
        $date = date('Y-m-d');
        $time = date('H:i');

        $value = $this->getValue();

        if (is_string($value) && $value != '') {
            $format = 'Y-m-d H:i:s';
            $date_object = DateTime::createFromFormat($format, $this->getValue());

            if ($date_object->format($format) == $value) {
                $date = $date_object->format('Y-m-d');
                $time = $date_object->format('H:i');
            }
        } elseif (is_array($value) && isset($value['date']) && isset($value['minutes']) && isset($value['hours'])) {
            $datetime = $value['date'].' '.$value['hours'].':'.$value['minutes'];
            $format = 'Y-m-d H:i';
            $date_object = DateTime::createFromFormat($format, $datetime);

            if ($date_object->format($format) == $datetime) {
                $date = $date_object->format('Y-m-d');
                $time = $date_object->format('H:i');
            }
        } elseif (rex_request('day', 'int') > 0) {
            $datetime = rex_request('day', 'int');
            $format = 'Ymd';
            $date_object = DateTime::createFromFormat($format, $datetime);

            if ($date_object->format($format) == $datetime) {
                $date = $date_object->format('Y-m-d');
                $time = '12:00'; // $date_object->format("H:i");
            }
        }

        $time_array = explode(':', $time);
        $hours = $time_array[0];
        $minutes = $time_array[1];

        if ($hours < 10) {
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        }

        if ($minutes > 40) {
            $minutes = 45;
        } elseif ($minutes > 25) {
            $minutes = 30;
        } elseif ($minutes > 10) {
            $minutes = 15;
        } elseif ($minutes < 10) {
            $minutes = 0;
        }

        if ($minutes < 10) {
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        }

        $this->setValue($date.' '.$hours.':'.$minutes.':00');
    }

    public function enterObject()
    {
        $dt_e = explode(' ', $this->getValue());
        $date = $dt_e[0];
        $t_e = explode(':', $dt_e[1]);

        $hours = $t_e[0];
        $minutes = $t_e[1];

        // ----------------------------------------------------------------

        $class = $this->getHTMLClass();
        $classes = $class;

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

        // ----------------------------------------------------------------

        $calendar = '<input id="'.$this->getHtmlId('date').'" class="yform-date" type="text" name="'.$this->getFieldName('date').'" value="'.$date.'"  />

                <ul class="sl3 fsl3">
                  <li class="first last selected"><a class="selected tooltip calendar bt2" href="javascript:void(0);" onclick="$(\'#'.$this->getHtmlId('date').'\').focus();"><span class="icon"></span><span class="tooltip"><span class="inner">'.pz_i18n::msg('calendar').'</span></span></a>
                    <div class="calendar view-flyout">
					</div>
                  </li>
                </ul>
                <script language="Javascript">
                $(document).ready(function(){

					$.datepicker.regional["'.pz_i18n::msg('locale').'"] = {
						closeText: "'.pz_i18n::msg('close').'", // Display text for close link
						prevText: "'.pz_i18n::msg('previous').'", // Display text for previous month link
						nextText: "'.pz_i18n::msg('next').'", // Display text for next month link
						currentText: "'.pz_i18n::msg('today').'", // Display text for current month link
						monthNames: ["'.pz_i18n::msg('january').'","'.pz_i18n::msg('february').'","'.pz_i18n::msg('march').'","'.pz_i18n::msg('april').'","'.pz_i18n::msg('may').'","'.pz_i18n::msg('june').'", "'.pz_i18n::msg('july').'", "'.pz_i18n::msg('august').'", "'.pz_i18n::msg('september').'", "'.pz_i18n::msg('october').'", "'.pz_i18n::msg('november').'", "'.pz_i18n::msg('december').'"],
						monthNamesShort: ["'.pz_i18n::msg('january_short').'","'.pz_i18n::msg('february_short').'","'.pz_i18n::msg('march_short').'","'.pz_i18n::msg('april_short').'","'.pz_i18n::msg('may_short').'","'.pz_i18n::msg('june_short').'", "'.pz_i18n::msg('july_short').'", "'.pz_i18n::msg('august_short').'", "'.pz_i18n::msg('september_short').'", "'.pz_i18n::msg('october_short').'", "'.pz_i18n::msg('november_short').'", "'.pz_i18n::msg('december_short').'"],
						dayNames: ["'.pz_i18n::msg('sunday').'", "'.pz_i18n::msg('monday').'", "'.pz_i18n::msg('tuesday').'", "'.pz_i18n::msg('wednesday').'", "'.pz_i18n::msg('thursday').'", "'.pz_i18n::msg('friday').'", "'.pz_i18n::msg('saturday').'"],
						dayNamesShort: ["'.pz_i18n::msg('sunday_short').'", "'.pz_i18n::msg('monday_short').'", "'.pz_i18n::msg('tuesday_short').'", "'.pz_i18n::msg('wednesday_short').'", "'.pz_i18n::msg('thursday_short').'", "'.pz_i18n::msg('friday_short').'", "'.pz_i18n::msg('saturday_short').'"], // For formatting
						dayNamesMin: ["'.pz_i18n::msg('sunday_short').'", "'.pz_i18n::msg('monday_short').'", "'.pz_i18n::msg('tuesday_short').'", "'.pz_i18n::msg('wednesday_short').'", "'.pz_i18n::msg('thursday_short').'", "'.pz_i18n::msg('friday_short').'", "'.pz_i18n::msg('saturday_short').'"], // Column headings for days starting at Sunday
						weekHeader: "'.pz_i18n::msg('calendarweek_short').'", // Column header for week of the year
						dateFormat: "yy-mm-dd",
						firstDay: 1, // The first day of the week, Sun = 0, Mon = 1, ...
						isRTL: false, // True if right-to-left language, false if left-to-right
						showMonthAfterYear: false, // True if the year select precedes month, false for month then year
						yearSuffix: "", // Additional text to append to the year in the month headers
						showWeek: true
					};

                	$.datepicker.setDefaults( $.datepicker.regional["'.pz_i18n::msg('locale').'"] );
					$(\'#'.$this->getHtmlId('date').'\').datepicker();

				});
				</script>
                ';

        // ---------------------------------------------------------------- Stunden Butler
        $tpl_entries = [];

        $update_time = 'h=$(\'#'.$this->getFieldId('hours').'\').val();';
        $update_time .= 'm=$(\'#'.$this->getFieldId('minutes').'\').val();';
        $update_time .= '$(\'#'.$this->getFieldId('time').'\').val(h+\':\'+m);';
        $update_time .= '$(\'.'.$this->getFieldId('i_hours').'\').removeClass(\'active\');';
        $update_time .= '$(\'.'.$this->getFieldId('i_hours').'\'+h).addClass(\'active\');';
        $update_time .= '$(\'.'.$this->getFieldId('i_minutes').'\').removeClass(\'active\');';
        $update_time .= '$(\'.'.$this->getFieldId('i_minutes').'\'+m).addClass(\'active\');';
        $update_time .= 'void(0);';
        // $update_time.= 'alert(h+m);';

        for ($i = 0; $i <= 23; $i++) {
            $hh = ($i <= 9) ? '0'.$i : $i;

            $tpl_entries[$hh]['name'] = $hh;
            $tpl_entries[$hh]['url'] = 'javascript:$(\'#'.$this->getFieldId('hours').'\').val(\''.$hh.'\');'.$update_time;
            $tpl_entries[$hh]['attributes']['class'] = 'bt11 '.$this->getFieldId('i_hours').' '.$this->getFieldId('i_hours').$hh;
            if ($i == $hours) {
                $tpl_entries[$hh]['attributes']['class'] .= ' active ';
            }
        }

        for ($i = 0;$i < 4;$i++) {
            $tpl_entries['n'.$i]['name'] = '&nbsp;';
            $tpl_entries['n'.$i]['attributes']['class'] = 'bt11';
        }

        for ($i = 0; $i <= 59; $i += 15) {
            $mm = ($i <= 9) ? '0'.$i : $i;
            $m = ':'.$mm;

            $tpl_entries[$m]['name'] = $m;
            $tpl_entries[$m]['url'] = 'javascript:$(\'#'.$this->getFieldId('minutes').'\').val(\''.$mm.'\');'.$update_time;
            $tpl_entries[$m]['attributes']['class'] = 'bt11 '.$this->getFieldId('i_minutes').' '.$this->getFieldId('i_minutes').$mm;
            if ($i == $minutes) {
                $tpl_entries[$m]['attributes']['class'] .= ' active';
            }
        }

        $f = new pz_fragment();
        $f->setVar('entries', $tpl_entries, false);
        $butler = '
		<input id="' . $this->getFieldId('hours') .'" type="hidden" name="'.$this->getFieldName('hours').'" value="'.$hours.'" />
		<input id="' . $this->getFieldId('minutes') .'" type="hidden" name="'.$this->getFieldName('minutes').'" value="'.$minutes.'" />
		<input id="' . $this->getFieldId('time') .'" class="yform-time" type="text" name="'.$this->getFieldName('time').'" value="'.$hours.':'.$minutes.'" disabled="disabled" />';

        $butler2 = $f->parse('pz_screen_navi_butler.tpl');

        // ---------------------------------------------------------------- Form Element
        $before = '';
        $after = $butler2;
        $label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
        $field = $calendar.$butler;
        $extra = '';
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' yform-date fdate1-sl3-sl1-navi-butler';

        $f = new pz_fragment();
        $f->setVar('before', $before, false);
        $f->setVar('after', $after, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('extra', $extra, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $this->setValue($date.' '.$hours.':'.$minutes.':00');

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
