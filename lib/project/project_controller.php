<?php

class pz_project_controller extends pz_controller
{
    /** @var pz_project */
    protected $project;
    protected $project_id;
    /** @var pz_projectuser */
    protected $projectuser;

    public function isVisible()
    {
        return false;
    }

    public function setProject($project_id)
    {
        $filter = [];
        $filter[] = ['field' => 'id','type' => '=','value' => $project_id];
        $project = pz::getUser()->getAllProjects($filter);
        if (count($project) != 1) {
            return false;
        }

        $this->project = current($project);
        $this->project_id = $project_id;

        if (pz::getUser()->isAdmin()) {
            $vars = [
                'id' => -1,
                'admin' => 1,
                'calendar' => 1,
                'calendar_jobs' => 1,
                'files' => 1,
                'emails' => 1,
                'wiki' => 1,
            ];
            $this->projectuser = new pz_projectuser($vars, pz::getUser(), $project);
            return true;
        } else {
            if ($this->projectuser = $this->project->getProjectuserByUserId(pz::getUser()->getId())) {
                return true;
            }
        }

        return false;
    }
}
