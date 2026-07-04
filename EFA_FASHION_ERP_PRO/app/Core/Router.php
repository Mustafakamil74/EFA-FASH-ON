<?php

namespace App\Core;

/**
 * Tiny regex-based router. Routes are registered as
 *   $router->get('/customers/{id}', [CustomerController::class, 'show']);
 * Path params are passed to the action in order.
 */
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path   = '/' . trim(parse_url($uri, PHP_URL_PATH) ?? '', '/');
    
       
$path = str_replace('/EFA_FASHION_ERP_PRO/public', '', $path);
$path = $path === '' ? '/' : $path;
        // Normalize a base-path prefix if the app is served from a subfolder.
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $m)) {
                array_shift($m);
                [$class, $action] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $action], $m);
                return;
            }
        }

        http_response_code(404);
        echo View::render('errors.404', [], 'app');
    }
}
