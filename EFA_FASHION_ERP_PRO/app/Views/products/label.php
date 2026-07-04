<?php
/** @var array $row — printable barcode + QR label, rendered client-side */
$barcodeValue = $row['barcode'] ?: $row['code'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Label — <?= e($row['code']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 16px; }
        .label { width: 320px; border: 1px dashed #999; padding: 12px; text-align: center; margin: 0 auto; }
        .name { font-weight: bold; font-size: 14px; margin-bottom: 2px; }
        .price { font-size: 16px; margin: 6px 0; }
        .row { display: flex; align-items: center; justify-content: space-around; gap: 8px; }
        .toolbar { text-align: center; margin-bottom: 12px; }
        @media print { .toolbar { display: none; } .label { border: none; } }
        button { padding: 6px 14px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print</button>
    </div>
    <div class="label">
        <div class="name"><?= e($row['name']) ?></div>
        <div><?= e($row['code']) ?></div>
        <div class="price"><?= e(money($row['sale_price'])) ?></div>
        <div class="row">
            <svg id="barcode"></svg>
            <canvas id="qrcode"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        var value = <?= json_encode($barcodeValue, JSON_UNESCAPED_UNICODE) ?>;
        try {
            JsBarcode("#barcode", value, { height: 50, fontSize: 12, margin: 4 });
        } catch (e) { document.getElementById('barcode').outerHTML = '<div>' + value + '</div>'; }
        QRCode.toCanvas(document.getElementById('qrcode'), value, { width: 90 }, function () {});
    </script>
</body>
</html>
