<?php

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 30.08.15
 * Time: 19:42
 */

class TimeframeEntity
{

    /** @var  DateTime $day */
    private $day;
    /** @var  DateTime $day */
    private $from;
    /** @var  DateTime $day */
    private $to;

    private $option;


    /**
     * TimeframeEntity constructor.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @param DateTime|null $day
     * @param null $option
     */
    public function __construct(DateTime $from, DateTime $to, DateTime $day = null, $option = null )
    {
        $this->from = $from;
        $this->to   = $to;
        $this->day  = $day;

        $this->option   = $option;
    }

    /**
     * @return DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return DateTime|string
     */
    public function getFrom($format = null)
    {
        if(null === $format)
            return $this->from;

        return $this->from->format($format);
    }

    /**
     * @return DateTime|string
     */
    public function getTo($format = null)
    {
        if(null === $format)
            return $this->to;

        return $this->to->format($format);
    }

    /**
     * @param $key
     * @return bool
     */
    public function isWeek()
    {
        return ($this->option === 'week');
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        $duration = $this->getTo()->diff($this->getFrom());

        $h = (int) $duration->format("%h")*60;
        $i = (int) $duration->format("%i");

        return $h+$i;
    }
}