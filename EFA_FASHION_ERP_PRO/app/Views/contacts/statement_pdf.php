<?php
/** @var array $row @var array $ledger @var array $company @var string $module */
$companyName = $company['company_name'] ?? 'EFA FASHION';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 12px; color: #222; }
        .header { border-bottom: 2px solid #6f42c1; padding-bottom: 8px; margin-bottom: 14px; }
        .company { font-size: 18px; font-weight: bold; color: #6f42c1; }
        .muted { color: #777; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f3f0fa; text-align: left; }
        .text-end { text-align: right; }
        .totals td { font-weight: bold; background: #faf8ff; }
        .meta td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company"><?= e($companyName) ?></div>
        <div class="muted"><?= e($company['company_address'] ?? '') ?> <?= e($company['company_phone'] ?? '') ?></div>
    </div>

    <h3>Account Statement</h3>
    <table class="meta" style="border:none">
        <tr class="meta"><td style="width:120px"><strong>Code:</strong></td><td><?= e($row['code']) ?></td></tr>
        <tr class="meta"><td><strong>Name:</strong></td><td><?= e($row['name']) ?></td></tr>
        <tr class="meta"><td><strong>Phone:</strong></td><td><?= e($row['phone'] ?? '') ?></td></tr>
        <tr class="meta"><td><strong>Date:</strong></td><td><?= e(date('Y-m-d')) ?></td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th><th>Reference</th>
                <th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ledger['rows'] as $r): ?>
            <tr>
                <td><?= e($r['date']) ?></td>
                <td><?= e($r['ref'] ?: '-') ?></td>
                <td class="text-end"><?= $r['debit'] ? e(money($r['debit'])) : '' ?></td>
                <td class="text-end"><?= $r['credit'] ? e(money($r['credit'])) : '' ?></td>
                <td class="text-end"><?= e(money($r['balance'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$ledger['rows']): ?>
            <tr><td colspan="5" style="text-align:center" class="muted">No records.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="2">Total</td>
                <td class="text-end"><?= e(money($ledger['totals']['charged'])) ?></td>
                <td class="text-end"><?= e(money($ledger['totals']['paid'])) ?></td>
                <td class="text-end"><?= e(money($ledger['totals']['balance'])) ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
