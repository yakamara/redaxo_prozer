<!DOCTYPE html>
<html lang="de">
<head>
	<title><?php echo pz_screen::getPageTitle(); ?></title>
	<meta charset="utf-8">
	<base href="<?php echo pz::getServerUrl(); ?>/" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/fonts/css_fonts.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/css_import.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/themes/blue_grey/css_theme.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/fileuploader.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/labels_screen.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/jquery_ui.css" media="screen" />
  <link rel="shortcut icon" type="image/x-icon" href="/assets/addons/prozer/themes/blue_grey/faviconn.ico" />
	
  <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="/assets/addons/prozer/themes/blue_grey/css_ie.css" media="screen" />
	<![endif]-->
	
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/prozer.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/fileuploader.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.bullseye-1.0.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/chosen.jquery.js"></script>
	
</head>
<body class="grid-a-bc<?php

if(pz::getUser() && pz::getUser()->getId() != pz::getLoginUser()->getId())
	echo ' notme';

?>"<?php echo (isset($this->page_id) && $this->page_id != '') ? ' id="'.$this->page_id.'"' : ''; ?>>

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
if(isset($this->footer) && $this->footer != '')
{
  echo '
  <footer id="footer">
    <div class="wrapper clearfix">
      '.$this->footer.'
    </div>
  </footer>';
}
?>

<?php 

echo '<script type="text/javascript">';
if(isset($this->javascript) && $this->javascript != "") {
	echo $this->javascript;
}

if(pz::getUser())
	echo 'pz_tracker();';

echo '</script>';

?>

</body>
</html>