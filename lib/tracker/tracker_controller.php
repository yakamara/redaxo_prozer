<?php

class pz_tracker_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser()) {

            return true;
        }

        return false;
    }
}
