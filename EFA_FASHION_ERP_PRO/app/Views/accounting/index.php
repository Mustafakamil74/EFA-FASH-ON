<?php
/** @var array $cashBoxes @var array $banks @var array $pl @var float $debts
 *  @var float $invValue @var float $capital @var float $cashTotal @var float $bankTotal
 *  @var string $periodFrom @var string $periodTo @var bool $canManage */
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= e(url('payments')) ?>" class="btn btn-outline-primary"><i class="bi bi-cash-coin me-1"></i><?= e(__('nav_payments')) ?></a>
    <a href="<?= e(url('checks')) ?>" class="btn btn-outline-primary"><i class="bi bi-bank me-1"></i><?= e(__('nav_checks')) ?></a>
    <a href="<?= e(url('transactions')) ?>" class="btn btn-outline-primary"><i class="bi bi-wallet2 me-1"></i><?= e(__('nav_expenses_income')) ?></a>
    <a href="<?= e(url('closings')) ?>" class="btn btn-outline-primary"><i class="bi bi-calendar-check me-1"></i><?= e(__('nav_closings')) ?></a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card stat-card border-start border-primary"><div class="card-body"><div class="text-muted small"><?= e(__('cash_total')) ?></div><div class="h4 mb-0"><?= e(money($cashTotal)) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-info"><div class="card-body"><div class="text-muted small"><?= e(__('bank_total')) ?></div><div class="h4 mb-0"><?= e(money($bankTotal)) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-warning"><div class="card-body"><div class="text-muted small"><?= e(__('total_debts')) ?></div><div class="h4 mb-0"><?= e(money($debts)) ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card border-start border-success"><div class="card-body"><div class="text-muted small"><?= e(__('capital')) ?></div><div class="h4 mb-0"><?= e(money($capital)) ?></div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header d-flex"><strong><?= e(__('profit_loss')) ?></strong><span class="ms-auto text-muted small"><?= e($periodFrom) ?> → <?= e($periodTo) ?></span></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><?= e(__('rtype_sale')) ?><span class="text-success"><?= e(money($pl['sales'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><?= e(__('rtype_purchase')) ?><span class="text-danger">-<?= e(money($pl['purchases'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><?= e(__('expenses')) ?><span class="text-danger">-<?= e(money($pl['expenses'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><?= e(__('income')) ?><span class="text-success"><?= e(money($pl['income'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between fw-bold"><?= e(__('profit')) ?><span class="<?= $pl['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= e(money($pl['profit'])) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><?= e(__('inventory_value')) ?><span><?= e(money($invValue)) ?></span></li>
            </ul>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header"><strong><?= e(__('cash_boxes')) ?></strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th><?= e(__('field_name')) ?></th><th><?= e(__('currency')) ?></th><th class="text-end"><?= e(__('balance')) ?></th><?php if ($canManage): ?><th></th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php foreach ($cashBoxes as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td><td><?= e($c['currency']) ?></td>
                            <td class="text-end fw-semibold"><?= e(money($c['balance'], $c['currency'])) ?></td>
                            <?php if ($canManage): ?><td class="text-end"><form method="post" action="<?= e(url('accounting/cashbox/' . $c['id'] . '/delete')) ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$cashBoxes): ?><tr><td colspan="4" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($canManage): ?>
            <div class="card-body border-top">
                <form method="post" action="<?= e(url('accounting/cashbox')) ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-md-5"><input name="name" class="form-control form-control-sm" placeholder="<?= e(__('field_name')) ?>" required></div>
                    <div class="col-md-3"><input name="currency" class="form-control form-control-sm" placeholder="USD" value="USD"></div>
                    <div class="col-md-2"><input name="balance" type="number" step="0.01" class="form-control form-control-sm" placeholder="0" value="0"></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><?= e(__('save')) ?></button></div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header"><strong><?= e(__('bank_accounts')) ?></strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th><?= e(__('bank')) ?></th><th>IBAN</th><th><?= e(__('currency')) ?></th><th class="text-end"><?= e(__('balance')) ?></th><?php if ($canManage): ?><th></th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php foreach ($banks as $b): ?>
                        <tr>
                            <td><?= e($b['bank_name']) ?></td><td class="small"><?= e($b['iban'] ?? '') ?></td><td><?= e($b['currency']) ?></td>
                            <td class="text-end fw-semibold"><?= e(money($b['balance'], $b['currency'])) ?></td>
                            <?php if ($canManage): ?><td class="text-end"><form method="post" action="<?= e(url('accounting/bank/' . $b['id'] . '/delete')) ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$banks): ?><tr><td colspan="5" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($canManage): ?>
            <div class="card-body border-top">
                <form method="post" action="<?= e(url('accounting/bank')) ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-md-4"><input name="bank_name" class="form-control form-control-sm" placeholder="<?= e(__('bank')) ?>" required></div>
                    <div class="col-md-4"><input name="iban" class="form-control form-control-sm" placeholder="IBAN"></div>
                    <div class="col-md-2"><input name="currency" class="form-control form-control-sm" value="USD"></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><?= e(__('save')) ?></button></div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
