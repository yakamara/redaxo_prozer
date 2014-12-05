<?php

class pz_sabre_single_line_parser extends \Sabre\VObject\Parser\MimeDir
{
    public static function parseSingeLine(Sabre\VObject\Document $document, $line)
    {
        $parser = new self();
        $parser->root = $document;
        $parser->setInput($line);
        $line = $parser->readLine();
        return $parser->readProperty($line);
    }
}
