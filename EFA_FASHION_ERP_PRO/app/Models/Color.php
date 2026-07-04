<?php

namespace App\Models;

use App\Core\Model;

class Color extends Model
{
    protected static string $table = 'colors';
    protected static array $fillable = ['name', 'hex'];
}
