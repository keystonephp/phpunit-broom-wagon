<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src');

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'phpdoc_align' => false,
        'array_syntax' => ['syntax' => 'short'],
    ]);
