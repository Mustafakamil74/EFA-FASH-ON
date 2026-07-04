<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Models\Check;

class CheckController extends Controller
{
    public function index(): void
    {
        $this->authorize('accounting.view');
        $status = in_array($this->input('status'), ['pending', 'cleared', 'bounced', 'cancelled'], true) ? $this->input('status') : '';
        $this->view('accounting.checks', [
            'title'     => __('nav_checks'),
            'rows'      => Check::listing($status),
            'status'    => $status,
            'canManage' => can('accounting.manage'),
        ]);
    }

    public function create(): void
    {
        $this->authorize('accounting.manage');
        $this->view('accounting.check_form', [
            'title'   => __('nav_checks'),
            'parties' => $this->partyOptions(),
        ]);
    }

    public function store(): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $data = [
            'direction'    => in_array($this->input('direction'), ['in', 'out'], true) ? $this->input('direction') : 'in',
            'party_type'   => in_array($this->input('party_type'), ['customer', 'shop', 'factory'], true) ? $this->input('party_type') : 'customer',
            'party_id'     => (int) $this->input('party_id', 0) ?: null,
            'check_number' => trim((string) $this->input('check_number', '')),
            'bank_name'    => trim((string) $this->input('bank_name', '')),
            'currency'     => (string) $this->input('currency', 'USD'),
            'amount'       => (float) $this->input('amount', 0),
            'issue_date'   => (string) $this->input('issue_date', '') ?: null,
            'due_date'     => (string) $this->input('due_date', date('Y-m-d')),
            'status'       => 'pending',
            'note'         => trim((string) $this->input('note', '')),
        ];
        if ($data['amount'] <= 0) {
            flash('error', __('invalid_input'));
            redirect('checks/create');
        }
        $id = Check::create($data);
        Audit::log('create', 'checks', $id, $data['check_number']);
        flash('success', __('saved_ok'));
        redirect('checks');
    }

    public function updateStatus(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        $status = $this->input('status');
        if (in_array($status, ['pending', 'cleared', 'bounced', 'cancelled'], true)) {
            Check::update((int) $id, ['status' => $status]);
            Audit::log('update', 'checks', $id, 'status=' . $status);
            flash('success', __('saved_ok'));
        }
        redirect('checks');
    }

    public function destroy(string $id): void
    {
        $this->authorize('accounting.manage');
        Csrf::check();
        Check::delete((int) $id);
        Audit::log('delete', 'checks', $id);
        flash('success', __('deleted_ok'));
        redirect('checks');
    }
}
