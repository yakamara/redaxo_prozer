<?php

class pz_addresses_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser()) {
            return true;
        } else {
            return false;
        }
    }
}
