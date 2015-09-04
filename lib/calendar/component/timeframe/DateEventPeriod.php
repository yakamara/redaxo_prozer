<?php

/**
 * Created by PhpStorm.
 * User: Jochen Mandl
 * Date: 31.08.15
 * Time: 19:31
 */
class DateEventPeriod extends SplObjectStorage
{
    /** @var DatePeriod $period */
    private $period;

    /** @var TimeframeEntity $entity */
    private $entity;

    /** @var int|null $countPeriod */
    private $countPeriod;


    /**
     * DateEventPeriod constructor.
     *
     * @param TimeframeEntity $entity
     */
    public function __construct(TimeframeEntity $entity)
    {
        $this->entity = $entity;

        $this->period = new DatePeriod(
            new DateTime($this->getEntity()->getFrom(TimeframeManagerInterface::TIME_FROM)),
            new DateInterval('P1D'),
            new DateTime($this->getEntity()->getTo(TimeframeManagerInterface::TIME_TO))
        );
    }

    /**
     * @return TimeframeEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return DatePeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return int
     */
    public function countPeriod()
    {
        if(!isset($this->countPeriod)){
            $this->countPeriod = count(iterator_to_array($this->period));
        }

        return $this->countPeriod;
    }


}