<?php

class pz_history_entry extends pz_model
{
    public $vars = [];

    public function __construct($vars)
    {
        parent::__construct($vars);
    }

    public static function get($id = '')
    {
        if ($id == '') {
            return false;
        }
        $id = (int) $id;

        $sql = rex_sql::factory();
        $sql->setQuery('select * from pz_history where id = ? LIMIT 2', [$id]);

        $entries = $sql->getArray();
        if (count($entries) != 1) {
            return false;
        }

        return new static($entries[0]);
    }

    public function getId()
    {
        return intval($this->vars['id']);
    }

    public function delete()
    {
        $d = rex_sql::factory();
        $d->setQuery('delete from pz_history where id = ?', [$this->getId()]);
        return true;
    }
}
