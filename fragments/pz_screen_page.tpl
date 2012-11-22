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
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/labels_screen.css" media="screen" />
	
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/facebox.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/prozer.js"></script>
	
</head>
<body class="grid-a-bc"<?php echo (isset($this->page_id) && $this->page_id != '') ? ' id="'.$this->page_id.'"' : ''; ?>>

<?php

foreach(pz::$controller as $page)
{
	echo '<div id="controller-'.$page.'" class="controller">';
	if(isset($this->content[$page])){
		echo $this->content[$page];	
	}
	echo '</div>';
}

?>
 
<!--

<div class="sidebar sidebar1">
  <ul class="navi">
    <li class="lev1 first"><a class="addresses" href="#">Adressbuch</a></li>
    <li class="lev1"><a class="clipboard active" href="#">Clipboard</a></li>
    <li class="lev1 last"><a class="close" href="#">Schließen</a></li>
  </ul>
			
  <?php echo $xform_search; ?>
  <ul class="list">
    <?php
    for ($i = 1; $i <= 100; $i++)
      echo '<li class="item"><a href="#">Clip '.$i.'</a></li>';
    ?>
  </ul>
</div>

<div class="sidebar sidebar2">
  <?php echo $xform_search; ?>
  <ul>
    <?php
    for ($i = 1; $i <= 30; $i++)
      echo '<li><a href="#">Adresse '.$i.'</a></li>';
    ?>
  </ul>
</div>
//-->

</body>
</html>