<?php

namespace App\Core;

use App\Models\Receipt;
use App\Models\Setting;

/**
 * Creates, updates and deletes receipts while keeping inventory consistent.
 *
 * A sale moves stock OUT of the receipt's branch; a purchase moves it IN;
 * a return reverses a sale. Edits/deletes post compensating movements so the
 * stock ledger always reflects reality.
 */
class ReceiptService
{
    /**
     * @param array $header  receipt header fields
     * @param array $items   list of line items (variant_id, description, qty, unit_price, ...)
     * @return int new receipt id
     */
    public static function create(array $header, array $items): int
    {
        Database::beginTransaction();
        try {
            [$subtotal, $rows] = self::normalizeItems($items);
            $discount = (float) ($header['discount'] ?? 0);
            $shipping = (float) ($header['shipping_cost'] ?? 0);
            $grand    = max(0, $subtotal - $discount + $shipping);

            $prefix = Setting::get('receipt_prefix', 'FIS-EFA-');
            $header['number']      = $header['number'] ?? Receipt::nextNumber($prefix);
            $header['subtotal']    = $subtotal;
$header['grand_total'] = $grand;

if (($header['payment_type'] ?? '') === 'cash') {
    $header['paid'] = $grand;
} elseif (($header['payment_type'] ?? '') === 'partial') {
    $header['paid'] = (float) ($header['paid'] ?? 0);
} else {
    $header['paid'] = 0;
}

$header['user_id'] = Auth::id();

            $id = Receipt::create($header);
            self::insertItems($id, $rows);
            self::applyStock((int) $id, $header, $rows, false);

            Database::commit();
            return (int) $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function update(int $id, array $header, array $items): void
    {
        $existing = Receipt::find($id);
        if (!$existing) {
            throw new \RuntimeException('Receipt not found.');
        }
        Database::beginTransaction();
        try {
            // Reverse the original stock effect, then drop old items.
            $oldItems = Receipt::items($id);
            self::applyStock($id, $existing, $oldItems, true);
            Database::query('DELETE FROM receipt_items WHERE receipt_id = ?', [$id]);

            [$subtotal, $rows] = self::normalizeItems($items);
            $discount = (float) ($header['discount'] ?? 0);
            $shipping = (float) ($header['shipping_cost'] ?? 0);
            $header['subtotal']    = $subtotal;
            $header['grand_total'] = max(0, $subtotal - $discount + $shipping);
         if (($header['payment_type'] ?? '') === 'cash') {
    $header['paid'] = $header['grand_total'];
} elseif (($header['payment_type'] ?? '') === 'partial') {
    $header['paid'] = (float) ($header['paid'] ?? 0);
} else {
    $header['paid'] = 0;
}

            Receipt::update($id, $header);
            self::insertItems($id, $rows);
            self::applyStock($id, array_merge($existing, $header), $rows, false);

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /** Soft-delete a receipt and reverse its stock effect. */
    public static function delete(int $id): void
    {
        $receipt = Receipt::find($id);
        if (!$receipt) {
            return;
        }
        Database::beginTransaction();
        try {
            self::applyStock($id, $receipt, Receipt::items($id), true);
            Receipt::delete($id);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /** Duplicate a receipt with a fresh number and today's date. */
    public static function duplicate(int $id): int
    {
        $src = Receipt::find($id);
        if (!$src) {
            throw new \RuntimeException('Receipt not found.');
        }
        $items = array_map(fn ($i) => [
            'variant_id'    => $i['variant_id'],
            'product_id'    => $i['product_id'],
            'description'   => $i['description'],
            'serial_number' => $i['serial_number'],
            'color'         => $i['color'],
            'size'          => $i['size'],
            'quantity'      => $i['quantity'],
            'unit_price'    => $i['unit_price'],
        ], Receipt::items($id));

        $header = [
            'type'          => $src['type'],
            'party_type'    => $src['party_type'],
            'party_id'      => $src['party_id'],
            'branch_id'     => $src['branch_id'],
            'currency'      => $src['currency'],
            'receipt_date'  => date('Y-m-d'),
            'receipt_time'  => date('H:i:s'),
            'discount'      => $src['discount'],
            'shipping_cost' => $src['shipping_cost'],
            'notes'         => $src['notes'],
        ];
        return self::create($header, $items);
    }

    /** Compute line totals + subtotal, dropping empty rows. */
    private static function normalizeItems(array $items): array
    {
        $subtotal = 0.0;
        $rows = [];
        foreach ($items as $it) {
            $qty   = (float) ($it['quantity'] ?? 0);
            $price = (float) ($it['unit_price'] ?? 0);

            if ($price <= 0 && !empty($it['product_id'])) {

                $price = (float) \App\Core\Database::scalar(
                    "SELECT purchase_price
                     FROM products
                     WHERE id = ?",
                    [$it['product_id']]
                );

           }
            $desc  = trim((string) ($it['description'] ?? ''));
            if ($qty <= 0 && $desc === '' && empty($it['variant_id'])) {
                continue; // skip blank row
            }
            $line = round($qty * $price, 2);
            $subtotal += $line;
            $productId = (int)($it['product_id'] ?? 0);

            $variantId = $it['variant_id'] ?? null;

            if (!$variantId && $productId > 0 && !empty($it['color'])) {
                $variantId = \App\Models\Product::findOrCreateVariant(
                    $productId,
                    $it['color']
                );
            }

            $rows[] = [
                'variant_id'    => $variantId,
                'product_id'    => $productId,
                'description'   => $desc,
                'serial_number' => trim((string) ($it['serial_number'] ?? '')) ?: null,
                'color'         => trim((string) ($it['color'] ?? '')) ?: null,
                'size'          => trim((string) ($it['size'] ?? '')) ?: null,
                'quantity'      => $qty,
                'unit_price'    => $price,
                'line_total'    => $line,
            ];
        }
        if (!$rows) {
            throw new \RuntimeException(__('receipt_needs_items'));
        }
        return [$subtotal, $rows];
    }

    private static function insertItems(int $receiptId, array $rows): void
    {
        foreach ($rows as $r) {
            Database::query(
                'INSERT INTO receipt_items
                 (receipt_id, product_id, variant_id, description, serial_number, color, size, quantity, unit_price, line_total)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                [
                    $receiptId, $r['product_id'], $r['variant_id'], $r['description'],
                    $r['serial_number'], $r['color'], $r['size'],
                    $r['quantity'], $r['unit_price'], $r['line_total'],
                ]
            );
        }
    }

    /**
     * Apply (or reverse) the stock effect of a receipt's items.
     * sale   -> out  (reverse: in)
     * purchase -> in (reverse: out)
     * return -> in   (reverse: out)
     */
    private static function applyStock(int $receiptId, array $header, array $items, bool $reverse): void
    {
        $branchId = (int) ($header['branch_id'] ?? 0);
        if ($branchId <= 0) {
            return; // no branch -> not stock-affecting
        }
        $type = $header['type'] ?? 'sale';
        $outbound = $type === 'sale'; // sale removes stock; purchase/return add stock
        if ($reverse) {
            $outbound = !$outbound;
        }
        foreach ($items as $it) {
            $variantId = (int) ($it['variant_id'] ?? 0);
            $qty = (float) ($it['quantity'] ?? 0);
            if ($variantId <= 0 || $qty <= 0) {
                continue;
            }
            $movement = $outbound ? 'out' : 'in';
            // Reversals always allowed to go through (restoring/correcting stock).
            StockService::apply(
                $branchId, $variantId, $movement, $qty,
                'receipt', $receiptId, ($reverse ? 'void ' : '') . ($header['number'] ?? ''),
                $reverse
            );
        }
    }
}
