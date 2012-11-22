

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_address`
--

CREATE TABLE IF NOT EXISTS `pz_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `firstname` text NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `updated_user_id` int(11) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_address_field`
--

CREATE TABLE IF NOT EXISTS `pz_address_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `label` text NOT NULL,
  `preferred` varchar(255) NOT NULL,
  `value_type` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_address_history`
--

CREATE TABLE IF NOT EXISTS `pz_address_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` longtext NOT NULL,
  `stamp` varchar(255) NOT NULL,
  `mode` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_alarm`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_alarm` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_attendee`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_attendee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('NEEDS-ACTION','ACCEPTED','TENTATIVE','DECLINED') NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `default` (`event_id`,`user_id`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_event`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_event` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_history`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` enum('add','update','delete') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_rule`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned DEFAULT NULL,
  `todo_id` int(10) unsigned DEFAULT NULL,
  `frequence` enum('DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_todo`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_todo` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_clipboard`
--

CREATE TABLE IF NOT EXISTS `pz_clipboard` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_customer`
--

CREATE TABLE IF NOT EXISTS `pz_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `image_inline` text NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_email`
--

CREATE TABLE IF NOT EXISTS `pz_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
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
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `spam` tinyint(4) NOT NULL DEFAULT '0',
  `message_id` varchar(255) NOT NULL,
  `draft` tinyint(4) NOT NULL DEFAULT '0',
  `reply_id` int(11) NOT NULL DEFAULT '0',
  `forward_id` int(11) NOT NULL DEFAULT '0',
  `readed` tinyint(4) NOT NULL DEFAULT '0',
  `content_type` text NOT NULL,
  `trash` tinyint(4) NOT NULL DEFAULT '0',
  `send` tinyint(4) NOT NULL DEFAULT '0',
  `from_emails` text NOT NULL,
  `to_emails` text NOT NULL,
  `cc_emails` text NOT NULL,
  `bcc_emails` text NOT NULL,
  `account_id` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_email_account`
--

CREATE TABLE IF NOT EXISTS `pz_email_account` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_label`
--

CREATE TABLE IF NOT EXISTS `pz_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(255) NOT NULL,
  `border` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_project`
--

CREATE TABLE IF NOT EXISTS `pz_project` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `create_user_id` int(6) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `label_id` int(11) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `has_emails` tinyint(4) NOT NULL,
  `has_calendar` tinyint(4) NOT NULL,
  `has_files` tinyint(4) NOT NULL,
  `has_wiki` tinyint(4) NOT NULL,
  `update_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`create_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_project_file`
--

CREATE TABLE IF NOT EXISTS `pz_project_file` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_project_parent` (`name`,`project_id`,`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_project_file_history`
--

CREATE TABLE IF NOT EXISTS `pz_project_file_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_project_sub`
--

CREATE TABLE IF NOT EXISTS `pz_project_sub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_project_user`
--

CREATE TABLE IF NOT EXISTS `pz_project_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `calendar` varchar(255) NOT NULL,
  `wiki` varchar(255) NOT NULL,
  `admin` varchar(255) NOT NULL,
  `webdav` varchar(255) NOT NULL,
  `caldav` varchar(255) NOT NULL,
  `caldav_jobs` varchar(255) NOT NULL,
  `files` varchar(255) NOT NULL,
  `emails` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_user`
--

CREATE TABLE IF NOT EXISTS `pz_user` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `role` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `digest` varchar(255) NOT NULL,
  `login_tries` int(11) NOT NULL DEFAULT '0',
  `lasttrydate` int(11) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_user_perm`
--

CREATE TABLE IF NOT EXISTS `pz_user_perm` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_user_role`
--

CREATE TABLE IF NOT EXISTS `pz_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `perms` text NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_wiki`
--

CREATE TABLE IF NOT EXISTS `pz_wiki` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `stamp` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  `vt` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_wiki_history`
--

CREATE TABLE IF NOT EXISTS `pz_wiki_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wiki_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stamp` varchar(255) NOT NULL,
  `mode` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;







-- --------------------------------------------------------

--
-- Dummydatensaetze
--

INSERT INTO `pz_user` (`id`, `name`, `status`, `role`, `login`, `password`, `digest`, `login_tries`, `lasttrydate`, `session_id`, `cookiekey`, `admin`, `image_inline`, `image`, `created`, `updated`, `address_id`, `email`, `account_id`) VALUES
(1, 'admin', 1, 0, 'admin', 'admin', 'b6660eaed16bbc782ab8a1ce76aabb1d', 0, 1323940123, '97f8218f54b6190a83748796b1eb957c', '', '1', '', '1', '2011-11-02 17:54:56', '2011-12-11 21:17:14', 0, 'info@yakamara.de', 5);

INSERT INTO `pz_customer` (`id`, `name`, `created`, `status`, `description`, `archived`, `updated`, `image_inline`, `image`) VALUES
(1, 'Yakamara Media', '2011-04-02 12:35:43', 1, 'Yakamara Media GmbH & Co. KG', '', '', '', '');

INSERT INTO `pz_label` (`id`, `color`, `border`, `name`, `created`, `updated`) VALUES
(1, '#fcb819', '#e3a617', 'Support', '', ''),
(2, '#e118fc', '#cb17e3', 'Kundenprojekte', '', ''),
(3, '#119194', '#0d797a', 'Private Projekte', '', ''),
(4, '#678820', '#536e1a', 'Öffentliche Projekte', '', ''),
(5, '#0f5dca', '#0c52b3', 'Agenturprojekte', '', ''),
(6, '#aa6600', '#950c00', 'Interne Projekte', '', '');





-- -------------------------------------------------------- alpha 15

ALTER TABLE `pz_address` ADD `responsible_user_id` INT NOT NULL AFTER `updated_user_id` ;
ALTER TABLE `pz_user` ADD `last_login` VARCHAR( 255 ) NOT NULL AFTER `lasttrydate` ;
ALTER TABLE `pz_project_file` ADD `filesize` INT NOT NULL AFTER `filename` , ADD `mimetype` VARCHAR( 255 ) NOT NULL AFTER `filesize` ;

CREATE TABLE IF NOT EXISTS `pz_project_history` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pz_user_history` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_user_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `pz_calendar_history` CHANGE `mode` `mode` ENUM( 'create', 'update', 'delete' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

UPDATE `pz_calendar_history` SET MODE = "create" WHERE MODE = "";






-- -------------------------------------------------------- alpha 17

ALTER TABLE `pz_calendar_history` ADD `project_id` INT NOT NULL ;
ALTER TABLE `pz_project_file_history` ADD `project_id` INT NOT NULL ;

ALTER TABLE `pz_wiki_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `pz_calendar_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `pz_address_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

CREATE TABLE IF NOT EXISTS `pz_history` (
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
) ENGINE = MYISAM DEFAULT CHARSET = utf8;

ALTER TABLE `pz_project_user` ADD `calendar_jobs` TINYINT NOT NULL AFTER `calendar` ;
ALTER TABLE `pz_project_user` CHANGE `calendar` `calendar` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `wiki` `wiki` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `admin` `admin` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `webdav` `webdav` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `caldav` `caldav` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `caldav_jobs` `caldav_jobs` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `files` `files` TINYINT NOT NULL ;
ALTER TABLE `pz_project_user` CHANGE `emails` `emails` TINYINT NOT NULL;

DROP TABLE `pz_address_history`;
DROP TABLE `pz_calendar_history`;
DROP TABLE `pz_wiki_history`;
DROP TABLE `pz_project_file_history`;
DROP TABLE `pz_project_history`;
DROP TABLE `pz_user_history`;

ALTER TABLE `pz_project` ADD `has_calendar_jobs` TINYINT NOT NULL AFTER `has_calendar` ;
