<?php
/**
 * Application configuration. Accessed via config('app.*').
 */
return [
    'name'         => env('APP_NAME', 'EFA FASHION ERP PRO'),
    'env'          => env('APP_ENV', 'local'),
    'debug'        => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
    'url'          => env('APP_URL', 'http://localhost:8080'),
    'key'          => env('APP_KEY', 'efa-dev-key-change-me'),
    'langs'        => ['en', 'ar', 'tr', 'ru', 'zh'],
    'rtl_langs'    => ['ar'],
    'default_lang' => env('DEFAULT_LANG', 'ar'),
];
