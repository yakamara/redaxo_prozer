<div class="xform1 formtext data xform-text <?php echo $this->getHTMLClass() ?>"  id="<?php echo $this->getHTMLId() ?>">
    <div class="flabel"><label class="text" for="<?php echo $this->getFieldId() ?>"><?php echo $this->getLabel() ?></label></div>
    <div class="felement ">
        <input type="hidden" name="<?php echo $this->getFieldName() ?>" value="<?php echo htmlspecialchars(stripslashes($this->getValue())) ?>" />
        <input type="text" class="text inp_disabled" disabled="disabled" id="<?php echo $this->getFieldId() ?>" value="<?php echo htmlspecialchars(stripslashes($this->getValue())) ?>" />
    </div>
</div>
