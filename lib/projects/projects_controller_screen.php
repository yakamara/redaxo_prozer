<?php

class pz_projects_controller_screen extends pz_projects_controller {

	var $name = "projects";
	var $function = "";
	var $functions = array("my","all", "archive", "customers", "api", "labels");
	var $function_default = "my";
	var $navigation = array("my","all", "archive", "customers", "labels");

	function controller($function) {

		if(!in_array($function,$this->functions)) $function = $this->function_default;
		$this->function = $function;

		$p = array();
		$p["linkvars"] = array();
		$p["controll"] = "projects";
		$p["mediaview"] = "screen";
		$p["function"] = $this->function;

		switch($this->function)
		{
			case("my"):	return $this->getMyProjectsPage($p);
			case("all"): return $this->getAllProjectsPage($p);
			case("archive"): return $this->getArchiveProjectsPage($p);
			case("customers"): return $this->getCustomersPage($p);
			case("labels"): return $this->getLabelsPage($p);
			default: break;
		}
		
		return "";
	}


	private function getProjectFilter() {

		$filter = array();
		if(rex_request("search_name","string") != "")
		$filter[] = array(
			"field" => "name", 
			"type" => "like", 
			"value" => rex_request("search_name","string")
		);
		if(rex_request("search_label","string") != "")
			$filter[] = array(
				"field" => "label_id", 
				"type" => "=",
				"value" => rex_request("search_label","string")
			);
		if(rex_request("search_customer","string") != "")
		$filter[] = array(
			"field" => "customer_id", 
			"type" => "=",
			"value" => rex_request("search_customer","string")
		);
		return $filter;

	}

	



	// ------------------------------------------------------------------- Views

	// -------------------------------------------------------- Project Views

	/*
	private function getProjectListView($projects,$p = array())
	{
		$content = "";
		
		echo "ERROROR";
		
		$paginate_screen = new pz_paginate_screen($projects);
		$paginate = $paginate_screen->getPlainView($p);
		
		$first = " first";
		foreach($paginate_screen->getCurrentElements() as $project) {
			if($e = new pz_project_screen($project)) {
				$content .= '<li class="lev1 entry'.$first.'">'.$e->getListView($p).'</li>';
				$first = "";
			}
		}
		$content = $paginate.'<ul class="entries view-list">'.$content.'</ul>';
		$content = $this->getSearchPaginatePlainView().$content;
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		
		return '<div id="projects_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
	}
	*/


	private function getProjectTableView($projects,$p = array())
	{
		$content = "";
		
		$p["layer"] = 'projects_list';

		$paginate_screen = new pz_paginate_screen($projects);
		$paginate = $paginate_screen->getPlainView($p);
		
		foreach($paginate_screen->getCurrentElements() as $project) {
			$ps = new pz_project_screen($project);
			$content .= $ps->getTableView($p);
		}
		$content = $paginate.'
          <table class="projects tbl1">
          <thead><tr>
              <th></th>
              <th>'.rex_i18n::msg("customer").'</th>
              <th>'.rex_i18n::msg("project_name").'</th>
              <th>'.rex_i18n::msg("project_createdate").'</th>
              <th>'.rex_i18n::msg("project_admins").'</th>
              <th class="label"></th>
          </tr></thead>
          <tbody>
            '.$content.'
          </tbody>
          </table>';
		// $content = $this->getSearchPaginatePlainView().$content;
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projects_list" class="design2col">'.$f->parse('pz_screen_list').'</div>';
	}

	private function getProjectMatrixView($projects,$p = array())
	{
		$content = "";
		
		$p["layer"] = 'projects_list';

		$paginate_screen = new pz_paginate_screen($projects);
		$paginate_screen->setListAmount(15);
		$paginate = $paginate_screen->getPlainView($p);
		
		$first = ' first';
		foreach($paginate_screen->getCurrentElements() as $project) {
			$ps = new pz_project_screen($project);
			$content .= '<li class="lev1 entry'.$first.'">'.$ps->getMatrixView($p).'</li>';
			$first = '';
		}
		$content = $paginate.'<ul class="entries view-matrix clearfix">'.$content.'</ul>';
		// $content = $this->getSearchPaginatePlainView().$content;
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		
		return '<div id="projects_list" class="design3col">'.$f->parse('pz_screen_list').'</div>';
	}


	private function getProjectBlocklistView($projects,$p = array())
	{
		$design = "design1col";
		$p["view"] = "block3col";

		$content = "";
		$first = " first";
		foreach($projects as $project) {
			$ps = new pz_project_screen($project);
			$content .= '<li class="lev1 entry'.$first.'">'.$ps->getBlockView($p).'</li>';
			$first = "";
		}

		$content = '<ul class="entries view-block">'.$content.'</ul>';
		// $content = $this->getSearchPaginatePlainView().$content;

		$paginate = "";

		$f = new rex_fragment();
		$f->setVar('design', $design, false);
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', $paginate, false);

		return $f->parse('pz_screen_list');

	}

	function getNavigation($p = array())
	{
		return pz_screen::getNavigation($p,$this->navigation, $this->function, $this->name, pz_project_controller_screen::getProjectsFlyout($p) );
	}


	// --------------------------------------------------- Formular Views

	function getProjectsSearchForm ()
	{
	    $return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("search_for_projects").'</h1>
	          </div>
	        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projects_list','project_search_form','".pz::url('screen','projects',$this->function,array("mode"=>'list'))."')");
		$xform->setObjectparams("form_id", "project_search_form");
		
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform', 'runtime'));
		$xform->setValueField("text",array("search_name",rex_i18n::msg("project_name")));
		$xform->setValueField('pz_select_screen',array('search_label', rex_i18n::msg('project_label'), pz_labels::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField('pz_select_screen',array('search_customer', rex_i18n::msg('customer'), pz_customers::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		// $xform->setValueField('pz_date_screen',array('search_datetime', rex_i18n::msg('createdate')));
		$xform->setValueField("submit",array('submit',rex_i18n::msg('search'), '', 'search'));
		$return .= $xform->getForm();
		
		$return = '<div id="project_search" class="design1col xform-search">'.$return.'</div>';
		return $return;

	}

	// --------------------------------------------------- Main Pages Views

	function getMyProjectsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("my_projects");
		
		$filter = $this->getProjectFilter();
		$projects = pz::getUser()->getMyProjects($filter);
		
		$section_1 = $this->getProjectMatrixView(
							$projects,
							array_merge( $p, array("linkvars" => array( "mode" =>"list", "search_name" => rex_request("search_name"), "archived" => rex_request("archived") ) ) )
						);

		$mode = rex_request("mode","string");
		switch($mode) {
			case("list"):
				return $section_1;
				break;
			default:
		}
		
		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p) , false);
		$f->setVar('section_1', $section_1 , false);
		return $f->parse('pz_screen_main');
	}

	function getArchiveProjectsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("archived_projects");

		$filter = $this->getProjectFilter();

		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("list"):
				$projects = pz::getUser()->getArchivedProjects($filter);
				return $this->getProjectTableView($projects,$p);
				break;
		}

		$section_1 = $this->getProjectsSearchForm();
		$projects = pz::getUser()->getArchivedProjects($filter);
		$section_2 = $this->getProjectTableView($projects,$p);

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		return $f->parse('pz_screen_main');

	}

	function getAllProjectsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("all_projects");
		
		$s1_content = "";
		$s2_content = "";

		$filter = $this->getProjectFilter();
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_form"):
				return pz_project_screen::getAddForm($p);
				break;
			case("list"):
				$projects = pz::getUser()->getProjects($filter);
				return $this->getProjectTableView(
					$projects,
					array_merge(
						$p,
						array("linkvars" => array( "mode" =>"list", "search_name" => rex_request("search_name"), "archived" => rex_request("archived") ) )
					)
				);
				break;
			case(""):
				$s1_content .= $this->getProjectsSearchForm($p);
				$projects = pz::getUser()->getProjects($filter);
				$s2_content .= $this->getProjectTableView(
					$projects,
					array_merge(
						$p,
						array("linkvars" => array( "mode" =>"list", "search_name" => rex_request("search_name"), "archived" => rex_request("archived") ) )
					)
				);
				$form = pz_project_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$s1_content .= $form;

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');

	}

	// ----------------------------------------------------------- Customersview

	function getCustomersPage($p = array())
	{
		$p["title"] = rex_i18n::msg("customers");

		$s1_content = "";
		$s2_content = "";

		$filter = array();
		if(rex_request("search_name","string") != "")
			$filter[] = array(
				"field" => "name",
				"value" => rex_request("search_name","string"),
				"type" => "like"
				);
		if(rex_request("archived","int") == 1)
			$filter[] = array(
				"field" => "archived",
				"value" => rex_request("archived","int"),
				"type" => "="
				);
		
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_customer"):
				return pz_customer_screen::getAddForm($p);
				break;
			case("list"):
				$customers = pz::getUser()->getCustomers($filter);
				$cs = new pz_customers_screen($customers);
				return $cs->getCustomerListView(
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"search_name" => rex_request("search_name"),
							"archived" => rex_request("archived")
							) 
						)
					)
				);
				break;
			case("edit_customer"):
				$customer_id = rex_request("customer_id","int",0);
				if($customer_id > 0 && $customer = pz_customer::get($customer_id)) {
					$cs = new pz_customer_screen($customer);
					return $cs->getEditForm($p);
				}else {
					return '<p class="xform-warning">'.rex_i18n::msg("customer_not_exists").'</p>';
				}
				break;
			case(""):
				$s1_content .= pz_customers_screen::getCustomersSearchForm();
				$customers = pz::getUser()->getCustomers($filter);
				$cs = new pz_customers_screen($customers);
				$s2_content = $cs->getCustomerListView(
					array_merge(
						$p,
						array("linkvars" => array(
							"mode" =>"list",
							"search_name" => rex_request("search_name"),
							"archived" => rex_request("archived")
							) 
						)
					)
				);
				$s1_content .= pz_customer_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');

	}

	private function getLabelsPage($p = array())
	{
	
		$p["title"] = rex_i18n::msg("labels");
		$p["mediaview"] = "screen";
		$p["controll"] = "projects";
		$p["function"] = "labels";

		$s1_content = "";
		$s2_content = "";

		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_label"):
				return pz_label_screen::getAddForm($p);
				break;
			case("list"):
				$labels = pz_labels::get();
				$cs = new pz_labels_screen($labels);
				return $cs->getListView($p);
				break;
			case("edit_label"):
				$label_id = rex_request("label_id","int",0);
				if($label_id > 0 && $label = pz_label::get($label_id)) {
						$cs = new pz_label_screen($label);
						return $cs->getEditForm($p);
				}else {
					return '<div id="label_form"><p class="xform-warning">'.rex_i18n::msg("label_not_found").'</p></div>';
				}
				break;
			case("label_info"):
				$label_id = rex_request("label_id","int",0);
				if($label_id > 0 && $label = pz_label::get($label_id)) {
					$cs = new pz_label_screen($label);
					$s2_content = $cs->getInfoPage($p);
				}else {
					return '<div id="label_form"><p class="xform-warning">'.rex_i18n::msg("label_not_found").'</p></div>';
				}
				break;
			
			case(""):
				$labels = pz_labels::get();
				$cs = new pz_labels_screen($labels);
				$s2_content = $cs->getListView($p);
				$s1_content .= pz_label_screen::getAddForm($p);
				break;
			default:
				break;
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main');

	}


}