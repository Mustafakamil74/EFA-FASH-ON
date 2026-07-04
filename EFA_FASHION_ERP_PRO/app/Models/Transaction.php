<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Transaction extends Model
{
    protected static string $table = 'transactions';
    protected static array $fillable = ['kind', 'category', 'currency', 'amount', 'txn_date', 'note', 'user_id'];

    public static function listing(string $kind = ''): array
    {
        $sql = 'SELECT * FROM transactions WHERE 1=1';
        $params = [];
        if ($kind !== '') {
            $sql .= ' AND kind = ?';
            $params[] = $kind;
        }
        $sql .= ' ORDER BY txn_date DESC, id DESC';
        return Database::all($sql, $params);
    }

    public static function totalBetween(string $kind, string $from, string $to): float
    {
        return (float) Database::scalar(
            'SELECT COALESCE(SUM(amount),0) FROM transactions WHERE kind = ? AND txn_date BETWEEN ? AND ?',
            [$kind, $from, $to]
        );
    }
}
