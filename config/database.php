<?php

return [
    'usedriver' => 'mysql', // which driver to use in this project | keep empty if planning not to use any database

    'mysql' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'orvyn-test',
        'username' => 'root',
        'password' => '1234',
        'charset' => 'utf8mb4',
    ],
    'sqlite' => [
        'database' => 'testsqlite_database',
        'charset' => 'utf8mb4',
    ]
];