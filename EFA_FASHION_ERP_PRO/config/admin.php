<?php
/**
 * Default Super Admin used by database/migrate.php. Accessed via config('admin').
 */
return [
    'name'     => env('ADMIN_NAME', 'Super Admin'),
    'username' => env('ADMIN_USERNAME', 'admin'),
    'email'    => env('ADMIN_EMAIL', 'admin@efa.local'),
    'password' => env('ADMIN_PASSWORD', 'Admin@123'),
];
