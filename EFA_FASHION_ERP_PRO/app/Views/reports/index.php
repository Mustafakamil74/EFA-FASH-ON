<?php
/** @var string $from @var string $to @var string $preset @var array $salesByDay
 *  @var array $monthly @var array $topCustomers @var array $topProducts
 *  @var array $invByCat @var array $pl @var int $year */
$presetQs = function (string $p) use ($from, $to): string {
    return $p === 'custom' ? 'preset=custom&from=' . urlencode($from) . '&to=' . urlencode($to) : 'preset=' . $p;
};
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="btn-group">
        <?php foreach (['daily', 'weekly', 'monthly', 'yearly'] as $p): ?>
            <a href="<?= e(url('reports?preset=' . $p)) ?>" class="btn btn-outline-secondary <?= $preset === $p ? 'active' : '' ?>"><?= e(__('period_' . ($p === 'weekly' ? 'weekly' : $p))) ?></a>
        <?php endforeach; ?>
    </div>
    <form class="d-flex gap-2 align-items-center" method="get" action="<?= e(url('reports')) ?>">
        <input type="hidden" name="preset" value="custom">
        <input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>">
        <input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>">
        <button class="btn btn-sm btn-outline-primary"><?= e(__('apply')) ?></button>
    </form>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('reports/export/pdf?' . $presetQs($preset))) ?>" class="btn btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a href="<?= e(url('reports/export/excel?' . $presetQs($preset))) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-1"></i><?= e(__('print')) ?></button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card stat-card border-start border-success"><div class="card-body"><div class="text-muted small"><?= e(__('rtype_sale')) ?></div><div class="h4 mb-0"><?= e(money($pl['sales'])) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-danger"><div class="card-body"><div class="text-muted small"><?= e(__('expenses')) ?></div><div class="h4 mb-0"><?= e(money($pl['expenses'])) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-primary"><div class="card-body"><div class="text-muted small"><?= e(__('profit')) ?></div><div class="h4 mb-0 <?= $pl['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= e(money($pl['profit'])) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-info"><div class="card-body"><div class="text-muted small"><?= e($from) ?> → <?= e($to) ?></div><div class="h6 mb-0"><?= e(__('nav_reports')) ?></div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><strong><?= e(__('chart_sales')) ?></strong></div><div class="card-body"><canvas id="salesChart" height="110"></canvas></div></div>
        <div class="card mb-3"><div class="card-header"><strong><?= e(__('chart_profit')) ?> · <?= e($year) ?></strong></div><div class="card-body"><canvas id="profitChart" height="110"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><strong><?= e(__('chart_inventory')) ?></strong></div><div class="card-body"><canvas id="invChart" height="160"></canvas></div></div>
    </div>

    <div class="col-lg-6">
        <div class="card"><div class="card-header"><strong><?= e(__('chart_top_customers')) ?></strong></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th><?= e(__('field_name')) ?></th><th class="text-end"><?= e(__('total')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($topCustomers as $c): ?><tr><td><?= e($c['label']) ?></td><td class="text-end"><?= e(money($c['total'])) ?></td></tr><?php endforeach; ?>
                <?php if (!$topCustomers): ?><tr><td colspan="2" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><strong><?= e(__('chart_top_products')) ?></strong></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th><?= e(__('field_name')) ?></th><th class="text-end"><?= e(__('quantity')) ?></th><th class="text-end"><?= e(__('total')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($topProducts as $p): ?><tr><td><?= e($p['label']) ?></td><td class="text-end"><?= e(number_format((float) $p['qty'], 2)) ?></td><td class="text-end"><?= e(money($p['total'])) ?></td></tr><?php endforeach; ?>
                <?php if (!$topProducts): ?><tr><td colspan="3" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.EFA_REPORT = {
    sales: <?= json_encode($salesByDay, JSON_UNESCAPED_UNICODE) ?>,
    monthly: <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>,
    inv: <?= json_encode($invByCat, JSON_UNESCAPED_UNICODE) ?>,
    labels: {
        sales: <?= json_encode(__('chart_sales')) ?>,
        profit: <?= json_encode(__('profit')) ?>,
        expenses: <?= json_encode(__('expenses')) ?>
    }
};
</script>
<script src="<?= e(url('assets/js/reports.js')) ?>"></script>
