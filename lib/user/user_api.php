<?php

class pz_user_api
{
    public function __construct($user)
    {
        $this->user = $user;
    }

    // ---------------------------------------------------------------- VIEWS

    public function getDataArray()
    {
        $vars = $this->user->getVars();
        unset($vars['photo']);
        unset($vars['vt']);
        unset($vars['uri']);
        unset($vars['vt_email']);
        return $vars;
    }
}
