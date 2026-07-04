<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Shop extends Model
{
    protected static string $table = 'shops';
    protected static array $fillable = ['code', 'contact_name', 'name', 'phone', 'address', 'notes'];
    protected static bool $softDelete = true;

    public static function search(string $q): array
    {
        if ($q === '') {
            return self::all();
        }
        $like = '%' . $q . '%';
        return Database::all(
            'SELECT * FROM shops WHERE deleted_at IS NULL
             AND (code LIKE ? OR name LIKE ? OR contact_name LIKE ? OR phone LIKE ?)
             ORDER BY id DESC',
            [$like, $like, $like, $like]
        );
    }

    public static function codeExists(string $code, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM shops WHERE code = ?';
        $params = [$code];
        if ($exceptId) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        return (int) Database::scalar($sql, $params) > 0;
    }
}
