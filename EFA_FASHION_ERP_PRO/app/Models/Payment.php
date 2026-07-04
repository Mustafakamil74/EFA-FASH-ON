<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Payment extends Model
{
    protected static string $table = 'payments';
    protected static array $fillable = [
        'direction', 'party_type', 'party_id', 'receipt_id', 'method',
        'cash_box_id', 'bank_account_id', 'currency', 'amount', 'pay_date', 'note', 'user_id',
    ];
    protected static bool $softDelete = true;

    /** Listing with party name + receipt number. */
    public static function listing(string $direction = '', string $q = ''): array
    {
        $sql = "SELECT p.*, r.number AS receipt_number,
                   CASE p.party_type
                       WHEN 'customer' THEN (SELECT name FROM customers WHERE id = p.party_id)
                       WHEN 'shop'     THEN (SELECT name FROM shops     WHERE id = p.party_id)
                       WHEN 'factory'  THEN (SELECT name FROM factories WHERE id = p.party_id)
                   END AS party_name
                FROM payments p
                LEFT JOIN receipts r ON r.id = p.receipt_id
                WHERE p.deleted_at IS NULL";
        $params = [];
        if ($direction !== '') {
            $sql .= ' AND p.direction = ?';
            $params[] = $direction;
        }
        if ($q !== '') {
            $sql .= " AND (r.number LIKE ? OR p.note LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY p.id DESC';
        return Database::all($sql, $params);
    }
}
