<?php


$error = '';

$path_frontend = rex_path::frontend(".htaccess");
$path_backend = rex_path::addon("prozer","_htaccess");

if (!rex_file::copy($path_backend, $path_frontend)) {
    $error = rex_i18n::msg('install_failed_htaccess_copy')."<br />".$path_backend.'<br />'.$path_frontend;
}

$c = rex_sql::factory();

$c->setQuery('select * from pz_user where id=?',array(1));
if($c->getRows() != 1) {
  $c->setQuery("INSERT INTO `pz_user` (`id`, `name`, `status`, `role`, `login`, `password`, `digest`, `login_tries`, `lasttrydate`, `session_id`, `cookiekey`, `admin`, `image_inline`, `image`, `created`, `updated`, `address_id`, `email`, `account_id`) VALUES
(1, 'admin', 1, 0, 'admin', 'admin', '11111111111', 0, 1323940123, '11111111111', '', '1', '', '1', '2013-01-01 12:00:00', '2013-01-01 12:00:00', 0, 'info@yakamara.de', 5);");
}

$password = "admin";
$password = rex_login::passwordHash($password);
$u = rex_sql::factory();
$u->setTable('pz_user');
$u->setWhere( array( 'id' => 1 ) );
$u->setValue('password', $password );
$u->setValue('digest', sha1($password));
$u->update();

$c->setQuery('select * from pz_customer where id=?',array(1));
if($c->getRows() == 0) {
  $c->setQuery("INSERT INTO `pz_customer` (`id`, `name`, `created`, `status`, `description`, `archived`, `updated`, `image_inline`, `image`) VALUES
  (1, 'Yakamara Media', '2013-01-01 12:00:00', 1, 'Yakamara Media GmbH & Co. KG', '', '', '', '');");
}

$c->setQuery('select * from pz_label',array());
if($c->getRows() == 0) {
  $c->setQuery("INSERT INTO `pz_label` (`id`, `color`, `border`, `name`, `created`, `updated`) VALUES
  (1, '#fcb819', '#e3a617', 'Support', '', ''),
  (2, '#e118fc', '#cb17e3', 'Kundenprojekte', '', ''),
  (3, '#119194', '#0d797a', 'Private Projekte', '', ''),
  (4, '#678820', '#536e1a', 'Öffentliche Projekte', '', ''),
  (5, '#0f5dca', '#0c52b3', 'Agenturprojekte', '', ''),
  (6, '#aa6600', '#950c00', 'Interne Projekte', '', '');");
}


// create WebDAV tmp Ordner
$dav_path = rex_path::addonData("prozer", "dav");
rex_dir::create($dav_path);



if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);