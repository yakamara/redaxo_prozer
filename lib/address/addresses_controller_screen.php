<?php

class pz_addresses_controller_screen extends pz_addresses_controller
{
    public $name             = 'addresses';
    public $function         = '';
    public $functions        = ['my', 'all', 'addresses']; // "export",
    public $function_default = 'all';
    public $navigation       = ['all', 'my']; // "export"

    public function controller($function)
    {
        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];

        $p['mediaview'] = 'screen';
        $p['controll'] = 'addresses';
        $p['function'] = $this->function;
        $p['linkvars'] = [];

        switch ($this->function) {
            case 'all':
                return $this->getAddressesPage($p);
            case 'my':
                return $this->getMyAddressesPage($p);
            case 'address':
                return $this->getAddress($p);
            case 'addresses':
                return $this->getAddresses($p);
                break;
            default:
                return '';

        }
    }

    // ------------------------------------------------------------------- Views

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

    public function getAddresses()
    {
        $fulltext = rex_request('search_name', 'string');
        $mode = rex_request('mode', 'string', '');
        $format = rex_request('format', 'string', 'json');

        $r_addresses = [];
        switch ($mode) {

            case 'get_user_emails':
                $filter = [];
                $filter[] = ['field' => 'status', 'value' => 1];
                $filter[] = ['field' => 'name', 'type' => 'like', 'value' => '%' . $fulltext . '%'];
                // $fulltext
                // status = 1
                $users = pz::getUsers($filter);
                foreach ($users as $user) {
                    $r_addresses[] = [
                        'id'    => $user->getId(),
                        'label' => $user->getName() . ' [' . $user->getEmail() . ']',
                        'value' => $user->getEmail(),
                    ];
                }
                break;

            case 'get_emails':

                $addresses = pz_address::getAllByEmailFulltext($fulltext);

                foreach ($addresses as $address) {
                    foreach ($address->getFields() as $field) {
                        if ($field->getVar('type') == 'EMAIL') {
                            $r_addresses[] = [
                                'id'    => $field->getVar('value'),
                                'label' => $address->getFullname() . ' - ' . $field->getVar('value') . ' [' . $field->getVar('label') . ']',
                                'value' => $field->getVar('value'),
                            ];
                        }
                    }
                }
                break;
            default:
        }

        if ($format == 'json') {
            return json_encode($r_addresses);
        }

        return '';
    }

    public function getAddress()
    {
        // TODO

        $address_id = rex_request('address_id', 'int', 0);
        if ($address_id < 1) {
            return false;
        }

        if (!($address = pz_address::get($address_id))) {
            return false;
        }

        $mode = rex_request('mode', 'string', '');
        switch ($mode) {
            case 'vcard':
                // TODO:
                return false;
        }
    }

    // -------------------------------------------------------

    public static function getAddressListOrders($orders = [], $p = [])
    {
        //sort by ID DESC is latest/newest ID and ASC first/oldest ID
        $orders['iddesc'] = [
            'orderby' => 'id',
            'sort'    => 'desc',
            'name'    => pz_i18n::msg('address_orderby_iddesc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'iddesc'])
                ) . "')",
        ];
        $orders['idasc'] = [
            'orderby' => 'id',
            'sort'    => 'asc',
            'name'    => pz_i18n::msg('address_orderby_idasc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'idasc'])
                ) . "')",
        ];

        //sort by NAME
        $orders['lastnamedesc'] = [
            'orderby' => 'name',
            'sort'    => 'desc',
            'name'    => pz_i18n::msg('address_orderby_lastnamedesc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'lastnamedesc'])
                ) . "')",
        ];
        $orders['lastnameasc'] = [
            'orderby' => 'name',
            'sort'    => 'asc',
            'name'    => pz_i18n::msg('address_orderby_lastnameasc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'lastnameasc'])
                ) . "')",
        ];

        //sort by FIRSTNAME
        $orders['firstnamedesc'] = [
            'orderby' => 'firstname',
            'sort'    => 'desc',
            'name'    => pz_i18n::msg('address_orderby_firstnamedesc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'firstnamedesc'])
                ) . "')",
        ];
        $orders['firstnameasc'] = [
            'orderby' => 'firstname',
            'asc'     => 'asc',
            'name'    => pz_i18n::msg('address_orderby_firstnameasc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" .
                pz::url(
                    $p['mediaview'],
                    $p['controll'],
                    $p['function'],
                    array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'firstnameasc'])
                ) . "')",
        ];

        //sort COMPANY
        $orders['companydesc'] = [
            'orderby' => 'company',
            'sort'    => 'desc',
            'name'    => pz_i18n::msg('address_orderby_companydesc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" . pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'companydesc'])) . "')",
        ];
        $orders['companyasc'] = [
            'orderby' => 'company',
            'sort'    => 'asc',
            'name'    => pz_i18n::msg('address_orderby_companyasc'),
            'link'    => "javascript:pz_loadPage('addresses_list','" . pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['mode' => 'list', 'search_orderby' => 'companyasc'])) . "')",
        ];

        $current_order = 'lastnameasc';
        if (array_key_exists(rex_request('search_orderby'), $orders)) {
            $current_order = rex_request('search_orderby');
        }

        $orders[$current_order]['active'] = true;

        $p['linkvars']['search_orderby'] = $current_order;

        return ['orders' => $orders, 'p' => $p, 'current_order' => $current_order];
    }

    // ------------------------------------------------------- page views

    public function getMyAddressesPage($p = [])
    {
        $p['title'] = pz_i18n::msg('my_addresses');

        $s1_content = '';
        $s2_content = '';

        $fulltext = rex_request('search_name', 'string');
        $mode = rex_request('mode', 'string');
        switch ($mode) {
            /*
            case("upload_photo"):
                // TODO
                $address_id = rex_request("address_id","int");
                if($address = pz_address::get($address_id)) {

                }
                return "PHOTO";
            */
            /*
        case("view_address"):
            $address_id = rex_request("address_id","int");
            if($address = pz_address::get($address_id)) {
                $r = new pz_address_screen($address);
                return $r->getDetailView($p);
            }
            return "";
            */

            case 'delete_address':
                $address_id = rex_request('address_id', 'int');
                if ($address = pz_address::get($address_id)) {
                    $r = new pz_address_screen($address);
                    $return = $r->getDeleteForm($p);
                    $address->delete();

                    return $return;
                }

            case 'edit_address':
                $address_id = rex_request('address_id', 'int');
                if ($address = pz_address::get($address_id)) {
                    $r = new pz_address_screen($address);

                    return $r->getEditForm($p);
                }

                return '';
            case 'add_address':
                return pz_address_screen::getAddForm($p);
                break;
            case 'list':
                $addresses = pz::getUser()->getAddresses($fulltext);

                return pz_address_screen::getBlockListView(
                    $addresses,
                    array_merge($p, ['linkvars' => ['mode' => 'list', 'search_name' => $fulltext]])
                );
                break;
            case '':
                $s1_content .= pz_address_screen::getAddressesSearchForm($p);
                $addresses = pz::getUser()->getAddresses($fulltext);
                $s2_content .= pz_address_screen::getBlockListView(
                    $addresses,
                    array_merge($p, ['linkvars' => ['mode' => 'list', 'search_name' => $fulltext]])
                );
                $form = pz_address_screen::getAddForm($p);
                break;
            default:
                break;
        }

        $s1_content .= $form;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }

    public function getAddressesPage($p = [])
    {
        $p['title'] = pz_i18n::msg('all_addresses');

        $s1_content = '';
        $s2_content = '';

        $fulltext = rex_request('search_name', 'string');

        $orders = [];
        $result = self::getAddressListOrders($orders, $p);
        $orders = $result['orders'];
        $current_order = $result['current_order'];
        $p = $result['p'];

        $mode = rex_request('mode', 'string');
        switch ($mode) {
            case 'delete_address':
                $address_id = rex_request('address_id', 'int');
                if ($address = pz_address::get($address_id)) {
                    $r = new pz_address_screen($address);
                    $return = $r->getDeleteForm($p);
                    $address->delete();

                    return $return;
                }
            case 'edit_address':
                $address_id = rex_request('address_id', 'int');
                if ($address = pz_address::get($address_id)) {
                    $r = new pz_address_screen($address);

                    return $r->getEditForm($p);
                }

                return '';
            case 'add_address':
                return pz_address_screen::getAddForm($p);
                break;
            case 'list':
                $addresses = pz_address::getAllByFulltext($fulltext, [$orders[$current_order]]);
                $p['linkvars']['mode'] = 'list';
                $p['linkvars']['search_name'] = rex_request('search_name', 'string');

                return pz_address_screen::getBlockListView($addresses, $p, $orders);
                break;
            case '':
                $p['linkvars']['mode'] = 'list';
                $p['linkvars']['search_name'] = rex_request('search_name', 'string');
                $s1_content .= pz_address_screen::getAddressesSearchForm($p);
                $addresses = pz_address::getAllByFulltext($fulltext, [$orders[$current_order]]);
                $s2_content .= pz_address_screen::getBlockListView($addresses, $p, $orders);
                $form = pz_address_screen::getAddForm($p);
                break;
            default:
                break;
        }

        $s1_content .= $form;

        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section_1', $s1_content, false);
        $f->setVar('section_2', $s2_content, false);

        return $f->parse('pz_screen_main.tpl');
    }
}
