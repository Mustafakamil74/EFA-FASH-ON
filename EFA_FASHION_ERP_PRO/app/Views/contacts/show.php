<?php
/** @var array $row @var string $module @var array $ledger @var bool $hasContact */
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= e(url($module)) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i><?= e(__('back')) ?></a>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url($module . '/' . $row['id'] . '/pdf')) ?>" class="btn btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-1"></i><?= e(__('print')) ?></button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><?= e($row['name']) ?></strong></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_code')) ?></span><span><?= e($row['code']) ?></span></li>
                <?php if ($hasContact): ?>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_contact_name')) ?></span><span><?= e($row['contact_name'] ?? '—') ?></span></li>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_phone')) ?></span><span><?= e($row['phone'] ?? '—') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(__('field_address')) ?></span><span><?= e($row['address'] ?? '—') ?></span></li>
                <li class="list-group-item"><span class="text-muted d-block mb-1"><?= e(__('field_notes')) ?></span><?= nl2br(e($row['notes'] ?? '')) ?></li>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-journal-text me-2"></i><strong><?= e(__('account_statement')) ?></strong>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                </tbody>
                        <tr>
                            <th><?= e(__('date')) ?></th>
                                <th><?= e(__('reference')) ?></th>
                                <th><?= e(__('payment_type')) ?></th>
                                <th class="text-end"><?= e(__('paid')) ?></th>
                                <th class="text-end"><?= e(__('debit')) ?></th>
                                
                                
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$ledger['rows']): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr>
                    <?php else: foreach ($ledger['rows'] as $r): ?>
                        <tr>
                            <td><?= e($r['date']) ?></td>
                            <td><?= e($r['ref'] ?: '—') ?></td>
                            <td>
<?=
$r['payment_type'] === 'cash' ? e(__('cash')) :
($r['payment_type'] === 'credit' ? e(__('credit')) :
($r['payment_type'] === 'partial' ? e(__('partial')) :
e($r['payment_type'] ?? '-')))
?>
</td>
                            <td class="text-end"><?= $r['paid'] ? e(money($r['paid'])) : '' ?></td>
                            <td class="text-end"><?= $r['debit'] ? e(money($r['debit'])) : '' ?></td>
                            
                            
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3"><?= e(__('total')) ?></th>
                            <th class="text-end"><?= e(money($ledger['totals']['paid'])) ?></th>
                            <th class="text-end"><?= e(money($ledger['totals']['charged'])) ?></th>
                            
                            
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
