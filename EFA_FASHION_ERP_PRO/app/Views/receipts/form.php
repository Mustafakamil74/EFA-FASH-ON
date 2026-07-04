<?php
/** @var array|null $row @var array $items @var array $branches @var array $currencies
 *  @var array $parties @var array $variants @var string $nextNumber */
$isEdit = $row !== null;
$action = $isEdit ? url('receipts/' . $row['id'] . '/update') : url('receipts');
$cur = $isEdit ? $row['currency'] : 'USD';
?>
<form method="post" action="<?= e($action) ?>" id="receiptForm">
    <?= csrf_field() ?>
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center">
            <strong><?= e($isEdit ? $row['number'] : $nextNumber) ?></strong>
            <a href="<?= e(url('receipts')) ?>" class="btn btn-sm btn-outline-secondary ms-auto"><?= e(__('cancel')) ?></a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('type')) ?></label>
                    <select name="type" class="form-select">
                        <?php foreach (['sale', 'purchase', 'return'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($isEdit ? $row['type'] : 'sale') === $t ? 'selected' : '' ?>><?= e(__('rtype_' . $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('party_type')) ?></label>
                    <select name="party_type" id="partyType" class="form-select">
                        <?php foreach (['customer', 'shop', 'factory'] as $pt): ?>
                            <option value="<?= $pt ?>" <?= ($isEdit ? $row['party_type'] : 'customer') === $pt ? 'selected' : '' ?>><?= e(__('nav_' . $pt . 's')) ?></option>
                        <?php endforeach; ?>
                    </select><div class="col-md-3">
    <label class="form-label"><?= __('paid_amount') ?></label>
    <input
        type="number"
        step="0.01"
        min="0"
        name="paid"
        class="form-control"
        value="<?= $isEdit ? ($row['paid'] ?? 0) : 0 ?>">
</div>
                </div>
                <div class="col-md-3">
    <label class="form-label"><?= __('payment_type') ?></label>
<select name="payment_type" class="form-select">
    <option value="partial"
<?= ($isEdit ? $row['payment_type'] : '') === 'partial'
? 'selected'
: '' ?>>
    <?= e(__('partial')) ?>
</option>

    <option value="cash"
        <?= ($isEdit ? $row['payment_type'] : 'credit') === 'cash'
            ? 'selected'
            : '' ?>>
        <?= e(__('cash')) ?>
    </option>

    <option value="credit"
        <?= ($isEdit ? $row['payment_type'] : 'credit') === 'credit'
            ? 'selected'
            : '' ?>>
        <?= e(__('credit')) ?>
    </option>

</select>
</div>
                <div class="col-md-6">
                    <label class="form-label"><?= e(__('party')) ?> <span class="text-danger">*</span></label>
                    <select name="party_id" id="partyId" class="form-select" required></select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= e(__('branch')) ?></label>
                    <select name="branch_id" class="form-select">
                        <option value=""><?= e(__('none')) ?></option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= e($b['id']) ?>" <?= (string) ($isEdit ? $row['branch_id'] : '') === (string) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('currency')) ?></label>
                    <select name="currency" id="currency" class="form-select">
                        <?php foreach ($currencies as $c): ?>
                            <option value="<?= e($c['code']) ?>" <?= $cur === $c['code'] ? 'selected' : '' ?>><?= e($c['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('date')) ?></label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= e($isEdit ? $row['receipt_date'] : date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('time')) ?></label>
                    <input type="time" name="receipt_time" class="form-control" value="<?= e($isEdit ? substr((string) $row['receipt_time'], 0, 5) : date('H:i')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex align-items-center">
            <strong><?= e(__('items')) ?></strong>
            <button type="button" class="btn btn-sm btn-success ms-auto" id="addRow"><i class="bi bi-plus-lg me-1"></i><?= e(__('add_row')) ?></button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width:200px"><?= e(__('nav_products')) ?></th>
                        <th><?= e(__('description')) ?></th>
                        <th><?= e(__('serial')) ?></th>
                        <th style="width:90px"><?= e(__('field_color')) ?></th>
                        <th style="width:80px"><?= e(__('field_size')) ?></th>
                        <th style="width:90px"><?= e(__('quantity')) ?></th>
                        <th style="width:110px"><?= e(__('unit_price')) ?></th>
                        <th style="width:120px" class="text-end"><?= e(__('total')) ?></th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-body">
                    <label class="form-label"><?= e(__('field_notes')) ?></label>
                    <textarea name="notes" class="form-control" rows="3"><?= e($isEdit ? ($row['notes'] ?? '') : '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span><?= e(__('subtotal')) ?></span><strong id="subtotalLabel">0.00</strong></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?= e(__('discount')) ?></span>
                        <input type="number" step="0.01" name="discount" id="discount" class="form-control form-control-sm w-auto text-end" value="<?= e($isEdit ? $row['discount'] : '0') ?>" style="max-width:130px">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?= e(__('shipping')) ?></span>
                        <input type="number" step="0.01" name="shipping_cost" id="shipping" class="form-control form-control-sm w-auto text-end" value="<?= e($isEdit ? $row['shipping_cost'] : '0') ?>" style="max-width:130px">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5"><span><?= e(__('grand_total')) ?></span><strong id="grandLabel">0.00</strong></div>
                    <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i><?= e(__('save')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Row template -->
<template id="rowTpl">
    <tr class="item-row">
        <td>
            <select name="item_variant[]" class="form-select form-select-sm v-sel">
                <option value=""><?= e(__('manual_entry')) ?></option>
                <?php foreach ($variants as $v): ?>
                    <option value="<?= e($v['id']) ?>"
                        data-price="<?= e($v['sale_price']) ?>"
                        data-color="<?= e($v['color'] ?? '') ?>"
                        data-size="<?= e($v['size'] ?? '') ?>"
                        data-desc="<?= e($v['label']) ?>"><?= e($v['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="item_desc[]" class="form-control form-control-sm v-desc"></td>
        <td><input type="text" name="item_serial[]" class="form-control form-control-sm"></td>
        <td><input type="text" name="item_color[]" class="form-control form-control-sm v-color"></td>
        <td><input type="text" name="item_size[]" class="form-control form-control-sm v-size"></td>
        <td><input type="number" step="0.01" min="0" name="item_qty[]" class="form-control form-control-sm v-qty" value="1"></td>
        <td><input type="number" step="0.01" min="0" name="item_price[]" class="form-control form-control-sm v-price" value="0"></td>
        <td class="text-end v-line">0.00</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger rm"><i class="bi bi-x"></i></button></td>
    </tr>
</template>

<script>
window.EFA_RECEIPT = {
    parties: <?= json_encode($parties, JSON_UNESCAPED_UNICODE) ?>,
    selectedParty: <?= json_encode($isEdit ? (int) $row['party_id'] : 0) ?>,
    existingItems: <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>,
    debugItems: <?= count($items) ?>, 
    labels: { party: <?= json_encode(__('select')) ?> }
};
</script>

