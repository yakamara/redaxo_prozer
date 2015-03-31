<?php

class pz_emails_controller extends pz_controller
{
    public $search_order_fields = ['id_new' => 'id desc', 'title' => 'subject desc'];

    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isMe()) {
            return true;
        }
        if (pz::getUser() && pz::getUser()->getUserPerm()->hasEmailReadPerm()) {
            return true;
        } else {
            return false;
        }
    }
}
