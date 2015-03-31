<?php

class pz_admin_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isAdmin()) {
            return true;
        }
        return false;
    }
}
