<?php

use Ciconia\Common\Text;

class pz_wiki_markdown_internal_link_extension implements Ciconia\Extension\ExtensionInterface
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Ciconia\Markdown $markdown)
    {
        $markdown->on('inline', [$this, 'processInternalLink'], 100);
    }

    /**
     * Turn standard URL into markdown URL.
     *
     * @param Text $text
     */
    public function processInternalLink(Text $text)
    {
        $hashes = [];

        // escape <code>
        $text->replace('{<code>.*?</code>}m', function (Text $w) use (&$hashes) {
            $md5 = md5($w);
            $hashes[$md5] = $w;

            return "{gfm-extraction-$md5}";
        });

        $sql = rex_sql::factory();
        $sql->prepareQuery('SELECT id FROM pz_wiki WHERE project_id = ? AND title = ?');
        $text->replace('{\[\[(?:([^\]]*)\|)?([^\]]*)\]\]}', function (Text $complete, Text $title, Text $pageTitle) use ($sql) {
            if ($title->isEmpty()) {
                $title = $pageTitle;
            }
            $sql->execute([$this->projectId, (string) $pageTitle]);
            if ($sql->getRows()) {
                $url = pz::url('screen', 'project', 'wiki', ['project_id' => $this->projectId, 'wiki_id' => $sql->getValue('id')]);
                $class = '';
            } else {
                $url = pz::url('screen', 'project', 'wiki', ['project_id' => $this->projectId, 'mode' => 'create', 'wiki_title' => (string) $pageTitle]);
                $class = ' missing';
            }
            return '<a href="' . $url . '" class="internal' . $class . '">' . htmlspecialchars($title) . '</a>';
        });

        /* @noinspection PhpUnusedParameterInspection */
        $text->replace('/\{gfm-extraction-([0-9a-f]{32})\}/m', function (Text $w, Text $md5) use (&$hashes) {
            return $hashes[(string) $md5];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'internalLink';
    }
}
