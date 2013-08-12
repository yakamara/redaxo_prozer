<?php

class pz_sabre_project_file extends pz_sabre_project_node implements Sabre\DAV\IFile
{
    public function put($data)
    {
        $this->node->putContent($data);
    }

    public function get()
    {
        return fopen($this->node->getRealPath(), 'rb');
    }

    public function getSize()
    {
        return filesize($this->node->getRealPath());
    }

    public function getETag()
    {
        return '"' . md5_file($this->node->getRealPath()) . '"';
    }

    public function getContentType()
    {
        return null;
    }
}
