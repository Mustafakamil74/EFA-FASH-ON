<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Audit;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Setting;
use App\Models\Currency;

class SettingsController extends Controller
{
    /** Setting keys editable from the company form. */
    private const KEYS = [
        'company_name', 'company_phone', 'company_phone2', 'company_address',
        'company_email', 'company_website', 'manager_signature',
        'pdf_paper', 'print_footer', 'receipt_prefix', 'low_stock_default',
    ];

    public function index(): void
    {
        $this->authorize('settings.manage');
        $this->view('settings.index', [
            'title'      => __('nav_settings'),
            's'          => Setting::all(),
            'currencies' => Currency::all(),
        ]);
    }

    public function save(): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        foreach (self::KEYS as $key) {
            Setting::set($key, trim((string) $this->input($key, '')));
        }
        $this->handleUpload('company_logo');
        $this->handleUpload('company_stamp');
        Audit::log('update', 'settings', null, 'company settings');
        flash('success', __('saved_ok'));
        redirect('settings');
    }

    public function saveCurrency(): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        $code = trim((string) $this->input('code', ''));
        if ($code === '') {
            flash('error', __('invalid_input'));
            redirect('settings');
        }
        Currency::upsert(
            $code,
            trim((string) $this->input('name', $code)),
            trim((string) $this->input('symbol', '')) ?: null,
            (float) $this->input('rate_to_base', 1),
            (bool) $this->input('is_base', false)
        );
        Audit::log('update', 'currencies', $code);
        flash('success', __('saved_ok'));
        redirect('settings');
    }

    public function deleteCurrency(string $code): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        Currency::delete($code);
        Audit::log('delete', 'currencies', $code);
        flash('success', __('deleted_ok'));
        redirect('settings');
    }

    /** Stream a full SQL dump of the database for backup. */
    public function backup(): void
    {
        $this->authorize('settings.manage');
        $cfg  = require dirname(__DIR__, 2) . '/config/db.php';
        $file = sys_get_temp_dir() . '/efa-backup-' . date('Ymd-His') . '.sql';
        $cmd  = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s 2>/dev/null > %s',
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['pass']),
            escapeshellarg($cfg['name']),
            escapeshellarg($file)
        );
        exec($cmd, $out, $code);
        if ($code !== 0 || !is_file($file)) {
            flash('error', __('backup_failed'));
            redirect('settings');
        }
        Audit::log('backup', 'database');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        @unlink($file);
        exit;
    }

    /** Restore the database from an uploaded SQL dump. */
    public function restore(): void
    {
        $this->authorize('settings.manage');
        Csrf::check();
        if (empty($_FILES['sql_file']['tmp_name']) || !is_uploaded_file($_FILES['sql_file']['tmp_name'])) {
            flash('error', __('invalid_input'));
            redirect('settings');
        }
        $cfg = require dirname(__DIR__, 2) . '/config/db.php';
        $cmd = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s 2>/dev/null',
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['pass']),
            escapeshellarg($cfg['name']),
            escapeshellarg($_FILES['sql_file']['tmp_name'])
        );
        exec($cmd, $out, $code);
        if ($code !== 0) {
            flash('error', __('restore_failed'));
            redirect('settings');
        }
        Audit::log('restore', 'database');
        flash('success', __('restore_ok'));
        redirect('settings');
    }

    /** Store an uploaded image into public/uploads/company and save its path. */
    private function handleUpload(string $field): void
    {
        if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return;
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return;
        }
        $dir = dirname(__DIR__, 2) . '/public/uploads/company';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = $field . '.' . $ext;
        move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $name);
        Setting::set($field, 'uploads/company/' . $name);
    }
}
