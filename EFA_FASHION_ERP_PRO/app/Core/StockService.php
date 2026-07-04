<?php

namespace App\Core;

/**
 * Central service for all stock mutations. Every change writes both the
 * current level (stock_levels) and an immutable ledger entry (stock_movements)
 * inside a single transaction, so the two never drift apart.
 */
class StockService
{
    /**
     * Apply a single stock change.
     *
     * @param string $type   in|out|transfer_in|transfer_out|adjust
     * @param float  $qty    absolute (positive) quantity
     * @param bool   $allowNegative  when false, an 'out' that exceeds stock throws
     */
    public static function apply(
        int $branchId,
        int $variantId,
        string $type,
        float $qty,
        ?string $refType = null,
        $refId = null,
        ?string $note = null,
        bool $allowNegative = false
    ): void {
        $signed = self::signedQuantity($type, $qty);

        $current = (float) Database::scalar(
            'SELECT COALESCE(quantity,0) FROM stock_levels WHERE branch_id = ? AND variant_id = ?',
            [$branchId, $variantId]
        );

        if (!$allowNegative && $signed < 0 && ($current + $signed) < 0) {
            throw new \RuntimeException(__('not_enough_stock', [
                'available' => number_format($current, 2),
            ]));
        }

        // Upsert the current level.
        Database::query(
            'INSERT INTO stock_levels (branch_id, variant_id, quantity) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)',
            [$branchId, $variantId, $signed]
        );

        // Append to the ledger.
        Database::query(
            'INSERT INTO stock_movements (branch_id, variant_id, type, quantity, ref_type, ref_id, note, user_id)
             VALUES (?,?,?,?,?,?,?,?)',
            [$branchId, $variantId, $type, $signed, $refType, $refId, $note, Auth::id()]
        );
    }

    /** Move stock between two branches (two ledger entries). */
    public static function transfer(int $fromBranch, int $toBranch, int $variantId, float $qty, ?string $note = null): void
    {
        if ($fromBranch === $toBranch) {
            throw new \RuntimeException(__('transfer_same_branch'));
        }
        Database::beginTransaction();
        try {
            self::apply($fromBranch, $variantId, 'transfer_out', $qty, 'transfer', $toBranch, $note);
            self::apply($toBranch, $variantId, 'transfer_in', $qty, 'transfer', $fromBranch, $note);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    private static function signedQuantity(string $type, float $qty): float
    {
        $qty = abs($qty);
        return match ($type) {
            'in', 'transfer_in'   => $qty,
            'out', 'transfer_out' => -$qty,
            'adjust'              => $qty, // caller passes signed via separate calls
            default               => $qty,
        };
    }
}
