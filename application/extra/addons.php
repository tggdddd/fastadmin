<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'crontab',
        ],
        'config_init' => [
            'summernote',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
