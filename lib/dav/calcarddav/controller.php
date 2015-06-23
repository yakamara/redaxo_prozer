<?php

class pz_calcarddav_controller extends pz_controller
{
    public function controller($function)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('HTTP/1.1 204 No Content');
            exit;
        }

        /* Backends */
        $calendarBackend = new pz_sabre_caldav_backend();
        $carddavBackend = new pz_sabre_carddav_backend();
        $principalBackend = new pz_sabre_principal_backend();

        /* Directory structure */
        $tree = [
            new Sabre\DAV\SimpleCollection('principals', [new Sabre\CalDAV\Principal\Collection($principalBackend, 'users')]),
            new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
            new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
        ];

        /* Initializing server */
        $server = new pz_sabre_dav_server($tree, '/calcarddav/');

        /* Server Plugins */
        $aclPlugin = new Sabre\DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        $caldavPlugin = new Sabre\CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        $carddavPlugin = new Sabre\CardDAV\Plugin();
        $server->addPlugin($carddavPlugin);

        $server->addPlugin(new pz_sabre_caldav_attachments_plugin());

        // And off we go!
        $server->exec();
    }
}
