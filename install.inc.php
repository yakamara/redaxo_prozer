<?php

$error = '';

$path_frontend = rex_path::frontend(".htaccess");
$path_backend = rex_path::addon("prozer","_htaccess");

if (!copy($path_backend,$path_frontend)) {
    $error = rex_i18n::msg('install_failed_htaccess_copy');
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);