<?php

class pz_projects_controller_screen extends pz_projects_controller {

	var $name = "projects";
	var $function = "";
	var $functions = array("all", "archive", "api");
	var $function_default = "all";
	var $navigation = array("all", "archive");

	function controller($function) 
	{

		if(pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin')) 
		{
			$this->functions[] = "customers";
			$this->navigation[] = "customers";
		}

		if(pz::getUser()->isAdmin()) 
		{ 
			$this->functions[] = "labels";
			$this->navigation[] = "labels";
		}

		if(!in_array($function,$this->functions))
		{
		  $function = $this->function_default;
		}
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


	private function getProjectFilter() 
	{

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

	private function getProjectTableView($projects,$p = array())
	{
		$content = "";
		
		$p["layer"] = 'projects_list';

		$paginate_screen = new pz_paginate_screen($projects);
		$paginate = $paginate_screen->getPlainView($p);
		
		$list = '';
		foreach($paginate_screen->getCurrentElements() as $project) {
			$ps = new pz_project_screen($project);
			$list .= $ps->getTableView($p);
		}
		
		// $content = $this->getSearchPaginatePlainView().$content;
		
		$list .= ''; /*<script>
				$(document).ready(function() {
				  pz_screen_select_event("#emails_list li.selected");
				});
				</script>';*/
				
		$paginate_loader = $paginate_screen->setPaginateLoader($p, '#projects_list');

		if($paginate_screen->isScrollPage())
		{
		  $content = '
		        <table class="projects tbl1">
		        <tbody class="projects_table_list">
		          '.$list.'
		        </tbody>
		        </table>'.$paginate_loader;
		
		  return $content;
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
		      <tbody class="projects_table_list">
		        '.$list.'
		      </tbody>
		      </table>'
		      .$paginate_loader;
		
		$f = new rex_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="projects_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
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
		
		return '<div id="projects_list" class="design3col">'.$f->parse('pz_screen_list.tpl').'</div>';
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
		$paginate = "";

		$f = new rex_fragment();
		$f->setVar('design', $design, false);
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', $paginate, false);

		return $f->parse('pz_screen_list.tpl');

	}

	function getNavigation($p = array())
	{
		return pz_screen::getNavigation(
			$p,
			$this->navigation, 
			$this->function, 
			$this->name
			);
	}


	// --------------------------------------------------- Formular Views

	

	// --------------------------------------------------- Main Pages Views

	function getAllProjectsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("all_projects");
		
		$s1_content = "";
		$s2_content = "";

		$p["linkvars"]["search_name"] = rex_request("search_name");
		$p["linkvars"]["search_customer"] = rex_request("search_customer");
		$p["linkvars"]["search_label"] = rex_request("search_label");
		$p["linkvars"]["search_myprojects"] = rex_request("search_myprojects");
		$p["linkvars"]["archived"] = rex_request("archived");

		$filter = $this->getProjectFilter();
		if($p["linkvars"]["search_myprojects"] == 1)
		{
		  $projects = pz::getUser()->getMyProjects($filter);
		}else 
		{
  		$projects = pz::getUser()->getProjects($filter);
		}
		
		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("add_form"):
				if(pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin'))
					return pz_project_screen::getAddForm($p);
				return '';

			case("list"):
				$p["linkvars"]["mode"] = "list";
				return $this->getProjectTableView($projects, $p);

			default:
				$p["linkvars"]["mode"] = "list";
				$ignore_searchfields = array("myprojects");
				if(pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin'))
				  $ignore_searchfields = array();
				
				$s1_content .= pz_project_screen::getProjectsSearchForm($p, $ignore_searchfields);
				$s2_content .= $this->getProjectTableView( $projects, $p);
				if(pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin'))
				{
					$s1_content .= pz_project_screen::getAddForm($p);
				}
		}

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $s1_content, false);
		$f->setVar('section_2', $s2_content, false);
		return $f->parse('pz_screen_main.tpl');

	}
	
	
	function getArchiveProjectsPage($p = array())
	{
		$p["title"] = rex_i18n::msg("archived_projects");

		$p["linkvars"]["mode"] = "list";
		$p["linkvars"]["search_name"] = rex_request("search_name");
		$p["linkvars"]["search_customer"] = rex_request("search_customer");
		$p["linkvars"]["search_label"] = rex_request("search_label");
		$p["linkvars"]["archived"] = rex_request("archived");

		$filter = $this->getProjectFilter();
		$projects = pz::getUser()->getArchivedProjects($filter);

		$mode = rex_request("mode","string");
		switch($mode)
		{
			case("list"):
				return $this->getProjectTableView($projects,$p);
		}

		$section_1 = pz_project_screen::getProjectsSearchForm($p, array("myprojects"));
		$section_2 = $this->getProjectTableView($projects,$p);

		$f = new rex_fragment();
		$f->setVar('header', pz_screen::getHeader($p), false);
		$f->setVar('function', $this->getNavigation($p), false);
		$f->setVar('section_1', $section_1 , false);
		$f->setVar('section_2', $section_2 , false);
		return $f->parse('pz_screen_main.tpl');

	}


	// ----------------------------------------------------------- Customersview

	function getCustomersPage($p = array())
	{
		$p["title"] = rex_i18n::msg("customers");

		$s1_content = "";
		$s2_content = "";

		$filter = array();
		if(rex_request("search_name","string") != "")
			$filter[] = array( "field" => "name", "value" => rex_request("search_name","string"), "type" => "like" );
		
		$archived = 0;
		if(rex_request("archived","int") == 1)
			$archived = 1;
			
		$filter[] = array( "field" => "archived", "value" => $archived, "type" => "=" );
		
		$mode = rex_request("mode","string");
		
		switch($mode)
		{
			case("delete_customer"):
				if(!(pz::getUser()->isAdmin()))
					return '';
				$customer_id = rex_request("customer_id","int");
				if(($customer = pz_customer::get($customer_id))) {
					if($customer->hasProjects())
						return '';
					$r = new pz_customer_screen($customer);
					$customer->delete();
					$p["customer_name"] = $customer->getName();
					return $r->getDeleteForm($p);
				}
				return '';
		
			case("add_customer"):
				if(!(pz::getUser()->isAdmin()))
					return '';
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
				if(!(pz::getUser()->isAdmin()))
					return '';
				$customer_id = rex_request("customer_id","int",0);
				if($customer_id > 0 && $customer = pz_customer::get($customer_id)) {
					$cs = new pz_customer_screen($customer);
					$p["show_delete"] = false;
					if(!$customer->hasProjects())
						$p["show_delete"] = true;
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
				if(pz::getUser()->isAdmin())
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
		return $f->parse('pz_screen_main.tpl');

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
			case("delete_label"):
				$label_id = rex_request("label_id","int");
				if(($label = pz_label::get($label_id))) {
					if($label->hasProjects()) {
						return '';
					}
					$r = new pz_label_screen($label);
					$label->delete();
					$p["label_name"] = $label->getName();
					return $r->getDeleteForm($p);
				}
				return '';
				
			case("add_label"):
				return pz_label_screen::getAddForm($p);
			
			case("list"):
				$labels = pz_labels::get();
				$cs = new pz_labels_screen($labels);
				return $cs->getListView($p);
			
			case("edit_label"):
				$label_id = rex_request("label_id","int",0);
				if($label_id > 0 && $label = pz_label::get($label_id)) {
						$cs = new pz_label_screen($label);
						$p["show_delete"] = false;
						if(!$label->hasProjects()) {
							$p["show_delete"] = true;
						}
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
		return $f->parse('pz_screen_main.tpl');

	}


}