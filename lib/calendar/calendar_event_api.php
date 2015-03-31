<?php

class pz_calendar_event_api
{
    public function __construct($event)
    {
        $this->calendar_event = $event;
        if (!is_object($this->calendar_event)) {
            echo pz::error('CALEVENT_MISSING');
            exit;
        }
    }

    public function getVars()
    {
        $from = $this->calendar_event->getFrom();
        $to = $this->calendar_event->getTo();
        $duration = $this->calendar_event->getDuration();
        $duration = ($duration->format('%d') * 24 * 60) + ($duration->format('%h') * 60) + $duration->format('%i');

        $vars = [];
        $vars['job_id'] = (int) $this->calendar_event->getId();
        $vars['project_name'] = $this->calendar_event->getProject()->getName();
        $vars['user_name'] = $this->calendar_event->getUser()->getName();
        $vars['d_start'] = $from->format('Ymd');
        $vars['d_end'] = $to->format('Ymd');
        $vars['t_start'] = $from->format('Hi');
        $vars['t_end'] = $to->format('Hi');
        $vars['duration'] = $duration;
        $vars['subject'] = $this->calendar_event->getTitle();
        $vars['description'] = $this->calendar_event->getDescription();
        $vars['user_id'] = $this->calendar_event->getUser()->getId();
        $vars['project_id'] = $this->calendar_event->getProject()->getId();
        return $vars;
    }
}
