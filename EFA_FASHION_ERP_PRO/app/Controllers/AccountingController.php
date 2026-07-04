<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Models\CashBox;
use App\Models\BankAccount;
use App\Models\Accounting;

class AccountingController extends Controller
{
    public function index(): void
    {
        $this->authorize('accounting.view');
        $from = date('Y-m-01');
        $to   = date('Y-m-d');
        $this->view('accounting.index', [
            'title'      => __('nav_accounting'),
            'cashBoxes'  => CashBox::all('currency ASC'),
            'banks'      => BankAccount::all('bank_name ASC'),
            'pl'         => Accounting::profitAndLoss($from, $to),
            'debts'      => Accounting::totalDebts(),
            'invValue'   => Accounting::inventoryValue(),
            'capital'    => Accounting::capital(),
            'cashTotal'  => Accounting::cashTotal(),
            'bankTotal'  => Accounting::bankTotal(),
            'periodFrom' => $from,
            'periodTo'   => $to,
            'canManage'  => can('accounting.manage'),
        ]);
    }

    // -- Cash boxes ------------------------------------------------------
    public function storeCashBox(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $data = [
            'name'     => trim((string) $this->input('name', '')),
            'currency' => strtoupper(trim((string) $this->input('currency', 'USD'))),
            'balance'  => (float) $this->input('balance', 0),
        ];
        if ($data['name'] === '') {
            flash('error', __('invalid_input'));
            redirect('accounting');
        }
        $id = CashBox::create($data);
        Audit::log('create', 'cash_boxes', $id, $data['name']);
        flash('success', __('saved_ok'));
        redirect('accounting');
    }

    public function deleteCashBox(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        CashBox::delete((int) $id);
        Audit::log('delete', 'cash_boxes', $id);
        flash('success', __('deleted_ok'));
        redirect('accounting');
    }

    // -- Bank accounts ---------------------------------------------------
    public function storeBank(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $data = [
            'bank_name'  => trim((string) $this->input('bank_name', '')),
            'account_no' => trim((string) $this->input('account_no', '')),
            'iban'       => trim((string) $this->input('iban', '')),
            'currency'   => strtoupper(trim((string) $this->input('currency', 'USD'))),
            'balance'    => (float) $this->input('balance', 0),
        ];
        if ($data['bank_name'] === '') {
            flash('error', __('invalid_input'));
            redirect('accounting');
        }
        $id = BankAccount::create($data);
        Audit::log('create', 'bank_accounts', $id, $data['bank_name']);
        flash('success', __('saved_ok'));
        redirect('accounting');
    }

    public function deleteBank(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        BankAccount::delete((int) $id);
        Audit::log('delete', 'bank_accounts', $id);
        flash('success', __('deleted_ok'));
        redirect('accounting');
    }
}
