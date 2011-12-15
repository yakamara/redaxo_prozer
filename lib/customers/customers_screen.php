<?php

class pz_customers_screen {

	public $customers;

	function __construct($customers)
	{
		$this->customers = $customers;
	}

	function getCustomersSearchForm ($p = array())
	{
	    $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("search_for_customer").'</h1>
	          </div>
	        </header>';
        
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('customers_list','customer_search_form','".pz::url('screen','projects','customers',array("mode"=>'list'))."')");
		$xform->setObjectparams("form_id", "customer_search_form");

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("project_name")));
		$xform->setValueField("checkbox",array("archived","archived","1","1","0","","","",""));
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$return .= $xform->getForm();
		
		$return = '<div id="customer_search" class="design1col xform-search">'.$return.'</div>';
		
		return $return;
	}

	function getCustomerListView($p = array()) 
	{
		$content = "";
		$p["layer"] = 'customers_list';
		
		$paginate_screen = new pz_paginate_screen($this->customers);
		$paginate = $paginate_screen->getPlainView($p);
		
		$first = " first";
		foreach($paginate_screen->getCurrentElements() as $customer) {
			if($cs = new pz_customer_screen($customer)) {
				$content .= '<li class="lev1 entry'.$first.'">'.$cs->getListView($p).'</li>';
				$first = "";
			}
		}
		
		$content = $paginate.'<ul class="entries view-list">'.$content.'</ul>';

		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', "", false);
	
		return '<div id="customers_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';

	}
}