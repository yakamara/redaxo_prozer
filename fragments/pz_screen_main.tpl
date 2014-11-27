<!DOCTYPE html>
<html lang="de">
<head>
	<title><?php echo htmlspecialchars(pz_screen::getPageTitle()); ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1200" />
	<base href="<?php echo pz::getServerUrl(); ?>/" />
	<?php

	if(pz::getUser()) {
	  $theme = pz::getUser()->getTheme();
	} else {
	  $theme = pz_screen::getTheme();
	}

	$themes = pz_screen::getThemes();
	$themepath = $themes[$theme];

	?>
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/css_import.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/fileuploader.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/labels_screen.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/addons/prozer/css/jquery_ui.css" media="screen" />

	<link rel="stylesheet" type="text/css" href="<?php echo $themepath; ?>/theme.css" media="screen" />
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $themepath; ?>/favicon.ico" />

	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/fileuploader.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/jquery.bullseye-1.0.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/chosen.jquery.js"></script>
	<script type="text/javascript" src="/assets/addons/prozer/js/tasklist.js"></script>

	<script type="text/javascript" src="/assets/addons/prozer/js/prozer.js"></script>
	<script type="text/javascript" src="<?php echo $themepath; ?>/theme.js"></script>

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

echo '<script type="text/javascript">

$(document).ready(function() {

';
if(isset($this->javascript) && $this->javascript != "") {
echo $this->javascript;
}
if(pz::getUser()) {
echo 'pz_add_tracker("global", "/screen/tools/tracker/", 30000, 1);';
}
echo '


});

</script>';

echo '</body>
</html>';
