<?php
/** @var array $parties */
?>
<form method="post" action="<?= e(url('checks')) ?>" class="card">
    <?= csrf_field() ?>
    <div class="card-header d-flex align-items-center">
        <strong><?= e(__('nav_checks')) ?></strong>
        <a href="<?= e(url('checks')) ?>" class="btn btn-sm btn-outline-secondary ms-auto"><?= e(__('cancel')) ?></a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><?= e(__('direction')) ?></label>
                <select name="direction" class="form-select">
                    <option value="in"><?= e(__('check_in')) ?></option>
                    <option value="out"><?= e(__('check_out')) ?></option>
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
                <label class="form-label"><?= e(__('party')) ?></label>
                <select name="party_id" id="partyId" class="form-select"></select>
            </div>

            <div class="col-md-4">
                <label class="form-label"><?= e(__('check_number')) ?></label>
                <input name="check_number" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('bank')) ?></label>
                <input name="bank_name" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('currency')) ?></label>
                <input name="currency" class="form-control" value="USD">
            </div>

            <div class="col-md-4">
                <label class="form-label"><?= e(__('amount')) ?> <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('issue_date')) ?></label>
                <input type="date" name="issue_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(__('due_date')) ?> <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
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
})();
</script>
