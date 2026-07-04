<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Pdf;
use App\Models\PartyLedger;

/**
 * Shared CRUD + statement/PDF logic for the three "party" modules
 * (customers, factories, shops). Subclasses declare the differences.
 */
abstract class ContactController extends Controller
{
    /** @return class-string<\App\Core\Model> */
    abstract protected function model(): string;
    abstract protected function module(): string;     // e.g. 'customers'
    abstract protected function partyType(): string;  // e.g. 'customer'
    abstract protected function hasContactName(): bool;

    protected function viewPermission(): string { return $this->module() . '.view'; }
    protected function managePermission(): string { return $this->module() . '.manage'; }

    public function index(): void
    {
        $this->authorize($this->viewPermission());
        $model = $this->model();
        $q = trim((string) $this->input('q', ''));
        $rows = $model::search($q);
        $this->view('contacts.index', [
            'title'    => __('nav_' . $this->module()),
            'module'   => $this->module(),
            'rows'     => $rows,
            'q'        => $q,
            'hasContact' => $this->hasContactName(),
            'canManage'  => can($this->managePermission()),
        ]);
    }

    public function create(): void
    {
        $this->authorize($this->managePermission());
        $this->view('contacts.form', [
            'title'  => __('create'),
            'module' => $this->module(),
            'row'    => null,
            'hasContact' => $this->hasContactName(),
        ]);
    }

    public function store(): void
    {
        $this->authorize($this->managePermission());
        Csrf::check();
        [$data, $errors] = $this->collect();
        if ($errors) {
            $this->back($errors, $data);
        }
        $id = ($this->model())::create($data);
        Audit::log('create', $this->module(), $id, $data['name'] ?? '');
        flash('success', __('saved_ok'));
        redirect($this->module());
    }

    public function edit(string $id): void
    {
        $this->authorize($this->managePermission());
        $row = ($this->model())::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $this->view('contacts.form', [
            'title'  => __('edit'),
            'module' => $this->module(),
            'row'    => $row,
            'hasContact' => $this->hasContactName(),
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize($this->managePermission());
        Csrf::check();
        $row = ($this->model())::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        [$data, $errors] = $this->collect((int) $id);
        if ($errors) {
            $this->back($errors, $data);
        }
        ($this->model())::update((int) $id, $data);
        Audit::log('update', $this->module(), $id, $data['name'] ?? '');
        flash('success', __('saved_ok'));
        redirect($this->module());
    }

    public function destroy(string $id): void
    {
        $this->authorize($this->managePermission());
        Csrf::check();
        ($this->model())::delete((int) $id);
        Audit::log('delete', $this->module(), $id);
        flash('success', __('deleted_ok'));
        redirect($this->module());
    }

    public function show(string $id): void
    {
        $this->authorize($this->viewPermission());
        $row = ($this->model())::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $ledger = PartyLedger::statement($this->partyType(), (int) $id);
        $this->view('contacts.show', [
            'title'   => $row['name'],
            'module'  => $this->module(),
            'row'     => $row,
            'ledger'  => $ledger,
            'hasContact' => $this->hasContactName(),
        ]);
    }

    public function pdf(string $id): void
    {
        $this->authorize($this->viewPermission());
        $row = ($this->model())::find((int) $id);
        if (!$row) {
            $this->notFound();
        }
        $ledger = PartyLedger::statement($this->partyType(), (int) $id);
        Pdf::stream('contacts.statement_pdf', [
            'row'    => $row,
            'module' => $this->module(),
            'ledger' => $ledger,
            'company'=> \App\Models\Setting::all(),
        ], 'statement-' . $row['code'] . '.pdf', false);
    }

    /** Collect + validate form data. Returns [data, errors]. */
    private function collect(?int $exceptId = null): array
    {
        $model = $this->model();
        $data = [
            'code'    => trim((string) $this->input('code', '')),
            'name'    => trim((string) $this->input('name', '')),
            'phone'   => trim((string) $this->input('phone', '')),
            'address' => trim((string) $this->input('address', '')),
            'notes'   => trim((string) $this->input('notes', '')),
        ];
        if ($this->hasContactName()) {
            $data['contact_name'] = trim((string) $this->input('contact_name', ''));
        }
        $errors = $this->validate($data, [
            'code' => 'required|max:40',
            'name' => 'required|max:150',
        ]);
        if (!isset($errors['code']) && $model::codeExists($data['code'], $exceptId)) {
            $errors['code'] = __('code_taken');
        }
        return [$data, $errors];
    }

    protected function notFound(): void
    {
        http_response_code(404);
        echo \App\Core\View::render('errors.404', [], 'app');
        exit;
    }
}
