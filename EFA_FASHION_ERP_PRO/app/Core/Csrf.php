<?php

namespace App\Core;

/**
 * CSRF token generation and verification (synchronizer token pattern).
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    /** Abort the request if the submitted token is invalid. */
    public static function check(): void
    {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!self::verify($token)) {
            http_response_code(419);
            exit('Invalid or expired CSRF token. Please go back and retry.');
        }
    }
}
