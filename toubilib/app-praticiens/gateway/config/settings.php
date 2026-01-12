<?php
declare(strict_types=1);

return [
    'displayErrorDetails' => true,
    'logErrors' => true,
    'logErrorDetails' => true,
    
    'logs.dir' => __DIR__ . '/../var/logs',
    
    'api.toubilib' => [
        'base_uri' => 'http://api.toubilib:80',
        'timeout' => 10.0,
    ],
];
