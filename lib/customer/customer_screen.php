<?php

class pz_customer_screen{

	public $customer;
	
	function __construct($customer) 
	{
		$this->customer = $customer;
	}

	// --------------------------------------------------------------- Listviews

	function getListView($p = array())
	{
    	$p["linkvars"]["customer_id"] = $this->customer->getVar("id");
    
    	$edit_link = "javascript:pz_loadPage('customer_form','".pz::url("screen","projects","customers",array_merge($p["linkvars"],array("mode"=>"edit_customer","customer_id"=>$this->customer->getId())))."')";
    
		$return = '
          <article>
            <header>
              <a class="detail clearfix" href="'.$edit_link.'">
                <figure><img src="'.$this->customer->getInlineImage().'" width="40" height="40" alt="" /></figure>
                <hgroup>
                  <h3 class="hl7"><span class="title">'.$this->customer->getVar("name").'</span></h3>
                </hgroup>
                <span class="label">Label</span>
              </a>
            </header>
            <footer>
              <a class="bt2" href="'.$edit_link.'">'.rex_i18n::msg("customer_edit").'</a>
            </footer>
          </article>
        ';
	
		return $return;
	}



	// --------------------------------------------------------------- Pageviews




	// --------------------------------------------------------------- Formviews

	public function getEditForm($p = array()) 
	{
    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.rex_i18n::msg("customer_edit").': '.$this->customer->getName().'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_customer');
		$xform->setObjectparams("main_id",$this->customer->getId());
		$xform->setObjectparams("main_where",'id='.$this->customer->getId());
		$xform->setObjectparams('getdata',true);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('customer_edit','customer_edit_form','".pz::url('screen','projects','customers',array("mode"=>'edit_customer'))."')");
		$xform->setObjectparams("form_id", "customer_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);
		$xform->setHiddenField("customer_id",$this->customer->getId());
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$xform->setValueField("pz_image_screen",array("image_inline",rex_i18n::msg("photo"),pz_customer::getDefaultImage()));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("customer_name"),"","0"));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("customer_description"),"","0"));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("checkbox",array("archived",rex_i18n::msg("customer_archived"),"1","1","0"));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_customer_name_empty")));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0"));

		$xform->setActionField("db",array('pz_customer','id='.$this->customer->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
		
			$this->customer->update();
			$return = $header.'<p class="xform-info">'.rex_i18n::msg("customer_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('customers_list','customer_search_form',pz::url('screen','projects','customers',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="customer_form"><div id="customer_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}

	static function getAddForm($p = array()) 
	{
		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("customer_add").'</h1>
	          </div>
	        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_customer');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('customer_add','customer_add_form','".pz::url('screen','projects','customers',array("mode"=>'add_customer'))."')");
		$xform->setObjectparams("form_id", "customer_add_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		foreach($p["linkvars"] as $k => $v) { $xform->setHiddenField($k,$v); }
		
		$xform->setValueField("pz_image_screen",array("image_inline",rex_i18n::msg("photo"),pz_customer::getDefaultImage()));
		
		$xform->setValueField("text",array("name",rex_i18n::msg("customer_name"),"","0"));
		$xform->setValueField("textarea",array("description",rex_i18n::msg("customer_description"),"","0"));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("checkbox",array("archived",rex_i18n::msg("customer_archived"),"1","1","0"));
		$xform->setValidateField("empty",array("name",rex_i18n::msg("error_customer_name_empty")));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		$xform->setActionField("db",array());
		$return .= $xform->getForm();

		if($xform->getObjectparams("actions_executed")) {
			$customer_id = $xform->getObjectparams("main_id");
			if($customer = pz_customer::get($customer_id)) {
				$customer->create();
			}
			$return .= '<p class="xform-info">'.rex_i18n::msg("customer_added").'</p>';
			$return .= pz_screen::getJSLoadFormPage('customers_list','customer_search_form',pz::url('screen','projects','customers',array("mode"=>'list')));
			
		}
		$return = '<div id="customer_form"><div id="customer_add" class="design1col xform-add">'.$return.'</div></div>';

		return $return;	
		
	}


}


?>