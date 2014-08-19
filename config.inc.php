<?php

$REX['ADDON']['version']['prozer'] = '3.0beta1';
$REX['ADDON']['author']['prozer'] = 'Jan Kristinus, Gregor Harlan, Thomas Blum';

$REX['ADDON']['xform']['classpaths']['value']['prozer'] = rex_path::addon('prozer','xform/value/');
$REX['ADDON']['xform']['classpaths']['validate']['prozer'] = rex_path::addon('prozer','xform/validate/');


if(!$REX["REDAXO"]) {

  rex_register_extension('OUTPUT_FILTER', function($ep) {

      global $REX;

      $REX['ADDON']['xform']['templatepaths'][] = rex_path::addon('prozer','xform/templates/');

      require_once rex_path::addon('prozer','autoload.php');

      rex_autoload::register();
      rex_autoload::addDirectory(rex_path::addon('prozer','lib'));
      rex_autoload::addDirectory(rex_path::addon('prozer','vendor'));

      pz_fragment::addDirectory(rex_path::addon('prozer','fragments'));

      pz_i18n::addDirectory(rex_path::addon('prozer','lang'));

      pz::setProperty('instname','myinstant');
      pz::setProperty('session_duration',3000);
      pz::setProperty('lang', 'de_de');
      pz::setProperty('version', $REX['ADDON']['version']['prozer']);
      pz::setProperty('redaxo_version', $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION']);

      $output = ""; // $ep["subject"];
      $output .= pz::controller();

      echo $output;
      exit;
    });

}
