<?php

class rex_xform_pz_showvalue_screen extends rex_xform_abstract
{

	function enterObject()
	{

		$class = $this->getHTMLClass();
		$classes = $class;
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
		
		$before = '';
		$after = '';
		$label = '';
		$field = '';
		$extra = '';
		
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
		$field = '<input'.$classes.' id="'.$this->getFieldId().'" type="text" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'" DISABLED READONLY />';
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
		$this->params["form_output"][$this->getId()] = $f->parse($fragment);
		
	}

}