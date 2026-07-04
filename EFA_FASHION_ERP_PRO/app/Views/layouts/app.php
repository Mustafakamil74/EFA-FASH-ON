<?php
/** @var string $content */
use App\Core\Lang;
use App\Core\Auth;

$user    = Auth::user();
$theme   = $_COOKIE['theme'] ?? ($user['theme'] ?? 'light');
$current = $_SERVER['REQUEST_URI'] ?? '';
$title   = $title ?? __('app_name');

/** Sidebar items: [route, icon, label key, permission]. */
$nav = [
    ['dashboard',  'speedometer2',   'nav_dashboard',  'dashboard.view'],
    ['customers',  'people',         'nav_customers',  'customers.view'],
    ['factories',  'building',       'nav_factories',  'factories.view'],
    ['shops',      'shop',           'nav_shops',      'shops.view'],
    ['products',   'box-seam',       'nav_products',   'products.view'],
    ['inventory',  'boxes',          'nav_inventory',  'inventory.view'],
    ['factory-receipts', 'truck', 'nav_factory_receipts', 'receipts.view'],
    ['receipts',   'receipt',        'nav_receipts',   'receipts.view'],
    ['accounting', 'cash-coin',      'nav_accounting', 'accounting.view'],
    ['reports',    'bar-chart',      'nav_reports',    'reports.view'],
    ['users',      'person-badge',   'nav_users',      'users.manage'],
    ['audit',      'clock-history',  'nav_audit',      'audit.view'],
    ['recyclebin', 'trash',          'nav_recyclebin', 'settings.manage'],
    ['settings',   'gear',           'nav_settings',   'settings.manage'],
];
$flashes = flash();
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::current()) ?>" dir="<?= e(Lang::dir()) ?>" data-bs-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <title><?= e($title) ?> — EFA FASHION ERP PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-bag-check-fill"></i>
            <span class="brand-text">EFA FASHION</span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($nav as [$route, $icon, $labelKey, $perm]): ?>
                <?php if (!can($perm)) { continue; } ?>
                <a class="nav-link <?= str_contains($current, '/' . $route) ? 'active' : '' ?>"
                   href="<?= e(url($route)) ?>">
                    <i class="bi bi-<?= e($icon) ?>"></i>
                    <span class="nav-text"><?= e(__($labelKey)) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main -->
    <div class="main-area">
        <header class="topbar">
            <button class="btn btn-sm btn-icon" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="topbar-title"><?= e($title) ?></h1>
            <div class="topbar-actions">
                <!-- Theme toggle -->
                <button class="btn btn-sm btn-icon" id="themeToggle" type="button" title="<?= e(__('theme')) ?>">
                    <i class="bi bi-<?= $theme === 'dark' ? 'sun' : 'moon-stars' ?>"></i>
                </button>
                <!-- Language -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown" title="<?= e(__('language')) ?>">
                        <i class="bi bi-translate"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (config('app.langs', ['en']) as $lng): ?>
                            <li><a class="dropdown-item <?= $lng === Lang::current() ? 'active' : '' ?>"
                                   href="?lang=<?= e($lng) ?>"><?= strtoupper(e($lng)) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- User -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-user" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="d-none d-sm-inline"><?= e($user['name'] ?? '') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= e(Auth::role()) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= e(url('logout')) ?>">
                            <i class="bi bi-box-arrow-right me-1"></i><?= e(__('logout')) ?></a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            <?php foreach ($flashes as $f): ?>
                <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : e($f['type']) ?> alert-dismissible fade show">
                    <?= e($f['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<script src="<?= e(url('assets/js/receipt.js')) ?>"></script>
</body>
</html>