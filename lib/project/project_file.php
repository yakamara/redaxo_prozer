<?php

class pz_project_file extends pz_project_node
{
  public function getRealPath()
  {
    return rex_path::addonData('prozer', 'projects/'. $this->getProjectId() .'/files/'. $this->getId() .'/'. $this->getVar('filename'));
  }

  public function putContent($data, $saveToHistory = true)
  {
    if(!pz::getConfig('project_file_history') && is_file($this->getRealPath()))
      rex_file::delete($oldpath = $this->getRealPath());

    $filename = date('YmdHis');
    $this->setVar('filename', $filename);
    rex_file::put($this->getRealPath(), $data);
    
    $filesize = $this->getSize();
    $this->setVar('filesize', $filesize);

    $mimetype = $this->getMimeType();
    $this->setVar('mimetype', $mimetype);

    pz_sql::factory()->setQuery('UPDATE pz_project_file SET filename = ?, filesize = ?, mimetype = ?, updated = NOW(), updated_user_id = ? WHERE id = ?', array($filename, $filesize, $mimetype, pz::getUser()->getId(), $this->getId()));

    if($saveToHistory)
      $this->saveToHistory('update');
  }

  public function getContent()
  {
  	return file_get_contents($this->getRealPath());
  }

  public function getSize()
  {
  	return filesize($this->getRealPath());
  }

  public function getMimeType()
  {
    return pz::getMimeTypeByFilename($this->getVar('name'));
  }

  // only for update.inc.php
  public function updateFilesizeAndMimetype()
  {
    pz_sql::factory()->setQuery(
      'UPDATE pz_project_file SET filesize = ?, mimetype = ? WHERE id = ?', 
      array($this->getSize(), $this->getMimeType(), $this->getId() )
    );
  
  }

  public function delete()
  {
    if(!pz::getConfig('project_file_history'))
      rex_dir::delete(rex_path::addonData('prozer', 'projects/'. $this->getProjectId() .'/files/'. $this->getId()));
    parent::delete();
  }
}