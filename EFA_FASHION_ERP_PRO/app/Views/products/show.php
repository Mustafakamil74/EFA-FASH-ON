<?php
/** @var array $row @var array $variants @var array|null $category */
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= e(url('products')) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i><?= e(__('back')) ?></a>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('products/' . $row['id'] . '/label')) ?>" class="btn btn-dark" target="_blank"><i class="bi bi-upc-scan me-1"></i><?= e(__('barcode_label')) ?></a>
        <a href="<?= e(url('products/' . $row['id'] . '/edit')) ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i><?= e(__('edit')) ?></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($row['image_path'])): ?>
                    <img src="<?= e(url($row['image_path'])) ?>" class="img-fluid rounded mb-3" style="max-height:220px">
                <?php else: ?>
                    <div class="text-muted py-5"><i class="bi bi-image" style="font-size:3rem"></i></div>
                <?php endif; ?>
                <h5 class="mb-0"><?= e($row['name']) ?></h5>
                <div class="text-muted"><?= e($row['code']) ?></div>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_category')) ?></span><span><?= e($category['name'] ?? '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_brand')) ?></span><span><?= e($row['brand'] ?: '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_barcode')) ?></span><span><?= e($row['barcode'] ?: '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_purchase_price')) ?></span><span><?= e(money($row['purchase_price'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_sale_price')) ?></span><span><?= e(money($row['sale_price'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_min_stock')) ?></span><span><?= e(number_format((float) $row['min_stock'], 2)) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><strong><?= e(__('variants')) ?></strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>SKU</th><th><?= e(__('field_color')) ?></th><th><?= e(__('field_size')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($variants as $v): ?>
                        <tr>
                            <td><?= e($v['sku'] ?: '—') ?></td>
                            <td>
                                <?php if ($v['color_hex']): ?><span class="d-inline-block rounded-circle me-1" style="width:12px;height:12px;background:<?= e($v['color_hex']) ?>"></span><?php endif; ?>
                                <?= e($v['color_name'] ?? '—') ?>
                            </td>
                            <td><?= e($v['size_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
