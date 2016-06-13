<?php

class TimeframeFactroy implements TimeframeFactroyInterface
{
    /**
     * @todo simulate overlay
     *
     * @param $from
     * @param $to
     *
     * @return TimeframeEntity
     */
    public function get($from, $to)
    {
        return new TimeframeEntity(new DateTime($from),  new DateTime($to));
    }
}