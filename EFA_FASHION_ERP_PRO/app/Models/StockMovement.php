<?php

namespace App\Models;

use App\Core\Database;

class StockMovement
{
    /** Movement history with product/branch detail. */
    public static function history(?int $branchId = null, ?int $variantId = null, int $limit = 200): array
    {
        $sql = "SELECT m.*, b.name AS branch_name, p.code AS product_code, p.name AS product_name,
                       co.name AS color_name, sz.name AS size_name, u.name AS user_name
                FROM stock_movements m
                JOIN branches b ON b.id = m.branch_id
                JOIN product_variants pv ON pv.id = m.variant_id
                JOIN products p ON p.id = pv.product_id
                LEFT JOIN colors co ON co.id = pv.color_id
                LEFT JOIN sizes sz ON sz.id = pv.size_id
                LEFT JOIN users u ON u.id = m.user_id
                WHERE 1=1";
        $params = [];
        if ($branchId) { $sql .= ' AND m.branch_id = ?'; $params[] = $branchId; }
        if ($variantId) { $sql .= ' AND m.variant_id = ?'; $params[] = $variantId; }
        $sql .= ' ORDER BY m.id DESC LIMIT ' . (int) $limit;
        return Database::all($sql, $params);
    }
}
