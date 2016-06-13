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

if(!isset($this->class))
	$this->class = "";
$this->class = ($this->class == 'yform-checkbox') ? 'yform1-reverse '.$this->class : $this->class;
?>

<?php 
$wrapper_s = '';
$wrapper_e = '';
/*
if (isset($this->wrapper) && $this->wrapper != '')
{
  $ex = explode('###', $this->wrapper);
  
  if (count($ex) == 2)
  {
    $wrapper_s = $ex[0];
    $wrapper_e = $ex[1];
  }
}
*/

?>
<?php echo $wrapper_s; ?>
<div class="yform1 data<?php echo ($this->class != '') ? ' '.$this->class : ''; ?>"<?php if(isset($this->html_id) && $this->html_id != "") echo ' id="'.$this->html_id.'"'; ?>>
  <?php echo (isset($this->before) && $this->before != '') ? '<div class="fbefore">'.$this->before.'</div>' : ''; ?>
  <?php echo ($this->label != '') ? '<div class="flabel">'.$this->label.'</div>' : ''; ?>
  <?php echo ($this->field != '') ? '<div class="felement">'.$this->field.'</div>' : ''; ?>
  <?php echo (isset($this->after) && $this->after != '') ? '<div class="fafter">'.$this->after.'</div>' : ''; ?>
  <?php echo (isset($this->extra) && $this->extra != '') ? '<div class="fextra">'.$this->extra.'</div>' : ''; ?>
</div>
<?php echo $wrapper_e; ?>