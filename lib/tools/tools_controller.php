<?php

class pz_tools_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isMe()) {

            return true;
        }

        return false;
    }
}
