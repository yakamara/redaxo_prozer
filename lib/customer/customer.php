<?php

class pz_customer extends pz_model
{
    public $vars = [];
    public $isCustomer = false;

    public static $var_labels = 6;
    private static $customers = [];

    public function __construct($vars)
    {
        $this->isCustomer = true;
        parent::__construct($vars);
    }

    public static function get($id = '')
    {
        $id = (int) $id;
        if ($id == 0) {
            return false;
        }

        if (isset(pz_customer::$customers[$id])) {
            return pz_customer::$customers[$id];
        }

        $class = get_called_class();

        $sql = rex_sql::factory();

        $customers = $sql->getArray('select * from pz_customer where id = ? LIMIT 2', [$id]);
        if (count($customers) != 1) {
            return false;
        }

        pz_customer::$customers[$id] = new $class($customers[0]);
        return pz_customer::$customers[$id];
    }

    public function getId()
    {
        return $this->getVar('id');
    }

    public function getName()
    {
        return $this->getVar('name');
    }

    public function getFolder()
    {
        return rex_path::addonData('prozer', 'customers/'.$this->getId());
    }

    public function getInlineImage()
    {
        if ($this->getVar('image_inline') != '') {
            return $this->getVar('image_inline');
        }

        if ($this->getVar('image') == 1 && $image_path = $this->getFolder().'/'.$this->getId().'.png') {
            return pz::makeInlineImage($image_path, 'm');
        }

        return pz_customer::getDefaultImage();
    }

    public static function getDefaultImage()
    {
        return '/assets/addons/prozer/css/customer.png';
    }

    public function hasProjects()
    {
        $sql = rex_sql::factory();
        $customers = $sql->getArray('select * from pz_project where customer_id = ? LIMIT 2', [$this->getId()]);
        if (count($customers) > 0) {
            return true;
        }
        return false;
    }

    public function update()
    {
    }

    public function create()
    {
        rex_dir::create($this->getFolder());
    }

    public function delete()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from pz_customer where id = ?', [$this->getId()]);
        return true;
    }
}
