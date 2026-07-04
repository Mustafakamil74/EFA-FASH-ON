<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Pdf;
use App\Core\Database;
use App\Core\ReceiptService;
use App\Models\Receipt;
use App\Models\Branch;
use App\Models\stock;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\Factory;
use App\Models\Setting;

class ReceiptController extends Controller
{
    public function index(): void
    {
        $this->authorize('receipts.view');
        $q = trim((string) $this->input('q', ''));
        $this->view('receipts.index', [
            'title'     => __('nav_receipts'),
            'rows'      => Receipt::listing($q),
            'q'         => $q,
            'canManage' => can('receipts.manage'),
        ]);
    }

    public function create(): void
    {
        $this->authorize('receipts.manage');
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->authorize('receipts.manage');
        $row = Receipt::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $this->form($row);
    }

    private function form(?array $row): void
    {
        $this->view('receipts.form', [
            'title'      => $row ? __('edit') . ' · ' . $row['number'] : __('nav_receipts'),
            'row'        => $row,
            'items'      => $row ? Receipt::items((int) $row['id']) : [],
            'branches'   => Branch::active(),
            'currencies' => Database::all('SELECT code FROM currencies ORDER BY is_base DESC, code'),
            'parties'    => $this->parties(),
            'variants'   => Database::all(
                "SELECT pv.id,
                        CONCAT(p.code,' - ',p.name,
                               IF(co.name IS NULL,'',CONCAT(' / ',co.name)),
                               IF(sz.name IS NULL,'',CONCAT(' / ',sz.name))) AS label,
                        p.sale_price, co.name AS color, sz.name AS size, p.id AS product_id
                 FROM product_variants pv
                 JOIN products p ON p.id = pv.product_id
                 LEFT JOIN colors co ON co.id = pv.color_id
                 LEFT JOIN sizes sz ON sz.id = pv.size_id
                 WHERE p.deleted_at IS NULL ORDER BY p.name"
            ),
            'nextNumber' => Receipt::nextNumber(Setting::get('receipt_prefix', 'FIS-EFA-')),
        ]);
    }

    public function store(): void
    {
        $this->authorize('receipts.manage');
        Csrf::check();
        try {
            $id = ReceiptService::create($this->header(), $this->items());
            Audit::log('create', 'receipts', $id);
            flash('success', __('saved_ok'));
            redirect('factory-receipts/create');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('factory-receipts/create');
        }
    }

    public function update(string $id): void
    {
        $this->authorize('receipts.manage');
        Csrf::check();
        $row = Receipt::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        try {
            ReceiptService::update((int) $id, $this->header(), $this->items());
            Audit::log('update', 'receipts', $id);
            flash('success', __('saved_ok'));
            redirect('receipts/' . $id);
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('receipts/' . $id . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $this->authorize('receipts.manage');
        Csrf::check();
        ReceiptService::delete((int) $id);
        Audit::log('delete', 'receipts', $id);
        flash('success', __('deleted_ok'));
        redirect('receipts');
    }

    public function duplicate(string $id): void
    {
        $this->authorize('receipts.manage');
        Csrf::check();
        try {
            $newId = ReceiptService::duplicate((int) $id);
            Audit::log('duplicate', 'receipts', $newId, 'from ' . $id);
            flash('success', __('saved_ok'));
            redirect('receipts/' . $newId . '/edit');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('receipts');
        }
    }

    public function show(string $id): void
    {
        $this->authorize('receipts.view');
        $row = Receipt::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $this->view('receipts.show', [
            'title'     => $row['number'],
            'row'       => $row,
            'items'     => Receipt::items((int) $id),
            'partyName' => Receipt::partyName($row['party_type'], (int) $row['party_id']),
            'paid'      => Receipt::paid((int) $id),
            'company'   => Setting::all(),
            'canManage' => can('receipts.manage'),
        ]);
    }

    public function pdf(string $id): void
    {
        $this->authorize('receipts.view');
        $row = Receipt::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        Pdf::stream('receipts.pdf', [
            'row'       => $row,
            'items'     => Receipt::items((int) $id),
            'partyName' => Receipt::partyName($row['party_type'], (int) $row['party_id']),
            'paid'      => Receipt::paid((int) $id),
            'company'   => Setting::all(),
        ], $row['number'] . '.pdf', false);
    }

    /** Collect receipt header from POST. */
    private function header(): array
    {
        return [
            'type'          => in_array($this->input('type'), ['sale', 'purchase', 'return'], true) ? $this->input('type') : 'sale',
            'party_type'    => in_array($this->input('party_type'), ['customer', 'shop', 'factory'], true) ? $this->input('party_type') : 'customer',
            'party_id'      => (int) $this->input('party_id', 0),
            'branch_id'     => (int) $this->input('branch_id', 0) ?: null,
            'currency'      => (string) $this->input('currency', 'USD'),
            'receipt_date'  => (string) $this->input('receipt_date', date('Y-m-d')),
            'receipt_time'  => (string) $this->input('receipt_time', date('H:i:s')),
            'discount'      => (float) $this->input('discount', 0),
            'shipping_cost' => (float) $this->input('shipping_cost', 0),
            'payment_type'  => (string) $this->input('payment_type', 'credit'),
            'paid'          => (float) $this->input('paid', 0),
            'notes'         => trim((string) $this->input('notes', '')),
        ];
    }

    /** Collect line items (parallel arrays from the dynamic table). */
    private function items(): array
    {
    // إذا كان نموذج استلام المصنع
         if ($this->input('product_id')) {

             $product = (array)$this->input('product_id', []);
             $color   = (array)$this->input('color', []);
             $qty     = (array)$this->input('qty', []);
             $notes   = (array)$this->input('notes2', []);

             $items = [];

             foreach ($product as $i => $pid) {

                 if (empty($pid)) {
                     continue;
                }
                $variantId = Product::findOrCreateVariant(
                    (int)$pid,
                    trim($color[$i] ?? '')
                );

                $price = (float) Database::scalar(
                    "SELECT purchase_price FROM products WHERE id = ?",
                    [$pid]
                );

                $items[] = [
                    'product_id'    => (int)$pid,
                    'variant_id'    => $variantId,
                    'description'   => $notes[$i] ?? '',
                    'serial_number' => '',
                    'color'         => $color[$i] ?? '',
                    'size'          => '',
                    'quantity'      => (float)($qty[$i] ?? 0),
                    'unit_price'    => $price,
                ];
            }

            return $items;
        }

    // الفواتير العادية
    $variant = (array)$this->input('item_variant', []);
    $desc    = (array)$this->input('item_desc', []);
    $serial  = (array)$this->input('item_serial', []);
    $color   = (array)$this->input('item_color', []);
    $size    = (array)$this->input('item_size', []);
    $qty     = (array)$this->input('item_qty', []);
    $price   = (array)$this->input('item_price', []);

    $items = [];

    foreach ($desc as $i => $_) {
        $items[] = [
            'variant_id'    => $variant[$i] ?? null,
            'description'   => $desc[$i] ?? '',
            'serial_number' => $serial[$i] ?? '',
            'color'         => $color[$i] ?? '',
            'size'          => $size[$i] ?? '',
            'quantity'      => $qty[$i] ?? 0,
            'unit_price'    => $price[$i] ?? 0,
        ];
    }

    return $items;
    }

    /** Combined party list (type-prefixed) for the selector. */
    private function parties(): array
    {
        $out = ['customer' => [], 'shop' => [], 'factory' => []];
        foreach (Customer::all('name ASC') as $c) { $out['customer'][] = $c; }
        foreach (Shop::all('name ASC') as $s)     { $out['shop'][] = $s; }
        foreach (Factory::all('name ASC') as $f)  { $out['factory'][] = $f; }
        return $out;
    }

   protected function notFound(): void
    {
        http_response_code(404);
        echo \App\Core\View::render('errors.404', [], 'app');
        exit;
        
    }
        public function factoryIndex(): void
{
    $this->authorize('receipts.view');

    $this->form(null);
}

public function factoryCreate(): void
{
    $this->authorize('receipts.manage');

    $this->view('receipts.factory_form', [
        'row' => null,
        'items' => [],
        'branches' => Branch::all(),
        'currencies' => [
        ['code' => 'USD'],
        ['code' => 'TRY'],
],
        'parties' => $this->parties(),
        'products' => Product::search(''),
        'nextNumber' => Receipt::nextNumber(),
    ]);
    }
    public function factoryStore(): void
    {
    $this->authorize('receipts.manage');
    Csrf::check();

    try {

        $header = $this->header();
        $header['type'] = 'purchase';
        $header['party_type'] = 'factory';

        $productIds = (array)$this->input('product_id', []);
        $colors     = (array)$this->input('color', []);
        $qtys       = (array)$this->input('qty', []);
        $notes2     = (array)$this->input('notes2', []);

        $items = [];

        foreach ($productIds as $i => $productId) {

            $productId = (int)$productId;

            if ($productId <= 0) {
                continue;
            }

            $color = trim($colors[$i] ?? '');

            $variantId = Product::findOrCreateVariant($productId, $color);

            $price = (float)Database::scalar(
                "SELECT purchase_price FROM products WHERE id=?",
                [$productId]
            );

           $items[] = [
               'product_id'    => $productId,
               'variant_id'    => $variantId,
               'description'   => $notes2[$i] ?? '',
               'serial_number' => '',
               'color'         => $color,
               'size'          => '',
               'quantity'      => (float)($qtys[$i] ?? 0),
               'unit_price'    => $price,
            ];
        }

        $id = ReceiptService::create($header, $items);

        flash('success', __('saved_ok'));

        redirect('factory-receipts/create');

    } catch (\Throwable $e) {

        flash('error', $e->getMessage());

        redirect('factory-receipts/create');
    }
}
}
