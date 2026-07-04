<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        $this->authorize('products.view');
        $this->view('products.categories', [
            'title'     => __('nav_categories'),
            'rows'      => Category::withCounts(),
            'canManage' => can('products.manage'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            flash('error', __('validation_required', ['field' => __('field_name')]));
            redirect('categories');
        }
        $id = Category::create(['name' => $name]);
        Audit::log('create', 'categories', $id, $name);
        flash('success', __('saved_ok'));
        redirect('categories');
    }

    public function update(string $id): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        $name = trim((string) $this->input('name', ''));
        if ($name !== '') {
            Category::update((int) $id, ['name' => $name]);
            Audit::log('update', 'categories', $id, $name);
            flash('success', __('saved_ok'));
        }
        redirect('categories');
    }

    public function destroy(string $id): void
    {
        $this->authorize('products.manage');
        Csrf::check();
        Category::delete((int) $id);
        Audit::log('delete', 'categories', $id);
        flash('success', __('deleted_ok'));
        redirect('categories');
    }
}
