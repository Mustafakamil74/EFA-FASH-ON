<?php
/** @var array $rows @var array $pl @var string $from @var string $to @var array $company */
$total = 0.0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 12px; color: #222; }
        .company { font-size: 18px; font-weight: bold; color: #6f42c1; }
        .muted { color: #777; }
        h3 { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f0fa; text-align: left; }
        .text-end { text-align: right; }
        .summary td { border: none; padding: 3px 6px; }
    </style>
</head>
<body>
    <div class="company"><?= e($company['company_name'] ?? 'EFA FASHION') ?></div>
    <h3><?= e(__('nav_reports')) ?> — <?= e(__('rtype_sale')) ?></h3>
    <div class="muted"><?= e($from) ?> → <?= e($to) ?></div>

    <table>
        <thead>
            <tr><th>#</th><th>Number</th><th>Date</th><th>Party</th><th class="text-end">Total</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): $total += (float) $r['grand_total']; ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($r['number']) ?></td>
                <td><?= e($r['receipt_date']) ?></td>
                <td><?= e($r['party_name'] ?? '') ?></td>
                <td class="text-end"><?= e(money($r['grand_total'], $r['currency'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="text-end">—</td></tr><?php endif; ?>
        </tbody>
    </table>

    <table class="summary" style="width:45%; float:right; margin-top:10px;">
        <tr><td>Sales</td><td class="text-end"><?= e(money($pl['sales'])) ?></td></tr>
        <tr><td>Purchases</td><td class="text-end"><?= e(money($pl['purchases'])) ?></td></tr>
        <tr><td>Expenses</td><td class="text-end"><?= e(money($pl['expenses'])) ?></td></tr>
        <tr><td>Income</td><td class="text-end"><?= e(money($pl['income'])) ?></td></tr>
        <tr><td><strong>Profit</strong></td><td class="text-end"><strong><?= e(money($pl['profit'])) ?></strong></td></tr>
    </table>
</body>
</html>
