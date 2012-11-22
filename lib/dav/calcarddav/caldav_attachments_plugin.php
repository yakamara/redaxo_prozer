<?php

class pz_sabre_caldav_attachments_plugin extends Sabre_DAV_ServerPlugin
{
  public function initialize(Sabre_DAV_Server $server)
  {
  }

  public function getFeatures()
  {
    return array('calendar-managed-attachments');
  }
}