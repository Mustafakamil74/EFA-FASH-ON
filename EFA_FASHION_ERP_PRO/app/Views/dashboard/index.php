<?php
/** @var array $stats @var array $lowStock @var array $dueChecks */
$cards = [
    ['total_customers', $stats['customers'],  'people',    'primary'],
    ['total_shops',     $stats['shops'],      'shop',      'info'],
    ['total_factories', $stats['factories'],  'building',  'secondary'],
    ['total_products',  $stats['products'],   'box-seam',  'success'],
    ['total_receipts',  $stats['receipts'],   'receipt',   'warning'],
];
?>
<div class="row g-3 mb-3">
    <?php foreach ($cards as [$key, $value, $icon, $color]): ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card border-start border-<?= e($color) ?>">
                <div class="stat-icon text-<?= e($color) ?>"><i class="bi bi-<?= e($icon) ?>"></i></div>
                <div class="stat-meta">
                    <div class="stat-value"><?= e(number_format($value)) ?></div>
                    <div class="stat-label"><?= e(__($key)) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="stat-card border-start border-dark">
            <div class="stat-icon text-dark"><i class="bi bi-boxes"></i></div>
            <div class="stat-meta">
                <div class="stat-value"><?= e(money($stats['inventory_value'])) ?></div>
                <div class="stat-label"><?= e(__('inventory_value')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card border-start border-danger">
            <div class="stat-icon text-danger"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-meta">
                <div class="stat-value"><?= e(money($stats['debts'])) ?></div>
                <div class="stat-label"><?= e(__('total_debts')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-start border-success">
            <div class="stat-icon text-success"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-meta">
                <div class="stat-value"><?= e(money($stats['profit'])) ?></div>
                <div class="stat-label"><?= e(__('profit')) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                <strong><?= e(__('low_stock_alert')) ?></strong>
            </div>
            <div class="card-body p-0">
                <?php if (!$lowStock): ?>
                    <p class="text-muted p-3 mb-0"><?= e(__('no_alerts')) ?></p>
                <?php else: ?>
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php foreach ($lowStock as $row): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark"><?= e($row['code']) ?></span> <?= e($row['name']) ?></td>
                                <td class="text-end">
                                    <span class="badge bg-danger"><?= e(number_format((float) $row['on_hand'], 2)) ?></span>
                                    <span class="text-muted small">/ <?= e(number_format((float) $row['min_stock'], 2)) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-calendar-check text-primary me-2"></i>
                <strong><?= e(__('due_checks')) ?></strong>
            </div>
            <div class="card-body p-0">
                <?php if (!$dueChecks): ?>
                    <p class="text-muted p-3 mb-0"><?= e(__('no_alerts')) ?></p>
                <?php else: ?>
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php foreach ($dueChecks as $c): ?>
                            <tr>
                                <td><?= e($c['check_number'] ?: '—') ?></td>
                                <td class="text-end"><?= e(money($c['amount'], $c['currency'])) ?></td>
                                <td class="text-end text-danger small"><?= e($c['due_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
