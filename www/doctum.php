<?php

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
                  ->files()
                  ->name('*.php')
                  ->exclude([
                                'vendor',
                                'cache',
                                'var',
                                'tests',
                                'Test',
                                'views',
                                'logs',
                                'crons',
                                'public',
                                'tools',
                            ])
                  ->in(__DIR__ . '/app');

return new Doctum($iterator, [
    'title'      => 'Template App – API',
    'language'   => 'fr',
    'build_dir'  => __DIR__ . '/build/docs',
    'cache_dir'  => __DIR__ . '/build/doctum-cache',
    
    // important : pas de repo distant tant que le socle n’est pas stabilisé
    'source_dir' => __DIR__,
]);