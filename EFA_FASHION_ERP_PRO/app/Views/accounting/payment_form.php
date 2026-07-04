<?php
/** @var array $parties @var array $cashBoxes @var array $banks */
?>
<form method="post" action="<?= e(url('payments')) ?>" class="card">
    <?= csrf_field() ?>
    <div class="card-header d-flex align-items-center">
        <strong><?= e(__('record_payment')) ?></strong>
        <a href="<?= e(url('payments')) ?>" class="btn btn-sm btn-outline-secondary ms-auto"><?= e(__('cancel')) ?></a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><?= e(__('direction')) ?></label>
                <select name="direction" class="form-select">
                    <option value="in"><?= e(__('collection')) ?></option>
                    <option value="out"><?= e(__('payment_out')) ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('party_type')) ?></label>
                <select name="party_type" id="partyType" class="form-select">
                    <option value="customer"><?= e(__('nav_customers')) ?></option>
                    <option value="shop"><?= e(__('nav_shops')) ?></option>
                    <option value="factory"><?= e(__('nav_factories')) ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('party')) ?> <span class="text-danger">*</span></label>
                <select name="party_id" id="partyId" class="form-select" required></select>
            </div>

            <div class="col-md-4">
                <label class="form-label"><?= e(__('method')) ?></label>
                <select name="method" id="method" class="form-select">
                    <option value="cash"><?= e(__('method_cash')) ?></option>
                    <option value="bank"><?= e(__('method_bank')) ?></option>
                    <option value="check"><?= e(__('method_check')) ?></option>
                </select>
            </div>
            <div class="col-md-4 acct-cash">
                <label class="form-label"><?= e(__('cash_boxes')) ?></label>
                <select name="cash_box_id" class="form-select">
                    <option value=""><?= e(__('none')) ?></option>
                    <?php foreach ($cashBoxes as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name'] . ' (' . $c['currency'] . ')') ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 acct-bank d-none">
                <label class="form-label"><?= e(__('bank_accounts')) ?></label>
                <select name="bank_account_id" class="form-select">
                    <option value=""><?= e(__('none')) ?></option>
                    <?php foreach ($banks as $b): ?><option value="<?= e($b['id']) ?>"><?= e($b['bank_name'] . ' (' . $b['currency'] . ')') ?></option><?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label"><?= e(__('amount')) ?> <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= e(__('currency')) ?></label>
                <input name="currency" class="form-control" value="USD">
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= e(__('date')) ?></label>
                <input type="date" name="pay_date" class="form-control" value="<?= e(date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= e(__('receipt_number')) ?> (ID)</label>
                <input type="number" name="receipt_id" class="form-control" placeholder="<?= e(__('optional')) ?>">
            </div>
            <div class="col-12">
                <label class="form-label"><?= e(__('field_notes')) ?></label>
                <input name="note" class="form-control">
            </div>
        </div>
    </div>
    <div class="card-footer"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= e(__('save')) ?></button></div>
</form>

<script>
window.EFA_PARTIES = <?= json_encode($parties, JSON_UNESCAPED_UNICODE) ?>;
window.EFA_SELECT_LABEL = <?= json_encode(__('select')) ?>;
(function () {
    var pt = document.getElementById('partyType'), pid = document.getElementById('partyId');
    function fill() {
        var list = (window.EFA_PARTIES[pt.value] || []);
        pid.innerHTML = '<option value="">' + window.EFA_SELECT_LABEL + '</option>';
        list.forEach(function (p) { var o = document.createElement('option'); o.value = p.id; o.textContent = p.label; pid.appendChild(o); });
    }
    pt.addEventListener('change', fill); fill();
    var m = document.getElementById('method');
    function toggle() {
        document.querySelector('.acct-cash').classList.toggle('d-none', m.value !== 'cash');
        document.querySelector('.acct-bank').classList.toggle('d-none', m.value !== 'bank');
    }
    m.addEventListener('change', toggle); toggle();
})();
</script>
