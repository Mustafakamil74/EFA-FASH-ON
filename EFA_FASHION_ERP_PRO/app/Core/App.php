<?php

namespace App\Core;

/**
 * Application bootstrap: loads env, starts the session, configures error
 * handling, boots language, and dispatches the request through the router.
 */
class App
{
    public static function boot(): void
    {
        // Load environment (.env preferred, fall back to .env.example defaults)
        Env::load(base_path('.env'));

        // Sessions
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }

        // Error handling
        self::registerErrorHandling();

        // Language: user preference > query (?lang=) > session > default
        $lang = $_GET['lang']
            ?? ($_SESSION['lang'] ?? null);
        if (isset($_GET['lang'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $user = Auth::user();
        $lang = $_SESSION['lang']
            ?? ($user['lang'] ?? config('app.default_lang', 'en'));
        
        Lang::boot($lang);
    }

    public static function run(Router $router): void
   {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    $router->dispatch($method, $uri);
   }

    private static function registerErrorHandling(): void
    {
        $debug = config('app.debug', false);
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');

        set_exception_handler(function (\Throwable $e) use ($debug) {
            self::logError($e);
            http_response_code(500);
            if ($debug) {
                echo '<pre style="padding:20px;font-family:monospace">';
                echo e($e->getMessage()) . "\n\n" . e($e->getTraceAsString());
                echo '</pre>';
            } else {
                echo View::render('errors.500', [], 'app');
            }
        });
    }

    private static function logError(\Throwable $e): void
    {
        $line = sprintf("[%s] %s in %s:%d\n", date('c'), $e->getMessage(), $e->getFile(), $e->getLine());
        @file_put_contents(base_path('storage/logs/error.log'), $line, FILE_APPEND);
        try {
            Database::query(
                'INSERT INTO error_logs (level, message, file, line, trace) VALUES (?,?,?,?,?)',
                ['error', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()]
            );
        } catch (\Throwable $ignore) {
            // DB may be unavailable; file log already written.
        }
    }
}
