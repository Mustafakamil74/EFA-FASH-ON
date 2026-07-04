<?php

namespace App\Core;

/**
 * Authentication & authorization.
 * Manages the logged-in user in the session, "remember me" tokens,
 * and permission checks driven by role_permissions.
 */
class Auth
{
    private static ?array $user = null;
    private static ?array $permissions = null;

    /** Attempt to log a user in with username + password. */
    public static function attempt(string $username, string $password, bool $remember = false): bool
    {
        $user = Database::first(
            'SELECT * FROM users WHERE username = ? AND is_active = 1 AND deleted_at IS NULL',
            [$username]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        self::login($user);
        Database::query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        if ($remember) {
            self::issueRememberToken((int) $user['id']);
        }
        return true;
    }

    public static function login(array $user): void
    {
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        self::$user = $user;
        self::$permissions = null;
    }

    public static function logout(): void
    {
        if (!empty($_COOKIE['remember'])) {
            [$selector] = array_pad(explode(':', $_COOKIE['remember'], 2), 2, '');
            if ($selector) {
                Database::query('DELETE FROM auth_tokens WHERE selector = ?', [$selector]);
            }
            setcookie('remember', '', time() - 3600, '/');
        }
        $_SESSION = [];
        session_destroy();
        self::$user = null;
        self::$permissions = null;
    }

    /** Resolve the current user from the session or a remember cookie. */
    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        if (!empty($_SESSION['user_id'])) {
            self::$user = Database::first(
                'SELECT * FROM users WHERE id = ? AND deleted_at IS NULL',
                [$_SESSION['user_id']]
            );
            return self::$user;
        }
        if (!empty($_COOKIE['remember'])) {
            $u = self::userFromRememberCookie($_COOKIE['remember']);
            if ($u) {
                self::login($u);
                return $u;
            }
        }
        return null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        if (!$u) {
            return null;
        }
        return Database::scalar('SELECT name FROM roles WHERE id = ?', [$u['role_id']]) ?: null;
    }

    /** Permission check. Super admin always passes. */
    public static function can(string $permission): bool
    {
        $u = self::user();
        if (!$u) {
            return false;
        }
        if (self::permissions() === ['*']) {
            return true;
        }
        return in_array($permission, self::permissions(), true);
    }

    public static function permissions(): array
    {
        if (self::$permissions !== null) {
            return self::$permissions;
        }
        $u = self::user();
        if (!$u) {
            return self::$permissions = [];
        }
        if (self::role() === 'super_admin') {
            return self::$permissions = ['*'];
        }
        $rows = Database::all(
            'SELECT p.name FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?',
            [$u['role_id']]
        );
        return self::$permissions = array_column($rows, 'name');
    }

    // -- Remember-me token handling (selector/validator split) ----------

    private static function issueRememberToken(int $userId): void
    {
        $selector  = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $expires   = (new \DateTime('+30 days'))->format('Y-m-d H:i:s');
        Database::query(
            'INSERT INTO auth_tokens (user_id, selector, validator_hash, expires_at) VALUES (?,?,?,?)',
            [$userId, $selector, hash('sha256', $validator), $expires]
        );
        setcookie('remember', $selector . ':' . $validator, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function userFromRememberCookie(string $cookie): ?array
    {
        [$selector, $validator] = array_pad(explode(':', $cookie, 2), 2, '');
        if (!$selector || !$validator) {
            return null;
        }
        $row = Database::first(
            'SELECT * FROM auth_tokens WHERE selector = ? AND expires_at > NOW()',
            [$selector]
        );
        if (!$row || !hash_equals($row['validator_hash'], hash('sha256', $validator))) {
            return null;
        }
        return Database::first(
            'SELECT * FROM users WHERE id = ? AND is_active = 1 AND deleted_at IS NULL',
            [$row['user_id']]
        );
    }
}
