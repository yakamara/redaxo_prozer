<?php

class pz_wikiboard
{
    private $project;
    private $projectuser;

    public function __construct(pz_project $project, pz_projectuser $projectuser)
    {
        $this->project = $project;
        $this->projectuser = $projectuser;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getProjectuser()
    {
        return $this->projectuser;
    }

}
