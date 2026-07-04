<?php
/** @var array $rows @var string $q @var bool $canManage */
$typeBadge = ['sale' => 'success', 'purchase' => 'primary', 'return' => 'warning'];
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form class="d-flex" method="get" action="<?= e(url('receipts')) ?>" role="search">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="<?= e(__('search')) ?>" value="<?= e($q) ?>">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <?php if ($canManage): ?>
        <a href="<?= e(url('receipts/create')) ?>" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg me-1"></i><?= e(__('create')) ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><?= e(__('receipt_number')) ?></th>
                    <th><?= e(__('type')) ?></th>
                    <th><?= e(__('party')) ?></th>
                    <th><?= e(__('date')) ?></th>
                    <th class="text-end"><?= e(__('grand_total')) ?></th>
                    <th class="text-end"><?= e(__('paid')) ?></th>
                    <th class="text-end"><?= e(__('remaining')) ?></th>
                    <th class="text-end"><?= e(__('actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="8" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
            <?php else: foreach ($rows as $r):
                $remaining = (float) $r['grand_total'] - (float) $r['paid']; ?>
                <tr>
                    <td><a href="<?= e(url('receipts/' . $r['id'])) ?>" class="fw-semibold"><?= e($r['number']) ?></a></td>
                    <td><span class="badge bg-<?= e($typeBadge[$r['type']] ?? 'secondary') ?>"><?= e(__('rtype_' . $r['type'])) ?></span></td>
                    <td><?= e($r['party_name'] ?? '—') ?></td>
                    <td><?= e($r['receipt_date']) ?></td>
                    <td class="text-end"><?= e(money($r['grand_total'], $r['currency'])) ?></td>
                    <td class="text-end text-success"><?= e(money($r['paid'], $r['currency'])) ?></td>
                    <td class="text-end <?= $remaining > 0 ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= e(money($remaining, $r['currency'])) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= e(url('receipts/' . $r['id'])) ?>" class="btn btn-sm btn-outline-info" title="<?= e(__('details')) ?>"><i class="bi bi-eye"></i></a>
                        <a href="<?= e(url('receipts/' . $r['id'] . '/pdf')) ?>" class="btn btn-sm btn-outline-danger" target="_blank" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <?php if ($canManage): ?>
                            <a href="<?= e(url('receipts/' . $r['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary" title="<?= e(__('edit')) ?>"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= e(url('receipts/' . $r['id'] . '/duplicate')) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-secondary" title="<?= e(__('duplicate')) ?>"><i class="bi bi-files"></i></button>
                            </form>
                            <form method="post" action="<?= e(url('receipts/' . $r['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" title="<?= e(__('delete')) ?>"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
