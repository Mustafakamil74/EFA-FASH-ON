-- =====================================================================
-- EFA FASHION ERP PRO - Database Schema
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- This file creates every table used across all 8 phases of the system.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- PHASE 1: Authentication, roles & permissions, audit, notifications
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(50)  NOT NULL,            -- machine name e.g. super_admin
    label         VARCHAR(100) NOT NULL,            -- human label
    is_system     TINYINT(1)   NOT NULL DEFAULT 0,  -- system roles cannot be deleted
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,            -- e.g. customers.create
    label         VARCHAR(150) NOT NULL,
    module        VARCHAR(50)  NOT NULL,            -- grouping: customers, products...
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id       INT UNSIGNED NOT NULL,
    name          VARCHAR(120) NOT NULL,
    username      VARCHAR(60)  NOT NULL,
    email         VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    lang          VARCHAR(5)   NOT NULL DEFAULT 'en',
    theme         VARCHAR(10)  NOT NULL DEFAULT 'light',
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP    NULL DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,   -- soft delete / recycle bin
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Persistent "remember me" tokens
CREATE TABLE IF NOT EXISTS auth_tokens (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED NOT NULL,
    selector      VARCHAR(64)  NOT NULL,
    validator_hash VARCHAR(255) NOT NULL,
    expires_at    TIMESTAMP    NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_auth_selector (selector),
    KEY idx_auth_user (user_id),
    CONSTRAINT fk_auth_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail / activity logs / permission logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED DEFAULT NULL,
    action        VARCHAR(60)  NOT NULL,            -- login, create, update, delete...
    entity        VARCHAR(60)  DEFAULT NULL,        -- table / module
    entity_id     VARCHAR(60)  DEFAULT NULL,
    description   VARCHAR(255) DEFAULT NULL,
    ip_address    VARCHAR(45)  DEFAULT NULL,
    user_agent    VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_entity (entity, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Error logs
CREATE TABLE IF NOT EXISTS error_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    level         VARCHAR(20)  NOT NULL DEFAULT 'error',
    message       TEXT         NOT NULL,
    file          VARCHAR(255) DEFAULT NULL,
    line          INT          DEFAULT NULL,
    trace         TEXT         DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications center
CREATE TABLE IF NOT EXISTS notifications (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED DEFAULT NULL,        -- null => broadcast to all
    type          VARCHAR(40)  NOT NULL DEFAULT 'info',
    title         VARCHAR(150) NOT NULL,
    body          VARCHAR(255) DEFAULT NULL,
    url           VARCHAR(255) DEFAULT NULL,
    is_read       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notif_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 2: Customers / Factories / Shops
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS customers (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code          VARCHAR(40)  NOT NULL,
    name          VARCHAR(150) NOT NULL,
    phone         VARCHAR(60)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    notes         TEXT         DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customers_code (code),
    KEY idx_customers_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS factories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code          VARCHAR(40)  NOT NULL,
    contact_name  VARCHAR(150) DEFAULT NULL,
    name          VARCHAR(150) NOT NULL,            -- factory name
    phone         VARCHAR(60)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    notes         TEXT         DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_factories_code (code),
    KEY idx_factories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shops (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code          VARCHAR(40)  NOT NULL,
    contact_name  VARCHAR(150) DEFAULT NULL,
    name          VARCHAR(150) NOT NULL,            -- shop name
    phone         VARCHAR(60)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    notes         TEXT         DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shops_code (code),
    KEY idx_shops_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 3: Products, categories, sizes, colors
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS categories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120) NOT NULL,
    parent_id     INT UNSIGNED DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_categories_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code            VARCHAR(60)  NOT NULL,
    name            VARCHAR(180) NOT NULL,
    category_id     INT UNSIGNED DEFAULT NULL,
    brand           VARCHAR(120) DEFAULT NULL,
    barcode         VARCHAR(80)  DEFAULT NULL,
    purchase_price  DECIMAL(14,2) NOT NULL DEFAULT 0,
    sale_price      DECIMAL(14,2) NOT NULL DEFAULT 0,
    min_stock       DECIMAL(14,2) NOT NULL DEFAULT 0,   -- low-stock alert threshold
    image_path      VARCHAR(255) DEFAULT NULL,
    notes           TEXT         DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_code (code),
    KEY idx_products_category (category_id),
    KEY idx_products_barcode (barcode),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Master list of colors and sizes
CREATE TABLE IF NOT EXISTS colors (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(60)  NOT NULL,
    hex           VARCHAR(7)   DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_colors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sizes (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(20)  NOT NULL,            -- XS, S, M, L, XL, XXL
    sort_order    INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sizes_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A concrete stock-keeping unit: product + color + size
CREATE TABLE IF NOT EXISTS product_variants (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id    INT UNSIGNED NOT NULL,
    color_id      INT UNSIGNED DEFAULT NULL,
    size_id       INT UNSIGNED DEFAULT NULL,
    sku           VARCHAR(80)  DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_variant (product_id, color_id, size_id),
    KEY idx_variant_product (product_id),
    CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_variant_color FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL,
    CONSTRAINT fk_variant_size FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 4: Inventory (branches, stock levels, movements)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS branches (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120) NOT NULL,
    type          ENUM('warehouse','center') NOT NULL DEFAULT 'warehouse',
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_branches_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Current stock on hand per variant per branch
CREATE TABLE IF NOT EXISTS stock_levels (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    branch_id     INT UNSIGNED NOT NULL,
    variant_id    INT UNSIGNED NOT NULL,
    quantity      DECIMAL(14,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_stock (branch_id, variant_id),
    KEY idx_stock_variant (variant_id),
    CONSTRAINT fk_stock_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_stock_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Immutable ledger of all stock movements
CREATE TABLE IF NOT EXISTS stock_movements (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    branch_id     INT UNSIGNED NOT NULL,
    variant_id    INT UNSIGNED NOT NULL,
    type          ENUM('in','out','transfer_in','transfer_out','adjust') NOT NULL,
    quantity      DECIMAL(14,2) NOT NULL,           -- signed: +in / -out
    ref_type      VARCHAR(40)  DEFAULT NULL,        -- receipt, manual, transfer
    ref_id        BIGINT UNSIGNED DEFAULT NULL,
    note          VARCHAR(255) DEFAULT NULL,
    user_id       INT UNSIGNED DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_move_branch (branch_id),
    KEY idx_move_variant (variant_id),
    KEY idx_move_ref (ref_type, ref_id),
    CONSTRAINT fk_move_branch FOREIGN KEY (branch_id) REFERENCES branches(id),
    CONSTRAINT fk_move_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 5: Receipts & invoices
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS receipts (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    number          VARCHAR(40)  NOT NULL,          -- FIS-EFA-000001
    type            ENUM('sale','purchase','return') NOT NULL DEFAULT 'sale',
    party_type      ENUM('customer','shop','factory') NOT NULL,
    party_id        INT UNSIGNED NOT NULL,
    branch_id       INT UNSIGNED DEFAULT NULL,
    currency        VARCHAR(5)   NOT NULL DEFAULT 'USD',
    receipt_date    DATE         NOT NULL,
    receipt_time    TIME         DEFAULT NULL,
    discount        DECIMAL(14,2) NOT NULL DEFAULT 0,
    shipping_cost   DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0,
    grand_total     DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes           TEXT         DEFAULT NULL,
    user_id         INT UNSIGNED DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_receipts_number (number),
    KEY idx_receipts_party (party_type, party_id),
    KEY idx_receipts_date (receipt_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receipt_items (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_id      BIGINT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED DEFAULT NULL,
    variant_id      INT UNSIGNED DEFAULT NULL,
    description     VARCHAR(255) DEFAULT NULL,       -- snapshot of product name
    serial_number   VARCHAR(80)  DEFAULT NULL,
    color           VARCHAR(60)  DEFAULT NULL,
    size            VARCHAR(20)  DEFAULT NULL,
    quantity        DECIMAL(14,2) NOT NULL DEFAULT 0,
    unit_price      DECIMAL(14,2) NOT NULL DEFAULT 0,
    line_total      DECIMAL(14,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_items_receipt (receipt_id),
    CONSTRAINT fk_items_receipt FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 6: Accounting (cash boxes, payments, debts, checks, banks, P&L)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS cash_boxes (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(80)  NOT NULL,
    currency      VARCHAR(5)   NOT NULL DEFAULT 'USD',
    balance       DECIMAL(16,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cashbox (name, currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bank_accounts (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    bank_name     VARCHAR(120) NOT NULL,
    account_no    VARCHAR(80)  DEFAULT NULL,
    iban          VARCHAR(60)  DEFAULT NULL,
    currency      VARCHAR(5)   NOT NULL DEFAULT 'USD',
    balance       DECIMAL(16,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments & collections (money in/out tied to a party and optionally a receipt)
CREATE TABLE IF NOT EXISTS payments (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    direction       ENUM('in','out') NOT NULL,       -- collection vs payment
    party_type      ENUM('customer','shop','factory') NOT NULL,
    party_id        INT UNSIGNED NOT NULL,
    receipt_id      BIGINT UNSIGNED DEFAULT NULL,
    method          ENUM('cash','bank','check') NOT NULL DEFAULT 'cash',
    cash_box_id     INT UNSIGNED DEFAULT NULL,
    bank_account_id INT UNSIGNED DEFAULT NULL,
    currency        VARCHAR(5)   NOT NULL DEFAULT 'USD',
    amount          DECIMAL(16,2) NOT NULL,
    pay_date        DATE         NOT NULL,
    note            VARCHAR(255) DEFAULT NULL,
    user_id         INT UNSIGNED DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_payments_party (party_type, party_id),
    KEY idx_payments_receipt (receipt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Checks (cheques) with maturity tracking
CREATE TABLE IF NOT EXISTS checks (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    direction       ENUM('in','out') NOT NULL,
    party_type      ENUM('customer','shop','factory') DEFAULT NULL,
    party_id        INT UNSIGNED DEFAULT NULL,
    check_number    VARCHAR(80)  DEFAULT NULL,
    bank_name       VARCHAR(120) DEFAULT NULL,
    currency        VARCHAR(5)   NOT NULL DEFAULT 'USD',
    amount          DECIMAL(16,2) NOT NULL,
    issue_date      DATE         DEFAULT NULL,
    due_date        DATE         NOT NULL,
    status          ENUM('pending','cleared','bounced','cancelled') NOT NULL DEFAULT 'pending',
    note            VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_checks_due (due_date),
    KEY idx_checks_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses & income (general ledger of non-receipt money flows)
CREATE TABLE IF NOT EXISTS transactions (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kind            ENUM('expense','income') NOT NULL,
    category        VARCHAR(120) DEFAULT NULL,
    currency        VARCHAR(5)   NOT NULL DEFAULT 'USD',
    amount          DECIMAL(16,2) NOT NULL,
    txn_date        DATE         NOT NULL,
    note            VARCHAR(255) DEFAULT NULL,
    user_id         INT UNSIGNED DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_txn_kind (kind),
    KEY idx_txn_date (txn_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Period closings (daily / monthly / yearly snapshots)
CREATE TABLE IF NOT EXISTS closings (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    period_type     ENUM('daily','monthly','yearly') NOT NULL,
    period_label    VARCHAR(20)  NOT NULL,           -- 2025-06-13 / 2025-06 / 2025
    total_sales     DECIMAL(16,2) NOT NULL DEFAULT 0,
    total_expenses  DECIMAL(16,2) NOT NULL DEFAULT 0,
    total_income    DECIMAL(16,2) NOT NULL DEFAULT 0,
    profit          DECIMAL(16,2) NOT NULL DEFAULT 0,
    closed_by       INT UNSIGNED DEFAULT NULL,
    closed_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_closing (period_type, period_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PHASE 8: Settings, currencies / exchange rates
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS settings (
    skey          VARCHAR(80)  NOT NULL,
    svalue        TEXT         DEFAULT NULL,
    PRIMARY KEY (skey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS currencies (
    code          VARCHAR(5)   NOT NULL,            -- USD, EUR, TRY
    name          VARCHAR(60)  NOT NULL,
    symbol        VARCHAR(8)   DEFAULT NULL,
    rate_to_base  DECIMAL(16,6) NOT NULL DEFAULT 1, -- exchange rate to base currency
    is_base       TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
