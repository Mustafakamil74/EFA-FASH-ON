<?php
/** @var array $branches @var array $levels @var int|null $branchId @var string $q @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form class="d-flex gap-2" method="get" action="<?= e(url('inventory')) ?>">
        <select name="branch" class="form-select" onchange="this.form.submit()">
            <option value=""><?= e(__('all_branches')) ?></option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= e($b['id']) ?>" <?= (string) $branchId === (string) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="<?= e(__('search')) ?>" value="<?= e($q) ?>">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('inventory/movements')) ?>" class="btn btn-outline-secondary"><i class="bi bi-clock-history me-1"></i><?= e(__('movement_history')) ?></a>
        <?php if ($canManage): ?>
            <a href="<?= e(url('inventory/move?action=in')) ?>" class="btn btn-success"><i class="bi bi-box-arrow-in-down me-1"></i><?= e(__('stock_in')) ?></a>
            <a href="<?= e(url('inventory/move?action=out')) ?>" class="btn btn-warning"><i class="bi bi-box-arrow-up me-1"></i><?= e(__('stock_out')) ?></a>
            <a href="<?= e(url('inventory/move?action=transfer')) ?>" class="btn btn-info"><i class="bi bi-arrow-left-right me-1"></i><?= e(__('stock_transfer')) ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><?= e(__('field_code')) ?></th>
                    <th><?= e(__('nav_products')) ?></th>
                    <th><?= e(__('field_color')) ?></th>
                    <th><?= e(__('field_size')) ?></th>
                    <th><?= e(__('branch')) ?></th>
                    <th class="text-end"><?= e(__('on_hand')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$levels): ?>
                <tr><td colspan="6" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
            <?php else: foreach ($levels as $r):
                $low = $r['min_stock'] > 0 && (float) $r['quantity'] <= (float) $r['min_stock']; ?>
                <tr class="<?= $low ? 'table-danger' : '' ?>">
                    <td><span class="badge bg-secondary"><?= e($r['product_code']) ?></span></td>
                    <td><?= e($r['product_name']) ?></td>
                    <td><?= e($r['color_name'] ?? '—') ?></td>
                    <td><?= e($r['size_name'] ?? '—') ?></td>
                    <td><?= e($r['branch_name']) ?></td>
                    <td class="text-end"><span class="badge bg-<?= $low ? 'danger' : 'success' ?>"><?= e(number_format((float) $r['quantity'], 2)) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
