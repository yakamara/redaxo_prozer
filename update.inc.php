<?php

$error = '';

// bisher installierte Version:
$version = $this->getVersion();

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

if(version_compare($version, '2.0 alpha7', '<'))
{

	// clipboard
	rex_sql::factory()->setQuery('ALTER TABLE `pz_clipboard` CHANGE `hidden` `hidden` TINYINT( 1 ) NOT NULL;',array());
	rex_sql::factory()->setQuery('ALTER TABLE `pz_clipboard` ADD `open` TINYINT( 1 ) NOT NULL , ADD `online_date` DATETIME NOT NULL , ADD `offline_date` DATETIME NOT NULL , ADD `uri` TEXT NOT NULL;',array());

}

if(version_compare($version, '2.0 alpha8', '<'))
{
	rex_sql::factory()->setQuery('ALTER TABLE `pz_user` ADD `comment` TEXT NOT NULL;',array());

}

if(version_compare($version, '2.0 alpha10', '<'))
{
    $sql = rex_sql::factory();
    
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
}

if($error)
  $this->setProperty('updatemsg', $error);
else
  $this->setProperty('update', true);