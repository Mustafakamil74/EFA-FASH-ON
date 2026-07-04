<?php
/** @var array $row @var array $items @var ?string $partyName @var float $paid @var array $company @var bool $canManage */
$paid = (float) ($row['paid'] ?? $paid);
$remaining = (float) $row['grand_total'] - $paid;
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= e(url('receipts')) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i><?= e(__('back')) ?></a>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('receipts/' . $row['id'] . '/pdf')) ?>" class="btn btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-1"></i><?= e(__('print')) ?></button>
        <?php if ($canManage): ?>
            <a href="<?= e(url('receipts/' . $row['id'] . '/edit')) ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i><?= e(__('edit')) ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-4">
            <div>
                <h4 class="mb-0"><?= e($company['company_name'] ?? 'EFA FASHION') ?></h4>
                <div class="text-muted small"><?= e($company['company_address'] ?? '') ?></div>
                <div class="text-muted small"><?= e($company['company_phone'] ?? '') ?></div>
            </div>
            <div class="text-end">
                <h5 class="text-primary mb-1"><?= e($row['number']) ?></h5>
                <div><?= e(__('rtype_' . $row['type'])) ?></div>
                <div class="text-muted small"><?= e($row['receipt_date']) ?> <?= e(substr((string) $row['receipt_time'], 0, 5)) ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="text-muted small text-uppercase"><?= e(__('party')) ?></div>
                <div class="fw-semibold"><?= e($partyName ?? '—') ?> <span class="badge bg-light text-dark"><?= e(__('nav_' . $row['party_type'] . 's')) ?></span></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th><?= e(__('description')) ?></th>
                        <th><?= e(__('serial')) ?></th>
                        <th><?= e(__('field_color')) ?></th>
                        <th><?= e(__('field_size')) ?></th>
                        <th class="text-end"><?= e(__('quantity')) ?></th>
                        <th class="text-end"><?= e(__('unit_price')) ?></th>
                        <th class="text-end"><?= e(__('total')) ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i => $it): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($it['description'] ?? '') ?></td>
                        <td><?= e($it['serial_number'] ?? '') ?></td>
                        <td><?= e($it['color'] ?? '') ?></td>
                        <td><?= e($it['size'] ?? '') ?></td>
                        <td class="text-end"><?= e(number_format((float) $it['quantity'], 2)) ?></td>
                        <td class="text-end"><?= e(money($it['unit_price'], $row['currency'])) ?></td>
                        <td class="text-end"><?= e(money($it['line_total'], $row['currency'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-sm">
                    <tr><td><?= e(__('subtotal')) ?></td><td class="text-end"><?= e(money($row['subtotal'], $row['currency'])) ?></td></tr>
                    <tr><td><?= e(__('discount')) ?></td><td class="text-end">-<?= e(money($row['discount'], $row['currency'])) ?></td></tr>
                    <tr><td><?= e(__('shipping')) ?></td><td class="text-end"><?= e(money($row['shipping_cost'], $row['currency'])) ?></td></tr>
                    <tr class="fw-bold border-top"><td><?= e(__('grand_total')) ?></td><td class="text-end"><?= e(money($row['grand_total'], $row['currency'])) ?></td></tr>
                    <tr class="text-success"><td><?= e(__('paid')) ?></td><td class="text-end"><?= e(money($paid, $row['currency'])) ?></td></tr>
                    <tr class="<?= $remaining > 0 ? 'text-danger fw-bold' : '' ?>"><td><?= e(__('remaining')) ?></td><td class="text-end"><?= e(money($remaining, $row['currency'])) ?></td></tr>
                </table>
            </div>
        </div>

        <?php if (!empty($row['notes'])): ?>
            <div class="mt-3"><span class="text-muted"><?= e(__('field_notes')) ?>:</span> <?= nl2br(e($row['notes'])) ?></div>
        <?php endif; ?>
    </div>
</div>
