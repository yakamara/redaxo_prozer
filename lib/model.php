<?php

class pz_model
{
    public $vars = [];

    private $pager = '';

    public function __construct($vars = [])
    {
        if (!is_array($vars)) {
            return false;
        } else {
            $this->setVars($vars);
        }
        return true;
    }

    public function setVars($vars = [])
    {
        $this->vars = $vars;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function setVar($key, $var = '')
    {
        $this->vars[$key] = $var;
    }

    public function getVar($key, $type = false)
    {
        $return = '';
        if (isset($this->vars[$key]) && trim($this->vars[$key]) != '') {
            switch ($type) {
                case('overview'):
                    $return = trim($this->vars[$key]);
                    $return = strip_tags($return);
                    $return = htmlspecialchars($return);
                    $return = substr($return, 0, 20);           // anpassen !!
                    break;
                case('date'):
                    $return = trim($this->vars[$key]);
                    $return = strtotime($return);
                    $return = date('d.m.Y', $return);
                    break;
                case('time'):
                    $return = trim($this->vars[$key]);
                    $return = strtotime($return);
                    $return = date('H:i', $return);
                    break;
                case('datetime'):
                    $return = trim($this->vars[$key]);
                    $return = strtotime($return);
                    $return = date('d.m.Y H:i', $return);
                    break;
                default:
                    $return = $this->vars[$key];
            }
        }
        return $return;
    }

    public static function query($query, $params, $pager = '')
    {
        if (is_object($pager)) {
            $query = preg_replace('/^SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
            $query .= ' LIMIT '.$pager->getCursor().', '.$pager->getRowsPerPage();

            $sql = rex_sql::factory();
            $rows = $sql->getArray($query, $params);

            $sql->setQuery('SELECT FOUND_ROWS() as rows;');

            $pager->setRowCount($sql->getValue('rows'));

            return $rows;
        } else {
            $sql = rex_sql::factory();
            return $sql->getArray($query, $params);
        }
    }

    public function update()
    {
    }

    public function create()
    {
    }

    public function delete()
    {
    }
}
