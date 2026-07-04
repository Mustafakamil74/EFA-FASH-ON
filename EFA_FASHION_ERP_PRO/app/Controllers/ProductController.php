<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Product;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;

class ProductController extends Controller
{
    public function index(): void
    {
        $this->authorize('products.view');
        $q = trim((string) $this->input('q', ''));
        $this->view('products.index', [
            'title'     => __('nav_products'),
            'rows'      => Product::search($q),
            'q'         => $q,
            'canManage' => can('products.manage'),
        ]);
    }

    public function create(): void
    {
        $this->authorize('products.manage');
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->authorize('products.manage');
        $row = Product::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $this->form($row);
    }

    private function form(?array $row): void
    {
        $selectedVariants = $row ? Product::variants((int) $row['id']) : [];
        $this->view('products.form', [
            'title'      => $row ? __('edit') : __('create'),
            'row'        => $row,
            'categories' => Category::all('name ASC'),
            'colors'     => Color::all('name ASC'),
            'sizes'      => Size::all(),
            'selectedColors' => array_values(array_unique(array_filter(array_column($selectedVariants, 'color_id')))),
            'selectedSizes'  => array_values(array_unique(array_filter(array_column($selectedVariants, 'size_id')))),
        ]);
    }

    public function store(): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        [$data, $errors, $colors, $sizes] = $this->collect();
        if ($errors) {
            $this->back($errors, $data);
        }
        $data['image_path'] = $this->handleImage() ?? null;
        $id = Product::create($data);
        Product::syncVariants((int) $id, $colors, $sizes, $data['code']);
        Audit::log('create', 'products', $id, $data['name']);
        flash('success', __('saved_ok'));
        redirect('products');
    }

    public function update(string $id): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        $row = Product::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        [$data, $errors, $colors, $sizes] = $this->collect((int) $id);
        if ($errors) {
            $this->back($errors, $data);
        }
        $img = $this->handleImage();
        if ($img !== null) {
            $data['image_path'] = $img;
        }
        Product::update((int) $id, $data);
        Product::syncVariants((int) $id, $colors, $sizes, $data['code']);
        Audit::log('update', 'products', $id, $data['name']);
        flash('success', __('saved_ok'));
        redirect('products');
    }

    public function destroy(string $id): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        Product::delete((int) $id);
        Audit::log('delete', 'products', $id);
        flash('success', __('deleted_ok'));
        redirect('products');
    }

    public function show(string $id): void
    {
        $this->authorize('products.view');
        $row = Product::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $this->view('products.show', [
            'title'    => $row['name'],
            'row'      => $row,
            'variants' => Product::variants((int) $id),
            'category' => $row['category_id'] ? Category::find((int) $row['category_id']) : null,
        ]);
    }

    /** Printable barcode + QR label page (rendered client-side). */
    public function label(string $id): void
    {
        $this->authorize('products.view');
        $row = Product::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        echo \App\Core\View::render('products.label', ['row' => $row], null);
    }

    private function collect(?int $exceptId = null): array
    {
        $data = [
            'code'           => trim((string) $this->input('code', '')),
            'name'           => trim((string) $this->input('name', '')),
            'fabric_type'    => trim((string) $this->input('fabric_type', '')),
            'category_id'    => $this->input('category_id') ?: null,
            'brand'          => trim((string) $this->input('brand', '')),
            'barcode'        => trim((string) $this->input('barcode', '')),
            'purchase_price' => (float) $this->input('purchase_price', 0),
            'sale_price'     => (float) $this->input('sale_price', 0),
            'min_stock'      => (float) $this->input('min_stock', 0),
            'notes'          => trim((string) $this->input('notes', '')),
        ];
        $errors = $this->validate($data, [
            'code'           => 'required|max:60',
            'name'           => 'required|max:180',
            'purchase_price' => 'numeric',
            'sale_price'     => 'numeric',
            'min_stock'      => 'numeric',
        ]);
        if (!isset($errors['code']) && Product::codeExists($data['code'], $exceptId)) {
            $errors['code'] = __('code_taken');
        }
        $colors = array_map('intval', (array) $this->input('color_ids', []));

        // المقاسات المكتوبة مثل: S,M,L,XL
        $sizeText = trim((string)$this->input('sizes', ''));

        $sizes = [];

        if ($sizeText !== '') {

               foreach (explode(',', $sizeText) as $sizeName) {

                $sizeName = trim($sizeName);

               if ($sizeName === '') {
                 continue;
           }

                   $id = Database::scalar(
                     "SELECT id FROM sizes WHERE name=?",
                       [$sizeName]
                );

                  if (!$id) {

                         Database::query(
                           "INSERT INTO sizes    (name) VALUES(?)",
                         [$sizeName]
                   );

                $id = Database::lastInsertId();
             }

               $sizes[] = (int)$id;
             }
        }
        return [$data, $errors, $colors, $sizes];
    }

    /** Handle an optional product image upload; returns web path or null. */
    private function handleImage(): ?string
    {
        if (empty($_FILES['image']['tmp_name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            return null;
        }
        $dir = base_path('public/uploads/products');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = 'p_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $name);
        return 'uploads/products/' . $name;
    }

    protected function notFound(): void
    {
        http_response_code(404);
        echo \App\Core\View::render('errors.404', [], 'app');
        exit;
    }
}
