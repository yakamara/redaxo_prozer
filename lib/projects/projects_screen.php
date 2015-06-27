<?php

class pz_projects_screen
{
    /**
     * @param pz_project[] $projects
     */
    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function getProjectsFlyout($p = [], $current_project = null)
    {
        $project_id = rex_request('project_id', 'int');
        $project_name = pz_i18n::msg('select_project');

        $projects = [];
        $customers = [];

        foreach ($this->projects as $project) {
            $links = [];
            /*if($project->hasCalendar())
                $links[] = '<a href="'.pz::url('screen','calendars', 'day', array('project_id'=>$project->getId())).'">'.
                    '<span class="title">'.pz_i18n::msg("calendar").'</span></a>';
            */
            /**
             * @todo
             * pz_project::hasWiki()
             * Die Brechtigung, kann man derzeit noch nicht aendern.
             */
            if (true || $project->hasWiki()) {
                $links[] = '<a href="'.pz::url('screen', 'project', 'wiki', ['project_id' => $project->getId()]).'">'.
                    '<span class="title">'.pz_i18n::msg('project_wiki').'</span></a>';
            }
            if ($project->hasCalendarJobs()) {
                $links[] = '<a href="'.pz::url('screen', 'project', 'jobs', ['project_id' => $project->getId()]).'">'.
                    '<span class="title">'.pz_i18n::msg('jobs').'</span></a>';
            }
            if ($project->hasFiles()) {
                $links[] = '<a href="'.pz::url('screen', 'project', 'files', ['project_id' => $project->getId()]).'">'.
                    '<span class="title">'.pz_i18n::msg('files').'</span></a>';
            }
            if ($project->hasEmails()) {
                $links[] = '<a href="'.pz::url('screen', 'project', 'emails', ['project_id' => $project->getId()]).'">'.
                    '<span class="title">'.pz_i18n::msg('emails').'</span></a>';
            }

            if ($project_id == $project->getId()) {
                $project_name = $project->getName();
            }

            $project_row = '<li class="entry project-'.$project->getId().'">
				<div class="wrapper">
					<div class="links">'.implode('', $links).'</div>
					<span class="label-color-block '.pz_label_screen::getColorClass($project->getLabelId()).'"></span>
					<a href="'.pz::url('screen', 'project', 'view', ['project_id' => $project->getId()]).'"><span class="name">'.pz::cutText($project->getName(),52).'</span></a>
				</div>
			</li>';

            $customer_id = $project->getCustomerId();
            if (!isset($customers[$customer_id])) {
                $customers[$customer_id] = [];
            }

            $customers[$customer_id][] = $project_row;
            $projects[] = $project_row;
        }

        if (count($projects) > 20) {
            // user customers
            $return = '';
            foreach ($customers as $customer_id => $projects) {
                if ($customer_id > 0) {
                    $customer_name = pz_customer::get($customer_id)->getName();
                } else {
                    $customer_name = pz_i18n::msg('customer_notexists');
                }

                $return .= '<ul class="entries projects-customer-flyout customer-'.$customer_id.'"><li class="entry customer" onclick="pz_toggleClass($(this),\'customer-active\');">
        	<div class="wrapper">
        		<a href="javascript:void(0);" class="noclick"><span class="name">'.$customer_name.'</span></a>
        	</div>
          <ul class="entries" >'.implode('', $projects).'</ul>
        </li></ul>';
            }
        } else {
            $return = '<ul class="entries">'.implode('', $projects).'</ul>';
        }

        $project_selected = '';
        if (isset($current_project)) {
            $project_name = '<span class="label-color-block '.pz_label_screen::getColorClass($current_project->getLabelId()).'"></span>';
            $project_name .= pz::cutText($current_project->getName(), 60, ' ... ', 'center');
            $project_selected = ' selected-project';
        }

        $return = '	<ul class="sl1 sl1b sl-r">
						<li class="selected'.$project_selected.'"><span class="selected"  onclick="pz_screen_select(this)">'.$project_name.'</span>
							<div class="flyout">
								<div class="content">'.$return.'</div>
							</div>
						</li>
					</ul>
        ';
        return $return;
    }
}
