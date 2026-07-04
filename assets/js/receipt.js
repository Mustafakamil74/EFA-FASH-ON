/* Receipt form: dependent party selector, dynamic item rows, live totals. */
(function () {
    'use strict';
    var cfg = window.EFA_RECEIPT || {};
    var partyType = document.getElementById('partyType');
    var partyId = document.getElementById('partyId');
    var body = document.getElementById('itemsBody');
    var tpl = document.getElementById('rowTpl');
    var addBtn = document.getElementById('addRow');
    if (!tpl || !body) {
    return;
}

    function fmt(n) { return (Math.round(n * 100) / 100).toFixed(2); }

    /* Populate the party dropdown based on the selected party type. */
    function fillParties(keepSelected) {

    if (!partyType || !partyId) {
        return;
    }

    var type = partyType.value;
    var list = (cfg.parties && cfg.parties[type]) || [];

    partyId.innerHTML = '';

    var ph = document.createElement('option');
    ph.value = '';
    ph.textContent = cfg.labels.party;

    partyId.appendChild(ph);

    list.forEach(function (p) {
        var o = document.createElement('option');
        o.value = p.id;
        o.textContent = (p.code ? p.code + ' - ' : '') + p.name;

        if (keepSelected && String(p.id) === String(cfg.selectedParty)) {
            o.selected = true;
        }

        partyId.appendChild(o);
    });
}

    function recalc() {
        var subtotal = 0;
        body.querySelectorAll('.item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.v-qty').value) || 0;
            var price = parseFloat(row.querySelector('.v-price').value) || 0;
            var line = qty * price;
            row.querySelector('.v-line').textContent = fmt(line);
            subtotal += line;
        });
        var discount = parseFloat(document.getElementById('discount').value) || 0;
        var shipping = parseFloat(document.getElementById('shipping').value) || 0;
        document.getElementById('subtotalLabel').textContent = fmt(subtotal);
        document.getElementById('grandLabel').textContent = fmt(Math.max(0, subtotal - discount + shipping));
    }

    function wireRow(row) {
        var vsel = row.querySelector('.v-sel');
        vsel.addEventListener('change', function () {
            var opt = vsel.options[vsel.selectedIndex];
            if (!opt.value) return;
            row.querySelector('.v-desc').value = opt.dataset.desc || '';
            row.querySelector('.v-color').value = opt.dataset.color || '';
            row.querySelector('.v-size').value = opt.dataset.size || '';
            if (!parseFloat(row.querySelector('.v-price').value)) {
                row.querySelector('.v-price').value = opt.dataset.price || 0;
            }
            recalc();
        });
        row.querySelector('.v-qty').addEventListener('input', recalc);
        row.querySelector('.v-price').addEventListener('input', recalc);
        row.querySelector('.rm').addEventListener('click', function () {
            row.remove();
            recalc();
        });
    }

    function addRow(data) {
        var node = tpl.content.cloneNode(true);
        var row = node.querySelector('.item-row');
        body.appendChild(node);
        var inserted = body.lastElementChild;
        wireRow(inserted);
        if (data) {
            if (data.variant_id) inserted.querySelector('.v-sel').value = data.variant_id;
            inserted.querySelector('.v-desc').value = data.description || '';
            inserted.querySelector('input[name="item_serial[]"]').value = data.serial_number || '';
            inserted.querySelector('.v-color').value = data.color || '';
            inserted.querySelector('.v-size').value = data.size || '';
            inserted.querySelector('.v-qty').value = data.quantity || 1;
            inserted.querySelector('.v-price').value = data.unit_price || 0;
        }
        return inserted;
    }

    if (addBtn) {
    addBtn.addEventListener('click', function () {
        addRow();
        recalc();
    });
}

if (document.getElementById('discount')) {
    document.getElementById('discount').addEventListener('input', recalc);
}

if (document.getElementById('shipping')) {
    document.getElementById('shipping').addEventListener('input', recalc);
}

if (partyType) {
    partyType.addEventListener('change', function () {
        fillParties(false);
    });
}

    // Initial render.
    fillParties(true);

// لا تضف صفوف تلقائياً في نموذج المعمل
if (document.getElementById('rowTpl')) {
    if (cfg.existingItems && cfg.existingItems.length) {
        cfg.existingItems.forEach(addRow);
    } else {
        addRow();
    }

    recalc();
}
})();
