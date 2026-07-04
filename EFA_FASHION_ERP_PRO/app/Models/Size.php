<?php

namespace App\Models;

use App\Core\Model;

class Size extends Model
{
    protected static string $table = 'sizes';
    protected static array $fillable = ['name', 'sort_order'];

    public static function all(string $orderBy = 'sort_order ASC, name ASC', bool $withTrashed = false): array
    {
        return parent::all($orderBy, $withTrashed);
    }
}
