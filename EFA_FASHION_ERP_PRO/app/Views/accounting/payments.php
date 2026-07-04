<?php
/** @var array $rows @var string $dir @var string $q @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="btn-group">
        <a href="<?= e(url('payments')) ?>" class="btn btn-outline-secondary <?= $dir === '' ? 'active' : '' ?>"><?= e(__('all')) ?></a>
        <a href="<?= e(url('payments?dir=in')) ?>" class="btn btn-outline-success <?= $dir === 'in' ? 'active' : '' ?>"><?= e(__('collection')) ?></a>
        <a href="<?= e(url('payments?dir=out')) ?>" class="btn btn-outline-danger <?= $dir === 'out' ? 'active' : '' ?>"><?= e(__('payment_out')) ?></a>
    </div>
    <form class="d-flex" method="get" action="<?= e(url('payments')) ?>">
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
        <div class="input-group"><input name="q" class="form-control" placeholder="<?= e(__('search')) ?>" value="<?= e($q) ?>"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
    </form>
    <?php if ($canManage): ?><a href="<?= e(url('payments/create')) ?>" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg me-1"></i><?= e(__('record_payment')) ?></a><?php endif; ?>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr>
            <th><?= e(__('date')) ?></th><th><?= e(__('direction')) ?></th><th><?= e(__('party')) ?></th>
            <th><?= e(__('receipt_number')) ?></th><th><?= e(__('method')) ?></th>
            <th class="text-end"><?= e(__('amount')) ?></th><th><?= e(__('field_notes')) ?></th>
            <?php if ($canManage): ?><th></th><?php endif; ?>
        </tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['pay_date']) ?></td>
                <td><span class="badge bg-<?= $r['direction'] === 'in' ? 'success' : 'danger' ?>"><?= e($r['direction'] === 'in' ? __('collection') : __('payment_out')) ?></span></td>
                <td><?= e($r['party_name'] ?? '—') ?></td>
                <td><?= e($r['receipt_number'] ?? '—') ?></td>
                <td><?= e(__('method_' . $r['method'])) ?></td>
                <td class="text-end fw-semibold <?= $r['direction'] === 'in' ? 'text-success' : 'text-danger' ?>"><?= e(money($r['amount'], $r['currency'])) ?></td>
                <td class="small text-muted"><?= e($r['note'] ?? '') ?></td>
                <?php if ($canManage): ?><td class="text-end"><form method="post" action="<?= e(url('payments/' . $r['id'] . '/delete')) ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td><?php endif; ?>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div></div>
