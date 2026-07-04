<?php

namespace App\Models;

use App\Core\Database;

/**
 * Builds an account statement (ledger) for a party (customer/shop/factory)
 * by combining receipts (charges) and payments (settlements) into a single
 * chronological list with a running balance.
 */
class PartyLedger
{
    /**
     * @param string $type 'customer' | 'shop' | 'factory'
     * @return array{rows: array, totals: array}
     */
    public static function statement(string $type, int $partyId): array
    {
        // Sales receipts increase what the party owes us (debit).
        $receipts = Database::all(
        "SELECT receipt_date AS date,
        number AS ref,
      grand_total AS amount,
      currency,
      payment_type,
      paid,
      grand_total,
     'receipt' AS kind
        FROM receipts
     WHERE party_type = ? AND party_id = ? AND deleted_at IS NULL",
     [$type, $partyId]
    );

        // Incoming payments reduce the balance (credit).
        $direction = $type === 'factory' ? 'out' : 'in';

        $payments = Database::all(
            "SELECT pay_date AS date,
                    COALESCE(note,'') AS ref,
                    amount,
                    currency,
                    'payment' AS kind
            FROM payments
            WHERE party_type = ?
              AND party_id = ?
              AND direction = ?
              AND deleted_at IS NULL",
           [$type, $partyId, $direction]
       );

        $rows = array_merge($receipts, $payments);

        usort($rows, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        $balance     = 0.0;
        $totalCharge = 0.0;
        $totalPaid   = 0.0;
        foreach ($rows as &$r) {

        $amount = (float) $r['amount'];

        if ($r['kind'] === 'receipt') {

    $r['debit'] = max(0, $r['grand_total'] - $r['paid']);
    $r['credit'] = 0;

    $balance += $r['debit'];

    $totalCharge += $r['debit'];
    $totalPaid += $r['paid'];

   } else {

  $r['debit'] = 0.0;
$r['credit'] = 0.0;
$r['paid'] = $amount;

$balance -= $amount;

$totalPaid += $amount;

}

            $r['balance'] = $balance;
        }
        unset($r);
        
        return [
            'rows' => $rows,
            'totals' => [
                'charged'   => $totalCharge,
                'paid'      => $totalPaid,
                'balance'   => $balance,
            ],
        ];
    }
}
