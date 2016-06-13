<?php

pz::setProperty('lang', rex_clang::getCurrentId()); // $REX['LANG']


$path_frontend = rex_path::frontend('.htaccess');
$path_backend = rex_path::addon('prozer', 'install/_htaccess');

if (!rex_file::copy($path_backend, $path_frontend)) {
    $error = pz_i18n::msg('install_failed_htaccess_copy').'<br />'.$path_backend.'<br />'.$path_frontend;
}

$c = rex_sql::factory();
$c->setQuery('delete from pz_user where id=1');

$password = 'admin';
$password = pz_login::passwordHash($password);

$u = rex_sql::factory();
$u->setTable('pz_user');
$u->setValue('id', 1);
$u->setValue('name', 'admin');
$u->setValue('status', 1);
$u->setValue('login', 'admin');
$u->setValue('admin', 1);
$u->setValue('email', 'info@yakamara.de');
$u->setValue('login_tries', 0);
$u->setValue('session_id', $password);
$u->setValue('password', $password);
$u->setValue('digest', sha1($password));
$u->insert();

$c->setQuery('select * from pz_customer where id=1');
if ($c->getRows() == 0) {
    $c->setQuery("INSERT INTO `pz_customer` (`id`, `name`, `created`, `status`, `description`, `archived`, `updated`, `image_inline`, `image`) VALUES
    (1, 'Yakamara Media', '2016-01-01 12:00:00', 1, 'Yakamara Media GmbH & Co. KG', '', '', '', '');");

    $c->setQuery("INSERT INTO `pz_project` (`id`, `name`, `description`, `create_user_id`, `customer_id`, `label_id`, `created`, `updated`, `archived`, `has_emails`, `has_calendar`, `has_calendar_jobs`, `has_files`, `has_wiki`, `update_user_id`) VALUES
  (1, 'Projekt Yakamara', '', 1, 1, 5, '2016-01-01 12:00:00', '2016-01-01 12:00:00', 0, 1, 1, 1, 1, 0, 1);");

    $c->setQuery("INSERT INTO `pz_project_user` (`id`, `user_id`, `project_id`, `created`, `updated`, `calendar`, `calendar_jobs`, `wiki`, `admin`, `webdav`, `caldav`, `caldav_jobs`, `files`, `emails`) VALUES
  (1, 1, 1, '2016-01-01 12:00:00', '2016-01-01 12:00:00', 0, 0, 0, 1, 0, 0, 0, 0, 0);");
}

// -------------------------------------------------- create labels

$c->setQuery('select * from pz_label');
if ($c->getRows() == 0) {
    $c->setQuery("INSERT INTO `pz_label` (`id`, `color`, `border`, `name`, `created`, `updated`) VALUES
    (1, '#fcb819', '#e3a617', 'Support', '', ''),
    (2, '#e118fc', '#cb17e3', 'Kundenprojekte', '', ''),
    (3, '#119194', '#0d797a', 'Private Projekte', '', ''),
    (4, '#678820', '#536e1a', 'Öffentliche Projekte', '', ''),
    (5, '#0f5dca', '#0c52b3', 'Agenturprojekte', '', ''),
    (6, '#aa6600', '#950c00', 'Interne Projekte', '', '');");
}

// -------------------------------------------------- create WebDAV tmp Ordner

$dav_path = rex_path::addonData('prozer', 'dav');
rex_dir::create($dav_path);

// -------------------------------------------------- Output Info



return;


