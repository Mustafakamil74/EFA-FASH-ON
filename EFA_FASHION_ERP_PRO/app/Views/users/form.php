<?php
/** @var array|null $user @var array $roles */
$isEdit = $user !== null;
$action = $isEdit ? url('users/' . $user['id'] . '/update') : url('users');
?>
<form method="post" action="<?= e($action) ?>" class="card" style="max-width:720px">
    <?= csrf_field() ?>
    <div class="card-header d-flex align-items-center">
        <strong><?= e($isEdit ? __('user_edit') : __('user_new')) ?></strong>
        <a href="<?= e(url('users')) ?>" class="btn btn-sm btn-outline-secondary ms-auto"><?= e(__('cancel')) ?></a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label"><?= e(__('field_name')) ?> <span class="text-danger">*</span></label>
                <input name="name" class="form-control" value="<?= e($user['name'] ?? old('name')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= e(__('username')) ?> <span class="text-danger">*</span></label>
                <input name="username" class="form-control" value="<?= e($user['username'] ?? old('username')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= e(__('email')) ?></label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? old('email')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= e(__('role')) ?> <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e($r['id']) ?>" <?= ($user['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= e(__('password')) ?> <?= $isEdit ? '<span class="text-muted small">(' . e(__('leave_blank_keep')) . ')</span>' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= (!$isEdit || $user['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isActive"><?= e(__('active')) ?></label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i><?= e(__('save')) ?></button>
    </div>
</form>
