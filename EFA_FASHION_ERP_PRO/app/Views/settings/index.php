<?php
/** @var array $s @var array $currencies */
?>
<div class="row g-3">
    <div class="col-lg-7">
        <form method="post" action="<?= e(url('settings')) ?>" enctype="multipart/form-data" class="card mb-3">
            <?= csrf_field() ?>
            <div class="card-header"><strong><?= e(__('company_info')) ?></strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_name')) ?></label>
                        <input name="company_name" class="form-control" value="<?= e($s['company_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_email')) ?></label>
                        <input type="email" name="company_email" class="form-control" value="<?= e($s['company_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_phone')) ?></label>
                        <input name="company_phone" class="form-control" value="<?= e($s['company_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_phone')) ?> 2</label>
                        <input name="company_phone2" class="form-control" value="<?= e($s['company_phone2'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_website')) ?></label>
                        <input name="company_website" class="form-control" value="<?= e($s['company_website'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('manager_signature')) ?></label>
                        <input name="manager_signature" class="form-control" value="<?= e($s['manager_signature'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= e(__('company_address')) ?></label>
                        <textarea name="company_address" class="form-control" rows="2"><?= e($s['company_address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_logo')) ?></label>
                        <input type="file" name="company_logo" accept="image/*" class="form-control">
                        <?php if (!empty($s['company_logo'])): ?><img src="<?= e(url($s['company_logo'])) ?>" alt="logo" class="mt-2" style="max-height:48px"><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(__('company_stamp')) ?></label>
                        <input type="file" name="company_stamp" accept="image/*" class="form-control">
                        <?php if (!empty($s['company_stamp'])): ?><img src="<?= e(url($s['company_stamp'])) ?>" alt="stamp" class="mt-2" style="max-height:48px"><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= e(__('pdf_paper')) ?></label>
                        <select name="pdf_paper" class="form-select">
                            <?php foreach (['A4', 'A5', 'Letter'] as $p): ?>
                                <option value="<?= e($p) ?>" <?= ($s['pdf_paper'] ?? 'A4') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= e(__('receipt_prefix')) ?></label>
                        <input name="receipt_prefix" class="form-control" value="<?= e($s['receipt_prefix'] ?? 'FIS-EFA-') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= e(__('low_stock_default')) ?></label>
                        <input type="number" min="0" name="low_stock_default" class="form-control" value="<?= e($s['low_stock_default'] ?? '5') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= e(__('print_footer')) ?></label>
                        <input name="print_footer" class="form-control" value="<?= e($s['print_footer'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i><?= e(__('save')) ?></button>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><strong><?= e(__('currencies')) ?> &amp; <?= e(__('exchange_rates')) ?></strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th><?= e(__('field_code')) ?></th><th><?= e(__('field_name')) ?></th><th class="text-end"><?= e(__('rate_to_base')) ?></th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($currencies as $c): ?>
                        <tr>
                            <td><?= e($c['code']) ?> <?= $c['is_base'] ? '<span class="badge bg-primary">' . e(__('base')) . '</span>' : '' ?></td>
                            <td><?= e($c['name']) ?></td>
                            <td class="text-end"><?= e(number_format((float) $c['rate_to_base'], 4)) ?></td>
                            <td class="text-end">
                                <?php if (!$c['is_base']): ?>
                                <form method="post" action="<?= e(url('settings/currency/' . $c['code'] . '/delete')) ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <form method="post" action="<?= e(url('settings/currency')) ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-4"><input name="code" class="form-control" placeholder="<?= e(__('field_code')) ?>" maxlength="5" required></div>
                    <div class="col-8"><input name="name" class="form-control" placeholder="<?= e(__('field_name')) ?>" required></div>
                    <div class="col-4"><input name="symbol" class="form-control" placeholder="<?= e(__('symbol')) ?>"></div>
                    <div class="col-4"><input type="number" step="0.000001" min="0" name="rate_to_base" class="form-control" placeholder="<?= e(__('rate_to_base')) ?>" value="1"></div>
                    <div class="col-4 d-flex align-items-center">
                        <div class="form-check"><input type="checkbox" name="is_base" value="1" class="form-check-input" id="isBase"><label class="form-check-label small" for="isBase"><?= e(__('base')) ?></label></div>
                    </div>
                    <div class="col-12 text-end"><button class="btn btn-sm btn-primary"><?= e(__('add')) ?></button></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong><?= e(__('backup_restore')) ?></strong></div>
            <div class="card-body">
                <a href="<?= e(url('settings/backup')) ?>" class="btn btn-outline-primary mb-3"><i class="bi bi-download me-1"></i><?= e(__('backup_now')) ?></a>
                <form method="post" action="<?= e(url('settings/restore')) ?>" enctype="multipart/form-data" data-confirm="<?= e(__('confirm_restore')) ?>">
                    <?= csrf_field() ?>
                    <label class="form-label"><?= e(__('restore_from_sql')) ?></label>
                    <div class="input-group">
                        <input type="file" name="sql_file" accept=".sql" class="form-control" required>
                        <button class="btn btn-outline-danger"><i class="bi bi-upload me-1"></i><?= e(__('restore')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
