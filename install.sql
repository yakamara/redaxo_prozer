-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2016 at 12:40 PM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `redaxo_4_6_0`
--

-- --------------------------------------------------------

--
-- Table structure for table `pz_address`
--

CREATE TABLE `pz_address` (
  `id` int(11) NOT NULL,
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
  `vt_email` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_address_field`
--

CREATE TABLE `pz_address_field` (
  `id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `label` text NOT NULL,
  `preferred` varchar(255) NOT NULL,
  `value_type` text NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_calendar_alarm`
--

CREATE TABLE `pz_calendar_alarm` (
  `id` int(10) unsigned NOT NULL,
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
  `default` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_calendar_attendee`
--

CREATE TABLE `pz_calendar_attendee` (
  `id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'REQ-PARTICIPANT',
  `status` enum('NEEDS-ACTION','ACCEPTED','TENTATIVE','DECLINED') NOT NULL,
  `timestamp` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_calendar_event`
--

CREATE TABLE `pz_calendar_event` (
  `id` int(10) unsigned NOT NULL,
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
  `vt` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_calendar_rule`
--

CREATE TABLE `pz_calendar_rule` (
  `id` int(10) unsigned NOT NULL,
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
  `exceptions` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_calendar_todo`
--

CREATE TABLE `pz_calendar_todo` (
  `id` int(10) unsigned NOT NULL,
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
  `vt` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_clipboard`
--

CREATE TABLE `pz_clipboard` (
  `id` int(11) NOT NULL,
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
  `uri` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_config`
--

CREATE TABLE `pz_config` (
  `id` int(10) unsigned NOT NULL,
  `namespace` varchar(75) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_customer`
--

CREATE TABLE `pz_customer` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `updated` varchar(255) NOT NULL,
  `image_inline` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_customer`
--

INSERT INTO `pz_customer` (`id`, `name`, `created`, `status`, `description`, `archived`, `updated`, `image_inline`, `image`) VALUES
(1, 'Yakamara Media', '2016-01-01 12:00:00', 1, 'Yakamara Media GmbH & Co. KG', 0, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `pz_email`
--

CREATE TABLE `pz_email` (
  `id` int(11) NOT NULL,
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
  `has_attachments` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_email_account`
--

CREATE TABLE `pz_email_account` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `host` varchar(255) NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `smtp` text NOT NULL,
  `smtp_login` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
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
  `login_failed` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_history`
--

CREATE TABLE `pz_history` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `control` varchar(255) NOT NULL,
  `func` varchar(255) NOT NULL,
  `data_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=485 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_history`
--

-- --------------------------------------------------------

--
-- Table structure for table `pz_label`
--

CREATE TABLE `pz_label` (
  `id` int(11) NOT NULL,
  `color` varchar(255) NOT NULL,
  `border` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `updated` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_label`
--

INSERT INTO `pz_label` (`id`, `color`, `border`, `name`, `created`, `updated`) VALUES
(1, '#fcb819', '#e3a617', 'Support', '', ''),
(2, '#e118fc', '#cb17e3', 'Kundenprojekte', '', ''),
(3, '#119194', '#0d797a', 'Private Projekte', '', ''),
(4, '#678820', '#536e1a', 'Öffentliche Projekte', '', ''),
(5, '#0f5dca', '#0c52b3', 'Agenturprojekte', '', ''),
(6, '#aa6600', '#950c00', 'Interne Projekte', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `pz_project`
--

CREATE TABLE `pz_project` (
  `id` int(6) NOT NULL,
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
  `has_calendar_jobs` tinyint(4) NOT NULL,
  `has_files` tinyint(4) NOT NULL,
  `has_wiki` tinyint(4) NOT NULL,
  `update_user_id` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_project`
--

INSERT INTO `pz_project` (`id`, `name`, `description`, `create_user_id`, `customer_id`, `label_id`, `created`, `updated`, `archived`, `has_emails`, `has_calendar`, `has_calendar_jobs`, `has_files`, `has_wiki`, `update_user_id`) VALUES
(1, 'Projekt Yakamara', '', 1, 1, 5, '2012-08-11 14:00:00', '2012-08-11 14:00:00', 0, 1, 1, 1, 1, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pz_project_file`
--

CREATE TABLE `pz_project_file` (
  `id` int(10) unsigned NOT NULL,
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
  `mimetype` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_project_sub`
--

CREATE TABLE `pz_project_sub` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_project_user`
--

CREATE TABLE `pz_project_user` (
  `id` int(11) NOT NULL,
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
  `emails` tinyint(4) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_project_user`
--

INSERT INTO `pz_project_user` (`id`, `user_id`, `project_id`, `created`, `updated`, `calendar`, `calendar_jobs`, `wiki`, `admin`, `webdav`, `caldav`, `caldav_jobs`, `files`, `emails`) VALUES
(1, 1, 1, '2012-08-11 14:00:00', '2012-08-11 14:00:00', 0, 0, 0, 1, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pz_space`
--

CREATE TABLE `pz_space` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `shorttext` text NOT NULL,
  `text` text NOT NULL,
  `position` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `create_user_id` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  `update_user_id` int(10) unsigned NOT NULL,
  `admin` tinyint(1) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_space`
--


-- --------------------------------------------------------

--
-- Table structure for table `pz_user`
--

CREATE TABLE `pz_user` (
  `id` int(6) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `role` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `digest` varchar(255) NOT NULL,
  `login_tries` int(11) NOT NULL DEFAULT '0',
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
  `comment` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pz_user`
--

INSERT INTO `pz_user` (`id`, `name`, `status`, `role`, `login`, `password`, `digest`, `login_tries`, `lasttrydate`, `last_login`, `session_id`, `cookiekey`, `admin`, `image_inline`, `image`, `created`, `updated`, `address_id`, `email`, `account_id`, `config`, `perms`, `comment`) VALUES
(1, 'admin', 1, 0, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', '7b2e9f54cdff413fcde01f330af6896c3cd7e6cd', 0, 1447422998, '2015-11-13 14:56:38', '1ea592a8db263928ed237e182e4a6771', '', '1', '', '', '', '', 0, 'info@yakamara.de', 0, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `pz_user_perm`
--

CREATE TABLE `pz_user_perm` (
  `id` int(11) NOT NULL,
  `user_id` text,
  `to_user_id` text,
  `calendar_read` tinyint(4) NOT NULL,
  `calendar_write` tinyint(4) NOT NULL,
  `email_read` tinyint(4) NOT NULL,
  `email_write` tinyint(4) NOT NULL,
  `created` varchar(255) DEFAULT NULL,
  `updated` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_user_role`
--

CREATE TABLE `pz_user_role` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `perms` text NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pz_wiki`
--

CREATE TABLE `pz_wiki` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `position` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  `vt` text NOT NULL,
  `created` datetime NOT NULL,
  `create_user_id` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  `update_user_id` int(10) unsigned NOT NULL,
  `admin` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pz_address`
--
ALTER TABLE `pz_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `pz_address_field`
--
ALTER TABLE `pz_address_field`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_calendar_alarm`
--
ALTER TABLE `pz_calendar_alarm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD KEY `event` (`event_id`),
  ADD KEY `todo_id` (`todo_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pz_calendar_attendee`
--
ALTER TABLE `pz_calendar_attendee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `default` (`event_id`,`user_id`,`email`);

--
-- Indexes for table `pz_calendar_event`
--
ALTER TABLE `pz_calendar_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `default` (`rule_id`,`from`,`to`,`project_id`),
  ADD KEY `uri` (`uri`),
  ADD KEY `project` (`project_id`);

--
-- Indexes for table `pz_calendar_rule`
--
ALTER TABLE `pz_calendar_rule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event` (`event_id`),
  ADD KEY `todo_id` (`todo_id`);

--
-- Indexes for table `pz_calendar_todo`
--
ALTER TABLE `pz_calendar_todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uri` (`uri`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `pz_clipboard`
--
ALTER TABLE `pz_clipboard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_config`
--
ALTER TABLE `pz_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_key` (`namespace`,`key`);

--
-- Indexes for table `pz_customer`
--
ALTER TABLE `pz_customer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `pz_email`
--
ALTER TABLE `pz_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `createdmail` (`draft`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created` (`created`),
  ADD KEY `project_user` (`user_id`,`project_id`);

--
-- Indexes for table `pz_email_account`
--
ALTER TABLE `pz_email_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_history`
--
ALTER TABLE `pz_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `control data_id` (`control`,`data_id`);

--
-- Indexes for table `pz_label`
--
ALTER TABLE `pz_label`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_project`
--
ALTER TABLE `pz_project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`,`create_user_id`);

--
-- Indexes for table `pz_project_file`
--
ALTER TABLE `pz_project_file`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_project_parent` (`name`,`project_id`,`parent_id`);

--
-- Indexes for table `pz_project_sub`
--
ALTER TABLE `pz_project_sub`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_project_user`
--
ALTER TABLE `pz_project_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_space`
--
ALTER TABLE `pz_space`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `pz_user`
--
ALTER TABLE `pz_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`,`status`,`login`);

--
-- Indexes for table `pz_user_perm`
--
ALTER TABLE `pz_user_perm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_user_role`
--
ALTER TABLE `pz_user_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pz_wiki`
--
ALTER TABLE `pz_wiki`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pz_address`
--
ALTER TABLE `pz_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_address_field`
--
ALTER TABLE `pz_address_field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_calendar_alarm`
--
ALTER TABLE `pz_calendar_alarm`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_calendar_attendee`
--
ALTER TABLE `pz_calendar_attendee`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_calendar_event`
--
ALTER TABLE `pz_calendar_event`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_calendar_rule`
--
ALTER TABLE `pz_calendar_rule`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_calendar_todo`
--
ALTER TABLE `pz_calendar_todo`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_clipboard`
--
ALTER TABLE `pz_clipboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_config`
--
ALTER TABLE `pz_config`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_customer`
--
ALTER TABLE `pz_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pz_email`
--
ALTER TABLE `pz_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_email_account`
--
ALTER TABLE `pz_email_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_history`
--
ALTER TABLE `pz_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=485;
--
-- AUTO_INCREMENT for table `pz_label`
--
ALTER TABLE `pz_label`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `pz_project`
--
ALTER TABLE `pz_project`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pz_project_file`
--
ALTER TABLE `pz_project_file`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_project_sub`
--
ALTER TABLE `pz_project_sub`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_project_user`
--
ALTER TABLE `pz_project_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pz_space`
--
ALTER TABLE `pz_space`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `pz_user`
--
ALTER TABLE `pz_user`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pz_user_perm`
--
ALTER TABLE `pz_user_perm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_user_role`
--
ALTER TABLE `pz_user_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pz_wiki`
--
ALTER TABLE `pz_wiki`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;