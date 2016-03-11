<?php

class pz_admin_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser() && pz::getLoginUser()->isAdmin()) {

            return true;
        }

        if ($this->getUserPerm('hasAdminPerm')) {

            return true;
        }


        return false;
    }
}
