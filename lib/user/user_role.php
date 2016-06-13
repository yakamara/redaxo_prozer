<?php

class pz_user_role implements rex_user_role_interface
{
    private $perms = [];

    private function __construct()
    {
    }

    public function hasPerm($perm)
    {
        return in_array($perm, $this->perms);
    }

    public function getComplexPerm($user, $key)
    {
        return null;
    }

    public static function get($id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT perms FROM pz_user_role WHERE id = ?', [$id]);
        if ($sql->getRows() == 0) {
            return null;
        }
        return new self();
    }
}
