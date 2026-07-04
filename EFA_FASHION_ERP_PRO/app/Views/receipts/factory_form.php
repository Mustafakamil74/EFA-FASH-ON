<?php
$isEdit = $row !== null;
$action = url('factory-receipts');
?>

<form method="post" action="<?= e($action) ?>" id="factoryForm">

<?= csrf_field() ?>

<div class="card mb-3">

    <div class="card-header">
        <h5><?= e(__('nav_factory_receipts')) ?></h5>
    </div>

    <div class="card-body">

        <input type="hidden" name="type" value="purchase">

        <input type="hidden" name="party_type" value="factory">

        <div class="row">

            <div class="col-md-3">

                <label><?= e(__('receipt_number')) ?></label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= e($nextNumber) ?>"
                    readonly>

            </div>

            <div class="col-md-3">

                <label><?= e(__('date')) ?></label>

                <input
                    type="date"
                    name="receipt_date"
                    class="form-control"
                    value="<?= date('Y-m-d') ?>">

            </div>

            <div class="col-md-3">

                 <label><?= e(__('branch')) ?></label>

                 <select
                     name="branch_id"
                     class="form-control"
                     required>

                     <option value=""><?= e(__('choose_branch')) ?></option>

                     <?php foreach($branches as $b): ?>

                         <option value="<?= $b['id'] ?>">
                             <?= e($b['name']) ?>
                         </option>

                     <?php endforeach; ?>

                 </select>

             </div>

            <div class="col-md-3">

                <label><?= e(__('factory')) ?></label>

                <select
                    name="party_id"
                    class="form-control">

                    <option value=""><?= e(__('choose_factory')) ?></option>

                    <?php foreach($parties['factory'] as $f): ?>

                        <option value="<?= $f['id'] ?>">
                            <?= e($f['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

    </div>

</div>
<div class="card mb-3">

    <div class="card-header d-flex justify-content-between">

        <strong><?= e(__('products')) ?></strong>

        <button
            type="button"
            id="addRow"
            class="btn btn-success btn-sm">

            + <?= e(__('add_item')) ?>

        </button>

    </div>

    <div class="card-body">

        <table class="table table-bordered" id="itemsTable">

            <thead>

                <tr>

                    <th><?= e(__('product')) ?></th>
                    <th><?= e(__('color')) ?></th>
                    <th><?= e(__('quantity')) ?></th>
                    <th><?= e(__('note')) ?></th>
                    <th width="70"><?= e(__('delete')) ?></th>
                    
                    
                    
                    

                </tr>

            </thead>

            <tbody id="itemsBody">

                <tr>

                    <td>
                        <select name="product_id[]" class="form-select productSelect" required>

                            <option value=""><?= e(__('choose_product')) ?></option>

                            <?php foreach ($products as $p): ?>

                                <option
                                    value="<?= $p['id'] ?>"
                                    data-color="<?= e($p['color_name'] ?? '') ?>">

                                    <?= e($p['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>
                    </td>
                    
                    <td>
                        <input
                            type="text"
                            name="color[]"
                            class="form-control">
                    </td>

                    <td>
                        <input
                            type="number"
                            name="qty[]"
                            value="1"
                            min="1"
                            class="form-control">
                    </td>

                    <td>
                        <input
                            type="text"
                            name="notes2[]"
                            class="form-control">
                    </td>

                    <td class="text-center">

                        <button
                            type="button"
                            class="btn btn-danger removeRow">

                            ×

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div class="card mb-3">

    <div class="card-body">

        <label><?= e(__('general_notes')) ?></label>

        <textarea
            name="notes"
            rows="4"
            class="form-control"></textarea>

        <button
            type="submit"
            class="btn btn-primary mt-3">

            <?= e(__('save')) ?>

        </button>

    </div>

</div>

</form>
<script>

document.getElementById('addRow').addEventListener('click', function () {

    let tbody = document.getElementById('itemsBody');

    let tr = document.createElement('tr');

    tr.innerHTML = `
        <td>
           <select name="product_id[]" class="form-select productSelect" required>
               <option value=""><?= e(__('choose_product')) ?></option>

               <?php foreach ($products as $p): ?>
                  <option value="<?= $p['id'] ?>">
                     <?= e($p['name']) ?>
              </option>
          <?php endforeach; ?>

    </select>
        </td>

        <td>
            <input
            type="text"
            name="color[]"
            class="form-control"
            placeholder="<?= e(__('color')) ?>">
        </td>

        <td>
            <input type="number" name="qty[]" value="1" min="1" class="form-control">
        </td>

        <td>
            <input type="text" name="notes2[]" class="form-control">
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-danger removeRow">
                ×
            </button>
        </td>
    `;

    tbody.appendChild(tr);

});

document.addEventListener('click', function(e){

    if(e.target.classList.contains('removeRow')){

        let rows = document.querySelectorAll('#itemsBody tr');

        if(rows.length > 1){

            e.target.closest('tr').remove();

        }

    }

});

</script>