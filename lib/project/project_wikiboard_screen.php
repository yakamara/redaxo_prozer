<?php

class pz_project_wikiboard_screen
{
    protected $wikiboard;

    public function __construct(pz_wikiboard $wikiboard = null)
    {
        $this->wikiboard = $wikiboard;
    }

    public function getBoardView()
    {
        $return = '';
        $pages = pz_wiki_page::getAll($this->wikiboard->getProject()->getId());
    
        foreach($pages as $page) {
            $screen = new pz_project_wiki_screen($this->wikiboard->getProject(), $this->wikiboard->getProjectuser(), $page);
            $return .= $screen->getBoardView();
        
        }
    
        return $return;
    }

    protected function url(array $params = [], $split = '&')
    {
        return pz::url('screen', 'project', 'wikiboard', ['project_id' => $this->project->getId()], $split);
    }
}
