<?php

class rex_yform_pz_address_select extends rex_yform_abstract
{
    public function enterObject()
    {
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

        $disabled = '';
        if ($this->getElement('disabled')) {
            $disabled = ' disabled="disabled"';
        }

        $before = '';
        $after = '';
        $label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
        $field = '<input'.$classes.' id="'.$this->getFieldId().'" type="text" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'"'.$disabled.' />';
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

        $fragment = $this->params['fragment'];
        $this->params['form_output'][$this->getId()] = $f->parse($fragment);

        $this->params['value_pool']['email'][$this->getElement(1)] = stripslashes($this->getValue());
        if ($this->getElement(4) != 'no_db') {
            $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
        }
    }

    public function getDescription()
    {
        return 'text -> Beispiel: text|label|Bezeichnung|defaultwert|[no_db]|classes';
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'pz_address_select',
            'values' => [
                ['type' => 'name',   'label' => 'Feld'],
                ['type' => 'text',    'label' => 'Bezeichnung'],
                ['type' => 'text',    'label' => 'Defaultwert'],
                ['type' => 'no_db',   'label' => 'Datenbank',  'default' => 1],
                ['type' => 'text',    'label' => 'classes'],
            ],
            'description' => 'Ein einfaches Textfeld als Eingabe',
            'dbtype' => 'text',
            'famous' => true,
        ];
    }
}
