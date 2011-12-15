<?php

class pz_wiki_article_version extends pz_wiki_article
{
  public function revert()
  {
    $sql = rex_sql::factory();
    $sql->setTable('pz_wiki')
      ->setWhere(array('id' => $this->getId()))
      ->setTitle('title', $this->getTitle())
      ->setValue('text', $this->getRawText())
      ->setValue('vt', $this->getTitle() .' '. $this->getRawText())
      ->setRawValue('stamp', 'NOW()')
      ->update();
    $this->saveToHistory('revert');
  }
}