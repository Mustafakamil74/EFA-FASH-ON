<?php

namespace App\Models;

use App\Core\Model;

class Branch extends Model
{
    protected static string $table = 'branches';
    protected static array $fillable = ['name', 'type', 'is_active'];

    public static function active(): array
    {
        return self::all('name ASC');
    }
}
