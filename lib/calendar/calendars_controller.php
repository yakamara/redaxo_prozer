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
        } else {
            return false;
        }
    }

    protected function getSelectDay()
    {
        $request_day = rex_request('day', 'string', rex_cookie('calendar_day', 'string', '')); // 20112019 Ymd
        setcookie('calendar_day', $request_day, time() + 60 + 60 * 24, '/screen/calendars/');

        return $request_day;
    }

    protected function removeSelectDay()
    {
        setcookie('calendar_day', '', -1, '/screen/calendars/');
        unset($_COOKIE['calendar_day']);
    }
}
