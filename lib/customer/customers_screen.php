<?php

class pz_customers_screen {

	public $customers;

	function __construct($customers)
	{
		$this->customers = $customers;
	}

	static function getCustomersSearchForm ($p = array())
	{
	    $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.pz_i18n::msg("search_for_customer").'</h1>
	          </div>
	        </header>';
        
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('customers_list','customer_search_form','".pz::url('screen','projects','customers',array("mode"=>'list'))."')");
		$xform->setObjectparams("form_id", "customer_search_form");

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
		$xform->setValueField("text",array("search_name",pz_i18n::msg("project_name")));
		$xform->setValueField("checkbox",array("archived",pz_i18n::msg("archived"),"","0"));
		$xform->setValueField("submit",array('submit',pz_i18n::msg('search'), '', 'search'));
		$return .= $xform->getForm();
		
		$return = '<div id="customer_search" class="design1col xform-search">'.$return.'</div>';
		
		return $return;
	}

	function getCustomerListView($p = array()) 
	{
		$p["layer"] = 'customers_list';
		
		$paginate_screen = new pz_paginate_screen($this->customers);
		$paginate = $paginate_screen->getPlainView($p);
		
		$list = '';
		foreach($paginate_screen->getCurrentElements() as $customer) {
			if($cs = new pz_customer_screen($customer)) {
				$list .= '<li class="lev1 entry">'.$cs->getListView($p).'</li>';
			}
		}

    $paginate_loader = $paginate_screen->setPaginateLoader($p, '#customers_list');
		
		$list = '<ul class="entries view-list">'.$list.'</ul>';

    if($paginate_screen->isScrollPage())
    {
      $content = $list.$paginate_loader;
      return $content;
    }

    $content = $paginate.$list.$paginate_loader;

		$f = new pz_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', "", false);
	
		return '<div id="customers_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';

	}
}