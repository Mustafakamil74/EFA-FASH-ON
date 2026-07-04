<?php
/** @var array $users */
?>
<div class="d-flex mb-3">
    <a href="<?= e(url('users/create')) ?>" class="btn btn-primary ms-auto"><i class="bi bi-person-plus me-1"></i><?= e(__('user_new')) ?></a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>#</th><th><?= e(__('field_name')) ?></th><th><?= e(__('username')) ?></th>
                <th><?= e(__('email')) ?></th><th><?= e(__('role')) ?></th><th><?= e(__('status')) ?></th><th class="text-end"></th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['id']) ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email'] ?? '') ?></td>
                    <td><span class="badge bg-secondary"><?= e($u['role_label']) ?></span></td>
                    <td>
                        <?php if ($u['is_active']): ?><span class="badge bg-success"><?= e(__('active')) ?></span>
                        <?php else: ?><span class="badge bg-danger"><?= e(__('inactive')) ?></span><?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= e(url('users/' . $u['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="<?= e(url('users/' . $u['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?><tr><td colspan="7" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
