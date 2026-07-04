<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Database;

class RecycleBinController extends Controller
{
    /** Soft-delete tables exposed in the recycle bin: table => display column. */
    private const TABLES = [
        'customers' => 'name',
        'factories' => 'name',
        'shops'     => 'name',
        'products'  => 'name',
        'categories' => 'name',
        'receipts'  => 'number',
        'users'     => 'username',
    ];

    public function index(): void
    {
        $this->authorize('settings.manage');
        $groups = [];
        foreach (self::TABLES as $table => $label) {
            $rows = Database::all(
                "SELECT id, {$label} AS label, deleted_at
                 FROM {$table} WHERE deleted_at IS NOT NULL
                 ORDER BY deleted_at DESC LIMIT 100"
            );
            if ($rows) {
                $groups[$table] = $rows;
            }
        }
        $this->view('settings.recyclebin', [
            'title'  => __('nav_recyclebin'),
            'groups' => $groups,
        ]);
    }

    public function restore(string $table, string $id): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        if (!isset(self::TABLES[$table])) {
            $this->notFound();
        }
        Database::query("UPDATE {$table} SET deleted_at = NULL WHERE id = ?", [(int) $id]);
        Audit::log('restore', $table, $id);
        flash('success', __('restored_ok'));
        redirect('recyclebin');
    }

    public function purge(string $table, string $id): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        if (!isset(self::TABLES[$table])) {
            $this->notFound();
        }
        Database::query("DELETE FROM {$table} WHERE id = ? AND deleted_at IS NOT NULL", [(int) $id]);
        Audit::log('purge', $table, $id);
        flash('success', __('deleted_ok'));
        redirect('recyclebin');
    }
}
