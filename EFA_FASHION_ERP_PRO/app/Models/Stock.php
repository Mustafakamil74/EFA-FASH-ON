<?php

namespace App\Models;

use App\Core\Database;

/**
 * Read helpers over stock_levels (current quantity per variant per branch).
 * Mutations go through App\Core\StockService to keep the movement ledger in sync.
 */
class Stock
{
    /** Current quantity for a variant in a branch. */
    public static function onHand(int $branchId, int $variantId): float
    {
        return (float) Database::scalar(
            'SELECT COALESCE(quantity,0) FROM stock_levels WHERE branch_id = ? AND variant_id = ?',
            [$branchId, $variantId]
        );
    }

    /** Total quantity for a variant across all branches. */
    public static function totalForVariant(int $variantId): float
    {
        return (float) Database::scalar(
            'SELECT COALESCE(SUM(quantity),0) FROM stock_levels WHERE variant_id = ?',
            [$variantId]
        );
    }

    /** Stock levels listing with product/variant/branch detail. */
    public static function levels(?int $branchId = null, string $q = ''): array
    {
        $sql = "SELECT sl.id, sl.quantity, b.name AS branch_name, b.id AS branch_id,
                       p.id AS product_id, p.code AS product_code, p.name AS product_name, p.min_stock,
                       co.name AS color_name, sz.name AS size_name, pv.id AS variant_id, pv.sku
                FROM stock_levels sl
                JOIN branches b ON b.id = sl.branch_id
                JOIN product_variants pv ON pv.id = sl.variant_id
                JOIN products p ON p.id = pv.product_id
                LEFT JOIN colors co ON co.id = pv.color_id
                LEFT JOIN sizes sz ON sz.id = pv.size_id
                WHERE p.deleted_at IS NULL";
        $params = [];
        if ($branchId) {
            $sql .= ' AND sl.branch_id = ?';
            $params[] = $branchId;
        }
        if ($q !== '') {
            $sql .= ' AND (p.code LIKE ? OR p.name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY p.name, b.name';
        return Database::all($sql, $params);
    }

    /** Variants with product label, for selection dropdowns. */
    public static function variantOptions(): array
{
    return Database::all(
        "SELECT
            pv.id,
            pv.product_id,
            co.name AS color_name,
            CONCAT(
                p.code, ' - ', p.name,
                IF(co.name IS NULL,'',CONCAT(' / ',co.name)),
                IF(sz.name IS NULL,'',CONCAT(' / ',sz.name))
            ) AS label
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        LEFT JOIN colors co ON co.id = pv.color_id
        LEFT JOIN sizes sz ON sz.id = pv.size_id
        WHERE p.deleted_at IS NULL
        ORDER BY p.name"
    );
}
}
