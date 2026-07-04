<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Receipt extends Model
{
    protected static string $table = 'receipts';
    protected static array $fillable = [
    'number',
    'type',
    'party_type',
    'party_id',
    'branch_id',
    'currency',
    'receipt_date',
    'receipt_time',
    'discount',
    'shipping_cost',
    'subtotal',
    'grand_total',
    'paid',
    'payment_type',
    'notes',
    'user_id',
];
    protected static bool $softDelete = true;

    /** Listing with party name + paid amount, searchable by number. */
    public static function listing(string $q = ''): array
    {
        $sql = "SELECT r.*,
                  
                   CASE r.party_type
                       WHEN 'customer' THEN (SELECT name FROM customers WHERE id = r.party_id)
                       WHEN 'shop'     THEN (SELECT name FROM shops     WHERE id = r.party_id)
                       WHEN 'factory'  THEN (SELECT name FROM factories WHERE id = r.party_id)
                   END AS party_name
                FROM receipts r
                WHERE r.deleted_at IS NULL";
        $params = [];
        if ($q !== '') {
            $sql .= " AND (
                r.number LIKE ?
                OR (
                    CASE r.party_type
                        WHEN 'customer' THEN (SELECT name FROM customers WHERE id = r.party_id)
                        WHEN 'shop' THEN (SELECT name FROM shops WHERE id = r.party_id)
                        WHEN 'factory' THEN (SELECT name FROM factories WHERE id = r.party_id)
                     END
                 ) LIKE ?
            )";

            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        $sql .= ' ORDER BY r.id DESC';
        return Database::all($sql, $params);
    }

    public static function items(int $receiptId): array
    {
        return Database::all('SELECT * FROM receipt_items WHERE receipt_id = ? ORDER BY id', [$receiptId]);
    }

   public static function paid(int $receiptId): float
{
    return (float) Database::scalar(
        "SELECT COALESCE(paid,0)
         FROM receipts
         WHERE id = ?",
        [$receiptId]
    );
}

    /** Resolve a party's display name. */
    public static function partyName(string $type, int $id): ?string
    {
        $table = ['customer' => 'customers', 'shop' => 'shops', 'factory' => 'factories'][$type] ?? null;
        if (!$table) {
            return null;
        }
        return Database::scalar("SELECT name FROM {$table} WHERE id = ?", [$id]);
    }

    /** Generate the next sequential receipt number, e.g. FIS-EFA-000001. */
    public static function nextNumber(string $prefix = 'FIS-EFA-'): string
    {
        $max = (int) Database::scalar(
            "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, '-', -1) AS UNSIGNED)), 0)
             FROM receipts WHERE number LIKE ?",
            [$prefix . '%']
        );
        return $prefix . str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }
}
