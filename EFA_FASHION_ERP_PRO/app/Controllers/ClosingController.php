<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Auth;
use App\Models\Closing;
use App\Models\Accounting;

class ClosingController extends Controller
{
    public function index(): void
    {
        $this->authorize('accounting.view');
        $this->view('accounting.closings', [
            'title'     => __('nav_closings'),
            'rows'      => Closing::recent(),
            'canManage' => can('accounting.manage'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $type  = in_array($this->input('period_type'), ['daily', 'monthly', 'yearly'], true) ? $this->input('period_type') : 'daily';
        $label = trim((string) $this->input('period_label', ''));
        [$from, $to] = $this->resolveRange($type, $label);
        if (!$from) {
            flash('error', __('invalid_input'));
            redirect('closings');
        }

        $pl = Accounting::profitAndLoss($from, $to);
        // Upsert the closing snapshot for this period.
        \App\Core\Database::query(
            'INSERT INTO closings (period_type, period_label, total_sales, total_expenses, total_income, profit, closed_by)
             VALUES (?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE total_sales=VALUES(total_sales), total_expenses=VALUES(total_expenses),
                 total_income=VALUES(total_income), profit=VALUES(profit), closed_by=VALUES(closed_by), closed_at=CURRENT_TIMESTAMP',
            [$type, $label, $pl['sales'], $pl['expenses'], $pl['income'], $pl['profit'], Auth::id()]
        );
        Audit::log('closing', 'closings', null, "$type $label");
        flash('success', __('saved_ok'));
        redirect('closings');
    }

    /** Resolve [from, to] dates for a period label like 2026-06-13 / 2026-06 / 2026. */
    private function resolveRange(string $type, string $label): array
    {
        try {
            return match ($type) {
                'daily'   => [$label, $label],
                'monthly' => [$label . '-01', date('Y-m-t', strtotime($label . '-01'))],
                'yearly'  => [$label . '-01-01', $label . '-12-31'],
                default   => [null, null],
            };
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
