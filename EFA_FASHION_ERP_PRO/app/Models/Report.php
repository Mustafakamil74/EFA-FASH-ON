<?php

namespace App\Models;

use App\Core\Database;

/**
 * Read-only reporting queries (sales, profit, inventory, rankings).
 * All ranges are inclusive [from, to] on receipt_date / txn_date.
 */
class Report
{
    /** Daily sales totals within a range. */
    public static function salesByDay(string $from, string $to): array
    {
        return Database::all(
            "SELECT receipt_date AS label, SUM(grand_total) AS total
             FROM receipts
             WHERE type='sale' AND deleted_at IS NULL AND receipt_date BETWEEN ? AND ?
             GROUP BY receipt_date ORDER BY receipt_date",
            [$from, $to]
        );
    }

    /** Monthly sales vs expenses for a year (for the profit chart). */
    public static function monthly(int $year): array
    {
        $sales = Database::all(
            "SELECT DATE_FORMAT(receipt_date,'%Y-%m') AS m, SUM(grand_total) AS total
             FROM receipts WHERE type='sale' AND deleted_at IS NULL AND YEAR(receipt_date)=?
             GROUP BY m",
            [$year]
        );
        $exp = Database::all(
            "SELECT DATE_FORMAT(txn_date,'%Y-%m') AS m, SUM(amount) AS total
             FROM transactions WHERE kind='expense' AND YEAR(txn_date)=? GROUP BY m",
            [$year]
        );
        $purch = Database::all(
            "SELECT DATE_FORMAT(receipt_date,'%Y-%m') AS m, SUM(grand_total) AS total
             FROM receipts WHERE type='purchase' AND deleted_at IS NULL AND YEAR(receipt_date)=?
             GROUP BY m",
            [$year]
        );
        $salesMap = array_column($sales, 'total', 'm');
        $expMap   = array_column($exp, 'total', 'm');
        $purchMap = array_column($purch, 'total', 'm');
        $rows = [];
        for ($i = 1; $i <= 12; $i++) {
            $m = sprintf('%04d-%02d', $year, $i);
            $s = (float) ($salesMap[$m] ?? 0);
            $e = (float) ($expMap[$m] ?? 0);
            $p = (float) ($purchMap[$m] ?? 0);
            $rows[] = ['month' => $m, 'sales' => $s, 'expenses' => $e, 'purchases' => $p, 'profit' => $s - $p - $e];
        }
        return $rows;
    }

    public static function topCustomers(string $from, string $to, int $limit = 10): array
    {
        return Database::all(
            "SELECT c.name AS label, SUM(r.grand_total) AS total
             FROM receipts r JOIN customers c ON c.id = r.party_id
             WHERE r.type='sale' AND r.party_type='customer' AND r.deleted_at IS NULL
               AND r.receipt_date BETWEEN ? AND ?
             GROUP BY c.id ORDER BY total DESC LIMIT " . (int) $limit,
            [$from, $to]
        );
    }

    public static function topProducts(string $from, string $to, int $limit = 10): array
    {
        return Database::all(
            "SELECT COALESCE(p.name, ri.description) AS label,
                    SUM(ri.quantity) AS qty, SUM(ri.line_total) AS total
             FROM receipt_items ri
             JOIN receipts r ON r.id = ri.receipt_id
             LEFT JOIN product_variants pv ON pv.id = ri.variant_id
             LEFT JOIN products p ON p.id = pv.product_id
             WHERE r.type='sale' AND r.deleted_at IS NULL AND r.receipt_date BETWEEN ? AND ?
             GROUP BY label ORDER BY total DESC LIMIT " . (int) $limit,
            [$from, $to]
        );
    }

    public static function inventoryByCategory(): array
    {
        return Database::all(
            "SELECT COALESCE(cat.name, '—') AS label,
                    SUM(sl.quantity) AS qty,
                    SUM(sl.quantity * p.purchase_price) AS value
             FROM stock_levels sl
             JOIN product_variants pv ON pv.id = sl.variant_id
             JOIN products p ON p.id = pv.product_id
             LEFT JOIN categories cat ON cat.id = p.category_id
             WHERE p.deleted_at IS NULL
             GROUP BY label ORDER BY value DESC"
        );
    }

    /** Flat sales rows for table export within a range. */
    public static function salesRows(string $from, string $to): array
    {
        return Database::all(
            "SELECT r.number, r.receipt_date, r.currency, r.grand_total,
                    CASE r.party_type
                        WHEN 'customer' THEN (SELECT name FROM customers WHERE id=r.party_id)
                        WHEN 'shop'     THEN (SELECT name FROM shops     WHERE id=r.party_id)
                        WHEN 'factory'  THEN (SELECT name FROM factories WHERE id=r.party_id)
                    END AS party_name
             FROM receipts r
             WHERE r.type='sale' AND r.deleted_at IS NULL AND r.receipt_date BETWEEN ? AND ?
             ORDER BY r.receipt_date, r.id",
            [$from, $to]
        );
    }
}
