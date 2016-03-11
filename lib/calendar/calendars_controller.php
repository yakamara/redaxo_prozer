<?php

class pz_calendars_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isMe()) {
            return true;
        }
        if (pz::getUser() && pz::getUser()->getUserPerm()->hasCalendarReadPerm()) {
            return true;
        }

        return false;
    }
}
