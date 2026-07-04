<?php
/** @var array|null $row @var string $module @var bool $hasContact */
$isEdit = $row !== null;
$action = $isEdit ? url($module . '/' . $row['id'] . '/update') : url($module);
$val = fn (string $k) => e($isEdit ? ($row[$k] ?? '') : old($k));
?>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= e(__('field_code')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="<?= $val('code') ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label"><?= e(__('field_name')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required>
                </div>
                <?php if ($hasContact): ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('factory_job')) ?></label>
                        <input type="text" name="contact_name" class="form-control" value="<?= $val('contact_name') ?>">
                    </div>
                <?php endif; ?>
                <div class="col-md-<?= $hasContact ? '6' : '6' ?>">
                    <label class="form-label"><?= e(__('field_phone')) ?></label>
                    <input type="text" name="phone" class="form-control" value="<?= $val('phone') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= e(__('field_address')) ?></label>
                    <input type="text" name="address" class="form-control" value="<?= $val('address') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= e(__('field_notes')) ?></label>
                    <textarea name="notes" class="form-control" rows="3"><?= $val('notes') ?></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= e(__('save')) ?></button>
                <a href="<?= e(url($module)) ?>" class="btn btn-outline-secondary"><?= e(__('cancel')) ?></a>
            </div>
        </form>
    </div>
</div>
