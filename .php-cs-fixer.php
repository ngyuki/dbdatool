<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path('src/')
;

return (new PhpCsFixer\Config())
    ->setRules(['@PSR2' => true])
    ->setFinder($finder)
;
