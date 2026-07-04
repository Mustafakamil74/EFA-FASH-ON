<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\StockService;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockMovement;

class InventoryController extends Controller
{
    public function index(): void
    {
        $this->authorize('inventory.view');
        $branchId = (int) $this->input('branch', 0) ?: null;
        $q = trim((string) $this->input('q', ''));
        $this->view('inventory.index', [
            'title'     => __('nav_inventory'),
            'branches'  => Branch::active(),
            'levels'    => Stock::levels($branchId, $q),
            'branchId'  => $branchId,
            'q'         => $q,
            'canManage' => can('inventory.manage'),
        ]);
    }

    public function movements(): void
    {
        $this->authorize('inventory.view');
        $branchId = (int) $this->input('branch', 0) ?: null;
        $this->view('inventory.movements', [
            'title'    => __('movement_history'),
            'branches' => Branch::active(),
            'rows'     => StockMovement::history($branchId),
            'branchId' => $branchId,
        ]);
    }

    public function form(): void
    {
        $this->authorize('inventory.manage');
        $action = $this->input('action', 'in'); // in | out | transfer
        $this->view('inventory.form', [
            'title'    => __('stock_' . $action),
            'action'   => $action,
            'branches' => Branch::active(),
            'variants' => Stock::variantOptions(),
        ]);
    }

    public function store(): void
    {
        $this->authorize('inventory.manage');
        Csrf::check();
        $action    = (string) $this->input('action', 'in');
        $variantId = (int) $this->input('variant_id', 0);
        $qty       = (float) $this->input('quantity', 0);
        $note      = trim((string) $this->input('note', ''));

        if ($variantId <= 0 || $qty <= 0) {
            flash('error', __('invalid_input'));
            $this->redirectForm($action);
        }

        try {
            if ($action === 'transfer') {
                $from = (int) $this->input('from_branch', 0);
                $to   = (int) $this->input('to_branch', 0);
                StockService::transfer($from, $to, $variantId, $qty, $note);
                Audit::log('stock_transfer', 'inventory', $variantId, "from=$from to=$to qty=$qty");
            } else {
                $branchId = (int) $this->input('branch_id', 0);
                StockService::apply($branchId, $variantId, $action === 'out' ? 'out' : 'in', $qty, 'manual', null, $note);
                Audit::log('stock_' . $action, 'inventory', $variantId, "branch=$branchId qty=$qty");
            }
            flash('success', __('saved_ok'));
            redirect('inventory');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->redirectForm($action);
        }
    }

    private function redirectForm(string $action): void
    {
        redirect('inventory/move?action=' . urlencode($action));
    }
}
