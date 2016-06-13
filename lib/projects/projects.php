<?php

class pz_projects extends pz_search
{
    public $search_table = 'pz_project';
    public static $projects = [];

    public static function get()
    {
        if (count(self::$projects) > 0) {
            return self::$projects;
        }

        $sql = rex_sql::factory();
        $sql->setQuery('select * from pz_project order by id');

        foreach ($sql->getArray() as $l) {
            $project = new pz_project($l);
            self::$projects[$project->getId()] = $project;
        }

        return self::$projects;
    }

    public static function getAsString()
    {
        $return = [];
        foreach (self::get() as $project) {
            $v = $project->getName();
            $v = str_replace('=', '', $v);
            $v = str_replace(',', '', $v);
            $return[] = $v.'='.$project->getId();
        }
        return implode(',', $return);
    }
}
