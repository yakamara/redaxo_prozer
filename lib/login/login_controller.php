<?php


class pz_login_controller extends pz_controller
{
    public $visible = false;
    public $name = 'login';

    public function checkPerm()
    {
        if (!pz::getUser()) {
            return true;
        } else {
            return false;
        }
    }
}
