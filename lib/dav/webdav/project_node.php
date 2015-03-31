<?php

abstract class pz_sabre_project_node implements Sabre\DAV\INode
{
  protected $node;

    public function __construct(pz_project_node $node)
    {
        $this->node = $node;
    }

    protected static function factory(pz_project_node $node)
    {
        return $node instanceof pz_project_directory ? new pz_sabre_project_directory($node) : new pz_sabre_project_file($node);
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getName()
    {
        return $this->node->getName();
    }

    public function setName($name)
    {
        $this->node->setName($name);
    }

    public function moveTo($relPath, $name = null)
    {
        $this->node->moveTo($relPath, $name);
    }

    public function delete()
    {
        $this->node->delete();
    }

    public function getLastModified()
    {
        return strtotime($this->node->getVar('updated'));
    }
}
