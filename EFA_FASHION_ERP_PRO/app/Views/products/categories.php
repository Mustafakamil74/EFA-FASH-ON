<?php
/** @var array $rows @var bool $canManage */
?>
<div class="d-flex gap-2 mb-3">
    <a href="<?= e(url('products')) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i><?= e(__('nav_products')) ?></a>
</div>

<div class="row g-3">
    <?php if ($canManage): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><?= e(__('create')) ?></strong></div>
            <div class="card-body">
                <form method="post" action="<?= e(url('categories')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label"><?= e(__('field_name')) ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i><?= e(__('save')) ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-lg-<?= $canManage ? '8' : '12' ?>">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th><?= e(__('field_name')) ?></th><th class="text-end"><?= e(__('nav_products')) ?></th><th class="text-end"><?= e(__('actions')) ?></th></tr></thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <?php if ($canManage): ?>
                                    <form method="post" action="<?= e(url('categories/' . $r['id'] . '/update')) ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?= e($r['name']) ?>">
                                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                <?php else: ?>
                                    <?= e($r['name']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><span class="badge bg-secondary"><?= e($r['product_count']) ?></span></td>
                            <td class="text-end">
                                <?php if ($canManage): ?>
                                    <form method="post" action="<?= e(url('categories/' . $r['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
