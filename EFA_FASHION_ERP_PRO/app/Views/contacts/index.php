<?php
/** @var array $rows @var string $module @var string $q @var bool $hasContact @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form class="d-flex" method="get" action="<?= e(url($module)) ?>" role="search">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="<?= e(__('search')) ?>" value="<?= e($q) ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <?php if ($canManage): ?>
        <a href="<?= e(url($module . '/create')) ?>" class="btn btn-primary ms-auto">
            <i class="bi bi-plus-lg me-1"></i><?= e(__('create')) ?>
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><?= e(__('field_code')) ?></th>
                    <th><?= e(__('field_name')) ?></th>
                    <?php if ($hasContact): ?><th><?= e(__('field_contact_name')) ?></th><?php endif; ?>
                    <th><?= e(__('field_phone')) ?></th>
                    <th><?= e(__('field_address')) ?></th>
                    <th class="text-end"><?= e(__('actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= e($r['code']) ?></span></td>
                    <td><?= e($r['name']) ?></td>
                    <?php if ($hasContact): ?><td><?= e($r['contact_name'] ?? '') ?></td><?php endif; ?>
                    <td><?= e($r['phone'] ?? '') ?></td>
                    <td class="text-truncate" style="max-width:220px"><?= e($r['address'] ?? '') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= e(url($module . '/' . $r['id'])) ?>" class="btn btn-sm btn-outline-info" title="<?= e(__('details')) ?>"><i class="bi bi-eye"></i></a>
                        <?php if ($canManage): ?>
                            <a href="<?= e(url($module . '/' . $r['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary" title="<?= e(__('edit')) ?>"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= e(url($module . '/' . $r['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>">
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
