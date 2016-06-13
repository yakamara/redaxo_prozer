<?php

use FragSeb\Dashboard\Model\DashboardModelInterface;

class pz_dashboard extends pz_model implements DashboardModelInterface
{
    const TABLE = 'pz_dashboard';

    public $vars = [];
    private $fields = [];

    private static $_sql;

    /**
     * @var pz_user $owner
     */
    private $owner;


    public function __construct($vars = [])
    {
        static::$_sql = pz_sql::factory();
        $this->owner = pz::getUser();

        $this->setVars($vars);
    }

    public static function get()
    {
        $dashboard = static::find();

        if (null === $dashboard) {
            return null;
        }


        return new self($dashboard);
    }

    public function create()
    {
        $query = static::$_sql->setTable(self::TABLE)
            ->setValue('user_id', pz::getUser()->getId())
            ->setValue('data', $this->getVar('data'))
            ->insert()
        ;
        $dash = static::find();
        $this->setVars($dash);
    }

    public function update()
    {
        static::$_sql->setTable(self::TABLE)
            ->setWhere(['id' => $this->getId()])
            ->setValue('data', $this->getVar('data'))
            ->update()
        ;
    }

    public function remove()
    {
        static::$_sql->setTable(self::TABLE)
            ->setWhere(['id' => $this->getId()])
            ->delete()
        ;
    }

    public function getId()
    {
        return $this->getVar('id');
    }

    private static function find() {
        $sql = pz_sql::factory()->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE user_id=' . (int) pz::getUser()->getId() . ' ORDER BY id DESC LIMIT 1');
        $dashboard = $sql->getArray();

        if (count($dashboard) === 0) {
            return null;
        }

        return $dashboard[0];
    }

    /**
     * @return FragSeb\Dashboard\Entity\DashboardEntity
     */
    public function getEntity()
    {
        // TODO: Implement getEntity() method.
    }
}
