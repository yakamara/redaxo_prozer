<?php

class pz_emails_controller extends pz_controller
{
    public $search_order_fields = ['id_new' => 'id desc', 'title' => 'subject desc'];

    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isMe()) {

            return true;
        }

        if ($this->hasReadPerm() || $this->hasWritePerm()) {

            return true;
        }

        return false;
    }


    protected function hasReadPerm()
    {
        return (pz::getUser() && (pz::getUser()->isMe() || pz::getUser()->getUserPerm()->hasEmailReadPerm()));
    }

    protected function hasWritePerm()
    {
        return (pz::getUser() && (pz::getUser()->isMe() || pz::getUser()->getUserPerm()->hasEmailWritePerm()));
    }
}
