<?php

class pz_projects_controller_screen extends pz_projects_controller
{
    public $name = 'projects';
    public $function = '';
    public $functions = ['all', 'archive', 'api'];
    public $function_default = 'all';
    public $navigation = ['all', 'archive'];

    public function controller($function)
    {
        if (pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin')) {
            $this->functions[] = 'customers';
            $this->navigation[] = 'customers';
        }

        if (pz::getUser()->isAdmin()) {
            $this->functions[] = 'labels';
            $this->navigation[] = 'labels';
        }

        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];
        $p['linkvars'] = [];
        $p['controll'] = 'projects';
        $p['mediaview'] = 'screen';
        $p['function'] = $this->function;

        switch ($this->function) {
            case('my'):    return $this->getMyProjectsPage($p);
            case('all'): return $this->getAllProjectsPage($p);
            case('archive'): return $this->getArchiveProjectsPage($p);
            case('customers'): return $this->getCustomersPage($p);
            case('labels'): return $this->getLabelsPage($p);
            default: break;
        }

        return '';
    }

    private function getProjectFilter()
    {
        $filter = [];
        if (rex_request('search_name', 'string') != '') {
            $filter[] = [
                'field' => 'name',
                'type' => 'like',
                'value' => rex_request('search_name', 'string'),
            ];
        }
        if (rex_request('search_label', 'string') != '') {
            $filter[] = [
                'field' => 'label_id',
                'type' => '=',
                'value' => rex_request('search_label', 'string'),
            ];
        }
        if (rex_request('search_customer', 'string') != '') {
            $filter[] = [
                'field' => 'customer_id',
                'type' => '=',
                'value' => rex_request('search_customer', 'string'),
            ];
        }
        if (rex_request('search_projectuser', 'string') != '') {
            $filter[] = [
                'field' => 'user_id',
                'type' => '=',
                'value' => rex_request('search_projectuser', 'string'),
            ];
        }
        return $filter;
    }

    // ------------------------------------------------------------------- Views

    // -------------------------------------------------------- Project Views

    private function getProjectTableView($projects, $p = [], $orders = [])
    {
        $content = '';

        $p['layer'] = 'projects_list';

        $paginate_screen = new pz_paginate_screen($projects);
        $paginate = $paginate_screen->getPlainView($p);

        $list = '';
        foreach ($paginate_screen->getCurrentElements() as $project) {
            $ps = new pz_project_screen($project);
            $list .= $ps->getTableView($p);
        }

        // $content = $this->getSearchPaginatePlainView().$content;

        $paginate_loader = $paginate_screen->setPaginateLoader($p, '#projects_list');

        if ($paginate_screen->isScrollPage()) {
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
		          <th>'.pz_i18n::msg('customer').'</th>
		          <th>'.pz_i18n::msg('project_name').'</th>
		          <th>'.pz_i18n::msg('project_createdate').'</th>
		          <th>'.pz_i18n::msg('project_admins').'</th>
		          <th class="label"></th>
		      </tr></thead>
		      <tbody class="projects_table_list">
		        '.$list.'
		      </tbody>
		      </table>'
              .$paginate_loader;

        $link_refresh = pz::url('screen', $p['controll'], $p['function'],
            array_merge(
                $p['linkvars'],
                [
                    'mode' => 'list',
                ]
            )
        );

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);
        $f->setVar('orders', $orders);
        return '<div id="projects_list" class="design2col" data-url="'.$link_refresh.'">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    private function getProjectMatrixView($projects, $p = [])
    {
        $content = '';

        $p['layer'] = 'projects_list';

        $paginate_screen = new pz_paginate_screen($projects);
        $paginate_screen->setListAmount(15);
        $paginate = $paginate_screen->getPlainView($p);

        $first = ' first';
        foreach ($paginate_screen->getCurrentElements() as $project) {
            $ps = new pz_project_screen($project);
            $content .= '<li class="lev1 entry'.$first.'">'.$ps->getMatrixView($p).'</li>';
            $first = '';
        }
        $content = $paginate.'<ul class="entries view-matrix clearfix">'.$content.'</ul>';
        // $content = $this->getSearchPaginatePlainView().$content;
        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        return '<div id="projects_list" class="design3col">'.$f->parse('pz_screen_list.tpl').'</div>';
    }

    private function getProjectBlocklistView($projects, $p = [])
    {
        $design = 'design1col';
        $p['view'] = 'block3col';

        $content = '';
        $first = ' first';
        foreach ($projects as $project) {
            $ps = new pz_project_screen($project);
            $content .= '<li class="lev1 entry'.$first.'">'.$ps->getBlockView($p).'</li>';
            $first = '';
        }

        $content = '<ul class="entries view-block">'.$content.'</ul>';
        $paginate = '';

        $f = new pz_fragment();
        $f->setVar('design', $design, false);
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);
        $f->setVar('paginate', $paginate, false);

        return $f->parse('pz_screen_list.tpl');
    }

    public function getNavigation($p = [])
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

    public function getAllProjectsPage($p = [])
    {
        $p['title'] = pz_i18n::msg('all_projects');

        $s1_content = '';
        $s2_content = '';

        $p['linkvars']['search_name'] = rex_request('search_name');
        $p['linkvars']['search_customer'] = rex_request('search_customer');
        $p['linkvars']['search_label'] = rex_request('search_label');
        $p['linkvars']['search_myprojects'] = rex_request('search_myprojects');
        $p['linkvars']['search_projectuser'] = rex_request('search_projectuser');
        $p['linkvars']['archived'] = rex_request('archived');

        $p['layer_list'] = 'projects_list';
        $orders = [];
        $result = self::getProjectsListOrders($orders, $p);

        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $filter = $this->getProjectFilter();
        if ($p['linkvars']['search_myprojects'] == 1) {
            $projects = pz::getUser()->getMyProjects($filter, [$orders[$current_order]]);
        } else {
            $projects = pz::getUser()->getProjects($filter, [$orders[$current_order]]);
        }


        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('add_form'):
                if (pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin')) {
                    return pz_project_screen::getAddForm($p);
                }
                return '';

            case('list'):
                $p['linkvars']['mode'] = 'list';
                return $this->getProjectTableView($projects, $p, $orders);

            default:
                $p['linkvars']['mode'] = 'list';
                $ignore_searchfields = ['myprojects'];
                if (pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin')) {
                    $ignore_searchfields = [];
                }

                $s1_content .= pz_project_screen::getProjectsSearchForm($p, $ignore_searchfields);
                $s2_content .= $this->getProjectTableView($projects, $p, $orders);
                if (pz::getUser()->isAdmin() || pz::getUser()->hasPerm('projectsadmin')) {
                    $s1_content .= pz_project_screen::getAddForm($p);
                }
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public function getArchiveProjectsPage($p = [])
    {
        $p['title'] = pz_i18n::msg('archived_projects');

        $p['linkvars']['mode'] = 'list';
        $p['linkvars']['search_name'] = rex_request('search_name');
        $p['linkvars']['search_customer'] = rex_request('search_customer');
        $p['linkvars']['search_label'] = rex_request('search_label');
        $p['linkvars']['archived'] = rex_request('archived');

        $filter = $this->getProjectFilter();
        $projects = pz::getUser()->getArchivedProjects($filter);

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('list'):
                return $this->getProjectTableView($projects, $p);
        }

        $section_1 = pz_project_screen::getProjectsSearchForm($p, ['myprojects']);
        $section_2 = $this->getProjectTableView($projects, $p);

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $section_1, false);
        $f->setVar('section_2', $section_2, false);
        return $f->parse('pz_screen_main.tpl');
    }

    // ----------------------------------------------------------- Customersview

    public function getCustomersPage($p = [])
    {
        $p['title'] = pz_i18n::msg('customers');

        $s1_content = '';
        $s2_content = '';

        $filter = [];
        if (rex_request('search_name', 'string') != '') {
            $filter[] = ['field' => 'name', 'value' => rex_request('search_name', 'string'), 'type' => 'like'];
        }

        $archived = 0;
        if (rex_request('archived', 'int') == 1) {
            $archived = 1;
        }

        $filter[] = ['field' => 'archived', 'value' => $archived, 'type' => '='];

        $mode = rex_request('mode', 'string');

        switch ($mode) {
            case('delete_customer'):
                if (!(pz::getUser()->isAdmin())) {
                    return '';
                }
                $customer_id = rex_request('customer_id', 'int');
                if (($customer = pz_customer::get($customer_id))) {
                    if ($customer->hasProjects()) {
                        return '';
                    }
                    $r = new pz_customer_screen($customer);
                    $customer->delete();
                    $p['customer_name'] = $customer->getName();
                    return $r->getDeleteForm($p);
                }
                return '';

            case('add_customer'):
                if (!(pz::getUser()->isAdmin())) {
                    return '';
                }
                return pz_customer_screen::getAddForm($p);
                break;
            case('list'):
                $customers = pz::getUser()->getCustomers($filter);
                $cs = new pz_customers_screen($customers);
                return $cs->getCustomerListView(
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'search_name' => rex_request('search_name'),
                            'archived' => rex_request('archived'),
                            ],
                        ]
                    )
                );
                break;
            case('edit_customer'):
                if (!(pz::getUser()->isAdmin())) {
                    return '';
                }
                $customer_id = rex_request('customer_id', 'int', 0);
                if ($customer_id > 0 && $customer = pz_customer::get($customer_id)) {
                    $cs = new pz_customer_screen($customer);
                    $p['show_delete'] = false;
                    if (!$customer->hasProjects()) {
                        $p['show_delete'] = true;
                    }
                    return $cs->getEditForm($p);
                } else {
                    return '<p class="xform-warning">'.pz_i18n::msg('customer_not_exists').'</p>';
                }
                break;
            case(''):
                $s1_content .= pz_customers_screen::getCustomersSearchForm();
                $customers = pz::getUser()->getCustomers($filter);
                $cs = new pz_customers_screen($customers);
                $s2_content = $cs->getCustomerListView(
                    array_merge(
                        $p,
                        ['linkvars' => [
                            'mode' => 'list',
                            'search_name' => rex_request('search_name'),
                            'archived' => rex_request('archived'),
                            ],
                        ]
                    )
                );
                if (pz::getUser()->isAdmin()) {
                    $s1_content .= pz_customer_screen::getAddForm($p);
                }
                break;
            default:
                break;
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    private function getLabelsPage($p = [])
    {
        $p['title'] = pz_i18n::msg('labels');
        $p['mediaview'] = 'screen';
        $p['controll'] = 'projects';
        $p['function'] = 'labels';

        $s1_content = '';
        $s2_content = '';

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case('delete_label'):
                $label_id = rex_request('label_id', 'int');
                if (($label = pz_label::get($label_id))) {
                    if ($label->hasProjects()) {
                        return '';
                    }
                    $r = new pz_label_screen($label);
                    $label->delete();
                    $p['label_name'] = $label->getName();
                    return $r->getDeleteForm($p);
                }
                return '';

            case('add_label'):
                return pz_label_screen::getAddForm($p);

            case('list'):
                $labels = pz_labels::get();
                $cs = new pz_labels_screen($labels);
                return $cs->getListView($p);

            case('edit_label'):
                $label_id = rex_request('label_id', 'int', 0);
                if ($label_id > 0 && $label = pz_label::get($label_id)) {
                    $cs = new pz_label_screen($label);
                    $p['show_delete'] = false;
                    if (!$label->hasProjects()) {
                        $p['show_delete'] = true;
                    }
                    return $cs->getEditForm($p);
                } else {
                    return '<div id="label_form"><p class="xform-warning">'.pz_i18n::msg('label_not_found').'</p></div>';
                }
                break;

            case('label_info'):
                $label_id = rex_request('label_id', 'int', 0);
                if ($label_id > 0 && $label = pz_label::get($label_id)) {
                    $cs = new pz_label_screen($label);
                    $s2_content = $cs->getInfoPage($p);
                } else {
                    return '<div id="label_form"><p class="xform-warning">'.pz_i18n::msg('label_not_found').'</p></div>';
                }
                break;

            case(''):
                $labels = pz_labels::get();
                $cs = new pz_labels_screen($labels);
                $s2_content = $cs->getListView($p, true);
                $s1_content .= pz_label_screen::getAddForm($p);
                break;
            default:
                break;
        }

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);
        return $f->parse('pz_screen_main.tpl');
    }

    public static function getProjectsListOrders($orders = [], $p, $ignore_fields = [])
    {
        $orders['createddesc'] = [ 'orderby' => 'created', 'sort' => 'desc', 'name' => pz_i18n::msg('projects_orderby_createddesc'),
                'link' => "javascript:pz_loadPage('" . $p['layer_list'] ."', '" .
                    pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'createddesc'])) .
                "')", ];
        $orders['createdasc'] = ['orderby' => 'created', 'sort' => 'asc', 'name' => pz_i18n::msg('projects_orderby_createdasc'),
            'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'createdasc'])) .
                "')", ];

        $orders['nameasc'] = ['orderby' => 'name', 'sort' => 'asc', 'name' => pz_i18n::msg('projects_orderby_nameasc'),
            'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'nameasc'])) .
                "')", ];

        $orders['namedesc'] = ['orderby' => 'name', 'sort' => 'desc', 'name' => pz_i18n::msg('projects_orderby_namedesc'),
            'link' => "javascript:pz_loadPage('" . $p['layer_list'] . "','" .
                pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'namedesc'])) .
                "')", ];

        $current_order = 'nameasc';
        if (array_key_exists(rex_request('search_orderby'), $orders)) {
            $current_order = rex_request('search_orderby');
        }

        $orders[$current_order]['active'] = true;

        $p['linkvars']['search_orderby'] = $current_order;

        return ['orders' => $orders, 'p' => $p, 'current_order' => $current_order];
    }

}
