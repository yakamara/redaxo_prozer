<!DOCTYPE html>
<html lang="de">
<head>
	<title><?php echo htmlspecialchars(pz_screen::getPageTitle()); ?></title>
	<meta charset="utf-8">
    <?php
	//<meta name="viewport" content="width=1200" />
    //<meta name="viewport" content="width=device-width, initial-scale=1" />
    ?>
    <meta name="viewport" content="width=800" />
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
    <script type="text/javascript" src="/assets/addons/prozer/js/prozer_responsive.js"></script>
	<script type="text/javascript" src="<?php echo $themepath; ?>/theme.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>


</head>
<body class="grid-a-bc<?php

if(pz::getUser() && pz::getUser()->getId() != pz::getLoginUser()->getId())
	echo ' notme';

?>"<?php echo (isset($this->page_id) && $this->page_id != '') ? ' id="'.$this->page_id.'"' : ''; ?>>

<div class="modal fade" id="spaceModal" tabindex="-1" role="dialog" aria-labelledby="spaceModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="spaceModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
                ...
            </div>
        </div>
    </div>
</div>



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

  <div id="main" class="row" style="position:relative;">
      <?php echo @$this->section; ?>
  </div>

<?php

echo '<script type="text/javascript">

$(document).ready(function() {

});

</script>';

echo '</body>
</html>';
