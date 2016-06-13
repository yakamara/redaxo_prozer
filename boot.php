<?php

if (!rex::isBackend()) {

    rex_extension::register('OUTPUT_FILTER', function ($ep) {

        rex_autoload::register();
        rex_autoload::addDirectory(rex_path::addon('prozer', 'vendor'));

        pz_fragment::addDirectory(rex_path::addon('prozer', 'fragments'));

        pz_i18n::addDirectory(rex_path::addon('prozer', 'lang'));

        pz::setProperty('instname', 'myinstant');
        pz::setProperty('session_duration', 3000);
        pz::setProperty('lang', 'de_de');
        pz::setProperty('version', rex_addon::get('prozer')->getVersion());
        pz::setProperty('redaxo_version', rex::getVersion());

        $output = ''; // $ep["subject"];
        $output .= pz::controller();

        pz::sendHeader();

        echo $output;
        exit;
    });
}
