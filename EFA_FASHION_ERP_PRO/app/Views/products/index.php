<?php
/** @var array $rows @var string $q @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form class="d-flex" method="get" action="<?= e(url('products')) ?>" role="search">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="<?= e(__('search')) ?>" value="<?= e($q) ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('categories')) ?>" class="btn btn-outline-secondary"><i class="bi bi-tags me-1"></i><?= e(__('nav_categories')) ?></a>
        <?php if ($canManage): ?>
            <a href="<?= e(url('products/create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= e(__('create')) ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th></th>
                    <th><?= e(__('field_code')) ?></th>
                    <th><?= e(__('field_name')) ?></th>
                    <th><?= e(__('field_category')) ?></th>
                    <th class="text-end"><?= e(__('field_sale_price')) ?></th>
                    <th class="text-end"><?= e(__('on_hand')) ?></th>
                    <th class="text-end"><?= e(__('actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4"><?= e(__('no_records')) ?></td></tr>
            <?php else: foreach ($rows as $r):
                $low = $r['min_stock'] > 0 && (float) $r['on_hand'] <= (float) $r['min_stock']; ?>
                <tr class="<?= $low ? 'table-danger' : '' ?>">
                    <td style="width:48px">
                        <?php if (!empty($r['image_path'])): ?>
                            <img src="<?= e(url($r['image_path'])) ?>" alt="" class="rounded" style="width:40px;height:40px;object-fit:cover">
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-secondary"><?= e($r['code']) ?></span></td>
                    <td><?= e($r['name']) ?><?php if ($r['brand']): ?> <span class="text-muted small">· <?= e($r['brand']) ?></span><?php endif; ?></td>
                    <td><?= e($r['category_name'] ?? '—') ?></td>
                    <td class="text-end"><?= e(money($r['sale_price'])) ?></td>
                    <td class="text-end">
                        <span class="badge bg-<?= $low ? 'danger' : 'success' ?>"><?= e(number_format((float) $r['on_hand'], 2)) ?></span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= e(url('products/' . $r['id'])) ?>" class="btn btn-sm btn-outline-info" title="<?= e(__('details')) ?>"><i class="bi bi-eye"></i></a>
                        <a href="<?= e(url('products/' . $r['id'] . '/label')) ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="<?= e(__('barcode_label')) ?>"><i class="bi bi-upc-scan"></i></a>
                        <?php if ($canManage): ?>
                            <a href="<?= e(url('products/' . $r['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary" title="<?= e(__('edit')) ?>"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= e(url('products/' . $r['id'] . '/delete')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_delete')) ?>">
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
