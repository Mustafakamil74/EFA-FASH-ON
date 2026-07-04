<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Check extends Model
{
    protected static string $table = 'checks';
    protected static array $fillable = [
        'direction', 'party_type', 'party_id', 'check_number', 'bank_name',
        'currency', 'amount', 'issue_date', 'due_date', 'status', 'note',
    ];

    public static function listing(string $status = ''): array
    {
        $sql = "SELECT c.*,
                   CASE c.party_type
                       WHEN 'customer' THEN (SELECT name FROM customers WHERE id = c.party_id)
                       WHEN 'shop'     THEN (SELECT name FROM shops     WHERE id = c.party_id)
                       WHEN 'factory'  THEN (SELECT name FROM factories WHERE id = c.party_id)
                   END AS party_name
                FROM checks c WHERE 1=1";
        $params = [];
        if ($status !== '') {
            $sql .= ' AND c.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY c.due_date ASC';
        return Database::all($sql, $params);
    }

    /** Checks due within the next N days that are still pending. */
    public static function dueSoon(int $days = 7): array
    {
        return Database::all(
            "SELECT * FROM checks
             WHERE status = 'pending' AND due_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY due_date ASC",
            [$days]
        );
    }
}
