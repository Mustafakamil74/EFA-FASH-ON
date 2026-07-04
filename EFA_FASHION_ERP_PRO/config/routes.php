<?php
/**
 * Application routes. Returns a configured Router instance.
 * Routes are grouped by phase/module for readability.
 */

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CustomerController;
use App\Controllers\FactoryController;
use App\Controllers\ShopController;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\InventoryController;
use App\Controllers\ReceiptController;
use App\Controllers\AccountingController;
use App\Controllers\PaymentController;
use App\Controllers\CheckController;
use App\Controllers\TransactionController;
use App\Controllers\ClosingController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\UserController;
use App\Controllers\AuditController;
use App\Controllers\RecycleBinController;

$router = new Router();

// -- Phase 1: Authentication & dashboard --------------------------------
$router->get('/',         [AuthController::class, 'showLogin']);
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/logout',   [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

// -- Phase 2: Customers / Factories / Shops -----------------------------
// Register the standard CRUD + statement/PDF routes for each "party" module.
$contactModules = [
    'customers' => CustomerController::class,
    'factories' => FactoryController::class,
    'shops'     => ShopController::class,
];
foreach ($contactModules as $base => $controller) {
    $router->get("/$base",                [$controller, 'index']);
    $router->get("/$base/create",         [$controller, 'create']);
    $router->post("/$base",               [$controller, 'store']);
    $router->get("/$base/{id}",           [$controller, 'show']);
    $router->get("/$base/{id}/edit",      [$controller, 'edit']);
    $router->post("/$base/{id}/update",   [$controller, 'update']);
    $router->post("/$base/{id}/delete",   [$controller, 'destroy']);
    $router->get("/$base/{id}/statement", [$controller, 'show']);
    $router->get("/$base/{id}/pdf",       [$controller, 'pdf']);
}

// -- Phase 3: Products & categories -------------------------------------
$router->get('/products',              [ProductController::class, 'index']);
$router->get('/products/create',       [ProductController::class, 'create']);
$router->post('/products',             [ProductController::class, 'store']);
$router->get('/products/{id}',         [ProductController::class, 'show']);
$router->get('/products/{id}/edit',    [ProductController::class, 'edit']);
$router->post('/products/{id}/update', [ProductController::class, 'update']);
$router->post('/products/{id}/delete', [ProductController::class, 'destroy']);
$router->get('/products/{id}/label',   [ProductController::class, 'label']);

$router->get('/categories',              [CategoryController::class, 'index']);
$router->post('/categories',             [CategoryController::class, 'store']);
$router->post('/categories/{id}/update', [CategoryController::class, 'update']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy']);

// -- Phase 4: Inventory -------------------------------------------------
$router->get('/inventory',            [InventoryController::class, 'index']);
$router->get('/inventory/movements',  [InventoryController::class, 'movements']);
$router->get('/inventory/move',       [InventoryController::class, 'form']);
$router->post('/inventory/move',      [InventoryController::class, 'store']);

// -- Phase 5: Receipts & invoices ---------------------------------------
$router->get('/factory-receipts', [ReceiptController::class, 'factoryCreate']);
$router->get('/factory-receipts/create', [ReceiptController::class, 'factoryCreate']);
$router->post('/factory-receipts', [ReceiptController::class, 'store']);
$router->get('/receipts',                 [ReceiptController::class, 'index']);
$router->get('/receipts/create',          [ReceiptController::class, 'create']);
$router->post('/receipts',                [ReceiptController::class, 'store']);
$router->get('/receipts/{id}',            [ReceiptController::class, 'show']);
$router->get('/receipts/{id}/edit',       [ReceiptController::class, 'edit']);
$router->post('/receipts/{id}/update',    [ReceiptController::class, 'update']);
$router->post('/receipts/{id}/delete',    [ReceiptController::class, 'destroy']);
$router->post('/receipts/{id}/duplicate', [ReceiptController::class, 'duplicate']);
$router->get('/receipts/{id}/pdf',        [ReceiptController::class, 'pdf']);

// -- Phase 6: Accounting ------------------------------------------------
$router->get('/accounting',                       [AccountingController::class, 'index']);
$router->post('/accounting/cashbox',              [AccountingController::class, 'storeCashBox']);
$router->post('/accounting/cashbox/{id}/delete',  [AccountingController::class, 'deleteCashBox']);
$router->post('/accounting/bank',                 [AccountingController::class, 'storeBank']);
$router->post('/accounting/bank/{id}/delete',     [AccountingController::class, 'deleteBank']);

$router->get('/payments',                 [PaymentController::class, 'index']);
$router->get('/payments/create',          [PaymentController::class, 'create']);
$router->post('/payments',                [PaymentController::class, 'store']);
$router->post('/payments/{id}/delete',    [PaymentController::class, 'destroy']);

$router->get('/checks',                    [CheckController::class, 'index']);
$router->get('/checks/create',             [CheckController::class, 'create']);
$router->post('/checks',                   [CheckController::class, 'store']);
$router->post('/checks/{id}/status',       [CheckController::class, 'updateStatus']);
$router->post('/checks/{id}/delete',       [CheckController::class, 'destroy']);

$router->get('/transactions',              [TransactionController::class, 'index']);
$router->post('/transactions',             [TransactionController::class, 'store']);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'destroy']);

$router->get('/closings',                  [ClosingController::class, 'index']);
$router->post('/closings',                 [ClosingController::class, 'store']);

// -- Phase 7: Reports ---------------------------------------------------
$router->get('/reports',               [ReportController::class, 'index']);
$router->get('/reports/export/pdf',    [ReportController::class, 'exportPdf']);
$router->get('/reports/export/excel',  [ReportController::class, 'exportExcel']);

// -- Phase 8: Settings, users, audit, recycle bin -----------------------
$router->get('/settings',                    [SettingsController::class, 'index']);
$router->post('/settings',                   [SettingsController::class, 'save']);
$router->post('/settings/currency',          [SettingsController::class, 'saveCurrency']);
$router->post('/settings/currency/{code}/delete', [SettingsController::class, 'deleteCurrency']);
$router->get('/settings/backup',             [SettingsController::class, 'backup']);
$router->post('/settings/restore',           [SettingsController::class, 'restore']);

$router->get('/users',                 [UserController::class, 'index']);
$router->get('/users/create',          [UserController::class, 'create']);
$router->post('/users',                [UserController::class, 'store']);
$router->get('/users/{id}/edit',       [UserController::class, 'edit']);
$router->post('/users/{id}/update',     [UserController::class, 'update']);
$router->post('/users/{id}/delete',     [UserController::class, 'destroy']);

$router->get('/audit',                  [AuditController::class, 'index']);
$router->get('/audit/errors',           [AuditController::class, 'errors']);

$router->get('/recyclebin',                          [RecycleBinController::class, 'index']);
$router->post('/recyclebin/{table}/{id}/restore',    [RecycleBinController::class, 'restore']);
$router->post('/recyclebin/{table}/{id}/purge',      [RecycleBinController::class, 'purge']);

return $router;
