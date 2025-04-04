<?php

return [
    /**
     * ---------------------------------------------------------
     * Current database settings
     * ---------------------------------------------------------
     *
     * Current configuration to use.
     */
    'current' => 'sleekdb',
    
    /**
     * ---------------------------------------------------------
     * Database Connections
     * ---------------------------------------------------------
     *
     * You can define as many database configurations as you want.
     *
     * driver: mysql, pgsql, sqlite
     * host: The database server (localhost)
     * dbname: The database name
     * username: Username of the database server
     * password: Password of the database server
     * charset: Default charset
     */
    'mysql' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'dbname' => env('DB_NAME'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8'),
        'orm' => \Quantum\Libraries\Database\Adapters\Idiorm\IdiormDbal::class
    ],
    'sleekdb' => [
        'driver' => 'sleekdb',
        'config' => [
            'auto_cache' => false,
            'cache_lifetime' => null,
            'timeout' => false,
            'search' => [
                'min_length' => 2,
                'mode' => 'or',
                'score_key' => 'scoreKey',
                'algorithm' => 1
            ],
        ],
        'database_dir' => base_dir() . DS . 'shared' . DS . 'store',
        'orm' => \Quantum\Libraries\Database\Adapters\Sleekdb\SleekDbal::class
    ],
];