<?php

namespace App\Core;

/**
 * Base controller with view rendering, JSON responses, validation,
 * and authorization guards shared by all controllers.
 */
abstract class Controller
{
    /** Render a view within the main layout and echo it. */
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        echo View::render($view, $data, $layout);
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Require an authenticated user; redirect to login otherwise. */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            flash('error', __('please_login'));
            redirect('login');
        }
    }

    /** Require a specific permission; 403 otherwise. */
    protected function authorize(string $permission): void
    {
        $this->requireAuth();
        if (!Auth::can($permission)) {
            http_response_code(403);
            echo View::render('errors.403', [], 'app');
            exit;
        }
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Party options grouped by type for selectors, e.g.
     * ['customer' => [[id,label], ...], 'shop' => [...], 'factory' => [...]].
     */
    protected function partyOptions(): array
    {
        $out = [];
        $tables = ['customer' => 'customers', 'shop' => 'shops', 'factory' => 'factories'];
        foreach ($tables as $type => $table) {
            $out[$type] = Database::all(
                "SELECT id, CONCAT(code, ' - ', name) AS label FROM {$table} WHERE deleted_at IS NULL ORDER BY name"
            );
        }
        return $out;
    }

    protected function notFound(): void
    {
        http_response_code(404);
        echo View::render('errors.404', [], 'app');
        exit;
    }

    /**
     * Validate request data against simple rules.
     * Supported rules: required, numeric, email, max:N, min:N.
     * Returns array of [field => error message]; empty when valid.
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleset) {
            $value = trim((string) ($data[$field] ?? ''));
            foreach (explode('|', $ruleset) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $fail = match ($name) {
                    'required' => $value === '',
                    'numeric'  => $value !== '' && !is_numeric($value),
                    'email'    => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                    'max'      => mb_strlen($value) > (int) $param,
                    'min'      => mb_strlen($value) < (int) $param,
                    default    => false,
                };
                if ($fail) {
                    $errors[$field] = __('validation_' . $name, ['field' => $field, 'param' => (string) $param]);
                    break;
                }
            }
        }
        return $errors;
    }

    /** Flash old input + errors and redirect back. */
    protected function back(array $errors = [], array $old = []): void
    {
        $_SESSION['_old'] = $old;
        foreach ($errors as $msg) {
            flash('error', $msg);
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
        header('Location: ' . $ref);
        exit;
    }
}
