<?php

$error = '';

$sql = rex_sql::factory();

// $REX['ADDON']['version']['prozer']

// Change to 3.0beta3
$sql->setQuery('ALTER TABLE `pz_history` ADD `message` VARCHAR( 255 ) NOT NULL ;');

// -------------------------------------------------  allways check
$dav_path = rex_path::addonData("prozer", "dav");
rex_dir::create($dav_path);

// -------------------------------------------------

$REX['ADDON']['update']['prozer'] = true;
