<?php

class pz_project_directory extends pz_project_node
{
    public function getChildren($orders = [])
    {

        // ----- Orders
        $orders[] = ['orderby' => 'name', 'sort' => 'asc'];
        $order_sql = [];
        foreach ($orders as $order) {
            $order_sql[] = $order['orderby'].' '.$order['sort'];
        }

        $params = [];
        $params[] = $this->vars['project_id'];
        $params[] = $this->getId();
        $sql_folder = 'SELECT * FROM pz_project_file WHERE project_id = ? AND parent_id = ? AND is_directory = 1 ORDER BY name LIMIT 1000'; // without LIMIT UNION wont work

        $params[] = $this->vars['project_id'];
        $params[] = $this->getId();
        $sql_files = 'SELECT * FROM pz_project_file WHERE project_id = ? AND parent_id = ? AND is_directory = 0 ORDER BY '.implode(',', $order_sql).' LIMIT 1000'; // without LIMIT UNION wont work

        $sql = pz_sql::factory();
        // $sql->debugsql = 1;
        $array = $sql->getArray('('.$sql_folder.') UNION ALL ('.$sql_files.')', $params);
        $children = [];
        foreach ($array as $row) {
            $children[] = static::factory($row);
        }
        return $children;
    }

    public function getChild($name)
    {
        $sql = pz_sql::factory();
        $params = [$this->vars['project_id'], $name, $this->getId()];
        $array = $sql->getArray('SELECT * FROM pz_project_file WHERE project_id = ? AND name = ? AND parent_id = ? LIMIT 2', $params);
        if (count($array) != 1) {
            return null;
        }
        return static::factory($array[0]);
    }

    public function childExists($name)
    {
        return is_object($this->getChild($name));
    }

    public function createFile($name, $data = '', $comment = '')
    {
        if (!$this->createNode($name, false, $comment)) {
            return false;
        }

        $file = $this->getChild($name);
        $file->putContent($data, false);

        $file->saveToHistory('create');

        return true;
    }

    public function createDirectory($name)
    {
        if (!$this->createNode($name, true)) {
            return false;
        }

        $dir = $this->getChild($name);
        $dir->saveToHistory('create');

        return true;
    }

    private function createNode($name, $is_directoy = false, $comment = '')
    {
        if ($this->childExists($name)) {
            return false;
        }

        $sql = pz_sql::factory();
        $sql->setTable('pz_project_file')
            ->setValue('name', $name)
            ->setValue('parent_id', $this->getId())
            ->setValue('is_directory', $is_directoy)
            ->setValue('project_id', $this->vars['project_id'])
            ->setValue('comment', $comment)
            ->setRawValue('created', 'NOW()')
            ->setRawValue('updated', 'NOW()')
            ->setValue('created_user_id', pz::getUser()->getId())
            ->setValue('updated_user_id', pz::getUser()->getId())
            ->insert();
        return true;
    }

    public function delete()
    {
        foreach ($this->getChildren() as $child) {
            $child->delete();
        }
        parent::delete();
    }
}

class pz_project_root_directory extends pz_project_directory
{
    public function __construct(pz_project $project)
    {
        $vars['id'] = 0;
        $vars['name'] = str_replace('/', '-', $project->getName()) .' - '. $project->getId() .'';
        $vars['name'] = str_replace('[', '', $vars['name']);
        $vars['name'] = str_replace(']', '', $vars['name']);
        $vars['parent_id'] = 0;
        $vars['project_id'] = $project->getId();
        parent::__construct($vars);
    }

    public function moveTo(pz_project_directory $destination, $name = null)
    {
        throw new pz_exception('The project root directory can not be moved!');
    }

    public function delete()
    {
        throw new pz_exception('The project root directory can not be deleted!');
    }

    public function getAllPaths()
    {
        $sql = pz_sql::factory();
        $ps = $sql->getArray('SELECT id, name, parent_id FROM pz_project_file WHERE project_id = '. $this->getProjectId() .' AND is_directory = 1 ORDER BY name');
        $paths = [];
        foreach ($ps as $p) {
            $paths[$p['id']] = ['id' => $p['id'], 'name' => $p['name'], 'parent_id' => $p['parent_id']];
        }
        $array = [];
        $func = function ($id, $path) use (&$paths, &$func, &$array) {
            $array[] = ['id' => $id, 'label' => $path];
            $rows = [];
            foreach ($paths as $p) {
                if ($p['parent_id'] == $id) {
                    $rows[] = [$p['id'], $path.$p['name'].'/'];
                }
            }
            foreach ($rows as $row) {
                $func($row[0], $row[1]);
            }
        };
        $func(0, '/');
        return $array;
    }
}
