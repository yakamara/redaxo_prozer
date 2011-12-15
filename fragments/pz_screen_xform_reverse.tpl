<?php
/*
$this->before
$this->label
$this->field
$this->after
$this->extra
$this->name
$this->class
$this->html_id
*/
?>

<div class="xform1 xform1-reverse data<?php echo ($this->class != '') ? ' '.$this->class : ''; ?>">
  <div class="flabel"><?php echo $this->label; ?></div>
  <div class="felement"><?php echo $this->field; ?></div>
</div>