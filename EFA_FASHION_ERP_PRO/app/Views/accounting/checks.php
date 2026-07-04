<?php
/** @var array $rows @var string $status @var bool $canManage */
$badge = ['pending' => 'warning', 'cleared' => 'success', 'bounced' => 'danger', 'cancelled' => 'secondary'];
$today = date('Y-m-d');
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="btn-group">
        <a href="<?= e(url('checks')) ?>" class="btn btn-outline-secondary <?= $status === '' ? 'active' : '' ?>"><?= e(__('all')) ?></a>
        <?php foreach (['pending', 'cleared', 'bounced', 'cancelled'] as $s): ?>
            <a href="<?= e(url('checks?status=' . $s)) ?>" class="btn btn-outline-secondary <?= $status === $s ? 'active' : '' ?>"><?= e(__('check_' . $s)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php if ($canManage): ?><a href="<?= e(url('checks/create')) ?>" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg me-1"></i><?= e(__('create')) ?></a><?php endif; ?>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr>
            <th><?= e(__('direction')) ?></th><th><?= e(__('check_number')) ?></th><th><?= e(__('party')) ?></th>
            <th><?= e(__('bank')) ?></th><th class="text-end"><?= e(__('amount')) ?></th><th><?= e(__('due_date')) ?></th>
            <th><?= e(__('status')) ?></th><?php if ($canManage): ?><th></th><?php endif; ?>
        </tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
        <?php else: foreach ($rows as $r):
            $overdue = $r['status'] === 'pending' && $r['due_date'] < $today; ?>
            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                <td><span class="badge bg-<?= $r['direction'] === 'in' ? 'success' : 'danger' ?>"><?= e($r['direction'] === 'in' ? __('check_in') : __('check_out')) ?></span></td>
                <td><?= e($r['check_number'] ?? '—') ?></td>
                <td><?= e($r['party_name'] ?? '—') ?></td>
                <td><?= e($r['bank_name'] ?? '') ?></td>
                <td class="text-end fw-semibold"><?= e(money($r['amount'], $r['currency'])) ?></td>
                <td><?= e($r['due_date']) ?> <?= $overdue ? '<i class="bi bi-exclamation-triangle-fill text-danger"></i>' : '' ?></td>
                <td><span class="badge bg-<?= e($badge[$r['status']] ?? 'secondary') ?>"><?= e(__('check_' . $r['status'])) ?></span></td>
                <?php if ($canManage): ?>
                <td class="text-end text-nowrap">
                    <form method="post" action="<?= e(url('checks/' . $r['id'] . '/status')) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                            <?php foreach (['pending', 'cleared', 'bounced', 'cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(__('check_' . $s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <form method="post" action="<?= e(url('checks/' . $r['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div></div>
