<?php

class pz_clipboard_controller extends pz_controller
{
    public function checkPerm()
    {
        if (rex_request('clip_key', 'string') == '' && !pz::getUser()) {
            return false;
        }
        return true;
    }
}
