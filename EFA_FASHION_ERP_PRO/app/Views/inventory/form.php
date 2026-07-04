<?php
/** @var string $action @var array $branches @var array $variants */
$isTransfer = $action === 'transfer';
?>
<div class="card">
    <div class="card-header">
        <strong><?= e(__('stock_' . $action)) ?></strong>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e(url('inventory/move')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= e($action) ?>">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label"><?= e(__('nav_products')) ?> <span class="text-danger">*</span></label>
                    <select name="variant_id" class="form-select" required>
                        <option value=""><?= e(__('select')) ?></option>
                        <?php foreach ($variants as $v): ?>
                            <option value="<?= e($v['id']) ?>"><?= e($v['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($isTransfer): ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('from_branch')) ?> <span class="text-danger">*</span></label>
                        <select name="from_branch" class="form-select" required>
                            <?php foreach ($branches as $b): ?><option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('to_branch')) ?> <span class="text-danger">*</span></label>
                        <select name="to_branch" class="form-select" required>
                            <?php foreach ($branches as $b): ?><option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('branch')) ?> <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            <?php foreach ($branches as $b): ?><option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label"><?= e(__('quantity')) ?> <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label"><?= e(__('field_notes')) ?></label>
                    <input type="text" name="note" class="form-control">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= e(__('save')) ?></button>
                <a href="<?= e(url('inventory')) ?>" class="btn btn-outline-secondary"><?= e(__('cancel')) ?></a>
            </div>
        </form>
    </div>
</div>
