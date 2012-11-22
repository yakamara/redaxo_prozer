<?php

$error = '';

// bisher installierte Version:
$version = $this->getVersion();


// ------------------------------------------------- alpha5

if(version_compare($version, '2.0 alpha5', '<'))
{

	// user profile
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` DROP `config`;',array());
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` ADD `config` TEXT NOT NULL;',array());
	rex_sql::factory()->setQuery('REPLACE INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(203, "pz_user", 170, "value", "textarea", "config", "config", "", "0", "", "", "", "", "", 1, 1);',array());

	// user perms
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` DROP `perms`;',array());
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` ADD `perms` TEXT NOT NULL;',array());
	rex_sql::factory()->setQuery('REPLACE INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(204, "pz_user", 180, "value", "textarea", "perms", "perms", "", "0", "", "", "", "", "", 1, 1);',array());

}

// ------------------------------------------------- alpha7

if(version_compare($version, '2.0 alpha7', '<'))
{

	// clipboard
	rex_sql::factory()->setQuery('ALTER TABLE `pz_clipboard` CHANGE `hidden` `hidden` TINYINT( 1 ) NOT NULL;',array());
	rex_sql::factory()->setQuery('ALTER TABLE `pz_clipboard` ADD `open` TINYINT( 1 ) NOT NULL , ADD `online_date` DATETIME NOT NULL , ADD `offline_date` DATETIME NOT NULL , ADD `uri` TEXT NOT NULL;',array());

}

// ------------------------------------------------- alpha8

if(version_compare($version, '2.0 alpha8', '<'))
{
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` ADD `comment` TEXT NOT NULL;',array());

}

// ------------------------------------------------- alpha10

if(version_compare($version, '2.0 alpha10', '<'))
{
    $sql = rex_sql::factory();

    // project files

    $sql->setQuery('ALTER TABLE `pz_project_file` ADD `filename` VARCHAR(255) NOT NULL;',array());
    $sql->setQuery('REPLACE INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(205, "pz_project_file", 55, "value", "text", "filename", "filename", "", "0", "", "", "", "", "", 0, 1);',array());

    $sql->setQuery('
        CREATE TABLE `pz_project_file_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `file_id` text,
            `user_id` text,
            `data` text,
            `stamp` varchar(255) DEFAULT NULL,
            `mode` text,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    ');
    $sql->setQuery('INSERT INTO `rex_xform_table` (`status`, `table_name`, `name`, `description`, `list_amount`, `prio`, `search`, `hidden`, `export`, `import`) VALUES (1, "pz_project_file_history", "pz_project_file_history", "", 100, 1300, 1, 0, 1, 1)');

    $sql->setQuery('UPDATE pz_project_file SET filename = DATE_FORMAT(updated, "%Y%m%d%H%i%s") WHERE is_directory = 0');
    $sql->setQuery('SELECT id, project_id, filename FROM pz_project_file WHERE is_directory = 0');
    foreach($sql as $row)
    {
        $id = $row->getValue('id');
        $projectId = $row->getValue('project_id');
        $filename = $row->getValue('filename');
        $dir = rex_path::addonData('prozer', 'projects/'. $projectId .'/files/');
        rex_dir::create($dir .'_'. $id);
        rename($dir . $id, $dir .'_'. $id .'/'. $filename);
        rename($dir .'_'. $id, $dir . $id);
    }

    $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(206, "pz_project_file_history", 10, "value", "be_manager_relation", "file_id", "file_id", "pz_project_file", "id", "0", "0", "", "", "", 0, 1);');
    $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(207, "pz_project_file_history", 20, "value", "be_manager_relation", "user_id", "user_id", "pz_user", "name", "0", "0", "", "", "", 0, 1);');
    $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(208, "pz_project_file_history", 30, "value", "textarea", "data", "data", "", "0", "", "", "", "", "", 1, 1);');
    $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(209, "pz_project_file_history", 40, "value", "stamp", "stamp", "stamp", "mysql_datetime", "0", "1", "", "", "", "", 0, 0);');
    $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(210, "pz_project_file_history", 50, "value", "text", "mode", "mode", "", "0", "", "", "", "", "", 0, 0);');

    // customer

    $sql->setQuery('ALTER TABLE `pz_customer` CHANGE `archived` `archived` TINYINT( 4 ) NOT NULL;');

    // user perm

	$sql->setQuery('CREATE TABLE IF NOT EXISTS `pz_user_perm` (
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
		');

	$sql->setQuery('INSERT INTO `rex_xform_table` (`id`, `status`, `table_name`, `name`, `description`, `list_amount`, `prio`, `search`, `hidden`, `export`, `import`) VALUES(23, 1, "pz_user_perm", "Userrechte", "", 50, 150, 1, 0, 0, 0);');

	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(211, "pz_user_perm", 10, "value", "be_manager_relation", "user_id", "User", "pz_user", "name", "0", "0", "", "", "", 0, 0);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(212, "pz_user_perm", 20, "value", "be_manager_relation", "to_user_id", "Perm to User", "pz_user", "name", "0", "0", "", "", "", 0, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(213, "pz_user_perm", 30, "value", "checkbox", "calendar_read", "calendar_read", "1", "0", "0", "", "", "", "", 1, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(214, "pz_user_perm", 40, "value", "checkbox", "calendar_write", "calendar_write", "1", "0", "0", "", "", "", "", 1, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(215, "pz_user_perm", 50, "value", "checkbox", "email_read", "email_read", "1", "0", "0", "", "", "", "", 1, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(216, "pz_user_perm", 60, "value", "checkbox", "email_write", "email_write", "", "0", "0", "", "", "", "", 1, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(217, "pz_user_perm", 70, "value", "stamp", "created", "created", "mysql_datetime", "0", "1", "", "", "", "", 0, 1);');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(218, "pz_user_perm", 80, "value", "stamp", "updated", "updated", "mysql_datetime", "0", "0", "", "", "", "", 0, 1);');

}

// ------------------------------------------------- alpha11

// ------------------------------------------------- alpha12

if(version_compare($version, '2.0 alpha12', '<'))
{

	$sql = rex_sql::factory();
	$sql->setQuery('ALTER TABLE `pz_email` ADD `has_attachments` TINYINT NOT NULL;');
	$sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(219, "pz_email", 330, "value", "checkbox", "has_attachments", "has_attachments", "1", "0", "0", "", "", "", "", 1, 1);');

	$sql->setQuery('ALTER TABLE `pz_address` ADD `vt_email` TEXT NOT NULL;');
  $sql->setQuery('INSERT INTO `rex_xform_field` (`id`, `table_name`, `prio`, `type_id`, `type_name`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `list_hidden`, `search`) VALUES(220, "pz_address", 230, "value", "textarea", "vt_email", "vt_email", "", "0", "", "", "", "", "", 1, 1);');

}


// ------------------------------------------------- alpha13

if(version_compare($version, '2.0 alpha13', '<'))
{
  $sql = rex_sql::factory();
  $sql->setQuery('ALTER TABLE `pz_email_account` CHANGE `last_login` `last_login` DATETIME NOT NULL;');
  $sql->setQuery('ALTER TABLE `pz_email_account` ADD `last_login_finished` DATETIME NOT NULL AFTER `last_login`;');

  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `uri` `uri` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `project_id` `project_id` INT( 10 ) UNSIGNED NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `allday` `allday` TINYINT( 1 ) NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `title` `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `location` `location` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `url` `url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `booked` `booked` TINYINT( 1 ) NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `user_id` `user_id` INT( 10 ) UNSIGNED NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `rule_id` `rule_id` INT( 10 ) UNSIGNED NULL;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` CHANGE `base_from` `base_from` DATETIME NULL;');

}


// ------------------------------------------------- alpha14

if(version_compare($version, '2.0 alpha14', '<'))
{
  $sql = rex_sql::factory();
  $sql->setQuery('CREATE TABLE IF NOT EXISTS `pz_project_sub` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `project_id` INT NOT NULL ,
    `name` VARCHAR( 255 ) NOT NULL
    ) ENGINE = MYISAM;
  ');

  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` MODIFY `event_id` int(10) unsigned DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` MODIFY `emails` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` MODIFY `attachment` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `todo_id` int(10) unsigned DEFAULT NULL AFTER `event_id`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `location` varchar(255) DEFAULT NULL AFTER `attachment`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `structured_location` varchar(255) DEFAULT NULL AFTER `location`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `proximity` varchar(255) DEFAULT NULL AFTER `structured_location`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `acknowledged` datetime DEFAULT NULL AFTER `proximity`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD `related_id` int(10) unsigned DEFAULT NULL AFTER `acknowledged`');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD INDEX (`todo_id`)');
  $sql->setQuery('ALTER TABLE `pz_calendar_alarm` ADD INDEX (`user_id`)');

  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `uri` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `project_id` int(10) unsigned DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `allday` tinyint(1) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `title` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `location` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `description` text');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `url` varchar(255) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `booked` tinyint(1) DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `user_id` int(10) unsigned DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `rule_id` int(10) unsigned DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` MODIFY `base_from` datetime DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` ADD `project_sub_id` INT(10) unsigned DEFAULT NULL AFTER `project_id`;');
  $sql->setQuery('ALTER TABLE `pz_calendar_event` ADD `clip_ids` VARCHAR( 255 ) DEFAULT NULL AFTER `project_sub_id`;');

  $sql->setQuery('ALTER TABLE `pz_calendar_rule` MODIFY `event_id` int(10) unsigned DEFAULT NULL');
  $sql->setQuery('ALTER TABLE `pz_calendar_rule` ADD `todo_id` int(10) unsigned DEFAULT NULL AFTER `event_id`');
  $sql->setQuery('ALTER TABLE `pz_calendar_rule` DROP INDEX `event`');
  $sql->setQuery('ALTER TABLE `pz_calendar_rule` ADD INDEX (`event_id`)');
  $sql->setQuery('ALTER TABLE `pz_calendar_rule` ADD INDEX (`todo_id`)');

  $sql->setQuery('
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
  ');

}


// ------------------------------------------------- alpha15

if(version_compare($version, '2.0 alpha15', '<'))
{
  $sql = rex_sql::factory();
  $sql->setQuery('ALTER TABLE `pz_address` ADD `responsible_user_id` INT NOT NULL AFTER `updated_user_id` ;');
  $sql->setQuery('ALTER TABLE `pz_user` ADD `last_login` VARCHAR( 255 ) NOT NULL AFTER `lasttrydate` ;');
  $sql->setQuery('ALTER TABLE `pz_project_file` ADD `filesize` INT NOT NULL AFTER `filename` , ADD `mimetype` VARCHAR( 255 ) NOT NULL AFTER `filesize` ;');
  
  $sql->setQuery('CREATE TABLE IF NOT EXISTS `pz_project_history` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');


  $sql->setQuery('CREATE TABLE IF NOT EXISTS `pz_user_history` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_user_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` datetime NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');
  
  $sql->setQuery('ALTER TABLE `pz_calendar_history` CHANGE `mode` `mode` ENUM( \'create\', \'update\', \'delete\' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
  
  $sql->setQuery('UPDATE `pz_calendar_history` SET MODE = "create" WHERE MODE = ""');
  
}

// ------------------------------------------------- alpha16



// ------------------------------------------------- alpha17

if(version_compare($version, '2.0 alpha17', '<'))
{
  $sql = rex_sql::factory();
  $sql->setQuery('ALTER TABLE `pz_calendar_history` ADD `project_id` INT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_file_history` ADD `project_id` INT NOT NULL ;');
  
  $sql->setQuery('ALTER TABLE `pz_wiki_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_calendar_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_address_history` CHANGE `mode` `mode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;');

  $sql->setQuery('CREATE TABLE IF NOT EXISTS `pz_history` (
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

  $sql->setQuery('ALTER TABLE `pz_project_user` ADD `calendar_jobs` TINYINT NOT NULL AFTER `calendar` ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `calendar` `calendar` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `wiki` `wiki` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `admin` `admin` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `webdav` `webdav` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `caldav` `caldav` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `caldav_jobs` `caldav_jobs` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `files` `files` TINYINT NOT NULL ;');
  $sql->setQuery('ALTER TABLE `pz_project_user` CHANGE `emails` `emails` TINYINT NOT NULL;');

  $sql->setQuery('DROP TABLE `pz_address_history`;');
  $sql->setQuery('DROP TABLE `pz_calendar_history`;');
  $sql->setQuery('DROP TABLE `pz_wiki_history`;');
  $sql->setQuery('DROP TABLE `pz_project_file_history`;');
  $sql->setQuery('DROP TABLE `pz_project_history`;');
  $sql->setQuery('DROP TABLE `pz_user_history`;');

  $sql->setQuery('ALTER TABLE `pz_project` ADD `has_calendar_jobs` TINYINT NOT NULL AFTER `has_calendar`;');

  $sql->setQuery('UPDATE `pz_project_user` set calendar_jobs = 1 where calendar = 1;');
  $sql->setQuery('UPDATE `pz_project` SET has_calendar_jobs = 1 WHERE has_calendar = 1;'); 

}

// -------------------------------------------------

if($error)
  $this->setProperty('updatemsg', $error);
else
  $this->setProperty('update', true);