# EFA FASHION ERP PRO

A production-oriented, web-based ERP for a clothing company, built with **PHP 8**, **MySQL**,
**Bootstrap 5** and a clean, hand-rolled **MVC** architecture (no heavy framework). It covers
authentication & roles, customers/factories/shops, products & inventory, receipts & invoices,
accounting, reports, settings, multi-language (AR/TR/EN/RU/ZH), dark/light mode, audit logs and
backup/restore.

> This system is delivered **phase by phase**. See "Roadmap" below for what is implemented.

## Tech stack

- PHP 8+ (PDO, prepared statements everywhere)
- MySQL / MariaDB (InnoDB, `utf8mb4`)
- Bootstrap 5 + Bootstrap Icons (responsive, RTL-aware)
- Composer (Dompdf for PDF, PhpSpreadsheet for Excel)
- Clean MVC: `Router → Controller → Model/View`

## Project structure

```
EFA_FASHION_ERP_PRO/
├── app/
│   ├── Core/          # framework: App, Router, Controller, Model, Database, Auth, View, Lang, Csrf, Audit, Env
│   ├── Controllers/   # one controller per module
│   ├── Models/        # one model per table
│   ├── Views/         # PHP templates (layouts, modules, errors)
│   ├── Middleware/    # auth / permission guards
│   └── Helpers/       # global helper functions (autoloaded)
├── config/            # app.php, db.php, admin.php, routes.php
├── database/          # schema.sql, seed.sql, migrate.php
├── lang/              # en, ar, tr, ru, zh
├── public/            # web root: index.php (front controller), assets/
├── storage/           # logs, backups, uploads (git-ignored)
├── composer.json
└── .env.example
```

## Requirements

- PHP >= 8.0 with `pdo_mysql`, `mbstring`, `xml`, `zip`, `gd`
- MySQL 5.7+/8 or MariaDB 10.4+
- Composer

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
# edit .env: DB_* credentials and the default ADMIN_* account

# 3. Create the database (once)
mysql -u root -e "CREATE DATABASE efa_erp_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Build schema + seed reference data + default Super Admin
php database/migrate.php          # add --fresh to drop & rebuild everything

# 5. Run it (development server)
php -S 0.0.0.0:8080 -t public public/index.php
```

Open <http://localhost:8080> and sign in with the credentials printed by the migration
(default `admin` / `Admin@123` — change these in `.env` before any real use).

For production, point Apache/Nginx at the `public/` directory; `.htaccess` rewrites all
requests to the front controller.

## Roles & permissions

Five system roles ship out of the box: **Super Admin, Admin, Accountant, Sales Employee,
Warehouse Manager**. Permissions are stored in `permissions` and mapped per role in
`role_permissions`, so they are fully configurable. Super Admin implicitly has every permission.

## Multi-language & theming

- Languages: English, Arabic (RTL), Turkish, Russian, Chinese. Switch via the top bar; the
  choice is remembered per session and Arabic flips the layout to RTL.
- Light/Dark mode toggle in the top bar (persisted in a cookie).

## Security

- Passwords hashed with `password_hash()` (bcrypt).
- CSRF tokens on all state-changing forms.
- Output escaped via `e()` to prevent XSS.
- Prepared statements for all queries.
- Session fixation protection on login; HTTP-only "remember me" tokens (selector/validator).
- Audit logs, error logs, and soft-delete (recycle bin) for key entities.

## Roadmap (phase by phase)

- [x] **Phase 1 — Auth & admin core:** login/logout, remember-me, roles & permissions,
  dashboard with KPIs and alerts, audit/error logging, i18n, dark mode.
- [ ] Phase 2 — Customers / Factories / Shops (CRUD, search, statements, PDF/print)
- [ ] Phase 3 — Products, categories, sizes/colors, barcode/QR
- [ ] Phase 4 — Inventory (branches, stock in/out/transfer, low-stock, movement history)
- [ ] Phase 5 — Receipts & invoices (auto-numbering, multi-row, discounts, PDF/print/duplicate)
- [ ] Phase 6 — Accounting (cash boxes, payments, debts, checks, banks, P&L, closings)
- [ ] Phase 7 — Reports (daily/weekly/monthly/yearly, charts, PDF/Excel)
- [ ] Phase 8 — Settings, currencies/rates, notifications, recycle bin, backup/restore

The full database schema for **all** phases already exists in `database/schema.sql`.
