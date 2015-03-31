<?php

class pz_wiki_page_version extends pz_wiki_page
{
    public function __construct(pz_wiki_page $base, array $vars)
    {
        $vars['version_id'] = $vars['id'];
        $vars['id'] = $base->getId();
        $vars['created'] = $base->getCreated()->format('Y-m-d H:i:s');
        $vars['create_user_id'] = $base->getVar('create_user_id');
        $vars['updated'] = $base->getUpdated()->format('Y-m-d H:i:s');
        $vars['update_user_id'] = $base->getVar('update_user_id');
        $vars['admin'] = $base->getVar('admin');
        $vars['stamp'] = new DateTime($vars['stamp']);
        $data = json_decode($vars['data'], true);
        $vars['title'] = $data['title'];
        $vars['text'] = $data['text'];
        unset($vars['data']);
        $vars['current'] = $base;
        parent::__construct($vars);
    }

    public function getVersionId()
    {
        return $this->getVar('version_id');
    }

    /**
     * @return DateTime
     */
    public function getStamp()
    {
        return $this->vars['stamp'];
    }

    public function getStampFormatted()
    {
        return pz::strftime(pz_i18n::msg('show_datetime_normal'), $this->getStamp()->getTimestamp());
    }

    /**
     * @return pz_user
     */
    public function getUser()
    {
        return pz_user::get($this->vars['user_id']);
    }

    public function getMessage()
    {
        return $this->getVar('message');
    }

    /**
     * @return pz_wiki_page
     */
    public function getCurrent()
    {
        return $this->vars['current'];
    }

    public function revert()
    {
        $sql = pz_sql::factory();
        $sql->setTable('pz_wiki')
            ->setWhere(['id' => $this->getId()])
            ->setValue('title', $this->getTitle())
            ->setValue('text', $this->getRawText())
            ->setValue('vt', $this->getTitle() .' '. $this->getRawText())
            ->setRawValue('stamp', 'NOW()')
            ->update();
        $this->saveToHistory('revert');
    }
}
