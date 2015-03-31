<?php

class rex_xform_pz_date_screen extends rex_xform_abstract
{
    public function enterObject()
    {
        $date = ''; // date("Y-m-d");
        $value = $this->getValue();
        $format = 'Y-m-d';

        if (is_string($value) && $value != '') {
            $date_object = DateTime::createFromFormat($format, $this->getValue());
            if ($date_object->format($format) == $value) {
                $date = $date_object->format('Y-m-d');
                $this->setValue($date);
            }
        }

        // ----------------------------------------------------------------

        $class = $this->getHTMLClass();
        $classes = $class;

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

        // ----------------------------------------------------------------

        $calendar = '<input id="'.$this->getHtmlId('date').'" class="xform-date" type="text" name="'.$this->getFieldName().'" value="'.$date.'"  />

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

        // ---------------------------------------------------------------- Form Element
        $label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
        $field = $calendar;
        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment_class = $class.' xform-date';

        $f = new pz_fragment();
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $fragment_class, false);

        $this->setValue($date);

        $fragment = $this->params['fragment'];
        $this->params['form_output'][$this->getId()] = $f->parse($fragment);

        $this->params['value_pool']['email'][$this->getElement(1)] = $this->getValue();
        if ($this->getElement(4) != 'no_db') {
            $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
        }
    }

    public function getDescription()
    {
        return 'pz_date_screen -> Beispiel: pz_date_screen|field|Bezeichnung|';
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'pz_date_screen',
            'values' => [
                ['type' => 'name',   'label' => 'Feld'],
                ['type' => 'text',    'label' => 'Bezeichnung'],
            ],
            'description' => 'pz_date_screen',
            'dbtype' => 'text',
        ];
    }
}
