<?php

$error = '';

$sql = rex_sql::factory();

// $REX['ADDON']['version']['prozer']

// Change to 3.0beta3
$sql->setQuery('ALTER TABLE `pz_history` ADD `message` VARCHAR( 255 ) NOT NULL ;');

$sql->setQuery('ALTER TABLE `pz_wiki` DROP `stamp`;');
$sql->setQuery('ALTER TABLE `pz_wiki` ADD `created` DATETIME NOT NULL , ADD `create_user_id` INT(10) UNSIGNED NOT NULL , ADD `updated` DATETIME NOT NULL , ADD `update_user_id` INT(10) UNSIGNED NOT NULL ;');
$sql->setQuery('ALTER TABLE `pz_wiki` ADD `admin` TINYINT(1) NOT NULL');
$sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `default` TINYINT(1) NOT NULL ;');

// Change to 3.0
$sql->setQuery('ALTER TABLE `pz_email_account` ADD `smtp_login` VARCHAR( 255 ) NOT NULL AFTER `smtp` ,
ADD `smtp_password` VARCHAR( 255 ) NOT NULL AFTER `smtp_login`;');

// Change to 3.1
$sql->setQuery('ALTER TABLE `pz_calendar_attendee` ADD `role` VARCHAR( 255 ) NOT NULL DEFAULT "REQ-PARTICIPANT" AFTER `name`;');
$sql->setQuery('ALTER TABLE `pz_wiki` ADD `position` VARCHAR( 255 ) NOT NULL AFTER `text`;');

// -------------------------------------------------  allways check
$dav_path = rex_path::addonData('prozer', 'dav');
rex_dir::create($dav_path);

// -------------------------------------------------

$REX['ADDON']['update']['prozer'] = true;
