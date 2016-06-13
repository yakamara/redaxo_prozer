<?php

use Ciconia\Extension\Gfm;

class pz_wiki_page extends pz_model
{
    private $text;

    public function __construct($vars)
    {
        $this->vars = $vars;
        $this->vars['created'] = new DateTime($this->vars['created']);
        $this->vars['updated'] = new DateTime($this->vars['updated']);
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
        if (!is_null($this->text)) {
            return $this->text;
        }

        return $this->text = self::parseText($this->vars['project_id'], $this->vars['text']);
    }

    public function isAdminPage()
    {
        return (boolean) $this->getVar('admin');
    }

    public function countTasks()
    {
        return substr_count($this->getText(), '<input type="checkbox"');
    }

    public function countTasksChecked()
    {
        return substr_count($this->getText(), '<input type="checkbox" checked="checked"');
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->vars['created'];
    }

    public function getCreatedFormatted()
    {
        return pz::strftime(pz_i18n::msg('show_datetime_normal'), $this->getCreated()->getTimestamp());
    }

    public function getPosition()
    {
        $positions = explode(",", $this->vars['position']);
        
        if (count($positions) == 2){
            $x = (int) $positions[0];
            $y = (int) $positions[1];
            
        } else {
            $x = 0;
            $y = 0;

        }

        return [$x,$y];
    }

    public function setPosition($positions)
    {
        $positions = explode(",", $positions);
        
        $x = 0;
        $y = 0;
        if (count($positions) == 2) {
            $x = (int) $positions[0];
            $y = (int) $positions[1];
            
        }
        
        if ($x<0) $x = 0;
        if ($y<0) $y = 0;
        
        $this->vars["position"] = $x.",".$y;
        
    }

    /**
     * @return pz_user
     */
    public function getCreateUser()
    {
        return pz_user::get($this->vars['create_user_id']);
    }

    /**
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->vars['updated'];
    }

    public function getUpdatedFormatted()
    {
        return pz::strftime(pz_i18n::msg('show_datetime_normal'), $this->getUpdated()->getTimestamp());
    }

    /**
     * @return pz_user
     */
    public function getUpdateUser()
    {
        return pz_user::get($this->vars['update_user_id']);
    }

    public static function get($id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery(self::getBaseQuery() . 'id = ? LIMIT 2', [$id]);
        if ($sql->getRows() != 1) {
            return null;
        }
        $vars = $sql->getArray();
        return new self($vars[0]);
    }

    public static function getStart($project_id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery(self::getBaseQuery() . 'project_id = ? ORDER BY created LIMIT 1', [$project_id]);
        if ($sql->getRows() != 1) {
            return null;
        }
        $vars = $sql->getArray();
        return new self($vars[0]);
    }

    public static function getAll($project_id)
    {
        $pages = [];
        if ($start = self::getStart($project_id)) {
            $pages[] = $start;
            $sql = rex_sql::factory();
            $sql->setQuery(self::getBaseQuery() . 'project_id = ? AND id != ? ORDER BY title', [$project_id, $start->getId()]);
            foreach ($sql->getArray() as $row) {
                $pages[] = new self($row);
            }
        }
        return $pages;
    }

    private static function getBaseQuery()
    {
        $query = 'SELECT * FROM pz_wiki WHERE ';
        if (pz::getUser()->isAdmin()) {
            return $query;
        }
        return $query . '(admin = 0 OR (SELECT u.admin FROM pz_project_user u WHERE u.project_id = pz_wiki.project_id AND u.user_id = ' . (int) pz::getUser()->getId() . ') = 1) AND ';
    }

    public function getVersion($id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM pz_history WHERE control = "wiki" AND data_id = ? AND id = ? LIMIT 2', [$this->getId(), $id]);
        if ($sql->getRows() != 1) {
            return null;
        }
        $vars = $sql->getArray();
        return new pz_wiki_page_version($this, $vars[0]);
    }

    /**
     * @return pz_wiki_page_version[]
     */
    public function getVersions()
    {
        $versions = [];
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM pz_history WHERE control = "wiki" AND data_id = ? ORDER BY stamp DESC', [$this->getId()]);
        foreach ($sql->getArray() as $row) {
            $versions[] = new pz_wiki_page_version($this, $row);
        }
        return $versions;
    }

    /**
     * @return pz_wiki_page
     */
    public function getCurrent()
    {
        return $this;
    }

    public function saveToHistory($mode = 'update', $message = '')
    {
        $sql = rex_sql::factory();
        $sql->setTable('pz_history')
            ->setValue('control', 'wiki')
            ->setValue('data_id', $this->getId())
            ->setValue('project_id', $this->getVar('project_id'))
            ->setValue('user_id', pz::getUser()->getId())
            ->setValue('stamp', $this->getUpdated()->format('Y-m-d H:i:s'))
            ->setValue('mode', $mode)
            ->setValue('message', $message);
        if ($mode != 'delete') {
            $data = [
                'title' => $this->getTitle(),
                'text' => $this->getRawText(),
                'position' => implode(",",$this->getPosition()),
            ];
            $sql->setValue('data', json_encode($data));
        }
        $sql->insert();
    }

    private function updateVT()
    {
        $vt = [];
        $vt[] = $this->getTitle();
        $vt[] = $this->getRawText();
        $sql = rex_sql::factory();
        $sql->setTable('pz_wiki')
            ->setWhere(['id' => $this->getId()])
            ->setValue('vt', implode(' ', $vt))
            ->setValue('position', implode(",",$this->getPosition()))
            ->update();
    }

    public function create($message = '')
    {
        $this->updateVT();
        $this->saveToHistory('create', $message);
    }

    public function update($message = '')
    {
        $this->updateVT();
        $this->saveToHistory('update', $message);
    }

    public function delete()
    {
        $this->vars['updated'] = new DateTime();
        $this->saveToHistory('delete');

        rex_sql::factory()->setQuery('
            DELETE
            FROM pz_wiki
            WHERE id = ?
        ', [$this->vars['id']]);
    }

    public static function parseText($projectId, $text)
    {
        $ciconia = new Ciconia\Ciconia();
        $ciconia->addExtension(new pz_wiki_markdown_inline_style_extension());
        $ciconia->addExtension(new pz_wiki_markdown_internal_link_extension($projectId));
        $ciconia->addExtension(new Gfm\FencedCodeBlockExtension());
        $ciconia->addExtension(new Gfm\TaskListExtension());
        $ciconia->addExtension(new Gfm\InlineStyleExtension());
        $ciconia->addExtension(new Gfm\WhiteSpaceExtension());
        $ciconia->addExtension(new Gfm\TableExtension());
        $ciconia->addExtension(new Gfm\UrlAutoLinkExtension());

        return $ciconia->render($text);
    }
}
