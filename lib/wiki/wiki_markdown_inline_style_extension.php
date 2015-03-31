<?php

use Ciconia\Common\Text;

class pz_wiki_markdown_inline_style_extension extends \Ciconia\Extension\Core\InlineStyleExtension
{
    /**
     * @param Text $text
     */
    public function processBold(Text $text)
    {
        if (!$text->contains('*')) {
            return;
        }
        /* @noinspection PhpUnusedParameterInspection */
        $text->replace('{ (\*) (?=\S) (.+?[*]*) (?<=\S) \1 }sx', function (Text $w, Text $a, Text $target) {
            return $this->getRenderer()->renderBoldText($target);
        });
    }

    /**
     * @param Text $text
     */
    public function processItalic(Text $text)
    {
        if (!$text->contains('_')) {
            return;
        }
        /* @noinspection PhpUnusedParameterInspection */
        $text->replace('{ (_) (?=\S) (.+?) (?<=\S) \1 }sx', function (Text $w, Text $a, Text $target) {
            return $this->getRenderer()->renderItalicText($target);
        });
    }
}
