<?php

namespace App\Models;

use App\Core\Database;

class Currency
{
    public static function all(): array
    {
        return Database::all('SELECT * FROM currencies ORDER BY is_base DESC, code');
    }

    public static function upsert(string $code, string $name, ?string $symbol, float $rate, bool $isBase): void
    {
        if ($isBase) {
            Database::query('UPDATE currencies SET is_base = 0');
        }
        Database::query(
            'INSERT INTO currencies (code, name, symbol, rate_to_base, is_base)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE name=VALUES(name), symbol=VALUES(symbol),
                 rate_to_base=VALUES(rate_to_base), is_base=VALUES(is_base)',
            [strtoupper($code), $name, $symbol, $rate, $isBase ? 1 : 0]
        );
    }

    public static function delete(string $code): void
    {
        Database::query('DELETE FROM currencies WHERE code = ? AND is_base = 0', [strtoupper($code)]);
    }
}
