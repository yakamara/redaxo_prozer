<?php

class pz_webdav_controller extends pz_controller
{
    public function controller($function)
    {
        $tree = new pz_sabre_project_tree();

        /* Initializing server */
        $server = new pz_sabre_dav_server($tree, '/webdav/');

        $lockBackend = new Sabre\DAV\Locks\Backend\File(rex_path::addonData('prozer', 'dav/lock.dat'));
        $lockPlugin = new Sabre\DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        $server->addPlugin(new Sabre\DAV\Browser\GuessContentType());

        $tffp = new Sabre\DAV\TemporaryFileFilterPlugin(rex_path::addonData('prozer', 'dav/temp'));
        $server->addPlugin($tffp);

        // And off we go!
        $server->exec();
    }
}
