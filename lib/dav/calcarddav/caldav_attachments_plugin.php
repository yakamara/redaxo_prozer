<?php

class pz_sabre_caldav_attachments_plugin extends Sabre\DAV\ServerPlugin
{
    public function initialize(Sabre\DAV\Server $server)
    {
    }

    public function getFeatures()
    {
        return array('calendar-managed-attachments');
    }
}
