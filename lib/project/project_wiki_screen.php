<?php

class pz_project_wiki_screen
{
    protected $project;
    protected $projectuser;
    protected $page;
    protected $pageId;
    protected $versionId;

    function __construct(pz_project $project, pz_projectuser $projectuser, pz_wiki_page $page = null)
    {
        $this->project = $project;
        $this->projectuser = $projectuser;
        $this->page = $page;
        $this->pageId = $page ? $page->getId() : 0;
        if ($page instanceof pz_wiki_page_version) {
            $this->versionId = $page->getVersionId();
        }
    }


    // ------------------------------------------------------------------ Navigation

    /**
     * @param array          $p
     * @param pz_wiki_page[] $pages
     * @return string
     */
    public function getNavigationView($p = [], array $pages)
    {
        $return = '
          <div class="design1col" id="project_wiki_navigation">
            <header class="header">
                <h1 class="hl1">Wiki <span class="info">(' . count($pages) . ' Seiten)</span></h1>
            </header>

            <dl class="navi-box1">
              <dt>
                <div class="grid2col">
                  <div class="column first">Navigation</div>
                  <div class="column last"><a href="' . $this->url(['mode' => 'create'], '&amp;') . '" class="bt2">' . pz_i18n::msg('wiki_add') . '</a></div>
                </div>
              </dt>
              <dd>
                <ul class="navi-list1">';
        foreach ($pages as $page) {
            $class = '';
            if ($page->getId() == $this->pageId) {
                $class = ' active';
            }
            if ($page->isAdminPage()) {
                $class .= ' admin';
            }
            $tasks = '';
            if ($countTasks = $page->countTasks()) {
                $countChecked = $page->countTasksChecked();
                $percent = number_format($countChecked / $countTasks * 100, 2, '.', '');
                $tasks = '<div class="progress" title="' . pz_i18n::msg('wiki_page_tasks', $countChecked, $countTasks) . '"><div class="progress-bar" style="width:' . $percent . '%"></div></div>';
            }
            $return .= '<li class="lev1"><a class="lev1' . $class . '" href="' . $this->url(['wiki_id' => $page->getId()], '&amp;') . '"><span>' . htmlspecialchars($page->getTitle()) . '</span>' . $tasks . '</a></li>';
        }
        $return .= '
            </ul>
              </dd>
            </dl>
          </div>

        ';
        return $return;
    }


    // ------------------------------------------------------------------ Page

    public function getPageView($p = [])
    {
        $content = $this->getPageText($this->page->getText(), $this->page instanceof pz_wiki_page_version ? null : $this->page->getRawText());
        $content .= '
            <footer>
                <dl>
                    <dt>' . pz_i18n::msg('wiki_page_created') . ':</dt>
                    <dd>' . $this->page->getCreatedFormatted() . ' – ' . htmlspecialchars($this->page->getCreateUser()->getName()) . '</dd>
                    <dt>' . pz_i18n::msg('wiki_page_updated') . ':</dt>
                    <dd>' . $this->page->getUpdatedFormatted() . ' – ' . htmlspecialchars($this->page->getUpdateUser()->getName()) . '</dd>';
        if ($this->page instanceof pz_wiki_page_version) {
            $content .= '
                    <dt>' . pz_i18n::msg('wiki_page_version') . ':</dt>
                    <dd>' . $this->page->getStampFormatted() . ' – ' . htmlspecialchars($this->page->getUser()->getName()) . '</dd>';
        }
        $content .= '
                </dl>
            </footer>
        ';
        return $this->getPageWrapper('view', $content);
    }

    //protected function getPage

    public function getPageCreateView($p = [], $title = '')
    {
        $xform = new rex_xform;

        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_wiki_page','wiki_page_create_form','" . $this->url(['mode' => 'create_form']) . "')");
        $xform->setObjectparams('form_id', 'wiki_page_create_form');

        $this->addBaseFields($xform, $title);

        $xform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $xform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);

        $xform->setActionField('db', ['pz_wiki']);

        $content = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $page = pz_wiki_page::get($xform->getObjectparams('main_id'));
            $page->create($xform->getFieldValue('', '', 'message'));
            $content = pz_screen::getJSUpdatePage($this->url());
        }

        $content = '
            <div id="project_wiki_page" class="design2col wiki article">
                <header>
                    <div class="header">
                        <h1 class="hl1">' . pz_i18n::msg('wiki_add') . '</h1>
                    </div>
                    <ul class="navi-list2 clearfix">
                        <li class="lev1 active"><a class="lev1 active" href="#"><span>' . pz_i18n::msg('wiki_create') . '</span></a></li>
                    </ul>
                </header>
                ' . $content . '
            </div>';

        return $content;
    }

    public function getPageEditView($p = [])
    {
        $xform = new rex_xform;

        $xform->setObjectparams('main_table', 'pz_wiki');
        $xform->setObjectparams('main_id', $this->page->getId());
        $xform->setObjectparams('main_where', 'id=' . $this->page->getId());
        if ($this->page instanceof pz_wiki_page_version) {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM pz_wiki WHERE id = ' . (int) $this->pageId);
            $sql->setValue('title', $this->page->getTitle());
            $sql->setValue('text', $this->page->getRawText());
            $xform->setObjectparams('sql_object', $sql);
        }
        $xform->setObjectparams('getdata', true);

        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_wiki_page','wiki_page_edit_form','" . $this->url(['mode' => 'edit']) . "')");
        $xform->setObjectparams('form_id', 'wiki_page_edit_form');

        $this->addBaseFields($xform);

        $xform->setActionField('db', ['pz_wiki', 'id=' . $this->pageId]);

        $content = $xform->getForm();

        if ($xform->getObjectparams('actions_executed')) {
            $page = pz_wiki_page::get($this->pageId);
            $page->update($xform->getFieldValue('', '', 'message'));
            return pz_screen::getJSUpdatePage($this->url());
        }

        if (pz::getUser()->isAdmin() || $this->projectuser->isAdmin() || pz::getUser()->getId() == $this->page->getCreateUser()->getId()) {
            $url = $this->url(['mode' => 'delete']);
            $content .= '
                <div class="xform">
                    <p>
                        <a class="bt17" href="' . pz::url() . '" onclick="if (confirm(\'' . pz_i18n::msg('wiki_page_delete_question', $this->page->getCurrent()->getTitle()) . '\')) pz_loadPage(\'project_wiki_page\', \'' . $url . '\')">- ' . pz_i18n::msg('wiki_page_delete') . '</a>
                    </p>
                </div>
            ';
        }

        return $this->getPageWrapper('edit', $content);
    }

    protected function addBaseFields(rex_xform $xform, $title = null)
    {
        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);
        $xform->setObjectparams('real_field_names', true);

        $xform->setValueField('text', ['title', pz_i18n::msg('wiki_page_title'), 'default' => $title]);
        $xform->setValidateField('empty', ['title', pz_i18n::msg('error_wiki_title_empty')]);

        $xform->setValueField('html', ['open', '
            <ul class="navi-list2 clearfix" id="wiki_page_text_navi">
                <li><a class="active" href="#xform-formular-text">' . pz_i18n::msg('wiki_page_text_edit') . '</a></li>
                <li><a href="#wiki_page_text_preview">' . pz_i18n::msg('wiki_page_text_preview') . '</a></li>
            </ul>
            <div>
        ']);
        $xform->setValueField('textarea', ['text', pz_i18n::msg('wiki_page_text')]);
        $xform->setValueField('html', ['close', '
                <div id="wiki_page_text_preview"></div>
            </div>
            <script><!--
                $("#wiki_page_text_navi a").click(function () {
                    $("#wiki_page_text_navi a").removeClass("active");
                    $(this).addClass("active");
                    $("#wiki_page_text_navi").next().find("> *").hide();
                    $($(this).attr("href")).show();
                    if ("#wiki_page_text_preview" == $(this).attr("href")) {
                        pz_loadFormPage("wiki_page_text_preview", $(this).closest("form").attr("id"), "' . $this->url(['mode' => 'preview']) . '");
                    }
                    return false;
                });
            --></script>
        ']);

        if (pz::getUser()->isAdmin() || $this->projectuser->isAdmin()) {
            $xform->setValueField('checkbox', ['admin', pz_i18n::msg('wiki_page_admin')]);
        }

        $xform->setValueField('text', ['message', pz_i18n::msg('wiki_page_message'), 'no_db' => 'no_db']);

        $xform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $xform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);

        $xform->setValueField('hidden', ['project_id', $this->project->getId()]);
    }

    public function getPageTextPreview()
    {
        $text = rex_post('text', 'string');
        $text = pz_wiki_page::parseText($this->project->getId(), $text);

        return '<div id="wiki_page_text_preview">' . $this->getPageText($text) . '</div>';
    }

    protected function getPageText($text, $rawText = null)
    {
        $content = '
            <article class="formatted js-task-list-container" id="wiki_page_view">
                <div>
                    ' . $text . '
                </div>';
        if (!is_null($rawText)) {
            $content .= '
                <form class="hidden" id="wiki_page_view_form">
                    <textarea class="js-task-list-field" name="text">' . $this->page->getRawText() . '</textarea>
                </form>';
        }
        $content .= '
            </article>
            <script><!--
                var wrapper = $("#wiki_page_view");
                wrapper.find("> div ul, > div ol").each(function () {
                    var list = $(this);
                    var tasks = list.find("> li > input");
                    if (tasks.length) {
                        list.addClass("task-list");
                        tasks.addClass("task-list-item-checkbox").attr("disabled", "disabled").parent().addClass("task-list-item");
                    }
                });';
        if (!is_null($rawText)) {
            $content .= '
                wrapper.on("tasklist:changed", function (a, b, c) {
                    pz_loadFormPage("project_wiki_page", "wiki_page_view_form", "' . $this->url(['mode' => 'tasklist']) . '");
                });
                wrapper.taskList();';
        }
        $content .= '
            --></script>
            <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>';
        return $content;
    }

    public function getPageHistoryView($p = [])
    {
        $content = '
            <table class="tbl1">';
        $current = true;
        foreach ($this->page->getVersions() as $version) {
            $urlView = $this->url(['mode' => 'view', 'wiki_version_id' => $current ? '' : $version->getVersionId()]);
            $urlRevert = $this->url(['mode' => 'edit', 'wiki_version_id' => $version->getVersionId()]);
            $content .= '
                <tr>
                    <td>' . $version->getStampFormatted() . '</td>
                    <td>' . htmlspecialchars($version->getUser()->getName()) . '</td>
                    <td>' . htmlspecialchars($version->getMessage()) . '</td>
                    <td>
                        <a class="bt2" href="javascript:pz_loadPage(\'project_wiki_page\', \'' . $urlView . '\')">' . pz_i18n::msg('wiki_view') . '</a>
                        ' . ($current ? '' : '<a class="bt2" href="javascript:pz_loadPage(\'project_wiki_page\', \'' . $urlRevert . '\')">' . pz_i18n::msg('wiki_page_revert') . '</a>') . '
                    </td>
                </tr>
            ';
            $current = false;
        }
        $content .= '
            </table>
        ';
        return $this->getPageWrapper('history', $content);
    }

    private function getPageWrapper($active, $content)
    {
        $info = '';
        if ($this->page instanceof pz_wiki_page_version) {
            $info = ' <span class="info">(' . pz_i18n::msg('wiki_page_version') . ': ' . $this->page->getStampFormatted() . ')</span> ';
            $info .= '<a class="bt2" href="javascript:pz_loadPage(\'project_wiki_page\', \'' . $this->url(['mode' => $active]) . '\')">' . pz_i18n::msg('wiki_page_current') . '</a>';
        }
        $return = '
            <div id="project_wiki_page" class="design2col wiki article">
                <header>
                    <div class="header">
                        <h1 class="hl1">' . htmlspecialchars($this->page->getTitle()) . $info . '</h1>
                    </div>
                    <ul class="navi-list2 clearfix">';

        $navi = ['view', 'edit', 'history'];
        foreach ($navi as $mode) {
            $class = $mode == $active ? ' active' : '';
            $url = $this->url([
                'wiki_version_id' => 'history' === $mode ? '' : $this->versionId,
                'mode' => $mode
            ]);
            $return .= '<li class="lev1' . $class . '"><a class="lev1' . $class . '" href="javascript:pz_loadPage(\'project_wiki_page\', \'' . $url . '\')"><span>' . pz_i18n::msg('wiki_' . $mode) . '</span></a></li>';
        }

        $return .= '
                    </ul>
                </header>
                ' . $content . '
            </div>';
        return $return;
    }

    protected function url(array $params = [], $split = '&')
    {
        return pz::url('screen', 'project', 'wiki', array_merge([
            'project_id' => $this->project->getId(),
            'wiki_id' => $this->pageId
        ], $params), $split);
    }
}
