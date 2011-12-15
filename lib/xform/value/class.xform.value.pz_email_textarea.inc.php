<?php

class rex_xform_value_pz_email_textarea extends rex_xform_value_abstract
{

	function enterObject()
	{
		if ($this->getValue() == "" && !$this->params["send"])
		{
			$this->setValue($this->getElement(3));
		}

		$class = $this->getHTMLClass();
		$classes = $class;
		
		if ($this->getElement(5) != '')
		{
  			$classes .= ' '.$this->getElement(5);
		}
		
		if (isset($this->params["warning"][$this->getId()]))
		{
			$classes .= ' '.$this->params["warning"][$this->getId()];
		}
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';

		$disabled = "";
		if($this->getElement("disabled")) {
			$disabled = ' disabled="disabled"';
		}
		
		$before = '';
		$after = '';
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . rex_i18n::translate($this->getElement(2)) . '</label>' : '';
		$field = '<textarea'.$classes.'" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" cols="1" rows="1"'.$disabled.' style="height:250px">' . htmlspecialchars(stripslashes($this->getValue())) . '</textarea>';
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
		$this->params["form_output"][$this->getId()] = $f->parse($fragment);

		$this->params["value_pool"]["email"][$this->getName()] = stripslashes($this->getValue());
		if ($this->getElement(4) != "no_db")
		{
			$this->params["value_pool"]["sql"][$this->getName()] = $this->getValue();
		}
	}

}

?>