<?php

class pz_customer_screen
{
    public $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    // --------------------------------------------------------------- Listviews

    public function getListView($p = [])
    {
        $p['linkvars']['customer_id'] = $this->customer->getVar('id');

        $edit_link = "javascript:pz_loadPage('customer_form','".pz::url('screen', 'projects', 'customers', array_merge($p['linkvars'], ['mode' => 'edit_customer', 'customer_id' => $this->customer->getId()]))."')";

        $return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$edit_link.'">
                <figure><img src="'.$this->customer->getInlineImage().'" width="40" height="40" alt="" /></figure>
                <hgroup>
                  <h3 class="hl7"><span class="title">'.$this->customer->getVar('name').'</span></h3>
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="'.$edit_link.'">'.pz_i18n::msg('customer_edit').'</a>
            </footer>
          </article>
        ';

        return $return;
    }

    // --------------------------------------------------------------- Pageviews


    // --------------------------------------------------------------- Formviews


    public function getDeleteForm($p = [])
    {
        $header = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('delete_customer').'</h1>
	          </div>
	        </header>';

        $return = $header.'<p class="yform-info">'.pz_i18n::msg('customer_deleted', htmlspecialchars($p['customer_name'])).'</p>';
        $return .= pz_screen::getJSLoadFormPage('customers_list', 'customers_search_form', pz::url('screen', 'projects', 'customers', ['mode' => 'list']));
        $return = '<div id="customer_form"><div id="customer_delete" class="design1col yform-delete">'.$return.'</div></div>';

        return $return;
    }

    public function getEditForm($p = [])
    {
        $header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg('customer_edit').': '.$this->customer->getName().'</h1>
          </div>
        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('main_table', 'pz_customer');
        $yform->setObjectparams('main_id', $this->customer->getId());
        $yform->setObjectparams('main_where', 'id='.$this->customer->getId());
        $yform->setObjectparams('getdata', true);
        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('customer_edit','customer_edit_form','".pz::url('screen', 'projects', 'customers', ['mode' => 'edit_customer'])."')");
        $yform->setObjectparams('form_id', 'customer_edit_form');
        $yform->setObjectparams('form_showformafterupdate', 1);
        $yform->setHiddenField('customer_id', $this->customer->getId());
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);

        $yform->setValueField('pz_image_screen', ['image_inline', pz_i18n::msg('photo'), pz_customer::getDefaultImage()]);

        $yform->setValueField('text', ['name', pz_i18n::msg('customer_name'), '', '0']);
        $yform->setValueField('textarea', ['description', pz_i18n::msg('customer_description'), '', '0']);

        $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

        $yform->setValueField('checkbox', ['archived', pz_i18n::msg('customer_archived'), '', '0']);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_customer_name_empty')]);

        $yform->setActionField('db', ['pz_customer', 'id='.$this->customer->getId()]);

        $return = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $this->customer->update();
            $return = $header.'<p class="yform-info">'.pz_i18n::msg('customer_updated').'</p>'.$return;
            $return .= pz_screen::getJSLoadFormPage('customers_list', 'customer_search_form', pz::url('screen', 'projects', 'customers', ['mode' => 'list']));
        } else {
            $return = $header.$return;
        }

        if ($p['show_delete']) {
            $delete_link = pz::url('screen', 'projects', 'customers', ['customer_id' => $this->customer->getId(), 'mode' => 'delete_customer']);
            $return .= '<div class="yform">
				<p><a class="bt17" onclick="check = confirm(\''.
                str_replace(["'", "\n", "\r"], ['', '', ''], pz_i18n::msg('customer_confirm_delete', htmlspecialchars($this->customer->getName()))).
                '\'); if (check == true) pz_loadPage(\'customer_form\',\''.
                $delete_link.'\')" href="javascript:void(0);">- '.pz_i18n::msg('delete_customer').'</a></p>
				</div>';
        }

        $return = '<div id="customer_form"><div id="customer_edit" class="design1col yform-edit">'.$return.'</div></div>';

        return $return;
    }

    public static function getAddForm($p = [])
    {
        $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg('customer_add').'</h1>
	          </div>
	        </header>';

        $yform = new rex_yform();
        // $yform->setDebug(TRUE);

        $yform->setObjectparams('main_table', 'pz_customer');
        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('customer_add','customer_add_form','".pz::url('screen', 'projects', 'customers', ['mode' => 'add_customer'])."')");
        $yform->setObjectparams('form_id', 'customer_add_form');
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        foreach ($p['linkvars'] as $k => $v) {
            $yform->setHiddenField($k, $v);
        }

        $yform->setValueField('pz_image_screen', ['image_inline', pz_i18n::msg('photo'), pz_customer::getDefaultImage()]);

        $yform->setValueField('text', ['name', pz_i18n::msg('customer_name'), '', '0']);
        $yform->setValueField('textarea', ['description', pz_i18n::msg('customer_description'), '', '0']);

        $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);

        $yform->setValueField('checkbox', ['archived', pz_i18n::msg('customer_archived'), '', '0']);
        $yform->setValidateField('empty', ['name', pz_i18n::msg('error_customer_name_empty')]);

        $yform->setActionField('db', []);
        $return .= $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {
            $customer_id = $yform->getObjectparams('main_id');
            if ($customer = pz_customer::get($customer_id)) {
                $customer->create();
            }
            $return .= '<p class="yform-info">'.pz_i18n::msg('customer_added').'</p>';
            $return .= pz_screen::getJSLoadFormPage('customers_list', 'customer_search_form', pz::url('screen', 'projects', 'customers', ['mode' => 'list']));
        }
        $return = '<div id="customer_form"><div id="customer_add" class="design1col yform-add">'.$return.'</div></div>';

        return $return;
    }
}
