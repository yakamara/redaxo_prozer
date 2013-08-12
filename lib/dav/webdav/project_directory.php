<?php

class pz_sabre_project_directory extends pz_sabre_project_node implements Sabre\DAV\ICollection
{
    public function getChild($name)
    {
        $child = $this->node->getChild($name);
        if (!$child) {
            throw new Sabre\DAV\Exception\NotFound('File with name ' . $name . ' could not be located');
        }
        return self::factory($child);
    }

    public function getChildren()
    {
        $nodes = array();
        foreach ($this->node->getChildren() as $node) {
            $nodes[] = self::factory($node);
        }
        return $nodes;
    }

    public function childExists($name)
    {
        return $this->node->childExists($name);
    }

    public function createFile($name, $data = null)
    {
        $this->node->createFile($name, $data);
    }

    public function createDirectory($name)
    {
        $this->node->createDirectory($name);
    }
}

class pz_sabre_project_root_directory extends pz_sabre_project_directory
{
    public function __construct(pz_project $project)
    {
        parent::__construct($project->getDirectory());
    }
}
