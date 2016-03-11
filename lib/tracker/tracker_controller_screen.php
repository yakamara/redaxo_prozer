<?php

class pz_tracker_controller_screen extends pz_tracker_controller
{
    public $name = 'tracker';
    public $function = '';
    public $functions = ['tracker'];
    public $function_default = 'tracker';
    public $navigation = [];
    public $visible = false;


    public function controller($function)
    {
        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];
        $p['mediaview'] = 'screen';
        $p['controll'] = 'tracker';
        $p['function'] = $this->function;

        switch ($this->function) {
            case('tracker'):
                return $this->getTracker($p);
            default:
                return '';

        }
    }


    public function getTracker()
    {

        $emails = pz::getUser()->countInboxEmails();
        $attandees = pz::getUser()->countAttendeeEvents();

        $title = '['.$emails.'] '.pz_screen::getPageTitle();
        $return = '<script>
		pz_updateInfocounter('.$emails.', '.$attandees.',"'.$title.'");
		</script>';

        // Problem

        // Emails oder andere Dinge nachladen die ein neuerest Datum
        // als das aktuellste haben und diese an oberste Stelle der aktuellen
        // Liste stellen

        // Bsp. Inbox / Outbox / Müll / Projektemails / Kalendertermine / jobs / Adressen
        // immer in bestimmtem Kontext: Termine heute / 2 wochen ...

        // regelmaessige prüfung, nachladen und anzeigen

        // TODO: neue emails laden
        // - im tracker die page mit übergeben
        // -
        /*
        $filter = array();
        $filter[] = array('type' => 'plain', 'value' => '( (project_id>0 AND status=0) || (project_id=0))');
        $filter[] = array('type' => 'plain', 'value' => '( createdesc > )');
        $emails = pz::getUser()->getInboxEmails($filter, array(), array('createdesc'), $pager);
        */

        // - last trackingdate setzen
        // - Datum vom letzten Trackeraufruf mit übergeben und im pz_tracker mit übergeben
        // - prüfen ob auf email page
        // - email/s nachladen


        return $return;
    }

}