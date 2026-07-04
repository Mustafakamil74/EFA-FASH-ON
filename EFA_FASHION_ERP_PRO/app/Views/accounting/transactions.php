<?php
/** @var array $rows @var string $kind @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="btn-group">
        <a href="<?= e(url('transactions')) ?>" class="btn btn-outline-secondary <?= $kind === '' ? 'active' : '' ?>"><?= e(__('all')) ?></a>
        <a href="<?= e(url('transactions?kind=expense')) ?>" class="btn btn-outline-danger <?= $kind === 'expense' ? 'active' : '' ?>"><?= e(__('expenses')) ?></a>
        <a href="<?= e(url('transactions?kind=income')) ?>" class="btn btn-outline-success <?= $kind === 'income' ? 'active' : '' ?>"><?= e(__('income')) ?></a>
    </div>
</div>

<div class="row g-3">
    <?php if ($canManage): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><?= e(__('add')) ?></strong></div>
            <div class="card-body">
                <form method="post" action="<?= e(url('transactions')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label"><?= e(__('type')) ?></label>
                        <select name="kind" class="form-select">
                            <option value="expense"><?= e(__('expenses')) ?></option>
                            <option value="income"><?= e(__('income')) ?></option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label"><?= e(__('category')) ?></label><input name="category" class="form-control"></div>
                    <div class="row g-2 mb-2">
                        <div class="col-7"><label class="form-label"><?= e(__('amount')) ?></label><input type="number" step="0.01" min="0" name="amount" class="form-control" required></div>
                        <div class="col-5"><label class="form-label"><?= e(__('currency')) ?></label><input name="currency" class="form-control" value="USD"></div>
                    </div>
                    <div class="mb-2"><label class="form-label"><?= e(__('date')) ?></label><input type="date" name="txn_date" class="form-control" value="<?= e(date('Y-m-d')) ?>"></div>
                    <div class="mb-3"><label class="form-label"><?= e(__('field_notes')) ?></label><input name="note" class="form-control"></div>
                    <button class="btn btn-primary w-100"><?= e(__('save')) ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-<?= $canManage ? '8' : '12' ?>">
        <div class="card"><div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th><?= e(__('date')) ?></th><th><?= e(__('type')) ?></th><th><?= e(__('category')) ?></th><th class="text-end"><?= e(__('amount')) ?></th><th><?= e(__('field_notes')) ?></th><?php if ($canManage): ?><th></th><?php endif; ?></tr></thead>
                <tbody>
                <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['txn_date']) ?></td>
                        <td><span class="badge bg-<?= $r['kind'] === 'income' ? 'success' : 'danger' ?>"><?= e($r['kind'] === 'income' ? __('income') : __('expenses')) ?></span></td>
                        <td><?= e($r['category'] ?? '') ?></td>
                        <td class="text-end fw-semibold <?= $r['kind'] === 'income' ? 'text-success' : 'text-danger' ?>"><?= e(money($r['amount'], $r['currency'])) ?></td>
                        <td class="small text-muted"><?= e($r['note'] ?? '') ?></td>
                        <?php if ($canManage): ?><td class="text-end"><form method="post" action="<?= e(url('transactions/' . $r['id'] . '/delete')) ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td><?php endif; ?>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
