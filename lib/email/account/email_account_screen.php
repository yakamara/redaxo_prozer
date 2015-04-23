<?php

class pz_email_account_screen
{
    public $email_account;

    public function __construct($email_account)
    {
        $this->email_account = $email_account;
    }

    // --------------------------------------------------------------- Listviews

    public function getListView($p = [])
    {
        $edit_link = "javascript:pz_loadPage('email_account_form','".pz::url('screen', $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'edit_email_account', 'email_account_id' => $this->email_account->getId()]))."')";

        $del_link = "javascript:pz_loadPage('email_accounts_list','".pz::url('screen', $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'delete_email_account', 'email_account_id' => $this->email_account->getId()]))."')";

        $last_login = $this->email_account->getLastLoginDate();
        $last_login_finished = $this->email_account->getLastLoginFinishedDate();

        $info = [];

        $now = new DateTime('now');

        if ($this->email_account->getVar('status') != 1) {
            $info[] = '<span class="message warning">'.pz_i18n::msg('inactive').'</span>';
        }

        if ($last_login->format('Y') > 2000) {
            $info[] = pz_i18n::msg('email_account_last_login').': '.$last_login->format(pz_i18n::msg('format_datetime'));
        }

        if ($last_login_finished->format('Y') > 2000) {
            $info[] = pz_i18n::msg('email_account_last_login_finished').': '.$last_login_finished->format(pz_i18n::msg('format_datetime'));

            if ($this->email_account->getVar('login_failed') == 1) {
                $info[] = '<span class="message warning">'.pz_i18n::msg('email_account_last_login_failed').'</span>';
            } elseif ($this->email_account->getVar('login_failed') == -1) {
                $info[] = '<span class="message success">'.pz_i18n::msg('email_account_last_login_ok').'</span>';
            } else {
                $info[] = '<span class="message info">'.pz_i18n::msg('email_account_last_login_working').'</span>';
            }
        }

        // .message [.error, .warning, .info, .success]

        // last_login - start login
        // last_login_finished - letzter gelungener abruf


        $return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$edit_link.'">
                <hgroup>
                  <h3 class="hl7"><span class="title">'.htmlspecialchars($this->email_account->getVar('name')).' / '.htmlspecialchars($this->email_account->getVar('email')).'</span></h3>'.implode('<br />', $info).'
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="'.$del_link.'">'.pz_i18n::msg('delete').'</a>
            </footer>
          </article>
        ';

        return $return;
    }

    public static function getAccountsListView($email_accounts, $p)
    {
        $content = '';
        $p['layer'] = 'email_accounts_list';

        if (isset($p['info'])) {
            $content .= $p['info'];
        }

        $paginate_screen = new pz_paginate_screen($email_accounts);
        $paginate = $paginate_screen->getPlainView($p);

        $first = ' first';
        foreach ($paginate_screen->getCurrentElements() as $email_account) {
            if ($cs = new pz_email_account_screen($email_account)) {
                $content .= '<li class="lev1 entry'.$first.'">'.$cs->getListView($p).'</li>';
                $first = '';
            }
        }

        $content = $paginate.'<ul class="entries view-list">'.$content.'</ul>';

        $f = new pz_fragment();
        $f->setVar('title', pz_i18n::msg('email_accounts'), false);
        $f->setVar('content', $content, false);
        $f->setVar('paginate', '', false);

        return '<div id="email_accounts_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    // --------------------------------------------------------------- Pageviews


    // --------------------------------------------------------------- Formviews

    public function getEditForm($p = [])
    {
        $header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg('email_account_edit').': '.$this->email_account->getName().'</h1>
          </div>
        </header>';

        $xform = new rex_xform();
        // $xform->setDebug(TRUE);

        $xform->setObjectparams('main_table', 'pz_email_account');
        $xform->setObjectparams('main_id', $this->email_account->getId());
        $xform->setObjectparams('main_where', 'id='.$this->email_account->getId());
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('email_account_edit','email_account_edit_form','".pz::url('screen', $p['controll'], $p['function'], ['mode' => 'edit_email_account'])."')");
        $xform->setObjectparams('form_id', 'email_account_edit_form');
        $xform->setObjectparams('form_showformafterupdate', 1);

        $xform->setHiddenField('email_account_id', $this->email_account->getId());
        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);

        $xform->setValueField('text', ['name', pz_i18n::msg('email_account_name'), '', '0']);
        $xform->setValidateField('empty', ['name', pz_i18n::msg('error_email_account_name_empty')]);
        $xform->setValueField('text', ['email', pz_i18n::msg('email_account_email'), '', '0']);

        $xform->setValueField('pz_select_screen', ['mailboxtype', pz_i18n::msg('email_account_mailboxtype'), 'imap,pop3']);

        $xform->setValueField('text', ['host', pz_i18n::msg('email_account_host'), '', '0']);
        $xform->setValueField('text', ['login', pz_i18n::msg('email_account_login'), '', '0']);
        $xform->setValueField('text', ['password', pz_i18n::msg('email_account_password'), '', '0']);
        $xform->setValueField('text', ['smtp', pz_i18n::msg('email_account_smtp_host'), '', '0']);
        $xform->setValueField('text', ['smtp_login', pz_i18n::msg('email_account_smtp_login'), '', '0']);
        $xform->setValueField('text', ['smtp_password', pz_i18n::msg('email_account_smtp_password'), '', '0']);


        $xform->setValueField('textarea', ['signature', pz_i18n::msg('email_account_signature'), '', '0']);

        $xform->setValueField('checkbox', ['ssl', pz_i18n::msg('email_account_secure_download'), '', 1]);
        $xform->setValueField('checkbox', ['delete_emails', pz_i18n::msg('email_account_delete_emails'), '', 1]);
        $xform->setValueField('checkbox', ['status', pz_i18n::msg('active'), '', 1]);

        $xform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

        $xform->setActionField('db', ['pz_email_account', 'id='.$this->email_account->getId()]);

        $return = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $this->email_account->update();
            $return = $header.'<p class="xform-info">'.pz_i18n::msg('email_account_updated').'</p>'.$return;
            $return .= pz_screen::getJSUpdateLayer('email_accounts_list', pz::url('screen', $p['controll'], $p['function'], ['mode' => 'list']));
        } else {
            $return = $header.$return;
        }
        $return = '<div id="email_account_form"><div id="email_account_edit" class="design1col xform-edit">'.$return.'</div></div>';

        return $return;
    }

    public static function getAddForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('email_account_add').'</h1>
	          </div>
	        </header>';

        $xform = new rex_xform();
        // $xform->setDebug(TRUE);

        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('email_account_add','email_account_add_form','".pz::url('screen', $p['controll'], $p['function'], ['mode' => 'add_email_account'])."')");
        $xform->setObjectparams('form_id', 'email_account_add_form');
        $xform->setObjectparams('form_showformafterupdate', 1);

        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);

        $xform->setValueField('text', ['name', pz_i18n::msg('email_account_name'), '', '0']);
        $xform->setValidateField('empty', ['name', pz_i18n::msg('error_email_account_name_empty')]);
        $xform->setValueField('text', ['email', pz_i18n::msg('email_account_email'), '', '0']);

        $xform->setValueField('pz_select_screen', ['mailboxtype', pz_i18n::msg('email_account_mailboxtype'), 'imap,pop3']);

        $xform->setValueField('text', ['host', pz_i18n::msg('email_account_host'), '', '0']);
        $xform->setValueField('text', ['login', pz_i18n::msg('email_account_login'), '', '0']);
        $xform->setValueField('text', ['password', pz_i18n::msg('email_account_password'), '', '0']);
        $xform->setValueField('text', ['smtp', pz_i18n::msg('email_account_smtp_host'), '', '0']);
        $xform->setValueField('text', ['smtp_login', pz_i18n::msg('email_account_smtp_login'), '', '0']);
        $xform->setValueField('text', ['smtp_password', pz_i18n::msg('email_account_smtp_password'), '', '0']);

        $xform->setValueField('textarea', ['signature', pz_i18n::msg('email_account_signature'), '', '0']);

        $xform->setValueField('checkbox', ['ssl', pz_i18n::msg('email_account_secure_download'), '', '1']);
        $xform->setValueField('checkbox', ['delete_emails', pz_i18n::msg('email_account_delete_emails'), '', '1']);
        $xform->setValueField('checkbox', ['status', pz_i18n::msg('active'), '', '1']);

        $xform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

        $xform->setValueField('hidden', ['user_id', pz::getUser()->getId()]);

        $xform->setActionField('db', ['pz_email_account']);

        $return = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $customer_id = $xform->getObjectparams('main_id');
            if ($customer = pz_customer::get($customer_id)) {
                $customer->create();
            }
            $return = $header.'<p class="xform-info">'.pz_i18n::msg('email_account_added').'</p>';
            $return .= pz_screen::getJSUpdateLayer('email_accounts_list', pz::url('screen', $p['controll'], $p['function'], ['mode' => 'list']));
        } else {
            $return = $header.$return;
        }

        $return = '<div id="email_account_form"><div id="email_account_add" class="design1col xform-add">'.$return.'</div></div>';

        return $return;
    }
}
