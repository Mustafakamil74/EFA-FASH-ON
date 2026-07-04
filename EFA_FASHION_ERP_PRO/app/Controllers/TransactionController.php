<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(): void
    {
        $this->authorize('accounting.view');
        $kind = in_array($this->input('kind'), ['expense', 'income'], true) ? $this->input('kind') : '';
        $this->view('accounting.transactions', [
            'title'     => __('nav_expenses_income'),
            'rows'      => Transaction::listing($kind),
            'kind'      => $kind,
            'canManage' => can('accounting.manage'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $data = [
            'kind'     => in_array($this->input('kind'), ['expense', 'income'], true) ? $this->input('kind') : 'expense',
            'category' => trim((string) $this->input('category', '')),
            'currency' => (string) $this->input('currency', 'USD'),
            'amount'   => (float) $this->input('amount', 0),
            'txn_date' => (string) $this->input('txn_date', date('Y-m-d')),
            'note'     => trim((string) $this->input('note', '')),
            'user_id'  => \App\Core\Auth::id(),
        ];
        if ($data['amount'] <= 0) {
            flash('error', __('invalid_input'));
            redirect('transactions');
        }
        $id = Transaction::create($data);
        Audit::log('create', 'transactions', $id, $data['kind'] . ' ' . $data['amount']);
        flash('success', __('saved_ok'));
        redirect('transactions');
    }

    public function destroy(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        Transaction::delete((int) $id);
        Audit::log('delete', 'transactions', $id);
        flash('success', __('deleted_ok'));
        redirect('transactions');
    }
}
