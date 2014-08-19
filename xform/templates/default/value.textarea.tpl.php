<?php

/*<p class="formtextarea" id="<?php echo $this->getHTMLId() ?>">
    <label class="textarea <?php echo $this->getWarningClass() ?>" for="<?php echo $this->getFieldId() ?>" ><?php echo $this->getLabel() ?></label>
    <textarea class="textarea <?php echo $this->getElement(5), ' ', $this->getWarningClass() ?>" name="<?php echo $this->getFieldName() ?>"
id="<?php echo $this->getFieldId() ?>" cols="80" rows="10" <?php echo $this->getAttributeElement('placeholder'), $this->getAttributeElement('pattern'),
$this->getAttributeElement('required', true), $this->getAttributeElement('disabled', true), $this->getAttributeElement('readonly', true) ?>>
<?php echo htmlspecialchars(stripslashes($this->getValue())) ?></textarea>
</p>
*/

echo '
   <div id="'.$this->getHTMLId().'" class="xform1 data xform-textarea formtextarea">
    <div class="flabel"><label for="'.$this->getFieldName().'" class="xform-textarea">'.$this->getLabel().'</label></div>
    <div class="felement"><textarea rows="1" cols="1" '.$this->getAttributeElement('placeholder') .' '. $this->getAttributeElement('pattern') .' '.
$this->getAttributeElement('required', true) .' '. $this->getAttributeElement('disabled', true) .' '. $this->getAttributeElement('readonly', true).'
name="'.$this->getFieldName().'" id="'.$this->getFieldName().'"
class="xform-textarea textarea '.$this->getElement(5) . ' ' . $this->getWarningClass().'">'.htmlspecialchars(stripslashes($this->getValue())).'</textarea></div>
   </div>';
