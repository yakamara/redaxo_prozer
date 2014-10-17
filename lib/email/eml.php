<?php

class pz_eml{

	public
		$elements = array(),
		$src = "",
		$vars = array(),
		$children = array(),
		$parent = NULL,
		$part_id = 0,
		$depth = 0;

	function __construct($src, $part_id = 0, $depth = 0)
	{
		$this->src = $src;
		$this->parent = NULL;
		$this->part_id = $part_id;
		$this->depth = $depth;
		$this->element_id = $depth."-".$part_id;
		$this->children = array();
		$this->extractHeaderBody();
		$this->mail_filename = pz_i18n::msg("emlname");
		
	}


	// ----- getter
	
	

	public function getContentDisposition() 
	{
		return @$this->vars["header"]["content-disposition"];
	}

	public function getDepth() 
	{
		return $this->depth;
	}

	public function getPartId() 
	{
		return $this->part_id;
	}

	public function getElementId() 
	{
		return $this->element_id;
	}

	public function getContentType()
	{
		if(!isset($this->vars["header"]["content-type"]) && !$this->hasParent())
			return "message/rfc822";
		return @$this->vars["header"]["content-type"];
	}
	
	public function getContentTypeCharset()
	{
		return @$this->vars["header"]["content-type-charset"];
	}

	public function getContentTypeBoundary()
	{
		return @$this->vars["header"]["content-type-boundary"];
	}

	public function getContentTypeName()
	{
		return @$this->vars["header"]["content-type-name"];
	}

  public function setContentTypeName($name)
	{
		$this->vars["header"]["content-type-name"] = $name;
	}

	public function getContentDispositionFilename()
	{
		return @$this->vars["header"]["content-disposition-filename"];
	}

	public function getBody()
	{
		// if($this->parent === NULL)
		//	return $this->src;
		return @$this->vars["body"];
	}

	public function getSource()
	{
		return $this->src;
	}

	public function getFileName()
	{
		if ($this->parent === NULL) {
			return $this->getMailFilename().".eml";
		}
		
		$filename = $this->getContentTypeName();
		if ($filename == "") {
			$filename = $this->getContentDispositionFilename();
    }

		if ($filename == "") {
			$filename = pz_i18n::msg("no_filename");
  		if ($ext = pz::getExtensionByMimeType($this->getContentType())) {
  			$filename .= '.'.$ext;
  		}
		}

		$filename = pz_eml::decodeCharset($filename);

		return $filename;
	}

	public function getSize()
	{
		if($this->parent === NULL) {
			return strlen($this->src);
		}
		return strlen($this->vars["body"]);
	}

	public function getInlineImage() 
	{
		switch($this->getContentType()) {
			case("image/jpeg"):
			case("image/jpg"):
			case("image/gif"):
			case("image/png"):
				return pz::makeInlineImageFromSource($this->getBody(), "s", $this->getContentType());
				break;
		}
		return pz::getMimetypeIconPath($this->getContentType());

	}	

	public function setMailFilename($filename) 
	{
		$this->mail_filename = $filename;
		
	}

	public function getMailFilename() 
	{
		return $this->mail_filename;
		
	}

	// ---- func

  public function extractBody($body, $content_transfer_encoding = "", $content_type_charset = "")
  {
		if ($content_transfer_encoding == "base64") {
			$body = base64_decode($body);
			
		} else if($content_transfer_encoding == "quoted-printable"){
			$body = quoted_printable_decode($body);

		}

		if($content_type_charset != "" && $content_type_charset != "utf-8") {
			$body = @mb_convert_encoding($body, "UTF-8", $content_type_charset);

		}

    /*		
		if(isset($this->vars["header"]["content-type"]) && $this->vars["header"]["content-type"] == "text/plain")
		{
			$this->vars["body"] = $this->vars["body"];
		}
		*/
		return $body;
  
  }

	public function extractHeaderBody()
	{
		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $this->src, $elements))
		{
			$this->header = $elements[1];
			$this->body = $elements[2];

			$this->vars["header"] = $this->parseHeaderToArray($this->header);

      if($this->getContentTypeBoundary() == "")
			{
			  // -> no multipart
        $this->vars["body"] = $this->extractBody($this->body, @$this->vars["header"]["content-transfer-encoding"], @$this->vars["header"]["content-type-charset"]);

        // is extracted multipart ?
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $this->vars["body"], $elements))
		    {
			    $header = $this->parseHeaderToArray($elements[1]);
          if(isset($header["content-type-boundary"]) && $header["content-type-boundary"] != "")
     			{
     			  // yes
			      // echo '<pre>';var_dump($this->vars["header"]); echo '</pre>';
            // echo '<pre>';var_dump($header); echo '</pre>';
            $this->body = $elements[2];
            $content_type_name = $this->getContentTypeName();
            $this->vars["header"] = $header;
            if($content_type_name != "" && $this->getContentTypeName() == "")
              $this->setContentTypeName($content_type_name);

     			}
        }

			}
			
			
			if($this->getContentTypeBoundary() != "")
			{
				// -> multipart -> extract body
				
				$elements = explode("--".$this->getContentTypeBoundary(),$this->body);
				$i=1;

				$part_id = $this->part_id;
				$depth = 	$this->depth + 1;
				foreach($elements as $element)
				{
					if($i > 1 && $i < count($elements))
					{
						$part_id++;
						$e = new pz_eml($element, $part_id, $depth);
						$e->parent = $this;
						$this->children[] = $e;
					}
					$i++;
				}
			
				$this->vars["children"] = $this->children;
			
			}

		}
	
	}


	public function hasChildren()
	{
		if(isset($this->vars["children"]) && count($this->vars["children"]) > 0)
		{
			return true;
		}
		return false;
	}

  public function getChildren()
	{
		if($this->hasChildren())
			return $this->vars["children"];
		return array();
	}

	public function hasParent()
	{
		if($this->parent != NULL)
		{
		  return true;
		}
		return false;
	}


	public function getAllElements() 
	{
		$elements = array();
		$elements[] = $this;
		foreach($this->getChildren() as $child) 
		{
			$elements = array_merge($elements,$child->getAllElements());
		}
		return $elements;
	}


	public function getElementByElementId($element_id) 
	{
		$elements = $this->getAllElements();
		foreach($elements as $element) 
		{
			if($element->getElementId() == "$element_id") 
			{
				return $element;
			}
		}
		return FALSE;
	}


	public function getElementByContentId($element_id) 
	{
		$elements = $this->getAllElements();
		foreach($elements as $element) 
		{
			if(isset($element->vars["header"]["content-id"]) && $element->vars["header"]["content-id"] == "$element_id") {
				return $element;
			}
		}
		return FALSE;
	}

/*
	
header -- content-transfer-encoding -> quoted-printable
header -- content-type -> text/html
header -- content-type-charset -> "iso-8859-1"

header -- content-transfer-encoding -> base64
header -- content-type -> text/calendar
header -- content-type-charset -> request

Content-Type: text/calendar; charset="utf-8"; method=REQUEST

*/


	/*
		check for real Attachments, 
		- no inline images, 
		- no text or html part..
	*/
	public function hasRealAttachments()
	{
		foreach($this->getAllElements() as $e) {
			if(	$e->getContentDisposition() == "attachment" || $e->getContentDispositionFilename() != "" )  {
				return TRUE;
			}
		}
		return FALSE;
	}

	/*
		get all attached Elements, also inline images..
	*/

	public function getAttachmentElements() {
	
		$return = array();
		$elements = $this->getAllElements();
		
		foreach($elements as $e) {
			if(	
				isset($e->vars["body"]) && 
					(
					  ( 
					  $e->getContentType() != "" || // == "message/rfc822"
					  $e->getContentDisposition() == "attachment" || 
					  $e->getContentDispositionFilename() != "" ||
					  $e->getContentTypeName() != ""
					//  ||	$e->getContentDisposition() == "inline" 
				    ) 
				    and 
				    $e->hasParent()
					)
				) {
				
				$return[] = $e;
			}
			
			/*
			elseif($e->parent === NULL)
			{
				$return[] = $e;
			}
			*/
		}
		return $return;
	}

	public function getFirstHTML()
	{
		$body = "";
		$body = $this->getFirstContentTypeElement("text/html");

		// TODO: scripts, onlick etc deactivate

		$body = preg_replace("#<[ ]*script.*>.*<[ ]*/script[ ]*>#isU", "", $body);
		$body = preg_replace("#<!--.*-->#isU", "", $body);
		// $body = html_entity_decode(strip_tags($body),ENT_COMPAT,"UTF-8");
		
		return $body;
	}

	public function getFirstText()
	{
		$body = "";
		$body = $this->getFirstContentTypeElement("text/plain");
		if($body == "") {
			
			$body = $this->getFirstContentTypeElement("text/html");
			$body = preg_replace("#<[ ]*style.*>.*<[ ]*/style[ ]*>#isU", "", $body);
			$body = preg_replace("#<[ ]*script.*>.*<[ ]*/script[ ]*>#isU", "", $body);
			$body = preg_replace("#<!--.*-->#isU", "", $body);
			$body = preg_replace("#<[ ]*table.*>.*#isU", "\n", $body);
			$body = preg_replace("#<[ ]*tr.*>.*#isU", "\n", $body);
			$body = preg_replace("#<[ ]*td.*>.*#isU", " ", $body);
			$body = preg_replace("#<[ ]*p.*>.*#isU", "\n", $body);
			// $body = preg_replace("#<[ ]*br.*>.*#isU", "", $body);
			$body = html_entity_decode(strip_tags($body),ENT_COMPAT,"UTF-8");

		}

		if($body == "") 
			$body = strip_tags($this->getFirstContentTypeElement("multipart/report"));
		if($body == "") 
			$body = strip_tags($this->getFirstContentTypeElement("message/delivery-status"));
		if($body == "" && !$this->hasChildren() && isset($this->vars["body"]))
			$body = $this->vars["body"];
		
		$body = str_replace("\n\r","\n",$body);
		$body = str_replace("\r\n","\n",$body);
		
		$body = str_replace("\r","\n",$body);
		$body = str_replace("\t","",$body);
		
		$body = str_replace("#&nbsp;#isU"," ",$body);

		$body = preg_replace("#([\ ]{2,50})#", "", $body);
		$body = preg_replace("#([\n]{3,50})#", "\n", $body);
		
		$body = trim($body);
		
		return $body;
	}

	public function getFirstContentTypeElement($content_type = "text/plain", $get_body = true)
	{
		if(isset($this->vars["header"]["content-type"]) && $this->vars["header"]["content-type"] == $content_type)
		{
			if($get_body && isset($this->vars["body"]))
				return $this->vars["body"];
			elseif (isset($this->vars["body"]))
				return $this;
				
		}else
		{
			foreach($this->getChildren() as $child) {
				if($child->getFirstContentTypeElement($content_type) != "") {
					return $child->getFirstContentTypeElement($content_type, $get_body);
				}
			}
		}
		return "";
	}

	public function getDebugInfo()
	{
		$return = "";
		
		$count = 0;
		if($this->parent && isset($this->parent->vars["children"]))
			$count = count($this->parent->vars["children"]);
		elseif(isset($this->vars["children"]))
			$count = count($this->vars["children"]);
		
		$return .= '<div style="opacity:0.5;border:1px solid #333;padding-left:10px;">part_id='.$this->part_id.'/'.$count.' -- element_id: '.$this->getElementId();
		foreach($this->vars as $k => $v)
		{
			if($k == "body")
			$v = htmlspecialchars(substr($v,0,200));
			if($k == "children")
			{
				foreach($v as $c)
				{
					$return .= $c->getDebugInfo();
				}
			
			}elseif(is_array($v))
			{
				$return .= '<div style="border:1px solid #333;padding-left:10px;background-color:#f90;">';
				foreach($v as $w => $x)
				{
					$return .= '<br /><b>'.$k.' -- '.($w).'</b> -> '.($x);
				}			
				$return .= '</div>';
			
			}else
			{
				$return .= '<div style="border:1px solid #333;padding-left:10px;background-color:#0f0;">';
				$return .= '<br /><b>'.$k.'</b> -> '.($v);
				$return .= '</div>';
			}
		}
		$return .= '</div>';
	
		return $return;
	
	
	}


	// ---------------- Mail helper
	
	static function parseHeaderToArray($header) 
	{
		$header = str_replace("\r","",$header);
		$return = array();
		
		// if (preg_match("#\nto: (.*)$#im", $header, $regs)) { 						$return["to"] = pz_eml::decodeCharset(trim($regs[1]));  } 
		if($subject_all = pz_eml::parseHeaderType($header,"to")) {
   			if (preg_match("#to: (.*)$#imx", $subject_all, $subject)){
   				$return["to"] = trim($subject[1]); 
   				$return["to_emails"] = pz_eml::parseAddressList($return["to"]); 
   			}
		}
		
   		// if (preg_match("#\nCc: (.*)$#im", $header, $regs)) { 						$return["cc"] = pz_eml::decodeCharset(trim($regs[1]));  }
		if($subject_all = pz_eml::parseHeaderType($header,"cc")) {
   			if (preg_match("#cc: (.*)$#imx", $subject_all, $subject)){
   				$return["cc"] = trim($subject[1]);
   				$return["cc_emails"] = pz_eml::parseAddressList($return["cc"]); 
   			}
		}

   		// if (preg_match("#\nfrom: (.*)$#im", $header, $regs)) { 						$return["from"] = pz_eml::decodeCharset(trim($regs[1]));  }
		if($subject_all = pz_eml::parseHeaderType($header,"from")) {
   			if (preg_match("#from: (.*)$#imx", $subject_all, $subject)){
   				$return["from"] = trim($subject[1]);
   				$return["from_emails"] = pz_eml::parseAddressList($return["from"]); 
   			}
		}
		
   		if (preg_match("#\nReply-to: (.*)$#im", $header, $regs)) { 					$return["reply-to"] = strtolower(trim($regs[1]));  }
   		if (preg_match('#\nErrors-To:(.*)#im', $header, $regs)) { 					$return["errors-to"] = strtolower(trim($regs[1])); }
   		if (preg_match('#\nReturn-path:(.*)#im', $header, $regs)) { 				$return["return-path"] = strtolower(trim($regs[1])); }

   		if($subject_all = pz_eml::parseHeaderType($header,"subject")) {
   			if (preg_match("#subject: (.*)$#imx", $subject_all, $subject)){
   				$return["subject"] = trim($subject[1]); 
   			}
		}
   		
   		if (preg_match("#\ndate: (.*)$#im", $header, $regs)) { 						$return["date"] = trim($regs[1]);  }

   		if (preg_match('#\ncontent-transfer-encoding: (.*)$#im', $header, $regs)) { $return["content-transfer-encoding"] = strtolower(trim($regs[1])); }
   		if (preg_match('#\nmime-version:(.*)#im', $header, $regs)) { 				$return["mime-version"] = strtolower(trim(str_replace(array(">","<"),"",$regs[1]))); }

   		// if (preg_match('#\nContent-Disposition:(.*)#im', $header, $regs)) { 		$return["content-disposition"] = trim($regs[1]); }
   		if($subject_all = pz_eml::parseHeaderType($header,"Content-Disposition")) {
   			if (preg_match("#content-disposition: ([^;\ ]*)#im", $subject_all, $subject)){
   				$return["content-disposition"] = trim($subject[1]); 
   			}
   			if (preg_match("#[filename|filename\*]=([^;]*)#im", $subject_all, $subject)){
   				$return["content-disposition-filename"] = str_replace(array('"',"'"),"",trim($subject[1])); 
   			}
		}

   		if (preg_match('#\nContent-ID:(.*)#im', $header, $regs)) { 					$return["content-id"] = trim(str_replace(array(">","<"),"",$regs[1])); }
   		if (preg_match('#\nMessage-ID:(.*)#im', $header, $regs)) { 					$return["message-id"] = trim(str_replace(array(">","<"),"",$regs[1])); }
   		if (preg_match('#\nImportance:(.*)#im', $header, $regs)) { 					$return["importance"] = trim($regs[1]); }
   		if (preg_match('#\nSensitivity:(.*)#im', $header, $regs)) { 				$return["sensitivity"] = trim($regs[1]); }


		// X Fields
   		if (preg_match('#\nX-Priority:(.*)#im', $header, $regs)) { 					$return["x-priority"] = trim($regs[1]); }
   		if (preg_match('#\nx-mailer:(.*)$#im', $header, $regs)) { 					$return["x-mailer"] = trim($regs[1]); }
   		if (preg_match('#\nX-Provags-ID:(.*)#im', $header, $regs)) { 				$return["x-provags-id"] = trim($regs[1]); }
		
   		if($subject_all = pz_eml::parseHeaderType($header,"X-Spam-Status")) {
   			if (preg_match("#X-Spam-Status: (.*)$#imx", $subject_all, $subject)){
   				$return["x-spam-status"] = trim($subject[1]); 
   			}
		}

		if($content_type_all = pz_eml::parseHeaderType($header,"Content-Type")) {
			
			// echo "<br />".$content_type_all;
			
   			if (preg_match("#content-type: ([^;\ ]*)#im", $content_type_all, $content_type)){
   				$return["content-type"] = strtolower($content_type[1]); 
   			}
   			if (preg_match('#boundary[ ]*=[ ]*(.[^;\ ]*)#im', $content_type_all, $boundary)) {
   				$return["content-type-boundary"] = str_replace(array('"',"'"),"",trim($boundary[1]));
   			}
   			
/*
   			if (preg_match('#name=(.*)#im', $content_type_all, $boundary)) {
   				$return["content-type-name"] = str_replace(array('"',"'"),"",trim($boundary[1]));
   			}
	   		if (preg_match('#name[ ]*=[" \']*([a-zA-Z-0-9\.-_\[\]]*)[" \';,]*#im', $header, $regs)) {
*/
	   		if (preg_match('#name[ ]*=[ ]*["\']?([a-zA-Z-0-9\.-_\[\] ]*)["\';,]?#im', $header, $regs)) {
   				$return["content-type-name"] = (trim($regs[1])); // strtolower
   			}

   			
	   		// if (preg_match('#charset(.*)=(.*)(.*)#im', $header, $regs)) {
	   		if (preg_match('#charset[ ]*=[" \']*([a-zA-Z-0-9]*)[" \';,]*#im', $header, $regs)) {	
	   			$return["content-type-charset"] = strtolower(trim($regs[1]));
	   		}

	   		if (preg_match('#method[ ]*=[" \']*([a-zA-Z-0-9]*)[" \';,]*#im', $header, $regs)) {	
	   			$return["content-type-method"] = strtolower(trim($regs[1]));
	   		}

		}
		
		return $return;
	}

	static function parseHeaderType($header = "", $type = "", $decodeCharset = TRUE)
	{
		preg_match("#\n".$type.":(.*)((\n(\t| ))*(.*))*#im", $header, $type);
		if (isset($type[0]) && trim($type[0]) != "") {
			$type = trim(str_replace(array("\n")," ",$type[0]));
			$type = str_replace(array("\t"),"",$type);
			if ($decodeCharset) {
				return pz_eml::decodeCharset($type);
			} else {
				return pz_eml::decodeCharset($type);
			}
		}
		return FALSE;
	}

	static function decodeCharset($value)
	{
		$elements = imap_mime_header_decode($value);
		$value = "";
		
		for ($k = 0; $k < count($elements); $k++) {
			$v = $elements[$k]->text;
			$c = $elements[$k]->charset; // is set to default if no charset detected
			
			if (strtolower($c) != "utf-8" && strtolower($c) != "default") {
				// it happens sometimes but is wrong "iso8859-1"
				// correct is "iso-8859-1"
				$v = mb_convert_encoding($v, "UTF-8", $c);
			} else {
			  $v = iconv(mb_detect_encoding($v, mb_detect_order(), true), "UTF-8", $v);
			}
			$value .= $v;
		}
		return $value;
	}

	static function parseAddressList($address)
	{
	
		$address_array  = imap_rfc822_parse_adrlist($address, "example.com");
		if (!is_array($address_array) || count($address_array) < 1) {
			return $address;
		}
		
		$return = array();
		foreach ($address_array as $id => $val) {
			// $val->personal
			// $val->adl
			if(isset($val->mailbox) && isset($val->host) && $val->host != ".SYNTAX-ERROR." && $val->host != "example.com") {
				$return[] = strtolower($val->mailbox.'@'.$val->host);
			}
		}	
		return implode(",",$return);
	}


	// ---------------------------- Mail Refresh
	
	static function refreshInformations() {

		$return = "";
		$sql = pz_sql::factory();
		// $sql->debugsql = 1;
		$emails = $sql->getArray('select * from pz_email where header <> "" and id > ?', array(0));
		
		$return .= '<style>table td {padding:10px;border:1px solid #333;}</style> '.count($emails).' Emails found';
		
		$i = 0;
		foreach($emails as $email)
		{
			$i++;
			$headerinfo = pz_eml::parseHeaderToArray($email["header"]);
			
			if($email["header"] != "")
			{
				$update = array();
				if($email["from"] != "" && @$headerinfo["from"] != "" && $headerinfo["from"] != $email["from"]) {
					$update["from"] = $headerinfo["from"];
					$update["from_emails"] = $headerinfo["from_emails"];
				}
				if($email["to"] <> "" && @$headerinfo["to"] <> "" && $headerinfo["to"] <> $email["to"]) {
					$update["to"] = $headerinfo["to"];
					$update["to_emails"] = $headerinfo["to_emails"];
				}
				if($email["cc"] <> "" && @$headerinfo["cc"] <> "" && $headerinfo["cc"] <> $email["cc"]) {
					$update["cc"] = $headerinfo["cc"];
					$update["cc_emails"] = $headerinfo["cc_emails"];
				}

				if($email["subject"] != "" && @$headerinfo["subject"] != "" && $headerinfo["subject"] != $email["subject"]) {
					$update["subject"] = $headerinfo["subject"];
				}
				if($email["content_type"] != "" && @$headerinfo["content_type"] != "" && $headerinfo["content_type"] != $email["content_type"]) {
					$update["content_type"] = $headerinfo["content_type"];
				}
	
				if(count($update)>0)
				{
					// $return .= '<table>';
					$return .= '<tr><td colspan="3" style="background-color:#ddd">'.$email["id"].' <a href="javascript:void(0)" onclick="$(\'#sh'.$i.'\').show();return FALSE;">header</a><pre style="display:none;" id="sh'.$i.'">'.htmlspecialchars($email["header"]).'</pre></td></tr>';
					foreach($update as $k => $v)
					{
						$return .= '<tr><td>'.$k.'</td><td>'.substr(htmlspecialchars($email[$k]),0,100).'</td><td>'.htmlspecialchars($update[$k]).'</td></tr>';
					}
					
					$u = pz_sql::factory();
					// $u->debugsql = 1;
					$u->setTable('pz_email');
					$u->setWhere('id = '.$email["id"]);
					foreach($update as $k => $v)
					{
						$u->setValue($k,$update[$k]);
					}
					$u->update();
					
					// $return .= '</table>';
				}
				
			}
		}
		return '<table>'.$return.'</table>';

	}

}