<?php
/**
 * Front controller — the single entry point for all web requests.
 * Apache/Nginx (or the PHP built-in server router) sends every request here.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Router;

App::boot();

/** @var Router $router */
$router = require dirname(__DIR__) . '/config/routes.php';

App::run($router);
