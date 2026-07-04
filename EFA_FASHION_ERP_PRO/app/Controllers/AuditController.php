<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AuditController extends Controller
{
    public function index(): void
    {
        $this->authorize('audit.view');
        $q = trim((string) $this->input('q', ''));
        $params = [];
        $where = '';
        if ($q !== '') {
            $where = 'WHERE a.action LIKE ? OR a.entity LIKE ? OR a.description LIKE ? OR u.username LIKE ?';
            $like = '%' . $q . '%';
            $params = [$like, $like, $like, $like];
        }
        $logs = Database::all(
            "SELECT a.*, u.username
             FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
             {$where}
             ORDER BY a.id DESC LIMIT 300",
            $params
        );
        $this->view('audit.index', [
            'title' => __('nav_audit'),
            'logs'  => $logs,
            'q'     => $q,
        ]);
    }

    public function errors(): void
    {
        $this->authorize('audit.view');
        $logs = Database::all('SELECT * FROM error_logs ORDER BY id DESC LIMIT 300');
        $this->view('audit.errors', [
            'title' => __('error_logs'),
            'logs'  => $logs,
        ]);
    }
}
