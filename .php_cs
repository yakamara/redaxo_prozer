<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        '-psr0',
        '-concat_without_spaces',
        '-empty_return',
        '-return',
        '-phpdoc_no_package',
        'short_array_syntax',
    ))
    ->finder($finder)
;
