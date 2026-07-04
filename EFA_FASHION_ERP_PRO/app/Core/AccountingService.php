<?php

namespace App\Core;

use App\Models\Payment;

/**
 * Records payments/collections and keeps cash box / bank balances in sync.
 * direction 'in'  = money received  -> increases the account
 * direction 'out' = money paid out  -> decreases the account
 */
class AccountingService
{
    public static function recordPayment(array $data): int
    {
        Database::beginTransaction();
        try {
            $data['user_id'] = Auth::id();
            $id = Payment::create($data);
            self::adjustAccount($data, +1);
            Database::commit();
            return (int) $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function removePayment(int $id): void
    {
        $p = Payment::find($id);
        if (!$p) {
            return;
        }
        Database::beginTransaction();
        try {
            self::adjustAccount($p, -1);   // reverse the original effect
            Payment::delete($id);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /** Apply (sign=+1) or reverse (sign=-1) a payment's effect on its account. */
    private static function adjustAccount(array $p, int $sign): void
    {
        $delta = (float) $p['amount'] * ($p['direction'] === 'in' ? 1 : -1) * $sign;
        if ($p['method'] === 'cash' && !empty($p['cash_box_id'])) {
            Database::query('UPDATE cash_boxes SET balance = balance + ? WHERE id = ?', [$delta, $p['cash_box_id']]);
        } elseif ($p['method'] === 'bank' && !empty($p['bank_account_id'])) {
            Database::query('UPDATE bank_accounts SET balance = balance + ? WHERE id = ?', [$delta, $p['bank_account_id']]);
        }
        // 'check' method does not move cash until the cheque clears.
    }
}
