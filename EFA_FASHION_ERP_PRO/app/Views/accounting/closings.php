<?php
/** @var array $rows @var bool $canManage */
?>
<div class="row g-3">
    <?php if ($canManage): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><?= e(__('run_closing')) ?></strong></div>
            <div class="card-body">
                <form method="post" action="<?= e(url('closings')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label"><?= e(__('period_type')) ?></label>
                        <select name="period_type" class="form-select" id="ptype">
                            <option value="daily"><?= e(__('period_daily')) ?></option>
                            <option value="monthly"><?= e(__('period_monthly')) ?></option>
                            <option value="yearly"><?= e(__('period_yearly')) ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= e(__('period_label')) ?></label>
                        <input name="period_label" id="plabel" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
                        <div class="form-text" id="phint">YYYY-MM-DD</div>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-lock me-1"></i><?= e(__('run_closing')) ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-<?= $canManage ? '8' : '12' ?>">
        <div class="card"><div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th><?= e(__('period_type')) ?></th><th><?= e(__('period_label')) ?></th>
                    <th class="text-end"><?= e(__('rtype_sale')) ?></th><th class="text-end"><?= e(__('expenses')) ?></th>
                    <th class="text-end"><?= e(__('income')) ?></th><th class="text-end"><?= e(__('profit')) ?></th><th><?= e(__('date')) ?></th>
                </tr></thead>
                <tbody>
                <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e(__('period_' . $r['period_type'])) ?></td>
                        <td class="fw-semibold"><?= e($r['period_label']) ?></td>
                        <td class="text-end"><?= e(money($r['total_sales'])) ?></td>
                        <td class="text-end text-danger"><?= e(money($r['total_expenses'])) ?></td>
                        <td class="text-end text-success"><?= e(money($r['total_income'])) ?></td>
                        <td class="text-end fw-bold <?= $r['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= e(money($r['profit'])) ?></td>
                        <td class="small text-muted"><?= e($r['closed_at']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<script>
(function () {
    var t = document.getElementById('ptype'), l = document.getElementById('plabel'), h = document.getElementById('phint');
    if (!t) return;
    var now = new Date(), y = now.getFullYear(), m = String(now.getMonth() + 1).padStart(2, '0'), d = String(now.getDate()).padStart(2, '0');
    t.addEventListener('change', function () {
        if (t.value === 'daily') { l.value = y + '-' + m + '-' + d; h.textContent = 'YYYY-MM-DD'; }
        else if (t.value === 'monthly') { l.value = y + '-' + m; h.textContent = 'YYYY-MM'; }
        else { l.value = '' + y; h.textContent = 'YYYY'; }
    });
})();
</script>
