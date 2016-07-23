<?php

return [
    // ...
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'social-media-test',
                ]
            ]
        ]
    ],
    'db' => [
        'driver' => 'PDO',
        'dsn' => 'mysql:host=localhost;dbname=social-media-test',
        'username' => 'root',
        'password' => 'root',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ],
];