<?php

class pz_address_api
{
    public function __construct($address)
    {
        $this->address = $address;
    }

    // ---------------------------------------------------------------- VIEWS

    public function getDataArray()
    {
        $vars = $this->address->getVars();
        unset($vars['photo']);
        unset($vars['vt']);
        unset($vars['uri']);
        unset($vars['vt_email']);

        if (($user = pz_user::get($vars['created_user_id']))) {
            $vars['created_user_name'] = $user->getLogin();
        }

        if (($user = pz_user::get($vars['updated_user_id']))) {
            $vars['updated_user_name'] = $user->getLogin();
        }

        if (($user = pz_user::get($vars['responsible_user_id']))) {
            $vars['responsible_user_name'] = $user->getLogin();
        }

        foreach ($this->address->getFields() as $field) {
            $type = $field->getVar('type');
            $label = $field->getVar('label');
            $value = $field->getVar('value');

            switch ($type) {

                case('ADR'):

                    for ($i = 0;$i < 1000;$i++) {
                        $k = $type.'-'.$label.'_'.$i;
                        if (!isset($vars[$k.'_street'])) {
                            $f = explode(';', $value);
                            $vars[$k.'_street'] = $f[2];
                            $vars[$k.'_zip'] = $f[5];
                            $vars[$k.'_city'] = $f[3];
                            $vars[$k.'_a1'] = $f[1];
                            $vars[$k.'_area'] = $f[4];
                            $vars[$k.'_country'] = $f[6];
                            break;
                        }
                    }
                    break;

                default:
                    for ($i = 0;$i < 1000;$i++) {
                        $k = $type.'-'.$label.'_'.$i;
                        if (!isset($vars[$k])) {
                            $vars[$k] = $value;
                            break;
                        }
                    }
                    break;
            }
        }

        return $vars;
    }
}
