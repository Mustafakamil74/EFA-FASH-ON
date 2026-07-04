<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\User;

class UserController extends Controller
{
    public function index(): void
    {
        $this->authorize('users.manage');
        $this->view('users.index', [
            'title' => __('nav_users'),
            'users' => User::withRoles(),
        ]);
    }

    public function create(): void
    {
        $this->authorize('users.manage');
        $this->view('users.form', [
            'title' => __('user_new'),
            'user'  => null,
            'roles' => $this->roles(),
        ]);
    }

    public function edit(string $id): void
    {
        $this->authorize('users.manage');
        $user = User::find((int) $id);
        if (!$user) {
            $this->notFound();
        }
        $this->view('users.form', [
            'title' => __('user_edit'),
            'user'  => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function store(): void
    {
        $this->authorize('users.manage');
        Csrf::check();
        $data = $this->collect();
        $errors = $this->validate($data, [
            'name'     => 'required|max:150',
            'username' => 'required|max:60',
            'role_id'  => 'required|numeric',
            'email'    => 'email|max:150',
        ]);
        if (User::findByUsername($data['username'])) {
            $errors['username'] = __('username_taken');
        }
        if (trim((string) $this->input('password', '')) === '') {
            $errors['password'] = __('validation_required', ['field' => 'password']);
        }
        if ($errors) {
            $this->back($errors, $data);
        }
        $data['password_hash'] = password_hash((string) $this->input('password'), PASSWORD_DEFAULT);
        $id = User::create($data);
        Audit::log('create', 'users', $id, $data['username']);
        flash('success', __('saved_ok'));
        redirect('users');
    }

    public function update(string $id): void
    {
        $this->authorize('users.manage');
        Csrf::check();
        $user = User::find((int) $id);
        if (!$user) {
            $this->notFound();
        }
        $data = $this->collect();
        $errors = $this->validate($data, [
            'name'     => 'required|max:150',
            'username' => 'required|max:60',
            'role_id'  => 'required|numeric',
            'email'    => 'email|max:150',
        ]);
        $existing = User::findByUsername($data['username']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            $errors['username'] = __('username_taken');
        }
        if ($errors) {
            $this->back($errors, $data);
        }
        if (trim((string) $this->input('password', '')) !== '') {
            $data['password_hash'] = password_hash((string) $this->input('password'), PASSWORD_DEFAULT);
        }
        User::update((int) $id, $data);
        Audit::log('update', 'users', $id, $data['username']);
        flash('success', __('saved_ok'));
        redirect('users');
    }

    public function destroy(string $id): void
    {
        $this->authorize('users.manage');
        Csrf::check();
        if ((int) $id === (int) Auth::id()) {
            flash('error', __('cannot_delete_self'));
            redirect('users');
        }
        User::delete((int) $id);
        Audit::log('delete', 'users', $id);
        flash('success', __('deleted_ok'));
        redirect('users');
    }

    private function collect(): array
    {
        return [
            'name'      => trim((string) $this->input('name', '')),
            'username'  => trim((string) $this->input('username', '')),
            'email'     => trim((string) $this->input('email', '')) ?: null,
            'role_id'   => (int) $this->input('role_id', 0),
            'is_active' => (int) (bool) $this->input('is_active', 0),
        ];
    }

    private function roles(): array
    {
        return Database::all('SELECT id, label FROM roles ORDER BY id');
    }
}
