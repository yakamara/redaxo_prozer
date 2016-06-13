<?php

class rex_yform_pz_recommend_text extends rex_yform_abstract
{
    public function enterObject()
    {
        if ($this->getValue() == '' && !$this->params['send']) {
            $this->setValue($this->getElement(3));
        }

        $class = $this->getHTMLClass();
        $classes = $class;

        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $html_id = $this->getHTMLId();
        $name = $this->getName();

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('');
        $select->setId($this->getFieldId('select'));
        $select->addOption('', '');

        $labels = explode(',', $this->getElement('options'));
        foreach ($labels as $label) {
            $select->addOption($label, $label);
        }

        $label = '<label>' . $select->get() . '</label>';
        $field = '<input id="'.$this->getFieldId().'" class="'.$this->getHTMLClass().'" type="text" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'" />';

        $f = new pz_fragment();
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $classes, false);
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);

        $fragment = $this->params['fragment'];
        $output = $f->parse($fragment);

        $output .= '<script>

		$(document).ready(function()
		{
			$("#'.$this->getFieldId('select').'").change(
		    function()
		    {
		    	$("#'.$this->getFieldId('select').' option:selected").each(function () {
		    	  t = $("#'.$this->getFieldId().'").val();
		    	  if(t != "")
		    	  	t = t+" ";
                  if(this.text != "")
	                $("#'.$this->getFieldId().'").val(t+this.text);
                });
			});
		});

		</script>';

        $this->params['form_output'][$this->getId()] = $output;

        $this->params['value_pool']['email'][$this->getElement(1)] = stripslashes($this->getValue());
        if ($this->getElement(4) != 'no_db') {
            $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
        }
    }
}
