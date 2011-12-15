<?php

class pz_wiki_article extends pz_model
{
  private $vars = array();

  public function __construct($vars)
  {
    $this->vars = $vars;
    $this->vars['stamp'] = new DateTime($this->vars['stamp']);
  }

  public function getId()
  {
    return $this->vars['id'];
  }

  public function getTitle()
  {
    return $this->vars['title'];
  }

  public function getRawText()
  {
    return $this->vars['text'];
  }

  public function getText()
  {
    $text = $this->vars['text'];

    $text = htmlspecialchars($text, ENT_NOQUOTES);
    $text = str_replace(array("\r\n", "\r"), "\n", $text);

    $text = preg_replace('/\*(.*)\*/U', '<b>$1</b>', $text);
    $text = preg_replace('/(?<!\w)_(.*)_(?!\w)/U', '<i>$1</i>', $text);
    $text = preg_replace_callback('/(?:"([^"]*)":)?(https?:\/\/|mailto:)(\S+[0-9a-z?=\/#])/i', function($matches) {
      return '<a href="'. $matches[2] . $matches[3] .'">'. ($matches[1] ?: $matches[3]) .'</a>';
    }, $text);

    $parts = explode("\n\n", $text);
    $text = '';
    $classes = array('! ' => 'wiki-warning', '+ ' => 'wiki-checked', '- ' => 'wiki-unchecked');
    foreach($parts as $part)
    {
      $part = trim($part);
      if(preg_match("/(h[1-6])\. ([^\n]*)/s", $part, $matches))
      {
        $text .= '<'. $matches[1] .'>'. $matches[2] .'</'. $matches[1] .">\n\n";
      }
      elseif(preg_match("/^(\*|#) ([^\n]*(?:\n\\1 [^\n]*)*)$/s", $part, $matches))
      {
        $tag = $matches[1] == '#' ? 'ol' : 'ul';
        $text .= "<$tag>\n  <li>". str_replace("\n". $matches[1] .' ', "</li>\n  <li>", $matches[2]) ."</li>\n</$tag>\n\n";
      }
      else
      {
        $class = '';
        $begin = substr($part, 0, 2);
        if(in_array($begin, array_keys($classes)))
        {
          $part = substr($part, 2);
          $class = ' class="'. $classes[$begin] .'"';
        }
        $text .= "<p$class>". str_replace("\n", "<br />\n", $part) ."</p>\n\n";
      }
    }

    return $text;
  }

  public function getUser()
  {
    return pz_user::get($this->vars['user_id']);
  }

  public function getStamp()
  {
    return $this->vars['stamp'];
  }

  public function getVersions()
  {
    $versions = array($this);
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_wiki_history WHERE wiki_id = ? ORDER BY stamp DESC', array($this->getId()));
    foreach($sql->getArray() as $row)
    {
      $row = array_merge($row, json_decode($row['data'], true));
      unset($row['data']);
      $versions[] = new pz_wiki_article_version($row);
    }
    return $versions;
  }

  static public function get($id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_wiki WHERE id = ? LIMIT 2', array($id));
    if($sql->getRows() != 1)
      return false;
    $vars = $sql->getArray();
    return new self($vars[0]);
  }

  static public function getStart($project_id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_wiki WHERE project_id = ? ORDER BY stamp LIMIT 1', array($project_id));
    if($sql->getRows() != 1)
      return false;
    $vars = $sql->getArray();
    return new self($vars[0]);
  }

  static public function getAll($project_id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM pz_wiki WHERE project_id = ? ORDER BY title', array($project_id));
    $articles = array();
    foreach($sql->getArray() as $row)
    {
      $articles[] = new self($row);
    }
    return $articles;
  }

  public function saveToHistory($mode = 'update')
  {
    $sql = rex_sql::factory();
    $sql->setTable('pz_wiki_history')
      ->setValue('wiki_id', $this->getId())
      ->setValue('user_id', pz::getUser()->getId())
      ->setRawValue('stamp', 'NOW()')
      ->setValue('mode', $mode);
    if($mode != 'delete')
    {
      $data = $this->vars;
      unset($data['vt']);
      $sql->setValue('data', json_encode($data));
    }
    $sql->insert();
  }

  private function updateVT()
  {
    $vt = array();
    $vt[] = $this->getTitle();
    $vt[] = $this->getRawText();
    $sql = rex_sql::factory();
    $sql->setTable('pz_wiki')
      ->setWhere(array('id' => $this->getId()))
      ->setValue('vt', implode(' ', $vt))
      ->update();
  }

  public function create()
  {
    $this->updateVT();
    $this->saveToHistory('create');
  }

  public function update()
  {
    $this->updateVT();
    $this->saveToHistory('update');
  }

  public function delete()
  {
    $this->saveToHistory('delete');

    rex_sql::factory()->setQuery('
    	DELETE
    	FROM pz_wiki
			WHERE id = ?
    ', array($this->vars['id']));
  }
}