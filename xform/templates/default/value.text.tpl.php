<?php
    $type = isset($type) ? $type : 'text';
    $class = $type == 'text' ? '' : $type . ' ';
    $value = isset($value) ? $value : stripslashes($this->getValue());
?>

<?php

/*
<p class="formtext <?php echo $class ? 'form' . $class : '' ?>formlabel-<?php echo $this->getName() ?>" id="<?php echo $this->getHTMLId() ?>">
    <label class="text <?php echo $class, ' ', $this->getWarningClass() ?>" for="<?php echo $this->getFieldId() ?>" ><?php echo $this->getLabel() ?></label>
    <input type="<?php echo $type ?>" class="text <?php echo $class, ' ', $this->getElement(5), ' ', $this->getWarningClass() ?>"
name="<?php echo $this->getFieldName() ?>" id="<?php echo $this->getFieldId() ?>" value="<?php echo htmlspecialchars($value) ?>"
<?php echo $this->getAttributeElement('placeholder'), $this->getAttributeElement('autocomplete'), $this->getAttributeElement('pattern'),
$this->getAttributeElement('required', true), $this->getAttributeElement('disabled', true), $this->getAttributeElement('readonly', true) ?> />
</p>
*/

echo '
<div id="'.$this->getHTMLId().'" class="xform1 data xform-text">
  <div class="flabel"><label for="'.$this->getFieldId().'" class="'.$class. ' '. $this->getWarningClass().'">'.$this->getLabel().'</label></div>
  <div class="felement "><input type="'.$type.'" value="'.htmlspecialchars($value).'" name="'.$this->getFieldName().'" id="'.$this->getFieldId().'"
  class="text '.$class. ' ' . $this->getElement(5) . ' ' .$this->getWarningClass() .'" '. $this->getAttributeElement('placeholder') .' '. $this->getAttributeElement('autocomplete') .' '. $this->getAttributeElement('pattern') .' '.
$this->getAttributeElement('required', true).' '. $this->getAttributeElement('disabled', true).' '. $this->getAttributeElement('readonly', true).'></div>
</div>';

/*
<div id="xform-xform-search-fulltext" class="xform1 data xform-text">
    <div class="flabel"><label for="xform-xform-field-1" class="xform-text">Allgemein</label></div>  <div class="felement"><input type="text" value="" name="search_fulltext" id="xform-xform-field-1" class="xform-text"></div>    </div>
*/
