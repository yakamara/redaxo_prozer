<?php

class pz_project_space_screen
{
    protected $project;
    protected $projectuser;
    protected $page;
    protected $pageId;
    protected $versionId;

    public function __construct(pz_project $project, $page = NULL)
    {
        $this->project = $project;
        $this->page = $page;
        $this->pageId = 0;
        if ($this->page instanceof pz_space_page) {
            $this->pageId = $page->getId();
        }
    }

    // ------------------------------------------------------------------ Navigation

    /**
     * @param array          $p
     * @param pz_space_page[] $pages
     *
     * @return string
     */
    public function getNavigationView($p = [], array $pages)
    {
       $return = [];
        foreach ($pages as $page) {
            $page_screen = new pz_project_space_screen($this->project, $page);
            $return[] = $page_screen->getPageView();
        }

        return implode("\n",$return);
    }

    // ------------------------------------------------------------------ Page

    public function getPageView($p = [])
    {

        $positions = $this->page->getPosition();

        $content = '<div id="space-'.$this->page->getId().'" class="space-page" data-space-id="'.$this->page->getId().'"
            style="width:200px; height:150px;background-color:'.$this->page->getColor().';padding:10px 10px; overflow:scroll;
            position:absolute;left:'.$positions[0].'px; top:'.$positions[1].'px
            ">

        <p>
        <b>'.htmlspecialchars($this->page->getTitle()).'</b><br />
        '.nl2br(htmlspecialchars($this->page->getShortText())).'
        </p>
        </div>';

        return $content;


        $content = $this->getPageText($this->page->getText(), $this->page instanceof pz_space_page_version ? null : $this->page->getRawText());
        $content .= '
            <footer>
                <div class="space-meta">
                    <dl class="space-meta-list">
                        <dt>' . pz_i18n::msg('space_page_created') . ':</dt>
                        <dd><span class="time">' . $this->page->getCreatedFormatted() . '</span> <span class="author">' . htmlspecialchars($this->page->getCreateUser()->getName()) . '</span></dd>
                    </dl>
                    <dl class="space-meta-list">
                        <dt>' . pz_i18n::msg('space_page_updated') . ':</dt>
                        <dd><span class="time">' . $this->page->getUpdatedFormatted() . '</span> <span class="author">' . htmlspecialchars($this->page->getUpdateUser()->getName()) . '</span></dd>
                    </dl>';

        $content .= '
                </div>
            </footer>
        ';
        return $content;
    }

    public function getPageCreateView($p = [], $title = '')
    {
        $yform = new rex_yform();

        // $yform->setDebug();

        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_space_page','space_page_create_form','" . $this->url(['mode' => 'create_form']) . "')");
        $yform->setObjectparams('form_id', 'space_page_create_form');

        $this->addBaseFields($yform, $title);

        $yform->setValueField('datestamp', ['created', 'mysql', '', '0', '1']);
        $yform->setValueField('hidden', ['create_user_id', pz::getUser()->getId()]);

        $yform->setActionField('db', ['pz_space']);

        $content = $yform->getForm();

        if ($yform->getObjectparams('actions_executed')) {

            $this->page = pz_space_page::get($yform->getObjectparams('main_id'));
            $this->page->create($yform->getFieldValue('', '', 'created'));

            $content = pz_screen::getJSUpdatePage($this->url());
        }

        $content = '
            <div id="project_space_page">
                <header>
                    <div class="header">
                        <h1 class="hl1">' . pz_i18n::msg('space_add') . '</h1>
                    </div>
                </header>
                <article class="space-editor">
                ' . $content . '
                </article>
            </div>';

        return $content;
    }

    public function getPageEditView($p = [])
    {
        $yform = new rex_yform();

        $yform->setObjectparams('main_table', 'pz_space');
        $yform->setObjectparams('main_id', $this->page->getId());
        $yform->setObjectparams('main_where', 'id=' . $this->page->getId());

        $yform->setObjectparams('getdata', true);

        $yform->setObjectparams('form_action', "javascript:pz_loadFormPage('project_space_page','space_page_edit_form','" . $this->url(['mode' => 'edit']) . "')");
        $yform->setObjectparams('form_id', 'space_page_edit_form');

        $this->addBaseFields($yform);

        $yform->setActionField('db', ['pz_space', 'id=' . $this->pageId]);

        $content = '<article class="space-editor">' . $yform->getForm() . '</article>';

        if ($yform->getObjectparams('actions_executed')) {
            $page = pz_space_page::get($this->page->getId());
            $page->update($yform->getFieldValue('', '', 'message'));
            return pz_screen::getJSUpdatePage($this->url());
        }
        return $this->getPageWrapper('edit', $content);
    }

    protected function addBaseFields(rex_yform $yform, $title = null)
    {
        $yform->setValueField('objparams', ['fragment', 'pz_screen_yform.tpl']);
        $yform->setObjectparams('real_field_names', true);
        $yform->setObjectparams('form_skin', "bootstrap");

        $yform->setValueField('text', ['title', pz_i18n::msg('space_page_title'), 'default' => $title]);
        $yform->setValidateField('empty', ['title', pz_i18n::msg('error_space_title_empty')]);

        $yform->setValueField('html', ['open', '
            <nav class="tabnav tabnav-down space-editor-tabnav" id="space_page_text_navi">
                <ul class="tabnav-tabs">
                    <li><a class="tabnav-tab active" href="#space_page_text_edit">' . pz_i18n::msg('space_page_text_edit') . '</a></li>
                    <li><a class="tabnav-tab" href="#space_page_text_preview">' . pz_i18n::msg('space_page_text_preview') . '</a></li>
                    <li><a class="tabnav-tab" href="#space_page_text_help">' . pz_i18n::msg('space_page_text_help') . '</a></li>
                </ul>
            </nav>
            <div class="space-editor-write-content">
                <div id="space_page_text_edit">
        ']);
        $yform->setValueField('textarea', ['shorttext', pz_i18n::msg('space_page_shorttext')]);
        $yform->setValueField('textarea', ['text', pz_i18n::msg('space_page_text')]);
        $yform->setValueField('html', ['close', '
                </div>
                <div class="space-preview-content markdown-body" id="space_page_text_preview"></div>
                <div class="markdown-body" id="space_page_text_help" style="display: none">
                    <table>
                        <thead>
                            <tr>
                                <th>Eingabe</th>
                                <th>Ausgabe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    # Überschrift 1<br>
                                    ## Überschrift 2<br>
                                    ### Überschrift 3
                                </td>
                                <td>
                                    <h1>Überschrift 1</h1>
                                    <h2>Überschrift 2</h2>
                                    <h3>Überschrift 3</h3>
                                </td>
                            </tr>
                            <tr>
                                <td>Dies ist *fetter Text*.</td>
                                <td>Dies ist <strong>fetter Text</strong>.</td>
                            </tr>
                            <tr>
                                <td>Dies ist _kursiver Text_.</td>
                                <td>Dies ist <em>kursiver Text</em>.</td>
                            </tr>
                            <tr>
                                <td>
                                    Automatische Verlinkung: http://prozer.de<br>
                                    Expliziter Link mit Linktext: [Prozer](http://prozer.de)<br>
                                    Interner Link: [[Seitentitel]]<br>
                                    Interner Link mit Linktext: [[Linktext|Seitentitel]]
                                </td>
                                <td>
                                    Automatische Verlinkung: <a href="http://prozer.de">http://prozer.de</a><br>
                                    Expliziter Link mit Linktext: <a href="http://prozer.de">Prozer</a><br>
                                    Interner Link: <a href="#" class="internal">Seitentitel</a><br>
                                    Interner Link mit Linktext: <a href="#" class="internal">Linktext</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    * Listenpunkt 1<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;* Unterpunkt 1 (Einrückung mit 4 Leerzeichen)<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;* Unterpunkt 2<br>
                                    * Listenpunkt 2<br>
                                    * Listenpunkt 3
                                </td>
                                <td>
                                    <ul>
                                        <li>Listenpunkt 1
                                            <ul>
                                                <li>Unterpunkt 1 (Einrückung mit 4 Leerzeichen)</li>
                                                <li>Unterpunkt 2</li>
                                            </ul>
                                        </li>
                                        <li>Listenpunkt 2</li>
                                        <li>Listenpunkt 3</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    1. Listenpunkt 1<br>
                                    2. Listenpunkt 2<br>
                                    3. Listenpunkt 3
                                </td>
                                <td>
                                    <ol>
                                        <li>Listenpunkt 1</li>
                                        <li>Listenpunkt 2</li>
                                        <li>Listenpunkt 3</li>
                                    </ol>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    - [ ] Task 1<br>
                                    - [x] Task 2<br>
                                    - [ ] Task 3
                                </td>
                                <td>
                                    <ul class="task-list">
                                        <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox"/> Task 1</li>
                                        <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox" checked/> Task 2</li>
                                        <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox"/> Task 3</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    > Dies ist ein Zitat<br>
                                    > über mehrere Zeilen.
                                </td>
                                <td>
                                    <blockquote>Dies ist ein Zitat<br>über mehrere Zeilen.</blockquote>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <script><!--
                $("#space_page_text_navi a").click(function () {
                    $("#space_page_text_navi a").removeClass("active");
                    $(this).addClass("active");
                    $("#space_page_text_navi").next().find("> *").hide();
                    $($(this).attr("href")).show();
                    if ("#space_page_text_preview" == $(this).attr("href")) {
                        pz_loadFormPage("space_page_text_preview", $(this).closest("form").attr("id"), "' . $this->url(['mode' => 'preview']) . '");
                    }
                    return false;
                });
            --></script>
        ']);

        $yform->setValueField("select",['color',pz_i18n::msg('space_page_position'),'#eee,#f90,#e4d836']);

        $yform->setValueField('datestamp', ['updated', 'mysql', '', '0', '0']);
        $yform->setValueField('hidden', ['update_user_id', pz::getUser()->getId()]);
        $yform->setValueField('hidden', ['project_id', $this->project->getId()]);
        $yform->setValueField('text', ['position', pz_i18n::msg('space_page_position')]);

    }

    public function getPageTextPreview()
    {
        $text = stripslashes(rex_post('text', 'string'));
        $text = pz_space_page::parseText($this->project->getId(), $text);

        return '<div class="space-preview-content markdown-body" id="space_page_text_preview">' . $this->getPageText($text) . '</div>';
    }

    protected function getPageText($text, $rawText = null)
    {
        $content = '
            <article class="markdown-body js-task-list-container" id="wiki_page_view">
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
                    var tasks = list.find("> li input");
                    if (tasks.length) {
                        list.addClass("task-list");
                        tasks.addClass("task-list-item-checkbox").attr("disabled", "disabled").closest("li").addClass("task-list-item");
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

    private function getPageWrapper($active, $content)
    {
        $info = '';
        $button = '';
        $return = '
            <div id="project_wiki_page" class="design2col wiki article">
                <header>
                    <div class="header grid2col">
                        <div class="column first">
                            <h1 class="hl1">' . htmlspecialchars($this->page->getTitle()) . $info . '</h1>
                        </div>
                        <div class="column last">
                            ' . $button . '
                        </div>
                    </div>
                </header>

                <nav class="tabnav">
                    <ul class="tabnav-tabs">';

        $navi = ['view', 'edit', 'history'];
        foreach ($navi as $mode) {
            $class = $mode == $active ? ' active' : '';
            $url = $this->url([
                'wiki_version_id' => 'history' === $mode ? '' : $this->versionId,
                'mode' => $mode,
            ]);
            $return .= '<li><a class="tabnav-tab' . $class . '" href="javascript:pz_loadPage(\'project_wiki_page\', \'' . $url . '\')"><span>' . pz_i18n::msg('wiki_' . $mode) . '</span></a></li>';
        }

        $return .= '
                    </ul>
                </nav>
                ' . $content . '
            </div>';
        return $return;
    }


    protected function url(array $params = [], $split = '&')
    {
        return pz::url('screen', 'project', 'space', array_merge([
            'project_id' => $this->project->getId(),
            'space_id' => $this->pageId,
        ], $params), $split);
    }
}
