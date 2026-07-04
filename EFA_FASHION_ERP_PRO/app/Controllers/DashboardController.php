<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->authorize('dashboard.view');

        $stats = [
            'customers' => (int) Database::scalar('SELECT COUNT(*) FROM customers WHERE deleted_at IS NULL'),
            'shops'     => (int) Database::scalar('SELECT COUNT(*) FROM shops WHERE deleted_at IS NULL'),
            'factories' => (int) Database::scalar('SELECT COUNT(*) FROM factories WHERE deleted_at IS NULL'),
            'products'  => (int) Database::scalar('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL'),
            'receipts'  => (int) Database::scalar('SELECT COUNT(*) FROM receipts WHERE deleted_at IS NULL'),
        ];

        // Inventory value at purchase price = sum(stock * purchase_price)
        $stats['inventory_value'] = (float) Database::scalar(
            'SELECT COALESCE(SUM(sl.quantity * p.purchase_price), 0)
             FROM stock_levels sl
             JOIN product_variants pv ON pv.id = sl.variant_id
             JOIN products p ON p.id = pv.product_id'
        );

        // Outstanding debts = receipts (sales) total - collected payments
        $salesTotal = (float) Database::scalar(
            "SELECT COALESCE(SUM(grand_total),0) FROM receipts WHERE type='sale' AND deleted_at IS NULL"
        );
        $stats['debts'] = (float) Database::scalar(
        "SELECT COALESCE(SUM(grand_total),0)
        FROM receipts
         WHERE type='sale'
        AND deleted_at IS NULL"
        );
        // Profit (simple): sales - purchases - expenses + income
        $purchases = (float) Database::scalar(
            "SELECT COALESCE(SUM(grand_total),0) FROM receipts WHERE type='purchase' AND deleted_at IS NULL"
        );
        $expenses = (float) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE kind='expense'");
        $income   = (float) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE kind='income'");
        $stats['profit'] = $salesTotal - $purchases - $expenses + $income;

        // Alerts: low stock + checks due within 7 days
        $lowStock = Database::all(
            'SELECT p.code, p.name, p.min_stock, COALESCE(SUM(sl.quantity),0) AS on_hand
             FROM products p
             JOIN product_variants pv ON pv.product_id = p.id
             LEFT JOIN stock_levels sl ON sl.variant_id = pv.id
             WHERE p.deleted_at IS NULL
             GROUP BY p.id, p.code, p.name, p.min_stock
             HAVING on_hand <= p.min_stock AND p.min_stock > 0
             ORDER BY on_hand ASC LIMIT 10'
        );
        $dueChecks = Database::all(
            "SELECT check_number, amount, currency, due_date
             FROM checks
             WHERE status='pending' AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY due_date ASC LIMIT 10"
        );

        $this->view('dashboard.index', [
            'title'     => __('nav_dashboard'),
            'stats'     => $stats,
            'lowStock'  => $lowStock,
            'dueChecks' => $dueChecks,
        ]);
    }
}
