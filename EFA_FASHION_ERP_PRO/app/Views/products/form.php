<?php
use App\Models\Product;
/** @var array|null $row @var array $categories @var array $colors @var array $sizes
 *  @var array $selectedColors @var array $selectedSizes */
$isEdit = $row !== null;
$action = $isEdit ? url('products/' . $row['id'] . '/update') : url('products');
$val = fn (string $k, $d = '') => e($isEdit ? ($row[$k] ?? $d) : old($k, $d));
?>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('field_code')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="<?= $val('code') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= e(__('field_name')) ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('field_fabric_type')) ?></label>
                    <input type="text"
       name="fabric_type"
       class="form-control"
       value="<?= $val('fabric_type') ?>"
       placeholder="e.g. Cotton, Polyester, Denim">
                        
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['id']) ?>" <?= (string) ($isEdit ? $row['category_id'] : old('category_id')) === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= e(__('field_brand')) ?></label>
                    <input type="text" name="brand" class="form-control" value="<?= $val('brand') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('field_barcode')) ?></label>
                    <input type="text" name="barcode" class="form-control" value="<?= $val('barcode') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?= e(__('field_purchase_price')) ?></label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= $val('purchase_price', '0') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?= e(__('field_sale_price')) ?></label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= $val('sale_price', '0') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?= e(__('currency')) ?></label>
                    <select name="currency" class="form-control">
                    <option value="TRY">TRY</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
 
                  </select>
                 </div>
                <div class="col-md-2">
                    <label class="form-label"><?= e(__('field_min_stock')) ?></label>
                    <input type="number" step="0.01" name="min_stock" class="form-control" value="<?= $val('min_stock', '0') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= e(__('field_colors_text')) ?></label>
                    <input type="text"
                           name="colors"
                           class="form-control"
                           placeholder="Red, Green, Blue">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= e(__('field_sizes_text')) ?></label>
               <input type="text"
       name="size_ids"
       class="form-control"
       value="<?= $isEdit ? implode(', ', array_column(Product::variants((int)$row['id']), 'size_name')) : '' ?>"
       placeholder="S, M, L, XL, XXL">
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= e(__('field_image')) ?></label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if ($isEdit && !empty($row['image_path'])): ?>
                        <img src="<?= e(url($row['image_path'])) ?>" class="rounded mt-2" style="height:60px">
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label"><?= e(__('field_notes')) ?></label>
                    <textarea name="notes" class="form-control" rows="2"><?= $val('notes') ?></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= e(__('save')) ?></button>
                <a href="<?= e(url('products')) ?>" class="btn btn-outline-secondary"><?= e(__('cancel')) ?></a>
            </div>
        </form>
    </div>
</div>
