<?php
/**
 * Global helper functions, autoloaded by Composer.
 */

if (!function_exists('env')) {
    /**
     * Read an environment variable with a fallback default.
     * Values are loaded from .env by App\Core\Env at bootstrap.
     */
    function env(string $key, $default = null)
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }
        return $val;
    }
}

if (!function_exists('config')) {
    /** Access a config value using dot notation, e.g. config('db.host'). */
    function config(string $key, $default = null)
    {
        static $cache = [];
        $parts = explode('.', $key);
        $file  = array_shift($parts);
        if (!isset($cache[$file])) {
            $path = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            $cache[$file] = is_file($path) ? require $path : [];
        }
        $value = $cache[$file];
        foreach ($parts as $p) {
            if (is_array($value) && array_key_exists($p, $value)) {
                $value = $value[$p];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('e')) {
    /** HTML-escape a string for safe output. */
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('url')) {
    /** Build an absolute URL from a path relative to the app root. */
function url(string $path = ''): string
{
    $base = '/EFA_FASHION_ERP_PRO/public';
    return $base . '/' . ltrim($path, '/');
}
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('old')) {
    /** Retrieve a previously submitted form value flashed to the session. */
    function old(string $key, $default = '')
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = \App\Core\Csrf::token();
        return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
    }
}

if (!function_exists('__')) {
    /** Translate a key for the current language. */
    function __(string $key, array $replace = []): string
    {
        return \App\Core\Lang::get($key, $replace);
    }
}

if (!function_exists('money')) {
    /** Format a monetary amount. */
    function money($amount, string $currency = ''): string
    {
        $n = number_format((float) $amount, 2);
        return $currency ? ($n . ' ' . $currency) : $n;
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return \App\Core\Auth::can($permission);
    }
}

if (!function_exists('flash')) {
    /** Set or get a one-time flash message. */
    function flash(?string $type = null, ?string $message = null)
    {
        if ($type === null) {
            $msgs = $_SESSION['_flash'] ?? [];
            unset($_SESSION['_flash']);
            return $msgs;
        }
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}
