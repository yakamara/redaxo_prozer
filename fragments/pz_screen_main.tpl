<!DOCTYPE html>
<html lang="de">
<head>
	<title>Home | Prozer </title>
	<meta charset="utf-8">
	<base href="http://<?php echo rex::getProperty('server'); ?>/" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/fonts/css_fonts.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/css_import.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/themes/blue_grey/css_theme.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/facebox.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/fileuploader.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/labels_screen.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/jquery_ui.css" media="screen" />
	
	
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/facebox.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/prozer.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/fileuploader.js"></script>

</head>
<body class="grid-a-bc"<?php echo (isset($this->page_id) && $this->page_id != '') ? ' id="'.$this->page_id.'"' : ''; ?>>

  <header id="header">
    <div class="wrapper clearfix">
      <?php echo @$this->header; ?>
    </div>
  </header>
  
  <div id="function">
    <div class="wrapper clearfix grid2col">
        <?php echo @$this->function; ?>
    </div>
  </div>
  
  <div id="main">
    <div class="wrapper clearfix">

      <?php echo @$this->main_header; ?>
      
      <section class="area section1">
        <?php echo @$this->section_1; ?>
      </section>

      <section class="area section2">
        <?php echo @$this->section_2; ?>
      </section>

      <section class="area section3">
        <?php echo @$this->section_3; ?>
      </section>
        
    </div>
  </div>

<?php 

if(isset($this->javascript) && $this->javascript != "") {
	echo '<script type="text/javascript">';
	echo $this->javascript;
	echo '</script>';
}

?>

<div id="sidebar" class="sidebar sidebar1" style="display:none"></div>
 
<div id="pz_tracker"></div>

</body>
</html>