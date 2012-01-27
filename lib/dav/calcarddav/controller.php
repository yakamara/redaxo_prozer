<?php

class pz_calcarddav_controller extends pz_controller
{
  function controller($function)
  {
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      header('HTTP/1.1 204 No Content');
      exit;
    }

    /* Backends */
    $calendarBackend = new pz_sabre_caldav_backend();
    $carddavBackend = new pz_sabre_carddav_backend();
    $principalBackend = new pz_sabre_principal_backend();

    /* Directory structure */
    $tree = array(
      new Sabre_DAV_SimpleCollection('principals', array(new Sabre_CalDAV_Principal_Collection($principalBackend, 'users'))),
      new Sabre_CalDAV_CalendarRootNode($principalBackend, $calendarBackend),
      new Sabre_CardDAV_AddressBookRoot($principalBackend, $carddavBackend),
    );

    /* Initializing server */
    $server = new pz_sabre_dav_server($tree, '/calcarddav/');

    /* Server Plugins */
    $aclPlugin = new Sabre_DAVACL_Plugin();
    $aclPlugin->defaultUsernamePath = 'principals/users';
    $server->addPlugin($aclPlugin);

    $caldavPlugin = new Sabre_CalDAV_Plugin();
    $server->addPlugin($caldavPlugin);

    $carddavPlugin = new Sabre_CardDAV_Plugin();
    $server->addPlugin($carddavPlugin);

    // And off we go!
    $server->exec();
  }
}