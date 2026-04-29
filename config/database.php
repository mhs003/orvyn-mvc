<?php

return [
    'enable' => true,
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'orvyn-db',
            'username' => 'root',
            'password' => '1234',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database.sqlite',
        ]
    ]
];
