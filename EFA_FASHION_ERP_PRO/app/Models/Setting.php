<?php

namespace App\Models;

use App\Core\Database;

/**
 * Key/value application settings (company info, currencies, PDF/print options).
 */
class Setting
{
    private static ?array $cache = null;

    /** Return all settings as an associative array (cached per request). */
    public static function all(): array
    {
        if (self::$cache === null) {
            $rows = Database::all('SELECT skey, svalue FROM settings');
            self::$cache = array_column($rows, 'svalue', 'skey');
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        Database::query(
            'INSERT INTO settings (skey, svalue) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)',
            [$key, $value]
        );
        self::$cache = null;
    }
}
