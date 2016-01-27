<?php

use FragSeb\Dashboard\Model\WidgetModelAdapterInterface;

class CalendarWidgetAdapter implements WidgetModelAdapterInterface
{

    private $settings;

    /**
     * @var DateTimeInterface $from
     */
    private $from;

    /**
     * @var DatTimeInterface|null $to
     */
    private $to;


    /**
     * The get methode is a reference of model and
     * it is very important that the return is a array.
     *
     * @param array|null $settings
     * @return array
     */
    public function get(array $settings = null)
    {
        $this->settings = $settings;
        
        $this->createTimeframe();

        return $this->getAllBirthDayKids();
    }


    private function getAllBirthDayKids()
    {
        $projects = pz::getUser()->getCalendarProjects();
        $project_ids = pz_project::getProjectIds($projects);

        /**
         * @var pz_calendar_event[]
         */
        $events = pz::getUser()->getAllEvents($project_ids, $this->from, $this->to);

        return $this->events_map($events);
    }


    /**
     * @param pz_calendar_event[] $events
     * @return array
     */
    private function events_map($events)
    {
        $approach = new ApproachItems();

        $maps =  array_map(function($event) use ($approach) {
            /**
             * @var pz_calendar_event $event
             */
            if(!$event->isBooked()) {
                $data = [];

                $data['event'] = $event;

                $data['id'] = $event->getId();
                $data['clip_ids'] = $event->getClipIds();
                $data['location'] = $event->getLocation();
                $data['url'] = $event->getUrl();

                $data['allday'] = $event->isAllDay();
                $data['attendees'] = $event->getAttendees();
                $data['uri'] = $event->getUri();
                $data['project_id'] = $event->getProjectId();
                $data['project_sub_id'] = $event->getProjectSubId();
                $data['title'] = $event->getTitle();
                $data['description'] = $event->getDescription();
                $data['from'] = $event->getFrom()->format(DateTime::ATOM);
                $data['to'] = $event->getTo()->format(DateTime::ATOM);

                $settingsDay = new DateTime($this->settings['from']);
                $period  = new DateEventPeriod(new TimeframeEntity($event->getFrom(), $event->getTo(), $settingsDay));
                $manager = new TimeframeManager($period);

                foreach($manager->getEventPeriod() as $item){
                    if ($item->getFrom()->format('d') !== $settingsDay->format('d')) {
                        continue;
                    }
                    $timeFrame = $item;
                }
                $data['style'] = [];
                $approach->add($event, $timeFrame);

                $data['created'] = $event->getCreated()->format(DateTime::ATOM);
                $data['updated'] = $event->getUpdated()->format(DateTime::ATOM);
                $data['sequenc'] = $event->getSequence();
                $data['user_id'] = $event->getUserId();
                $data['alarms'] = $event->getAlarms();

                return $data;
            }

            return null;

        }, $events);

        $maps = array_values(array_filter($maps));

        $approach->execute();

        foreach($maps as $key => $map) {
            $entity = $approach->getEntity($map['id']);

            $maps[$key]['style'] = array_merge($maps[$key]['style'], $entity->getStyle());
            $maps[$key]['form_top'] = $entity->getFrom();
            $maps[$key]['to_top'] = $entity->getTo();

            unset($maps[$key]['event']);
        }

        return $maps;

    }

    private function timeframe($datetime, $from = null)
    {
        $clone_datetime = clone $datetime;

        $hours = $clone_datetime->format('H');
        $sub = $clone_datetime->format('i');

        $pixel = $hours * 60;

        if(null === $from) {
            return $pixel + $sub;
        }

        return ($pixel + $sub)-$from;
    }


    private function createTimeframe()
    {
        $this->to = null;

        if (isset($this->settings['from'])) {
            $this->from = new DateTime($this->settings['from']);
            $this->from->setTime(00, 00, 01);
        }

        if (!$this->from instanceof DateTimeInterface) {
            $this->from = new DateTime();
        }

        if (isset($this->settings['to'])) {
            $this->to = new DateTime($this->settings['to']);
        }

        if (null === $this->to) {
            $this->to = clone $this->from;
            $this->to->setTime(23, 59, 59);
        }
    }
}