<?php

class pz_clipboard_controller_screen extends pz_clipboard_controller {

	var $name = "clipboard";
	var $function = "";
	var $functions = array("my", "upload","get");
	var $function_default = "my";
	var $visible = FALSE;

	function controller($function) {

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["linkvars"] = array();
		$p["mediaview"] = "screen";
		$p["controll"] = "clipboard";
		$p["function"] = $this->function;

		switch($this->function)
		{
			case("get"): return $this->getClip($p);
			case("upload"): return $this->setUpload($p);
			case("my"):	return $this->getClipboard($p);
			default: break;
		}
		
		return "";
	}

	public function getClip($p) {
		
		$clip_id = rex_request("clip_id","int");
		if($clip = pz_clipboard::getClipById($clip_id))
		{
			$mode = rex_request("mode","string");
			switch($mode) {
				case("image_src_raw"):
					$image_size = rex_request("image_size","string","m");
					$image_type = rex_request("image_type","string","image/jpg");
					$clip_path = pz_clipboard::getPath($clip_id);
					$data = file_get_contents($clip_path);
					$image = pz::makeInlineImageFromSource($data, $image_size, $image_type, FALSE); // raw image
					return $image;
				case("image_inline"):
				case("image_src"):
					$image_size = rex_request("image_size","string","m");
					$image_type = rex_request("image_type","string","image/jpg");
					$clip_path = pz_clipboard::getPath($clip_id);
					$data = file_get_contents($clip_path);
					$image = pz::makeInlineImageFromSource($data, $image_size, $image_type); // image for inline
					return $image;
			}	
			
		}
		
	}



	public function setUpload($p) {

		$clipboard = pz_clipboard::getByUserId( pz::getUser()->getId() );

		// Size Limit checken
		// File Extensions checken

		$return = array();
		$return["clipdata"] = array();

		$filename = rex_request('qqfile','string');
		if ($filename != "") {
			
			$input = fopen("php://input", "r");
			$temp = tmpfile();
			$real_size = stream_copy_to_stream($input, $temp);
			fclose($input);
			
			if (isset($_SERVER["CONTENT_LENGTH"]) && isset($_SERVER["CONTENT_TYPE"])) {
				$content_length = (int) $_SERVER["CONTENT_LENGTH"];
				$content_type = $_SERVER["CONTENT_TYPE"];
				if ($real_size == $content_length) {
					$return["clipdata"] = $clipboard->addClipAsStream($temp,$filename,$content_length,$content_type);
					$return["success"] = true;
				}
			
			}
        }
        
		// $_FILES auch beachten..
		// move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
		// return $_FILES['qqfile']['name'];
		// return $_FILES['qqfile']['size'];

		return htmlspecialchars(json_encode($return), ENT_NOQUOTES);
	}

	public function getClipboard($p) {
		
		$xform = new rex_xform;
		$xform->setDebug(true);

		$xform->setValueField('objparams',array('form_wrap', '<div class="xform xform-search-small">#</div>'));
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField('objparams',array('form_action', 'javascript:alert(3);'));
		
		
		$xform->setValueField('text',array('title',rex_i18n::msg('label_title'), 'Kontakt'));
		$xform->setValueField('submit',array('submit',rex_i18n::msg('ok')));
//		$xform->setValueField('html',array('', '<a class="bt1 search" href=""><span>Suche</span></a>'));
		$xform_search = $xform->getForm();
		
		$return = '		
		<ul class="navi">
		<li class="lev1 first"><a class="addresses" href="#">'.rex_i18n::msg("addressbook").'</a></li>
		<li class="lev1"><a class="clipboard active" href="#">'.rex_i18n::msg("clipboard").'</a></li>
		<li class="lev1 last"><a class="close" href="javascript:void(0);" onclick="$(\'#sidebar\').hide();">'.rex_i18n::msg("close").'</a></li>
		</ul>';
		
		$return .= $xform_search;

		$return .= '<ul class="list">';
		
		$cb = pz_clipboard::getByUserId( pz::getUser()->getId() );
		
		foreach($cb->getClips() as $file) {
			$return .= '<li class="item"><a href="javascript:void(0);" title="'.htmlspecialchars($file["filename"]).'">'.htmlspecialchars(pz::cutText($file["filename"],25)).'<br />['.$file["id"].']</a></li>';
			
			
		}

		$return .= '</ul>';
		
		$return = '<div id="sidebar" class="sidebar sidebar1" >'.$return.'</div>';
		
		return $return;	
	}



}



/*
< ? p hp
    
		$xform = new rex_xform;
		$xform->setDebug(true);

		$xform->setValueField('objparams',array('form_wrap', '<div class="xform xform-search-small">#</div>'));
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField('text',array('title',rex_i18n::msg('label_title'), 'Kontakt'));
		$xform->setValueField('submit',array('submit',rex_i18n::msg('ok')));
//		$xform->setValueField('html',array('', '<a class="bt1 search" href=""><span>Suche</span></a>'));
		$xform_search = $xform->getForm();
  ? >
  
 
<div class="sidebar sidebar1" >
  <ul class="navi">
    <li class="lev1 first"><a class="addresses" href="#">Adressbuch</a></li>
    <li class="lev1"><a class="clipboard active" href="#">Clipboard</a></li>
    <li class="lev1 last"><a class="close" href="#">Schließen</a></li>
  </ul>
			
  <?php echo $xform_search; ?>
  <ul class="list">
    <?php
    for ($i = 1; $i <= 40; $i++)
      echo '<li class="item"><a href="#">Clip '.$i.'</a></li>';
    ?>
  </ul>
</div>

<!--
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
*/