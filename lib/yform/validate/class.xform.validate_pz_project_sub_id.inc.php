<?php

class rex_yform_validate_pz_project_sub_id extends rex_yform_validate_abstract
{
    public function enterObject()
    {
        if ($this->params['send'] == '1') {
            $field = $this->getElement(2); // project_id-project_sub_id / 28-2

            foreach ($this->obj_array as $o) {
                if ($o->getName() == $field) {
                    $p = explode('-', $o->getValue());
                    $project_id = $p[0];
                    $project_sub_id = 0;
                    if (isset($p[1])) {
                        $project_sub_id = $p[1];
                    }

                    $filter = [];
                    $filter[] = ['field' => 'id', 'value' => $project_id];
                    $projects = pz::getUser()->getAllProjects($filter);

                    if (count($projects) != 1) {
                        $this->params['warning'][$o->getId()] = $this->params['error_class'];
                        $this->params['warning_messages'][$o->getId()] = $this->getElement(3);
                    } else {
                        $project = $projects[0];

                        if (!$project->hasProjectSubId($project_sub_id)) {
                            $this->params['warning'][$o->getId()] = $this->params['error_class'];
                            $this->params['warning_messages'][$o->getId()] = $this->getElement(4);
                        }
                    }
                }
            }
        }

        return;
    }

    public function getDescription()
    {
        return 'pz_project_sub_id|field|Project Warning Message|Subproject Warning Message';
    }
}
