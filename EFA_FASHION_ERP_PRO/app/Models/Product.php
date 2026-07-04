<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Product extends Model
{
    protected static string $table = 'products';

    protected static array $fillable = [
        'code',
        'name',
        'fabric_type',
        'category_id',
        'brand',
        'barcode',
        'purchase_price',
        'sale_price',
        'min_stock',
        'image_path',
        'notes',
    ];

    protected static bool $softDelete = true;

    public static function search(string $q): array
    {
        $base = "SELECT p.*, c.name AS category_name,
                    COALESCE(
                        (
                            SELECT SUM(sl.quantity)
                            FROM stock_levels sl
                            JOIN product_variants pv
                                ON pv.id = sl.variant_id
                            WHERE pv.product_id = p.id
                        ),
                    0) AS on_hand
                FROM products p
                LEFT JOIN categories c
                    ON c.id = p.category_id
                WHERE p.deleted_at IS NULL";

        if ($q === '') {
            return Database::all($base . " ORDER BY p.id DESC");
        }

        $like = "%{$q}%";

        return Database::all(
            $base . " AND (
                p.code LIKE ?
                OR p.name LIKE ?
                OR p.brand LIKE ?
                OR p.barcode LIKE ?
            )
            ORDER BY p.id DESC",
            [$like, $like, $like, $like]
        );
    }

    public static function codeExists(string $code, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM products WHERE code = ?";
        $params = [$code];

        if ($exceptId) {
            $sql .= " AND id <> ?";
            $params[] = $exceptId;
        }

        return (int) Database::scalar($sql, $params) > 0;
    }

    public static function variants(int $productId): array
    {
        return Database::all(
            "SELECT
                pv.*,
                co.name AS color_name,
                co.hex AS color_hex,
                sz.name AS size_name
            FROM product_variants pv
            LEFT JOIN colors co ON co.id = pv.color_id
            LEFT JOIN sizes sz ON sz.id = pv.size_id
            WHERE pv.product_id = ?
            ORDER BY pv.id",
            [$productId]
        );
    }

    public static function syncVariants(
        int $productId,
        array $colorIds,
        array $sizeIds,
        string $code
    ): void {

        $colorIds = $colorIds ?: [null];
        $sizeIds = $sizeIds ?: [null];

        $existing = [];

        foreach (self::variants($productId) as $v) {
            $existing[
                ($v['color_id'] ?? '0') . ':' . ($v['size_id'] ?? '0')
            ] = $v['id'];
        }

        $wanted = [];

        foreach ($colorIds as $cid) {
            foreach ($sizeIds as $sid) {
                $key = ($cid ?? '0') . ':' . ($sid ?? '0');
                $wanted[$key] = [$cid ?: null, $sid ?: null];
            }
        }

        foreach ($wanted as $key => [$cid, $sid]) {

            if (!isset($existing[$key])) {

                $sku = $code . '-' . ($cid ?? '0') . '-' . ($sid ?? '0');

                Database::query(
                    "INSERT INTO product_variants
                    (product_id,color_id,size_id,sku)
                    VALUES (?,?,?,?)",
                    [$productId, $cid, $sid, $sku]
                );
            }
        }

        foreach ($existing as $key => $vid) {

            if (!isset($wanted[$key])) {

                $hasStock = (int) Database::scalar(
                    "SELECT COUNT(*)
                     FROM stock_levels
                     WHERE variant_id = ?
                     AND quantity <> 0",
                    [$vid]
                );

                $hasMoves = (int) Database::scalar(
                    "SELECT COUNT(*)
                     FROM stock_movements
                     WHERE variant_id = ?",
                    [$vid]
                );

                if ($hasStock === 0 && $hasMoves === 0) {
                    Database::query(
                        "DELETE FROM product_variants WHERE id = ?",
                        [$vid]
                    );
                }
            }
        }
    }

    public static function findOrCreateVariant(
        int $productId,
        string $colorName
    ): int {

        $colorName = trim($colorName);

        $colorId = Database::scalar(
            "SELECT id
             FROM colors
             WHERE name = ?
             LIMIT 1",
            [$colorName]
        );

        if (!$colorId) {

            Database::query(
                "INSERT INTO colors(name)
                 VALUES(?)",
                [$colorName]
            );

            $colorId = Database::lastInsertId();
        }

        $variantId = Database::scalar(
            "SELECT id
             FROM product_variants
             WHERE product_id = ?
             AND color_id = ?
             AND size_id IS NULL
             LIMIT 1",
            [$productId, $colorId]
        );

        if (!$variantId) {

            $code = Database::scalar(
                "SELECT code
                 FROM products
                 WHERE id = ?",
                [$productId]
            );

            Database::query(
                "INSERT INTO product_variants
                (product_id,color_id,size_id,sku)
                VALUES (?,?,NULL,?)",
                [
                    $productId,
                    $colorId,
                    $code . "-" . $colorId
                ]
            );

            $variantId = Database::lastInsertId();
        }

        return (int) $variantId;
    }
}