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
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		$xform->setValueField("text",array("login",pz_i18n::msg("login")));
		$xform->setValueField("password",array("password",pz_i18n::msg("password")));
		$xform->setValueField("checkbox",array("stay",pz_i18n::msg("stay")));

		$xform->setValidateField("pz_auth",array("login","password","stay",pz_i18n::msg("pz_login_please"),pz_i18n::msg("pz_login_enterloginpsw"),pz_i18n::msg("pz_login_failed")));

		$return = '<div id="pz_login" >'.$xform->getForm().'</div>'; // style="display:none;"
	
		if($xform->getObjectparams("actions_executed")) {
			return 1;
		}
		$return = '<div id="loginbox" class="popbox">'.$return.'</div>';
		$return .= '<script>pz_centerPopbox("",180);</script>';
		return $return;	
	}



	public function getMainPage($p = array()) {
		
		$function = '
		        <div class="slogan">
		          <h1><span>PROZER 2.0 </span><br /><span>Agentursoftware</span></h1>
		          <h2><span>Das kostenlose Open-Source </span><br /><span>Projekt-Kommunikations-System</span></h2>
		        </div>
		        <figure class="me1">
		          <img src="/assets/addons/prozer/themes/blue_grey/assets/page_login_yakamara.png" alt="" width="100%" />
		        </figure>';
		
		$section1 = '
		        <div class="grid2col formatted">
		          <div class="column first">
                <h1>Willkommen bei PROZER 2.0</h1>
                <p>PROZER ist eine Agentursoftware, die alle typischen Kommunikationsaufgaben der Projektarbeit abdeckt - vielseitig und leicht zu handhaben. Termine oder Arbeitsstunden können online sowie über CalDav-Clients (iPhone, Android) gepflegt und synchronisiert werden. Auch Adressen, E-Mails und andere Dateien sind mit PROZER immer leicht im Blick zu behalten.</p>
              </div>
              
              <div class="column last">
                <h2>Software-Updates, Weiterentwicklung & Unterstützung</h2>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. <a href="http://prozer.org">mehr Infos auf PROZER.ORG</a></p>
                <h2>Impressum</h2>
                <p>Yakamara Media GmbH & Co. KG  .  Kaiserstrasse 69  .  60329 Frankfurt<br />
                Tel.: 069 900.20.60.30  .  Fax.: 069 900.20.60.33<br />
                <a href="mailto:info@yakamara.de">info@yakamara.de</a>  .  <a href="http://www.yakamara.de">www.yakamara.de</a></p>
              </div>
		        </div>';

		$footer = '
		        <div class="grid2col">
		          <div class="column first">
		            <p>Yakamara Media, Frankfurt, Germany</p>
		          </div>
              
              <div class="column last">
                <ul>
                  <li><a href="http://www.yakamara.de">www.yakamara.de</a></li>
                  <li><a href="http://www.prozer.org">www.prozer.org</a></li>
                </ul>
              </div>
            </div>';
		
		$f = new pz_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $function , false);
		$f->setVar('section_1', $section1 , false); // $this->getLoginForm($p)
//		$f->setVar('section_2', $section2 , false); // $this->getLoginForm($p)
		$f->setVar('footer', $footer , false);
		$f->setVar('page_id', 'login');
		$f->setVar('javascript', "pz_getLogin();");
		
		return $f->parse('pz_screen_main.tpl');
	}



}