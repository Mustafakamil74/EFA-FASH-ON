<?php

namespace App\Models;

use App\Core\Model;

class CashBox extends Model
{
    protected static string $table = 'cash_boxes';
    protected static array $fillable = ['name', 'currency', 'balance'];
}
