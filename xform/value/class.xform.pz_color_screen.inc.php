<?php
/**
 * User: Jochen
 * Date: 27.07.15
 * Time: 23:03
 */

class rex_xform_pz_color_screen extends rex_xform_abstract
{
    /**
     * @see rex_xform_pz_color_screen::enterObject()
     *
     * @throws pz_exception
     */
    public function enterObject()
    {
        $extra = $disabled = $after = $before = '';

        if ($this->getValue() == '' && !$this->params['send']) {
            $this->setValue($this->getElement(3));
        }

        $class = $this->getHTMLClass();
        $classes = $class;

        if ($this->getElement(5) != '') {
            $classes .= ' '.$this->getElement(5);
        }

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

        if ($this->getElement('disabled')) {
            $disabled = ' disabled="disabled"';
        }

        $value = htmlspecialchars(stripslashes($this->getValue()));
        if ($this->getElement('default_color') && !$this->getValue()) {
            $value = htmlspecialchars(stripslashes($this->getElement('default_color')));
        }


        $label = ($this->getElement(2) == '')?:
            '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>';

        $field = '<input'.$classes.' id="'.$this->getFieldId().'" ';
        $field .= 'type="color" name="'.$this->getFieldName().'" value="'.$value.'"'.$disabled.' />';

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

        $fragment = $this->params['fragment'];
        $this->params['form_output'][$this->getId()] = $f->parse($fragment);

        $this->params['form_output'][$this->getId()] .= '<script>
		$(document).ready(function() {
		  pz_setEmailAutocomplete("#'.$html_id.' input");
		});
		</script>';

        $this->params['value_pool']['email'][$this->getElement(1)] = stripslashes($this->getValue());
        if ($this->getElement(4) != 'no_db') {
            $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
        }
    }

    /**
     * @see rex_xform_pz_color_screen::getDescription()
     */
    public function getDescription()
    {
        // do nothing
    }
}
