<?php


class pz_login_controller_screen extends pz_login_controller
{
    public function controller($function)
    {
        switch ($function) {

            case('form'):
                return $this->getLoginForm();
                break;

            default:
                return $this->getMainPage();
                break;
        }
    }

    // --------------- Views

    public function getLoginForm($p = [])
    {
        $yform = new rex_yform();
        $yform->setObjectparams('real_field_names', 1);
        $yform->setObjectparams('form_id', 'login_form');
        $yform->setValueField('objparams', ['form_action', 'javascript:pz_logIn()']);

        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        $yform->setValueField('text', ['login', pz_i18n::msg('login')]);
        $yform->setValueField('password', ['password', pz_i18n::msg('password')]);
        $yform->setValueField('checkbox', ['stay', pz_i18n::msg('stay')]);

        $yform->setValidateField('pz_auth', ['login', 'password', 'stay', pz_i18n::msg('pz_login_please'), pz_i18n::msg('pz_login_enterloginpsw'), pz_i18n::msg('pz_login_failed')]);

        $return = '<div id="pz_login" >'.$yform->getForm().'</div>'; // style="display:none;"

        if ($yform->getObjectparams('actions_executed')) {
            return 1;
        }
        $return = '<div id="loginbox" class="popbox">'.$return.'</div>';
        $return .= '<script>pz_centerPopbox("",180);</script>';
        return $return;
    }

    public function getMainPage($p = [])
    {
        $function = '
		        <div class="slogan">
		          <h1><span>PROZER 3.0 </span><br /><span>Agentursoftware</span></h1>
		          <h2><span>Das kostenlose Open-Source </span><br /><span>Projekt-Kommunikations-System</span></h2>
		        </div>
		        <figure class="me1">
		          <img src="/assets/addons/prozer/themes/blue_grey/assets/page_login_yakamara.png" alt="" width="100%" />
		        </figure>';

        $section1 = '
		        <div class="grid2col formatted">
		          <div class="column first">
                <h1>Willkommen bei PROZER 3.0</h1>
                <p>PROZER ist eine Agentursoftware, die alle typischen Kommunikationsaufgaben der Projektarbeit abdeckt - vielseitig und leicht zu handhaben. Termine oder Arbeitsstunden können online sowie über CalDav-Clients (iPhone, Android) gepflegt und synchronisiert werden. Auch Adressen, E-Mails und andere Dateien sind mit PROZER immer leicht im Blick zu behalten.</p>
              </div>

              <div class="column last">
                <h2>Software-Updates, Weiterentwicklung & Unterstützung</h2>
                <p>Als Open Source Software entwickeln die Anwender Prozer ständig weiter, sodass die Funktionen Up-To-Date bleiben. Es gibt regelmäßige Updates, die teils auch agenturindividuelle Wünsche beinhalten. Jede Weiterentwicklung wird geprüft und steht dann allen Anwendern zur Verfügung. So gestaltet die Anwendergemeinde von Prozer gemeinsam eine Agentursoftware, die einfach anzuwenden ist. <a href="http://prozer.org">mehr Infos auf PROZER.ORG</a></p>
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
        $f->setVar('function', $function, false);
        $f->setVar('section_1', $section1, false); // $this->getLoginForm($p)
//		$f->setVar('section_2', $section2 , false); // $this->getLoginForm($p)
        $f->setVar('footer', $footer, false);
        $f->setVar('page_id', 'login');
        $f->setVar('javascript', 'pz_getLogin();');

        return $f->parse('pz_screen_main.tpl');
    }
}
