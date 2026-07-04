<?php

namespace App\Models;

use App\Core\Model;

class BankAccount extends Model
{
    protected static string $table = 'bank_accounts';
    protected static array $fillable = ['bank_name', 'account_no', 'iban', 'currency', 'balance'];
}
