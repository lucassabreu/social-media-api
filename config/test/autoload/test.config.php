<?php

return [
    'db' => [
        'driver' => 'PDO',
        'dsn' => 'sqlite:' . __DIR__ . '/../../data/test.db',
    ],
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => [
                    'path'=> __DIR__ . '/../../data/test.db',
                ]
            ]
        ],
    ],
];