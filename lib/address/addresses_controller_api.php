<?php

class pz_addresses_controller_api extends pz_controller
{
    public function controller($func = '')
    {
        $format = rex_request('format', 'string', 'csv');

        switch ($func) {
            case('export'):
                $return = $this->getExportData($format);
                break;
            default:
                $return = [];
                break;
        }
        return pz_api::send($return, $format);  // formated_json / jspm / excel;
    }

    public function getExportData($format)
    {
        if (rex_request('search', 'string') != '') {
            $search = rex_request('search', 'string');
            $addresses = pz_address::getAllByFulltext($search);
        } else {
            $addresses = pz_address::getAll([]);
        }

        $return = [];
        foreach ($addresses as $address) {
            $a_api = new pz_address_api($address);
            $return[] = $a_api->getDataArray();
        }
        return $return;
    }
}
