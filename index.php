<?php

@include_once __DIR__ . '/vendor/autoload.php';

\Kirby\Cms\App::plugin('johannschopplich/kirbylog', [
    'options' => [
        'dir' => fn () => kirby()->root('logs'),
        'filename' => strftime('%Y-%m-%d') . '.log',
        'levels' => [
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        ]
    ]
]);
