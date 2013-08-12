<?php

class pz_sabre_project_tree extends Sabre\DAV\ObjectTree
{
    public function __construct()
    {
        parent::__construct(new pz_sabre_project_root_collection('projects'));
    }

    public function copy($source, $destination)
    {
        parent::copy($source, $destination);
    }

    public function move($source, $destination)
    {
        $sourceNode = $this->getNodeForPath($source);
        list($destinationDir, $destinationName) = Sabre\DAV\URLUtil::splitPath($destination);
        $destinationNode = $this->getNodeForPath($destinationDir);
        if (
            $sourceNode instanceof pz_sabre_project_root_collection ||
            $sourceNode instanceof pz_sabre_project_root_directory ||
            $destinationNode instanceof pz_sabre_project_root_collection
        ) {
            throw new Sabre\DAV\Exception('Forbidden!');
        }

        if ($sourceNode->getNode()->getVar('project_id') == $destinationNode->getNode()->getVar('project_id')) {
            $sourceNode->moveTo($destinationNode->getNode(), $destinationName);
        } else {
            $this->copy($source, $destination);
            $sourceNode->delete();
        }
    }
}
