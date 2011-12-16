<?php


class pz_login_controller_screen extends pz_login_controller{


	public function controller($function) {
	
		switch($function) {
		
			case("form"):
				return $this->getLoginForm();
				break;
		
			default:
				return $this->getMainPage();
				break;
		}
	
	}

	// --------------- Views

	public function getLoginForm($p = array()) {
		
		$xform = new rex_xform;
		$xform->setObjectparams('real_field_names', 1);
		$xform->setObjectparams("form_id", "login_form");
		$xform->setValueField('objparams',array('form_action', 'javascript:pz_logIn()'));
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		$xform->setValueField("text",array("login",rex_i18n::msg("login")));
		$xform->setValueField("password",array("password",rex_i18n::msg("password")));
		$xform->setValueField("checkbox",array("stay",rex_i18n::msg("stay")));
		$xform->setValidateField("pz_auth",array("login","password","stay",rex_i18n::msg("pz_login_please"),rex_i18n::msg("pz_login_enterloginpsw"),rex_i18n::msg("pz_login_failed")));
		$return = '<div id="pz_login" >'.$xform->getForm().'</div>'; // style="display:none;"
	
		if($xform->getObjectparams("actions_executed")) {
			return 1;
		}
		$return = '<div id="loginbox">'.$return.'</div>';	
		return $return;	
	}



	public function getMainPage($p = array()) {
		
		$function = '<figure class="me1"><img src="/assets/addons/prozer/themes/blue_grey/assets/intro.jpg" alt="" width="100%" /></figure>';
		
		$section1 = '
		        <div class="design2col formatted">
		          <h1>Willkommen bei Prozer</h1>
		          <p>Erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. 
Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. 
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod. 
Erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. 
Stet clita kasd gubergren, no sea takimata sanctus est .</p>
		        </div>';
		        
		$section2 = '
		        <div class="design1col formatted">
		          <h1>Impressum</h1>
		          <p>Yakamara Media GmbH & Co. KG  .  Kaiserstrasse 69  .  60329 Frankfurt<br />
		          Tel.: 069 900.20.60.30  .  Fax.: 069 900.20.60.33<br />
		          <a href="mailto:info@yakamara.de">info@yakamara.de</a>  .  <a href="http://www.yakamara.de">www.yakamara.de</a></p>
		        </div>';
		
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $function , false);
		$f->setVar('section_1', $section1 , false); // $this->getLoginForm($p)
		$f->setVar('section_2', $section2 , false); // $this->getLoginForm($p)
		// $f->setVar('page_id', 'login');
		$f->setVar('javascript', "pz_getLoginForm();");
		
		return $f->parse('pz_screen_main');
	}



}