<?php

/**
 * Created by PhpStorm.
 * User: Jochen Mandl
 * Date: 30.08.15
 * Time: 20:07
 */
class TimeframeManager implements TimeframeManagerInterface
{
    /** @var DateEventPeriod $event_period */
    private $event_period;

    /** @var  TimeframeFactroyInterface $factory */
    private $factory;


    /**
     * TimeframeEvent constructor.
     *
     * @param DateEventPeriod $period
     */
    public function __construct(DateEventPeriod $period)
    {
        $this->factory = new TimeframeFactroy();

        $this->event_period = $period;

        $this->buildPeriod();
    }

    /**
     * @todo This method is yet replaced by a Builder Pattern.
     */
    private function buildPeriod ()
    {
        $appointment = $this->getEventPeriod()->getEntity();
        $event_period = $this->getEventPeriod();
        $factory = $this->factory;

        if( $event_period->countPeriod() === 0) {
            throw new Exception;
        }

        if( $event_period->countPeriod() === 1) {
            $event_period->attach(
                $factory->get($appointment->getFrom(static::TIME), $appointment->getTo(static::TIME))
            );

            return;
        }

        foreach($this->getPeriod() as $date) {

            if($date->format(static::DATE) === $appointment->getFrom(static::DATE)){
                $event_period->attach(
                    $factory->get($appointment->getFrom(static::TIME), $appointment->getFrom(static::TIME_TO))
                );
                continue;
            }

            if($date->format(static::DATE) > $appointment->getFrom(static::DATE)
            && $date->format(static::DATE) < $appointment->getTo(static::DATE)

            ){
                $event_period->attach(
                    $factory->get($date->format(static::TIME_FROM), $date->format(static::TIME_TO))
                );
                continue;
            }

            if($date->format(static::DATE) === $appointment->getTo(static::DATE)){
                $event_period->attach(
                    $factory->get($appointment->getTo(static::TIME_FROM), $appointment->getTo(static::TIME))
                );
                continue;
            }
        }
        return;
    }

    /**
     * @param TimeframeEntity $event
     * @return bool
     */
    public function isValid(TimeframeEntity $event)
    {
        $day = $this->getEventPeriod()
            ->getEntity()
            ->getDay()
            ->format(static::DATE)
        ;

        return ($event->getFrom()->format(static::DATE) == $day);
    }

    /**
     * @return DateEventPeriod
     */
    public function getEventPeriod()
    {
        return $this->event_period;
    }

    /**
     * @return DatePeriod
     */
    public function getPeriod()
    {
        return $this->event_period->getPeriod();
    }

    /**
     * @return bool
     */
    public function isWeekAndPeriod()
    {
        return ($this->getEventPeriod()->getEntity()->isWeek()
            && $this->getEventPeriod()->count() > 0);
    }

}