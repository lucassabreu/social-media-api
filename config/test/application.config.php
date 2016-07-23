<?php

return [
    'module_listener_options' => [
        'config_glob_paths' => [
            'config/test/autoload/{{,*.}global,{,*.}local}.php',
        ],
    ],
    'ddl' => include __DIR__ . '/ddl.config.php',
];