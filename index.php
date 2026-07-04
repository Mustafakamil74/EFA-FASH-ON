<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Front controller — the single entry point for all web requests.
 * Apache/Nginx (or the PHP built-in server router) sends every request here.
 */
require __DIR__ . '/EFA_FASHION_ERP_PRO/vendor/autoload.php';
use App\Core\App;
use App\Core\Router;
App::boot();
/** @var Router $router */
$route = require __DIR__ . '/EFA_FASHION_ERP_PRO/config/routes.php';
App::run($router);
