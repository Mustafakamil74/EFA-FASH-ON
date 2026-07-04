<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Closing extends Model
{
    protected static string $table = 'closings';
    protected static array $fillable = [
        'period_type', 'period_label', 'total_sales', 'total_expenses',
        'total_income', 'profit', 'closed_by',
    ];

    public static function recent(int $limit = 50): array
    {
        return Database::all('SELECT * FROM closings ORDER BY closed_at DESC LIMIT ' . (int) $limit);
    }
}
