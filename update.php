<?php

$error = '';

$sql = rex_sql::factory();

// $REX['ADDON']['version']['prozer']


// -------------------------------------------------  allways check
$dav_path = rex_path::addonData("prozer", "dav");
rex_dir::create($dav_path);

// -------------------------------------------------

$REX['ADDON']['update']['prozer'] = true;
