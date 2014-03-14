<?php


$error = '';
$c = rex_sql::factory();


// -------------------------------------------------- SQL Patch

$path = rex_path::core("lib/sql.php");
$search = ' throw new rex_sql_exception(\'Field ';

$sql_lib_contents = file_get_contents($path);
$sql_lib_contents = str_replace($search, ' // '.$search, $sql_lib_contents);
file_put_contents($path, $sql_lib_contents);


// -------------------------------------------------- Install htaccess

$path_frontend = rex_path::frontend(".htaccess");
$path_backend = rex_path::addon("prozer","install/_htaccess");

if (!rex_file::copy($path_backend, $path_frontend)) {
    $error = rex_i18n::msg('install_failed_htaccess_copy')."<br />".$path_backend.'<br />'.$path_frontend;
}

// -------------------------------------------------- Base prozer tables

$c->setQuery('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `firstname` text NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `updated_user_id` int(11) NOT NULL,
  `responsible_user_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `company` text NOT NULL,
  `is_company` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `additional_names` text NOT NULL,
  `prefix` text NOT NULL,
  `suffix` text NOT NULL,
  `nickname` text NOT NULL,
  `birthname` text NOT NULL,
  `phonetic_name` text NOT NULL,
  `phonetic_firstname` text NOT NULL,
  `birthday` text NOT NULL,
  `department` text NOT NULL,
  `title` text NOT NULL,
  `photo` longtext NOT NULL,
  `vt` text NOT NULL,
  `vt_email` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_address_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `label` text NOT NULL,
  `preferred` varchar(255) NOT NULL,
  `value_type` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_calendar_alarm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) NOT NULL,
  `event_id` int(10) unsigned DEFAULT NULL,
  `todo_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `trigger` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `emails` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `structured_location` varchar(255) DEFAULT NULL,
  `proximity` varchar(255) DEFAULT NULL,
  `acknowledged` datetime DEFAULT NULL,
  `related_id` int(10) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `event` (`event_id`),
  KEY `todo_id` (`todo_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_calendar_attendee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum(\'NEEDS-ACTION\',\'ACCEPTED\',\'TENTATIVE\',\'DECLINED\') NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `default` (`event_id`,`user_id`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_calendar_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) DEFAULT NULL,
  `project_id` int(10) unsigned DEFAULT NULL,
  `project_sub_id` int(11) DEFAULT NULL,
  `clip_ids` varchar(255) DEFAULT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  `allday` tinyint(1) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `booked` tinyint(1) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `rule_id` int(10) unsigned DEFAULT NULL,
  `base_from` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `vt` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `default` (`rule_id`,`from`,`to`,`project_id`),
  KEY `uri` (`uri`),
  KEY `project` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_calendar_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned DEFAULT NULL,
  `todo_id` int(10) unsigned DEFAULT NULL,
  `frequence` enum(\'DAILY\',\'WEEKLY\',\'MONTHLY\',\'YEARLY\') NOT NULL,
  `interval` smallint(5) unsigned NOT NULL,
  `weekdays` varchar(255) NOT NULL,
  `days` varchar(255) NOT NULL,
  `months` varchar(255) NOT NULL,
  `nth` tinyint(1) NOT NULL,
  `end` date DEFAULT NULL,
  `count` smallint(5) unsigned DEFAULT NULL,
  `exceptions` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event` (`event_id`),
  KEY `todo_id` (`todo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_calendar_todo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `project_sub_id` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL,
  `order` int(10) unsigned DEFAULT NULL,
  `from` datetime DEFAULT NULL,
  `due` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `rule_id` int(10) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `vt` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_clipboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filename` text NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `content_length` text NOT NULL,
  `content_type` text NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `open` tinyint(1) NOT NULL,
  `online_date` datetime NOT NULL,
  `offline_date` datetime NOT NULL,
  `uri` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT \'0\',
  `description` text NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `image_inline` text NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT \'0\',
  `project_id` int(11) NOT NULL DEFAULT \'0\',
  `from` text NOT NULL,
  `to` text NOT NULL,
  `reply_to` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `date` text NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `header` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `prio` varchar(255) NOT NULL,
  `importance` varchar(255) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT \'0\',
  `spam` tinyint(4) NOT NULL DEFAULT \'0\',
  `message_id` varchar(255) NOT NULL,
  `draft` tinyint(4) NOT NULL DEFAULT \'0\',
  `reply_id` int(11) NOT NULL DEFAULT \'0\',
  `forward_id` int(11) NOT NULL DEFAULT \'0\',
  `readed` tinyint(4) NOT NULL DEFAULT \'0\',
  `content_type` text NOT NULL,
  `trash` tinyint(4) NOT NULL DEFAULT \'0\',
  `send` tinyint(4) NOT NULL DEFAULT \'0\',
  `from_emails` text NOT NULL,
  `to_emails` text NOT NULL,
  `cc_emails` text NOT NULL,
  `bcc_emails` text NOT NULL,
  `account_id` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT \'0\',
  `body_html` text NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NOT NULL,
  `replied_id` int(11) NOT NULL,
  `forwarded_id` int(11) NOT NULL,
  `clip_ids` text NOT NULL,
  `has_attachments` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `createdmail` (`draft`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_email_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `host` varchar(255) NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `smtp` text NOT NULL,
  `email` text NOT NULL,
  `signature` text NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `delete_emails` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `mailboxtype` text NOT NULL,
  `ssl` tinyint(4) NOT NULL,
  `last_login` datetime NOT NULL,
  `last_login_finished` datetime NOT NULL,
  `login_failed` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(255) NOT NULL,
  `border` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_project` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `create_user_id` int(6) NOT NULL DEFAULT \'0\',
  `customer_id` int(11) NOT NULL DEFAULT \'0\',
  `label_id` int(11) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `has_emails` tinyint(4) NOT NULL,
  `has_calendar` tinyint(4) NOT NULL,
  `has_calendar_jobs` tinyint(4) NOT NULL,
  `has_files` tinyint(4) NOT NULL,
  `has_wiki` tinyint(4) NOT NULL,
  `update_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`create_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_project_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `is_directory` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_user_id` int(10) unsigned NOT NULL,
  `updated_user_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_project_parent` (`name`,`project_id`,`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');


$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_project_sub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_project_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `calendar` tinyint(4) NOT NULL,
  `calendar_jobs` tinyint(4) NOT NULL,
  `wiki` tinyint(4) NOT NULL,
  `admin` tinyint(4) NOT NULL,
  `webdav` tinyint(4) NOT NULL,
  `caldav` tinyint(4) NOT NULL,
  `caldav_jobs` tinyint(4) NOT NULL,
  `files` tinyint(4) NOT NULL,
  `emails` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_user` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT \'\',
  `status` tinyint(4) NOT NULL DEFAULT \'0\',
  `role` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `digest` varchar(255) NOT NULL,
  `login_tries` int(11) NOT NULL DEFAULT \'0\',
  `lasttrydate` int(11) NOT NULL,
  `last_login` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `cookiekey` varchar(255) NOT NULL,
  `admin` varchar(255) NOT NULL,
  `image_inline` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `address_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `account_id` int(11) NOT NULL,
  `config` text NOT NULL,
  `perms` text NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`status`,`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_user_perm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` text,
  `to_user_id` text,
  `calendar_read` tinyint(4) NOT NULL,
  `calendar_write` tinyint(4) NOT NULL,
  `email_read` tinyint(4) NOT NULL,
  `email_write` tinyint(4) NOT NULL,
  `created` varchar(255) DEFAULT NULL,
  `updated` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `perms` text NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_wiki` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `stamp` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  `vt` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

$c->setQuery('CREATE TABLE IF NOT EXISTS `pz_history` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`project_id` int( 11 ) NOT NULL ,
`user_id` int( 11 ) NOT NULL ,
`control` varchar( 255 ) NOT NULL ,
`func` varchar( 255 ) NOT NULL ,
`data_id` int( 11 ) NOT NULL ,
`data` text NOT NULL ,
`stamp` datetime NOT NULL ,
`mode` varchar( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8;');

$c->setQuery('ALTER TABLE `pz_email` ADD INDEX `project_id` (`project_id`);');
$c->setQuery('ALTER TABLE `pz_email` ADD INDEX `user_id` (`user_id`);');
$c->setQuery('ALTER TABLE `pz_email` ADD INDEX `created` (`created`);');
$c->setQuery('ALTER TABLE `pz_email` ADD INDEX `project_user` (`user_id`,`project_id`);');


// -------------------------------------------------- create admin user

$c = rex_sql::factory();

$c->setQuery('select * from pz_user where id=?',array(1));
if($c->getRows() != 1) {
  $c->setQuery("INSERT INTO `pz_user` (`id`, `name`, `status`, `role`, `login`, `password`, `digest`, `login_tries`, `lasttrydate`, `session_id`, `cookiekey`, `admin`, `image_inline`, `image`, `created`, `updated`, `address_id`, `email`, `account_id`) VALUES
(1, 'admin', 1, 0, 'admin', 'admin', '11111111111', 0, 1323940123, '11111111111', '', '1', '', '1', '2012-08-11 14:00:00', '2012-08-11 05:00:00', 0, 'info@yakamara.de', 5);");
}

$password = "admin";
$password = rex_login::passwordHash($password);
$u = rex_sql::factory();
$u->setTable('pz_user');
$u->setWhere( array( 'id' => 1 ) );
$u->setValue('password', $password );
$u->setValue('digest', sha1($password));
$u->update();

// -------------------------------------------------- create dummy data / customer yakamara

$c->setQuery('select * from pz_customer where id=?',array(1));
if($c->getRows() == 0) {
  $c->setQuery("INSERT INTO `pz_customer` (`id`, `name`, `created`, `status`, `description`, `archived`, `updated`, `image_inline`, `image`) VALUES
  (1, 'Yakamara Media', '2012-08-11 05:00:00', 1, 'Yakamara Media GmbH & Co. KG', '', '', '', '');");

  $c->setQuery("INSERT INTO `pz_project` (`id`, `name`, `description`, `create_user_id`, `customer_id`, `label_id`, `created`, `updated`, `archived`, `has_emails`, `has_calendar`, `has_calendar_jobs`, `has_files`, `has_wiki`, `update_user_id`) VALUES
(1, 'Projekt Yakamara', '', 1, 1, 5, '2012-08-11 14:00:00', '2012-08-11 14:00:00', 0, 1, 1, 1, 1, 0, 1);");
  
  $c->setQuery("INSERT INTO `pz_project_user` (`id`, `user_id`, `project_id`, `created`, `updated`, `calendar`, `calendar_jobs`, `wiki`, `admin`, `webdav`, `caldav`, `caldav_jobs`, `files`, `emails`) VALUES
(1, 1, 1, '2012-08-11 14:00:00', '2012-08-11 14:00:00', 0, 0, 0, 1, 0, 0, 0, 0, 0);");

}

// -------------------------------------------------- create labels

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


// -------------------------------------------------- create WebDAV tmp Ordner

$dav_path = rex_path::addonData("prozer", "dav");
rex_dir::create($dav_path);

// -------------------------------------------------- Output Info

if($error != "") {
  $this->setProperty('installmsg', $error);
} else {
  $this->setProperty('install', true);
}
