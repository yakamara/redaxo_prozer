<?php

class pz {

	static $user = NULL;
	static $users = NULL;
	static $properties = array();
	static $mediaviews = array('screen', 'api', 'calcarddav', 'caldav', 'webdav', 'carddav', 'cronjob'); // 'mobile'
	static $mediaview = 'screen';

	static function controller() {

		// TODO UTF8 einstellen
		// ini_set("mbstring.func_overload",7);
		// mb_internal_encoding("UTF-8");

		// error_reporting(E_ALL);
		// ini_set("display_errors",1);

		// ob_start();
		
		$func = rex_request('func');

		self::$mediaview = rex_request('mediaview');
		if(!in_array(self::$mediaview,self::$mediaviews)) {
			self::$mediaview = 'screen';
		}

		$class = 'pz_'.pz::$mediaview.'_controller';
		if(!class_exists($class)) {
			return "ERROR PCNE".$class;	
		}
		$ctr = new $class;
		return $ctr->controller($func);

	}


	// -------------------------------------------------------------------------

	// ----------- user/s

	static public function setUser(pz_user $user)
	{
	  self::$user = $user;
	}

	static public function getUser()
	{
	  return self::$user;
	}

	static function getUsers()
	{
		if(count(self::$users) >0)
			return self::$users;

		$params = array();
		$where = "";

		$sql = rex_sql::factory();
	    $sql->setQuery('SELECT u.* FROM pz_user u '.$where.' ORDER BY u.name',$params);
	    $users = array();
	    foreach($sql->getArray() as $row)
	    {
	      $users[$row["id"]] = pz_user::get($row["id"]);
	    }

	    self::$users = $users;

	    return $users;

	}

	static function getUsersAsString() {
		$return = array();
		foreach(pz::getUsers() as $user) {
			$v = $user->getName();
			$v = str_replace('=','',$v);
			$v = str_replace(',','',$v);
			$return[] = $v.'='.$user->getId();
		}
		return implode(",",$return);
	}



	// ------ props

	static function getProperty($prop) {

		return static::$properties[$prop];

	}

	static public function setConfig($key, $value)
	{
	  rex_config::set('prozer', $key, $value);
	}

	static public function hasConfig($key)
	{
	  return rex_config::has('prozer', $key);
	}

	static public function getConfig($key, $default = null)
	{
	  return rex_config::get('prozer', $key, $default);
	}

	static public function removeConfig($key)
	{
	  rex_config::remove('prozer', $key);
	}


	// ----------- tools

	static function cutText($text = '', $size = 30, $ext = " ...") {
		if(strlen($text.$ext) > $size) {
			$text = substr($text, 0, $size-strlen($ext)).$ext;
		}
		return $text;
	}


	static function url($mediaview = '', $controll = '', $func = '', $params = array(), $split = "&")
	{
		if($mediaview == '' or $controll == '') return 'javascript:void(0);';

		$return = "/".$mediaview."/".urlencode($controll)."/";

		if(is_array($func)) return 'XXXXXXXX';
		elseif($func != '') $return .= urlencode($func)."/";

		$p = '';
		if(count($params)>0) {
			foreach($params as $k => $v) {
				if($p != '') $p .= $split;
				$p .= urlencode($k)."=".urlencode($v);
			}
			$return .= "?".$p;
		}
		return $return;
	}


	static function debug($message, $p = '')
	{
		// JMK - übergangsweise.. wegen debug addon
		return $message;

		if(is_array($p))
		{
			rex_logger_debug::log('pz: '.$message.' - array (');
			foreach($p as $k => $m) {
				if(is_array($m))
				{
					rex_logger_debug::log('pz: array '.$k.' - '.$m);
					// pz::debug('pz: '.$message.' '.$k);
				}else
				{		
					rex_logger_debug::log('pz: array '.$k.' - '.$m);
				}
			}
			rex_logger_debug::log('pz: )');
		}elseif($p != '')
		{
			rex_logger_debug::log('pz: '.$message);
			rex_logger_debug::log($p);
		}else
		{
			rex_logger_debug::log('pz: '.$message);
		}

	}

	/* reads the amount of bytes of a file */
	function strBytes($str)
	{
		$strlen_var = strlen($str);
		$d = 0;
		for ($c = 0; $c < $strlen_var; ++$c) {
			$ord_var_c = ord($str{$d});
			switch (true) {
				case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)): $d++; break;
				case (($ord_var_c & 0xE0) == 0xC0): $d+=2; break;
				case (($ord_var_c & 0xF0) == 0xE0): $d+=3; break;
				case (($ord_var_c & 0xF8) == 0xF0): $d+=4; break;
				case (($ord_var_c & 0xFC) == 0xF8): $d+=5; break;
				case (($ord_var_c & 0xFE) == 0xFC): $d+=6; break;
				default: $d++;
			}
		}
		return $d;
	}

	static function makeInlineImage($image_path, $size = "m", $mimetype = "image/png")
	{
		// TODO
		// anhand vom mimetype erkennen welches Bildrenderer genommen werdenkann oder muss oder auch nicht
		// if(isset(pz::$mimetypes[$mimetype]))
		// 	return pz::$mimetypes[$mimetype]["extension"];
	
		$src = @imagecreatefrompng($image_path);
	    if($src) {
			return pz::makeInlineImageFromSource($src, $size);
	    }
		return "";
	}

	static function makeInlineImageFromSource($data, $size = "m", $mimetype = "image/png", $inline = TRUE)
	{

		$src = @imagecreatefromstring($data);
	    if($src) {

	    	imagealphablending($src, true);
			imagesavealpha($src, true);

			$image_width = imagesx($src);
			$image_height = imagesy($src);

	    	$new_width = 25;
	    	$new_height = 25;

	    	if($size == "xxl") {
		    	$new_width = 400;
		    	$new_height = 400;
	    	}elseif($size == "xl") {
		    	$new_width = 200;
		    	$new_height = 200;
	    	}elseif($size == "m") {
		    	$new_width = 40;
		    	$new_height = 40;
	    	}elseif($size == "s") {
		    	$new_width = 20;
		    	$new_height = 20;
	    	}

			$dest_width = $new_width;
			$dest_height = $new_width;

			$image_ratio  = $image_width / $image_height;
			$resize_ratio = $new_width / $new_height;

			if ($image_ratio < $resize_ratio) {
				$new_height = ceil ($new_width / $image_width * $image_height);
			}else {
				$new_width  = ceil ($new_height / $image_height * $image_width);
			}

	    	$tmp = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height);
			$src = $tmp;

			$image_width = imagesx($src);
			$image_height = imagesy($src);

			$offset_height = (int) (($image_height - $dest_height) / 2);
			$offset_width   = (int) (($image_width - $dest_width) / 2);

	    	$tmp = imagecreatetruecolor($dest_width, $dest_height);
			imagecopyresampled($tmp, $src, 0, 0, $offset_width , $offset_height, $dest_width, $dest_height, $dest_width, $dest_height);

			$grey   = ImageColorAllocate ($tmp, 200, 200, 200);
			imageline ( $tmp , 0 , 0 , ($dest_width-2) , 0 ,  $grey );
			imageline ( $tmp , ($dest_width-1) , 0 , ($dest_width-1) , ($dest_height-1) , $grey );
			imageline ( $tmp , ($dest_width-1) , ($dest_height-1) , 0 , ($dest_height-1) , $grey );
			imageline ( $tmp , 0 , ($dest_height-1) , 0 , 0 , $grey );

			$src = $tmp;

			ob_start();
			switch($mimetype) {
				case("image/jpeg"):
				case("image/jpg"):
					imagePNG($src,NULL);
					$mimetype = "image/jpg";
					break;
				default:
					imagePNG($src,NULL);
					$mimetype = "image/png";
			}
			
			$image = ob_get_contents();
			ob_end_clean();

			if($inline)
				$base64_img = 'data:'.$mimetype.';base64,'.base64_encode($image);
			else
				$base64_img = base64_encode($image);
			
			return $base64_img;
	    }

		return "";

	}

	static function readableFilesize($size)
	{
		$size = $size + 0;
		if ($size==0) return "0 Bytes";
		$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
	}

	// --------- mimetypes

	static $mimetypes = array(
		"text/html" 				=> array( "extension" => "html" ),
		"application/zip" 			=> array( "extension" => "zip" ),
		"image/gif" 				=> array( "extension" => "gif" ),
		"image/jpeg" 				=> array( "extension" => "jpg" ),
		"image/jpg" 				=> array( "extension" => "jpg" ),
		"image/png" 				=> array( "extension" => "png"  ),
		"audio/mpeg" 				=> array( "extension" => "mp3" ),
		"message/rfc822"			=> array( "extension" => "eml" ),
		"application/pdf"			=> array( "extension" => "pdf" ),
		"text/plain"				=> array( "extension" => "txt" ),
		"text/html"					=> array( "extension" => "html" ),
		"text/calendar"				=> array( "extension" => "ics" )
	);

	static function getFilenameByMimetype($mimetype) {
		
		if(isset(pz::$mimetypes[$mimetype]) && isset(pz::$mimetypes[$mimetype]["filename"]))
			return pz::$mimetypes[$mimetype]["filename"];
		
		return FALSE;	
		
	}

	static function getExtensionByMimetype($mimetype) {
		
		if(isset(pz::$mimetypes[$mimetype]))
			return pz::$mimetypes[$mimetype]["extension"];
		
		return FALSE;	
		
	}

	static function getMimetypeIconPath($mimetype) {
		
		if($ext = pz::getExtensionByMimetype($mimetype)) 
		{
			$file = rex_path::frontend("/layout_prozer/themes/blue_grey/mimetypes/".$ext.".png");
			if(file_exists($file))
				return "/layout_prozer/themes/blue_grey/mimetypes/".$ext.".png";
		}
		return "/layout_prozer/themes/blue_grey/mimetypes/file.png";;
	}



}


