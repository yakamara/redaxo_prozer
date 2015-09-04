<?php

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 01.09.15
 * Time: 00:19
 */
interface TimeframeFactroyInterface
{
    /**
     * @param $from
     * @param $to
     *
     * @return TimeframeEntity
     */
    public function get($from, $to);
}