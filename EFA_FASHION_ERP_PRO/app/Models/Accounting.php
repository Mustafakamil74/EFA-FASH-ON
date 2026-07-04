<?php

namespace App\Models;

use App\Core\Database;

/**
 * Read-only financial aggregates used by the accounting overview, dashboard
 * and reports. All sums are naive (per stored currency) unless noted.
 */
class Accounting
{
    /** Outstanding debt = sales billed - collections received (customers/shops). */
    public static function totalDebts(): float
    {
        $sales = (float) Database::scalar(
            "SELECT COALESCE(SUM(grand_total),0) FROM receipts WHERE type='sale' AND deleted_at IS NULL"
        );
        $collected = (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM payments WHERE direction='in' AND deleted_at IS NULL"
        );
        return $sales - $collected;
    }

    /** Inventory valuation at purchase price across all branches. */
    public static function inventoryValue(): float
    {
        return (float) Database::scalar(
            'SELECT COALESCE(SUM(sl.quantity * p.purchase_price),0)
             FROM stock_levels sl
             JOIN product_variants pv ON pv.id = sl.variant_id
             JOIN products p ON p.id = pv.product_id
             WHERE p.deleted_at IS NULL'
        );
    }

    /**
     * Profit = sales - purchases - expenses + other income, over a date range.
     */
    public static function profit(?string $from = null, ?string $to = null): float
    {
        [$from, $to] = self::range($from, $to);
        $sales     = self::receiptTotal('sale', $from, $to);
        $purchases = self::receiptTotal('purchase', $from, $to);
        $expenses  = Transaction::totalBetween('expense', $from, $to);
        $income    = Transaction::totalBetween('income', $from, $to);
        return $sales - $purchases - $expenses + $income;
    }

    public static function receiptTotal(string $type, ?string $from = null, ?string $to = null): float
    {
        [$from, $to] = self::range($from, $to);
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(grand_total),0) FROM receipts
             WHERE type = ? AND deleted_at IS NULL AND receipt_date BETWEEN ? AND ?",
            [$type, $from, $to]
        );
    }

    /** Capital = cash boxes + bank balances + inventory value - debts payable. */
    public static function capital(): float
    {
        $cash = (float) Database::scalar('SELECT COALESCE(SUM(balance),0) FROM cash_boxes');
        $bank = (float) Database::scalar('SELECT COALESCE(SUM(balance),0) FROM bank_accounts');
        return $cash + $bank + self::inventoryValue();
    }

    public static function cashTotal(): float
    {
        return (float) Database::scalar('SELECT COALESCE(SUM(balance),0) FROM cash_boxes');
    }

    public static function bankTotal(): float
    {
        return (float) Database::scalar('SELECT COALESCE(SUM(balance),0) FROM bank_accounts');
    }

    /** Profit & loss breakdown for a date range. */
    public static function profitAndLoss(string $from, string $to): array
    {
        $sales     = self::receiptTotal('sale', $from, $to);
        $purchases = self::receiptTotal('purchase', $from, $to);
        $expenses  = Transaction::totalBetween('expense', $from, $to);
        $income    = Transaction::totalBetween('income', $from, $to);
        return [
            'sales'     => $sales,
            'purchases' => $purchases,
            'expenses'  => $expenses,
            'income'    => $income,
            'profit'    => $sales - $purchases - $expenses + $income,
        ];
    }

    private static function range(?string $from, ?string $to): array
    {
        return [$from ?: '1970-01-01', $to ?: date('Y-m-d')];
    }
}
