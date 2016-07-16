<?php

class pz_calendars_controller extends pz_controller
{
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
        return (pz::getUser() && (pz::getUser()->isMe() || pz::getUser()->getUserPerm()->hasCalendarReadPerm()));
    }

    protected function hasWritePerm()
    {
        return (pz::getUser() && (pz::getUser()->isMe() || pz::getUser()->getUserPerm()->hasCalendarWritePerm()));
    }
}
