<?php
/** @var array $branches @var array $rows @var int|null $branchId */
$typeBadge = [
    'in' => 'success', 'transfer_in' => 'info',
    'out' => 'warning', 'transfer_out' => 'secondary', 'adjust' => 'dark',
];
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <a href="<?= e(url('inventory')) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i><?= e(__('nav_inventory')) ?></a>
    <form class="ms-auto" method="get" action="<?= e(url('inventory/movements')) ?>">
        <select name="branch" class="form-select" onchange="this.form.submit()">
            <option value=""><?= e(__('all_branches')) ?></option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= e($b['id']) ?>" <?= (string) $branchId === (string) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><?= e(__('date')) ?></th>
                    <th><?= e(__('nav_products')) ?></th>
                    <th><?= e(__('branch')) ?></th>
                    <th><?= e(__('type')) ?></th>
                    <th class="text-end"><?= e(__('quantity')) ?></th>
                    <th><?= e(__('field_notes')) ?></th>
                    <th><?= e(__('nav_users')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="text-nowrap small"><?= e($r['created_at']) ?></td>
                    <td><?= e($r['product_code']) ?> <?= e($r['product_name']) ?>
                        <?php if ($r['color_name'] || $r['size_name']): ?><span class="text-muted small">(<?= e(trim(($r['color_name'] ?? '') . ' ' . ($r['size_name'] ?? ''))) ?>)</span><?php endif; ?>
                    </td>
                    <td><?= e($r['branch_name']) ?></td>
                    <td><span class="badge bg-<?= e($typeBadge[$r['type']] ?? 'secondary') ?>"><?= e(__('stock_type_' . $r['type'])) ?></span></td>
                    <td class="text-end <?= (float) $r['quantity'] < 0 ? 'text-danger' : 'text-success' ?>"><?= e(number_format((float) $r['quantity'], 2)) ?></td>
                    <td class="small"><?= e($r['note'] ?? '') ?></td>
                    <td class="small"><?= e($r['user_name'] ?? '—') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
