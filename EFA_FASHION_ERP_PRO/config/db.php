<?php
/**
 * Database configuration. Accessed via config('db.*') or config('db').
 */
return [
    'host'    => env('DB_HOST', '127.0.0.1'),
    'port'    => env('DB_PORT', '3306'),
    'name'    => env('DB_NAME', 'efa_erp_pro'),
    'user'    => env('DB_USER', 'efa'),
    'pass'    => env('DB_PASS', 'efa_pass'),
    'charset' => 'utf8mb4',
];
