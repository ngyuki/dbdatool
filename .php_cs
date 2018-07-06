<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path('src/')
;

return PhpCsFixer\Config::create()
    ->setRules(['@PSR2' => true])
    ->setFinder($finder)
;
