<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'debug' => (bool) env('APP_DEBUG', true),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'valhalla'),
            'username' => env('DB_USERNAME', 'valhalla'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'postgres' => [
            'driver' => 'pdo_pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'valhalla'),
            'username' => env('DB_USERNAME', 'valhalla'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8',
        ],
    ],
];
