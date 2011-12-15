
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pz_calendar_alarm`
--

CREATE TABLE IF NOT EXISTS `pz_calendar_alarm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `trigger` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `emails` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `event` (`event_id`)
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
  `uri` varchar(255) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  `allday` tinyint(1) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `booked` tinyint(1) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `rule_id` int(10) unsigned NOT NULL,
  `base_from` datetime NOT NULL,
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
  `event_id` int(10) unsigned NOT NULL,
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
  UNIQUE KEY `event` (`event_id`)
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
  `hidden` varchar(255) NOT NULL,
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
  `archived` varchar(255) NOT NULL,
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
  `last_login` text NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_project_parent` (`name`,`project_id`,`parent_id`)
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`status`,`login`)
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





--
-- Daten für Tabelle `rex_xform_field`
--

TRUNCATE TABLE `rex_xform_field`;
TRUNCATE TABLE `rex_xform_relation`;
TRUNCATE TABLE `rex_xform_table`;

INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES
(3, 'pz_customer', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 0),
(4, 'pz_customer', 20, 'value', 'textarea', 'description', 'description', '', '0', '', '', '', '', '', 1, 0),
(12, 'pz_user', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 1),
(6, 'pz_label', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 0),
(7, 'pz_label', 20, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(8, 'pz_label', 30, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(11, 'pz_customer', 30, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(13, 'pz_user', 20, 'value', 'text', 'login', 'login', '', '0', '', '', '', '', '', 0, 1),
(14, 'pz_project', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 1),
(15, 'pz_project', 20, 'value', 'textarea', 'description', 'description', '', '0', '', '', '', '', '', 1, 1),
(16, 'pz_project', 30, 'value', 'be_manager_relation', 'label_id', 'label_id', 'pz_label', 'name', '0', '0', 'Bitte ein Label auswählen', '', '', 0, 0),
(17, 'pz_project', 40, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 1),
(18, 'pz_project', 50, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 1),
(31, 'pz_project', 90, 'value', 'checkbox', 'has_emails', 'has_emails', '1', '1', '0', '', '', '', '', 1, 0),
(20, 'pz_project', 60, 'value', 'be_manager_relation', 'customer_id', 'customer_id', 'pz_customer', 'name', '0', '1', '', '', '', 0, 0),
(21, 'pz_project', 70, 'value', 'checkbox', 'archived', 'archived', '1', '0', '0', '', '', '', '', 1, 1),
(22, 'pz_project_user', 10, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'login', '0', '0', '', '', '', 0, 0),
(23, 'pz_project_user', 20, 'value', 'be_manager_relation', 'project_id', 'project_id', 'pz_project', 'name', '0', '0', '', '', '', 0, 0),
(24, 'pz_project_user', 30, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(25, 'pz_project_user', 40, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(26, 'pz_project_user', 50, 'value', 'checkbox', 'calendar', 'calendar', '1', '1', '0', '', '', '', '', 0, 1),
(27, 'pz_project_user', 60, 'value', 'checkbox', 'wiki', 'wiki', '1', '1', '0', '', '', '', '', 0, 0),
(28, 'pz_project_user', 45, 'value', 'checkbox', 'admin', 'admin', '1', '0', '0', '', '', '', '', 0, 1),
(30, 'pz_project', 80, 'value', 'be_manager_relation', 'create_user_id', 'create_user_id', 'pz_user', 'name', '0', '0', 'BItte einen Ersteller auswählen', '', '', 1, 1),
(32, 'pz_project', 100, 'value', 'checkbox', 'has_calendar', 'has_calendar', '1', '1', '0', '', '', '', '', 1, 0),
(33, 'pz_project', 110, 'value', 'checkbox', 'has_files', 'has_files', '1', '1', '0', '', '', '', '', 1, 0),
(39, 'pz_customer', 40, 'value', 'checkbox', 'archived', 'archived', '1', '1', '0', '', '', '', '', 1, 1),
(40, 'pz_customer', 50, 'validate', 'empty', 'name', 'Bitte Namen eingeben', '', '', '', '', '', '', '', 1, 0),
(41, 'pz_customer', 60, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 1, 1),
(42, 'pz_project_user', 70, 'value', 'checkbox', 'webdav', 'webdav', '1', '1', '0', '', '', '', '', 1, 0),
(43, 'pz_project_user', 80, 'value', 'checkbox', 'caldav', 'caldav', '1', '1', '0', '', '', '', '', 1, 0),
(44, 'pz_project_user', 90, 'value', 'checkbox', 'caldav_jobs', 'caldav_jobs', '1', '1', '0', '', '', '', '', 1, 0),
(161, 'pz_user', 140, 'value', 'text', 'digest', 'digest', '', '0', '', '', '', '', '', 1, 1),
(46, 'pz_user', 40, 'value', 'checkbox', 'admin', 'admin', '1', '0', '0', '', '', '', '', 1, 1),
(47, 'pz_user', 50, 'value', 'text', 'password', 'password', '', '0', '', '', '', '', '', 1, 1),
(48, 'pz_project_user', 100, 'value', 'checkbox', 'files', 'files', '1', '1', '0', '', '', '', '', 1, 1),
(49, 'pz_address', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 1),
(50, 'pz_address', 20, 'value', 'text', 'firstname', 'firstname', '', '0', '', '', '', '', '', 0, 1),
(51, 'pz_address', 60, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(52, 'pz_address', 70, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(53, 'pz_address', 80, 'value', 'be_manager_relation', 'created_user_id', 'created_user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(54, 'pz_address', 90, 'value', 'be_manager_relation', 'updated_user_id', 'updated_user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(55, 'pz_address_field', 10, 'value', 'be_manager_relation', 'address_id', 'address_id', 'pz_address', 'name', '0', '0', '', '', '', 0, 0),
(56, 'pz_address_field', 20, 'value', 'select', 'type', 'type', 'address,phone,email', '0', '', '0', '3', '', '', 0, 0),
(57, 'pz_address_field', 30, 'value', 'text', 'label', 'label', '', '0', '', '', '', '', '', 0, 0),
(82, 'pz_address_field', 50, 'value', 'text', 'value', 'value', '', '0', '', '', '', '', '', 0, 0),
(83, 'pz_address_field', 60, 'value', 'text', 'value_type', 'value_type', '', '0', '', '', '', '', '', 0, 0),
(61, 'pz_address', 100, 'value', 'text', 'uri', 'uri', '', '0', '', '', '', '', '', 1, 0),
(62, 'pz_address', 30, 'value', 'text', 'company', 'company', '', '0', '', '', '', '', '', 0, 0),
(63, 'pz_address', 40, 'value', 'checkbox', 'is_company', 'is_company', '1', '0', '0', '', '', '', '', 0, 0),
(64, 'pz_address', 50, 'value', 'textarea', 'note', 'note', '', '0', '', '', '', '', '', 1, 0),
(84, 'pz_address_history', 10, 'value', 'be_manager_relation', 'address_id', 'address_id', 'pz_address', 'name', '0', '0', '', '', '', 0, 0),
(66, 'pz_address_field', 40, 'value', 'checkbox', 'preferred', 'preferred', '1', '0', '0', '', '', '', '', 0, 0),
(67, 'pz_address', 110, 'value', 'text', 'additional_names', 'additional_names', '', '0', '', '', '', '', '', 1, 0),
(68, 'pz_address', 120, 'value', 'text', 'prefix', 'prefix', '', '0', '', '', '', '', '', 1, 0),
(69, 'pz_address', 130, 'value', 'text', 'suffix', 'suffix', '', '0', '', '', '', '', '', 1, 0),
(71, 'pz_address', 150, 'value', 'text', 'nickname', 'nickname', '', '0', '', '', '', '', '', 1, 0),
(72, 'pz_address', 140, 'value', 'text', 'birthname', 'birthname', '', '0', '', '', '', '', '', 1, 0),
(73, 'pz_address', 160, 'value', 'text', 'phonetic_name', 'phonetic_name', '', '0', '', '', '', '', '', 1, 0),
(75, 'pz_address', 170, 'value', 'text', 'phonetic_firstname', 'phonetic_firstname', '', '0', '', '', '', '', '', 1, 0),
(76, 'pz_address', 180, 'value', 'text', 'birthday', 'birthday', '', '0', '', '', '', '', '', 1, 0),
(77, 'pz_address', 190, 'value', 'text', 'department', 'department', '', '0', '', '', '', '', '', 1, 0),
(78, 'pz_address', 200, 'value', 'text', 'title', 'title', '', '0', '', '', '', '', '', 1, 0),
(85, 'pz_address_history', 20, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(86, 'pz_address_history', 30, 'value', 'textarea', 'data', 'data', '', '0', '', '', '', '', '', 1, 0),
(87, 'pz_address_history', 40, 'value', 'stamp', 'stamp', 'stamp', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(88, 'pz_user', 60, 'value', 'checkbox', 'status', 'Aktiv', '1', '0', '0', '', '', '', '', 1, 1),
(89, 'pz_user', 70, 'value', 'text', 'session_id', 'Session', '', '0', '', '', '', '', '', 1, 1),
(90, 'pz_user', 80, 'value', 'text', 'cookiekey', 'cookiekey', '', '0', '', '', '', '', '', 1, 1),
(91, 'pz_project', 120, 'value', 'checkbox', 'has_wiki', 'has_wiki', '1', '0', '0', '', '', '', '', 1, 1),
(92, 'pz_project_user', 110, 'value', 'checkbox', 'emails', 'emails', '1', '0', '0', '', '', '', '', 0, 1),
(93, 'pz_project', 85, 'value', 'be_manager_relation', 'update_user_id', 'update_user_id', 'pz_user', 'name', '0', '0', 'BItte einen Updater auswählen', '', '', 1, 1),
(94, 'pz_wiki', 30, 'value', 'text', 'title', 'title', '', '0', '', '', '', '', '', 0, 1),
(95, 'pz_wiki', 40, 'value', 'textarea', 'text', 'text', '', '0', '', '', '', '', '', 1, 0),
(174, 'pz_wiki', 50, 'value', 'textarea', 'vt', 'vt', '', '0', '', '', '', '', '', 1, 0),
(97, 'pz_wiki', 60, 'value', 'stamp', 'stamp', 'stamp', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(98, 'pz_wiki', 10, 'value', 'be_manager_relation', 'project_id', 'project_id', 'pz_project', 'name', '0', '0', '', '', '', 0, 0),
(101, 'pz_address', 210, 'value', 'textarea', 'photo', 'photo', '', '0', '', '', '', '', '', 1, 0),
(102, 'pz_email_account', 10, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 1),
(103, 'pz_email_account', 20, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 1),
(109, 'pz_email_account', 30, 'value', 'text', 'email', 'email', '', '0', '', '', '', '', '', 1, 1),
(150, 'pz_email_account', 130, 'value', 'text', 'host', 'host', '', '0', '', '', '', '', '', 0, 1),
(106, 'pz_email_account', 50, 'value', 'text', 'login', 'login', '', '0', '', '', '', '', '', 1, 1),
(107, 'pz_email_account', 60, 'value', 'text', 'password', 'password', '', '0', '', '', '', '', '', 1, 1),
(108, 'pz_email_account', 70, 'value', 'text', 'smtp', 'smtp', '', '0', '', '', '', '', '', 1, 1),
(110, 'pz_email_account', 80, 'value', 'textarea', 'signature', 'signature', '', '0', '', '', '', '', '', 1, 1),
(111, 'pz_user', 90, 'value', 'textarea', 'image_inline', 'image_inline', '', '0', '', '', '', '', '', 1, 0),
(112, 'pz_user', 100, 'value', 'checkbox', 'image', 'image', '', '0', '0', '', '', '', '', 1, 1),
(113, 'pz_customer', 70, 'value', 'textarea', 'image_inline', 'image_inline', '', '0', '', '', '', '', '', 1, 0),
(114, 'pz_customer', 80, 'value', 'checkbox', 'image', 'image', '', '0', '0', '', '', '', '', 1, 1),
(115, 'pz_email_account', 90, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(116, 'pz_email_account', 100, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(117, 'pz_email_account', 110, 'value', 'checkbox', 'delete_emails', 'delete_emails', '1', '0', '0', '', '', '', '', 1, 1),
(118, 'pz_email_account', 120, 'value', 'checkbox', 'status', 'aktiviert', '1', '0', '0', '', '', '', '', 1, 1),
(119, 'pz_address_history', 50, 'value', 'text', 'mode', 'mode', '', '0', '', '', '', '', '', 0, 0),
(120, 'pz_wiki_history', 10, 'value', 'be_manager_relation', 'wiki_id', 'wiki_id', 'pz_wiki', 'title', '0', '0', '', '', '', 0, 0),
(121, 'pz_wiki_history', 20, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(122, 'pz_wiki_history', 30, 'value', 'stamp', 'stamp', 'stamp', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(123, 'pz_wiki_history', 40, 'value', 'text', 'mode', 'mode', '', '0', '', '', '', '', '', 0, 0),
(124, 'pz_wiki_history', 50, 'value', 'textarea', 'data', 'data', '', '0', '', '', '', '', '', 0, 0),
(125, 'pz_email', 10, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'name', '0', '1', '', '', '', 0, 1),
(126, 'pz_email', 20, 'value', 'be_manager_relation', 'project_id', 'project_id', 'pz_project', 'name', '0', '1', '', '', '', 0, 1),
(127, 'pz_email', 30, 'value', 'text', 'subject', 'subject', '', '0', '', '', '', '', '', 0, 1),
(128, 'pz_email', 40, 'value', 'textarea', 'header', 'header', '', '0', '', '', '', '', '', 1, 1),
(129, 'pz_email', 50, 'value', 'text', 'from', 'from', '', '0', '', '', '', '', '', 1, 1),
(130, 'pz_email', 60, 'value', 'text', 'to', 'to', '', '0', '', '', '', '', '', 1, 1),
(131, 'pz_email', 70, 'value', 'text', 'reply_to', 'reply_to', '', '0', '', '', '', '', '', 1, 1),
(132, 'pz_email', 80, 'value', 'text', 'cc', 'cc', '', '0', '', '', '', '', '', 1, 1),
(133, 'pz_email', 90, 'value', 'text', 'bcc', 'bcc', '', '0', '', '', '', '', '', 1, 1),
(134, 'pz_email', 100, 'value', 'text', 'date', 'date', '', '0', '', '', '', '', '', 1, 1),
(135, 'pz_email', 110, 'value', 'textarea', 'body', 'body', '', '0', '', '', '', '', '', 1, 1),
(136, 'pz_email', 120, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 1, 1),
(137, 'pz_email', 130, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 1, 1),
(138, 'pz_email', 140, 'value', 'text', 'importance', 'importance', '', '0', '', '', '', '', '', 1, 1),
(139, 'pz_email', 150, 'value', 'text', 'message_id', 'message_id', '', '0', '', '', '', '', '', 1, 1),
(140, 'pz_email', 160, 'value', 'checkbox', 'readed', 'readed', '1', '0', '0', '', '', '', '', 1, 1),
(141, 'pz_email', 170, 'value', 'text', 'content_type', 'content_type', '', '0', '', '', '', '', '', 1, 1),
(142, 'pz_email', 180, 'value', 'checkbox', 'trash', 'trash', '1', '0', '0', '', '', '', '', 1, 1),
(143, 'pz_email', 190, 'value', 'checkbox', 'send', 'send', '1', '0', '0', '', '', '', '', 1, 1),
(144, 'pz_email', 200, 'value', 'text', 'from_emails', 'from_emails', '', '0', '', '', '', '', '', 1, 1),
(145, 'pz_email', 210, 'value', 'text', 'to_emails', 'to_emails', '', '0', '', '', '', '', '', 1, 1),
(146, 'pz_email', 220, 'value', 'text', 'cc_emails', 'cc_emails', '', '0', '', '', '', '', '', 1, 1),
(147, 'pz_email', 230, 'value', 'text', 'bcc_emails', 'bcc_emails', '', '0', '', '', '', '', '', 1, 1),
(148, 'pz_email', 240, 'value', 'checkbox', 'spam', 'spam', '1', '0', '0', '', '', '', '', 1, 1),
(149, 'pz_email', 250, 'value', 'checkbox', 'deleted', 'deleted', '1', '0', '0', '', '', '', '', 1, 1),
(151, 'pz_email_account', 140, 'value', 'select', 'mailboxtype', 'mailboxtype', 'pop3,imap', '0', '', '0', '', '', '', 1, 1),
(152, 'pz_email_account', 150, 'value', 'checkbox', 'ssl', 'ssl', '1', '0', '0', '', '', '', '', 1, 1),
(153, 'pz_user', 110, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 1),
(154, 'pz_user', 120, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 1),
(155, 'pz_email', 25, 'value', 'be_manager_relation', 'account_id', 'account_id', 'pz_email_account', 'name', '0', '1', '', '', '', 1, 1),
(156, 'pz_email', 260, 'value', 'checkbox', 'status', 'status / erledigt', '1', '0', '0', '', '', '', '', 1, 1),
(157, 'pz_address', 220, 'value', 'textarea', 'vt', 'vt', '', '0', '', '', '', '', '', 1, 0),
(159, 'pz_user', 25, 'validate', 'unique', 'login', 'login already exists', '', '', '', '', '', '', '', 1, 0),
(160, 'pz_user', 22, 'validate', 'empty', 'login', 'enter login', '', '', '', '', '', '', '', 1, 0),
(162, 'pz_user', 150, 'value', 'text', 'email', 'email', '', '0', '', '', '', '', '', 1, 1),
(163, 'pz_email_account', 160, 'value', 'text', 'last_login', 'last_login', '', '0', '', '', '', '', '', 0, 1),
(164, 'pz_email_account', 170, 'value', 'checkbox', 'login_failed', 'login_failed', '1', '0', '0', '', '', '', '', 0, 1),
(165, 'pz_email', 115, 'value', 'textarea', 'body_html', 'body_html', '', '0', '', '', '', '', '', 1, 1),
(166, 'pz_email', 132, 'value', 'be_manager_relation', 'create_user_id', 'create_user_id', 'pz_user', 'name', '0', '0', '', '', '', 1, 1),
(167, 'pz_email', 134, 'value', 'be_manager_relation', 'update_user_id', 'update_user_id', 'pz_user', 'name', '0', '0', '', '', '', 1, 1),
(168, 'pz_email', 270, 'value', 'checkbox', 'draft', 'draft', '1', '1', '0', '', '', '', '', 1, 1),
(169, 'pz_email', 280, 'value', 'text', 'reply_id', 'reply_id', '', '0', '', '', '', '', '', 1, 1),
(170, 'pz_email', 290, 'value', 'text', 'forward_id', 'forward_id', '', '0', '', '', '', '', '', 1, 1),
(171, 'pz_user', 160, 'value', 'be_manager_relation', 'account_id', 'account_id', 'pz_email_account', 'name', '0', '1', '-', '', '', 1, 1),
(172, 'pz_email', 300, 'value', 'text', 'replied_id', 'replied_id', '', '0', '', '', '', '', '', 1, 1),
(173, 'pz_email', 310, 'value', 'text', 'forwarded_id', 'forwarded_id', '', '0', '', '', '', '', '', 1, 1),
(199, 'pz_project_file', 70, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 0),
(198, 'pz_project_file', 60, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 0),
(197, 'pz_project_file', 50, 'value', 'checkbox', 'is_directory', 'is_directory', '1', '0', '0', '', '', '', '', 0, 0),
(185, 'pz_clipboard', 10, 'value', 'be_manager_relation', 'user_id', 'user_id', 'pz_user', 'name', '0', '0', '', '', '', 1, 1),
(186, 'pz_clipboard', 20, 'value', 'text', 'filename', 'filename', '', '0', '', '', '', '', '', 0, 1),
(187, 'pz_clipboard', 30, 'value', 'stamp', 'created', 'created', 'mysql_datetime', '0', '1', '', '', '', '', 0, 1),
(188, 'pz_clipboard', 40, 'value', 'stamp', 'updated', 'updated', 'mysql_datetime', '0', '0', '', '', '', '', 0, 1),
(189, 'pz_clipboard', 50, 'value', 'text', 'content_length', 'content_length', '', '0', '', '', '', '', '', 0, 1),
(190, 'pz_clipboard', 60, 'value', 'text', 'content_type', 'content_type', '', '0', '', '', '', '', '', 0, 1),
(191, 'pz_email', 320, 'value', 'text', 'clip_ids', 'clip_ids', '', '0', '', '', '', '', '', 1, 1),
(192, 'pz_clipboard', 70, 'value', 'checkbox', 'hidden', 'hidden', '1', '0', '0', '', '', '', '', 0, 1),
(193, 'pz_project_file', 10, 'value', 'text', 'name', 'name', '', '0', '', '', '', '', '', 0, 1),
(195, 'pz_project_file', 30, 'value', 'be_manager_relation', 'project_id', 'project_id', 'pz_project', 'name', '0', '0', '', '', '', 0, 0),
(196, 'pz_project_file', 40, 'value', 'textarea', 'comment', 'comment', '', '0', '', '', '', '', '', 0, 1),
(200, 'pz_project_file', 80, 'value', 'be_manager_relation', 'created_user_id', 'created_user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(201, 'pz_project_file', 90, 'value', 'be_manager_relation', 'updated_user_id', 'updated_user_id', 'pz_user', 'name', '0', '0', '', '', '', 0, 0),
(202, 'pz_project_file', 20, 'value', 'be_manager_relation', 'parent_id', 'parent_id', 'pz_project_file', 'name', '0', '1', '', '', '', 0, 0);

--
-- Daten für Tabelle `rex_xform_table`
--

INSERT INTO `rex_xform_table` (`id`, `status`, `table_name`, `name`, `description`, `list_amount`, `prio`, `search`, `hidden`, `export`, `import`) VALUES
(2, 1, 'pz_user', 'User', '', 100, 100, 1, 0, 1, 1),
(3, 1, 'pz_project', 'projects', '', 50, 200, 1, 0, 1, 1),
(5, 1, 'pz_customer', 'customer', '', 50, 300, 0, 0, 1, 1),
(9, 1, 'pz_email', 'emails', '', 100, 400, 1, 0, 1, 1),
(8, 1, 'pz_label', 'Label', '', 50, 50, 0, 0, 0, 0),
(10, 1, 'pz_project_user', 'pz_project_user', '', 50, 600, 0, 0, 1, 1),
(11, 1, 'pz_address', 'Adressen', '', 100, 700, 1, 0, 1, 1),
(12, 1, 'pz_address_field', 'Adressfelder', '', 100, 800, 1, 0, 1, 1),
(13, 1, 'pz_address_history', 'Adresshistorie', '', 100, 900, 0, 0, 1, 1),
(14, 1, 'pz_wiki', 'pz_wiki', '', 100, 1000, 1, 0, 1, 1),
(15, 1, 'pz_email_account', 'pz_email_account', '', 50, 410, 1, 0, 1, 1),
(16, 1, 'pz_wiki_history', 'pz_wiki_history', '', 50, 1100, 1, 0, 1, 1),
(20, 1, 'pz_clipboard', 'pz_clipboard', '', 50, 200, 1, 0, 1, 0),
(21, 1, 'pz_project_file', 'pz_project_file', '', 100, 1200, 1, 0, 1, 1);

