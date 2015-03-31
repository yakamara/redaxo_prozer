<?php

/*
<p class="formsubmit <?php echo $this->getHTMLClass() ?>">
    <input type="submit" class="submit <?php echo $this->getElement(4), ' ', $this->getWarningClass() ?>"
name="<?php echo $this->getFieldName() ?>" id="<?php echo $this->getFieldId() ?>"
value="<?php echo htmlspecialchars(stripslashes(rex_translate($this->getValue()))) ?>" />
</p>
*/

echo '
  <div id="'.$this->getHTMLId().'" class="formsubmit xform1 data xform-submit '.$this->getHTMLClass().'">
    <div class="felement"><input type="submit" value="'.htmlspecialchars(stripslashes(rex_translate($this->getValue()))).'" name="'.$this->getFieldName().'" id="'.$this->getFieldId().'"
    class="xform-submit '.$this->getElement(4).' '.$this->getWarningClass().'"></div>
  </div>';
