<?php

$value = isset($value) ? $value : 1;

/*
<p class="formcheckbox formlabel-<?php echo $this->getName() ?>" id="<?php echo $this->getHTMLId() ?>">
    <input type="checkbox" class="checkbox<?php echo $this->getWarningClass() ?>"
name="<?php echo $this->getFieldName() ?>" id="<?php echo $this->getFieldId() ?>"
value="<?php echo $value ?>"<?php echo $this->getValue() == $value ? ' checked="checked"' : '' ?> />

    <label class="checkbox <?php echo $this->getWarningClass() ?>" for="<?php echo $this->getFieldId() ?>" >
<?php echo $this->getLabel() ?></label>
</p>
*/

echo '<div id="'.$this->getHTMLId().'" class="yform1 data yform1-reverse yform-checkbox">
  <div class="flabel"><label for="'.$this->getFieldId().'" class="checkbox yform-checkbox '.$this->getWarningClass().'">'.$this->getLabel().'</label></div>
  <div class="felement"><input type="checkbox" value="'.$value.'" name="'.$this->getFieldName().'" id="'.$this->getFieldId().'"
  class="checkbox yform-checkbox '.$this->getWarningClass().'" ';
echo $this->getValue() == $value ? ' checked="checked"' : '';
echo '></div>
</div>';
