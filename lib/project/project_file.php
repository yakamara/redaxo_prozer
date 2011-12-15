<?php

class pz_project_file extends pz_project_node
{
  public function getRealPath()
  {
    return rex_path::addonData('prozer', 'projects/'. $this->getProjectId() .'/files/'. $this->getId());
  }

  public function putContent($data)
  {
    file_put_contents($this->getRealPath(), $data);

    rex_sql::factory()->setQuery('UPDATE pz_project_file SET updated = NOW(), updated_user_id = ? WHERE id = ?', array(pz::getUser()->getId(), $this->getId()));
  }

  public function getContent()
  {
    return fopen($this->getRealPath(), 'r');
  }

  public function delete()
  {
    unlink($this->getRealPath());
    parent::delete();
  }
}