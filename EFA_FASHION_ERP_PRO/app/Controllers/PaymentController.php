<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\AccountingService;
use App\Models\Payment;
use App\Models\CashBox;
use App\Models\BankAccount;

class PaymentController extends Controller
{
    public function index(): void
    {
        $this->authorize('accounting.view');
        $dir = in_array($this->input('dir'), ['in', 'out'], true) ? $this->input('dir') : '';
        $q = trim((string) $this->input('q', ''));
        $this->view('accounting.payments', [
            'title'     => __('nav_payments'),
            'rows'      => Payment::listing($dir, $q),
            'dir'       => $dir,
            'q'         => $q,
            'canManage' => can('accounting.manage'),
        ]);
    }

    public function create(): void
    {
        $this->authorize('accounting.manage');
        $this->view('accounting.payment_form', [
            'title'     => __('record_payment'),
            'parties'   => $this->partyOptions(),
            'cashBoxes' => CashBox::all('currency ASC'),
            'banks'     => BankAccount::all('bank_name ASC'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $data = [
            'direction'       => in_array($this->input('direction'), ['in', 'out'], true) ? $this->input('direction') : 'in',
            'party_type'      => in_array($this->input('party_type'), ['customer', 'shop', 'factory'], true) ? $this->input('party_type') : 'customer',
            'party_id'        => (int) $this->input('party_id', 0),
            'receipt_id'      => (int) $this->input('receipt_id', 0) ?: null,
            'method'          => in_array($this->input('method'), ['cash', 'bank', 'check'], true) ? $this->input('method') : 'cash',
            'cash_box_id'     => (int) $this->input('cash_box_id', 0) ?: null,
            'bank_account_id' => (int) $this->input('bank_account_id', 0) ?: null,
            'currency'        => (string) $this->input('currency', 'USD'),
            'amount'          => (float) $this->input('amount', 0),
            'pay_date'        => (string) $this->input('pay_date', date('Y-m-d')),
            'note'            => trim((string) $this->input('note', '')),
        ];
        if ($data['party_id'] <= 0 || $data['amount'] <= 0) {
            flash('error', __('invalid_input'));
            redirect('payments/create');
        }
        try {
            $id = AccountingService::recordPayment($data);
            Audit::log('create', 'payments', $id, $data['direction'] . ' ' . $data['amount']);
            flash('success', __('saved_ok'));
            redirect('payments');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('payments/create');
        }
    }

    public function destroy(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        AccountingService::removePayment((int) $id);
        Audit::log('delete', 'payments', $id);
        flash('success', __('deleted_ok'));
        redirect('payments');
    }
}
