<?php

class pz_address_field extends pz_model
{
    public $vars = [];

    public function __construct($vars = [])
    {
        $this->setVars($vars);
    }
}
