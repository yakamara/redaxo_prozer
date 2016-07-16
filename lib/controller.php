<?php

class pz_controller
{
    public $visible = true;
    public $name = 'undefined';
    // var $controll = "";
    public $navigation = [];
    public $function = '';

    public function getUrl()
    {
        return pz::url('', $this->name);
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function getName()
    {
        return pz_i18n::translate($this->name);
    }

    public function checkPerm()
    {
        if (pz::getUser() && pz::getUser()->isMe()) {

            return true;
        }

        return false;
    }

    protected function getUserPerm($perm)
    {

        if (pz::getUser() && pz::getUser()->getUserPerm()) {

            return !empty(pz::getUser()->getUserPerm()->{$perm}());
        }

        return false;
    }

    public function controller($func)
    {
        return 'Controller';
    }
}
