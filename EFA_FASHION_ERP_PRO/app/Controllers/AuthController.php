<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Controller;
use App\Core\Csrf;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $this->view('auth.login', [], 'auth');
    }

    public function login(): void
    {
        Csrf::check();

        $username = trim((string) $this->input('username', ''));
        $password = (string) $this->input('password', '');
        $remember = (bool) $this->input('remember', false);

        $errors = $this->validate(
            ['username' => $username, 'password' => $password],
            ['username' => 'required', 'password' => 'required']
        );
        if ($errors) {
            $this->loginFailed($errors, $username);
        }

        if (!Auth::attempt($username, $password, $remember)) {
            Audit::log('login_failed', 'users', null, 'username=' . $username);
            $this->loginFailed(['auth' => __('invalid_credentials')], $username);
        }

        Audit::log('login', 'users', Auth::id());
        flash('success', __('logged_in'));
        redirect('dashboard');
    }

    public function logout(): void
    {
        Audit::log('logout', 'users', Auth::id());
        Auth::logout();
        flash('success', __('logged_out'));
        redirect('login');
    }

    /** Flash login errors + old input, then return to the login screen. */
    private function loginFailed(array $errors, string $username): void
    {
        $_SESSION['_old'] = ['username' => $username];
        foreach ($errors as $msg) {
            flash('error', $msg);
        }
        redirect('login');
    }
}
