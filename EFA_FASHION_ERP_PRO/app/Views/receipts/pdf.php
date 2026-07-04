<?php
/** @var array $row @var array $items @var ?string $partyName @var float $paid @var array $company */
$remaining = (float) $row['grand_total'] - $paid;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 12px; color: #222; }
        .head { width: 100%; }
        .head td { vertical-align: top; }
        .company { font-size: 18px; font-weight: bold; color: #6f42c1; }
        .num { font-size: 16px; font-weight: bold; color: #6f42c1; }
        .muted { color: #777; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.items th, table.items td { border: 1px solid #ddd; padding: 6px; }
        table.items th { background: #f3f0fa; text-align: left; }
        .text-end { text-align: right; }
        .totals { width: 45%; float: right; border-collapse: collapse; margin-top: 10px; }
        .totals td { padding: 4px 6px; }
        .totals .grand { font-weight: bold; border-top: 2px solid #6f42c1; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td>
                <div class="company"><?= e($company['company_name'] ?? 'EFA FASHION') ?></div>
                <div class="muted"><?= e($company['company_address'] ?? '') ?></div>
                <div class="muted"><?= e($company['company_phone'] ?? '') ?></div>
            </td>
            <td class="text-end">
                <div class="num"><?= e($row['number']) ?></div>
                <div><?= e(ucfirst($row['type'])) ?></div>
                <div class="muted"><?= e($row['receipt_date']) ?> <?= e(substr((string) $row['receipt_time'], 0, 5)) ?></div>
            </td>
        </tr>
    </table>

    <p><strong>To:</strong> <?= e($partyName ?? '-') ?> (<?= e(ucfirst($row['party_type'])) ?>)</p>

    <table class="items">
        <thead>
            <tr>
                <th>#</th><th>Description</th><th>Serial</th><th>Color</th><th>Size</th>
                <th class="text-end">Qty</th><th class="text-end">Unit</th><th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $it): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($it['description'] ?? '') ?></td>
                <td><?= e($it['serial_number'] ?? '') ?></td>
                <td><?= e($it['color'] ?? '') ?></td>
                <td><?= e($it['size'] ?? '') ?></td>
                <td class="text-end"><?= e(number_format((float) $it['quantity'], 2)) ?></td>
                <td class="text-end"><?= e(money($it['unit_price'], $row['currency'])) ?></td>
                <td class="text-end"><?= e(money($it['line_total'], $row['currency'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-end"><?= e(money($row['subtotal'], $row['currency'])) ?></td></tr>
        <tr><td>Discount</td><td class="text-end">-<?= e(money($row['discount'], $row['currency'])) ?></td></tr>
        <tr><td>Shipping</td><td class="text-end"><?= e(money($row['shipping_cost'], $row['currency'])) ?></td></tr>
        <tr class="grand"><td>Grand Total</td><td class="text-end"><?= e(money($row['grand_total'], $row['currency'])) ?></td></tr>
        <tr><td>Paid</td><td class="text-end"><?= e(money($paid, $row['currency'])) ?></td></tr>
        <tr><td>Remaining</td><td class="text-end"><?= e(money($remaining, $row['currency'])) ?></td></tr>
    </table>

    <?php if (!empty($row['notes'])): ?>
        <div style="clear:both; padding-top:20px"><strong>Notes:</strong> <?= e($row['notes']) ?></div>
    <?php endif; ?>
</body>
</html>
