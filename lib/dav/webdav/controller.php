<?php

class pz_webdav_controller extends pz_controller
{
  function controller($function)
  {
  	$tree = new pz_sabre_project_tree();

    /* Initializing server */
    $server = new pz_sabre_dav_server($tree, '/webdav/');

    $lockBackend = new Sabre_DAV_Locks_Backend_File(rex_path::addonData('prozer', 'dav/lock.dat'));
    $lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
    $server->addPlugin($lockPlugin);

    $server->addPlugin(new Sabre_DAV_Browser_GuessContentType());

    $tffp = new Sabre_DAV_TemporaryFileFilterPlugin(rex_path::addonData('prozer', 'dav/temp'));
    $server->addPlugin($tffp);

    // And off we go!
    $server->exec();
  }
}