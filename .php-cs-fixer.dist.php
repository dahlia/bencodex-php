<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . "/src",
        __DIR__ . "/tests",
    ]);

$config = (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short']
    ])
    ->setFinder($finder);

return $config;
