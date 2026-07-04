-- =====================================================================
-- EFA FASHION ERP PRO - Seed / reference data
-- Idempotent: uses INSERT ... ON DUPLICATE KEY / INSERT IGNORE.
-- The default Super Admin USER is created by database/migrate.php so the
-- password can be hashed with PHP's password_hash().
-- =====================================================================

SET NAMES utf8mb4;

-- Roles -----------------------------------------------------------------
INSERT INTO roles (name, label, is_system) VALUES
    ('super_admin',     'Super Admin',       1),
    ('admin',           'Admin',             1),
    ('accountant',      'Accountant',        1),
    ('sales',           'Sales Employee',    1),
    ('warehouse',       'Warehouse Manager', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Permissions -----------------------------------------------------------
INSERT INTO permissions (name, label, module) VALUES
    ('dashboard.view',     'View dashboard',         'dashboard'),
    ('customers.view',     'View customers',         'customers'),
    ('customers.manage',   'Manage customers',       'customers'),
    ('factories.view',     'View factories',         'factories'),
    ('factories.manage',   'Manage factories',       'factories'),
    ('shops.view',         'View shops',             'shops'),
    ('shops.manage',       'Manage shops',           'shops'),
    ('products.view',      'View products',          'products'),
    ('products.manage',    'Manage products',        'products'),
    ('inventory.view',     'View inventory',         'inventory'),
    ('inventory.manage',   'Manage inventory',       'inventory'),
    ('receipts.view',      'View receipts',          'receipts'),
    ('receipts.manage',    'Manage receipts',        'receipts'),
    ('accounting.view',    'View accounting',        'accounting'),
    ('accounting.manage',  'Manage accounting',      'accounting'),
    ('reports.view',       'View reports',           'reports'),
    ('settings.manage',    'Manage settings',        'settings'),
    ('users.manage',       'Manage users & roles',   'users'),
    ('audit.view',         'View audit logs',        'audit'),
    ('backup.manage',      'Backup & restore',       'backup')
ON DUPLICATE KEY UPDATE label = VALUES(label), module = VALUES(module);

-- Role -> permission assignments ---------------------------------------
-- Super Admin: everything
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name = 'super_admin';

-- Admin: everything except managing users/roles and backup
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'admin' AND p.name NOT IN ('users.manage','backup.manage');

-- Accountant: accounting + reports + view of parties/receipts
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'accountant' AND p.name IN (
    'dashboard.view','customers.view','factories.view','shops.view',
    'receipts.view','accounting.view','accounting.manage','reports.view');

-- Sales Employee: customers/shops + receipts
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'sales' AND p.name IN (
    'dashboard.view','customers.view','customers.manage','shops.view','shops.manage',
    'products.view','receipts.view','receipts.manage','reports.view');

-- Warehouse Manager: products + inventory
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
WHERE r.name = 'warehouse' AND p.name IN (
    'dashboard.view','products.view','products.manage','inventory.view','inventory.manage','reports.view');

-- Branches --------------------------------------------------------------
INSERT IGNORE INTO branches (name, type) VALUES
    ('ANA DEPO', 'warehouse'),
    ('MAĞAZA',   'center');

-- Sizes -----------------------------------------------------------------
INSERT IGNORE INTO sizes (name, sort_order) VALUES
    ('XS', 1), ('S', 2), ('M', 3), ('L', 4), ('XL', 5), ('XXL', 6);

-- Colors (a few defaults) ----------------------------------------------
INSERT IGNORE INTO colors (name, hex) VALUES
    ('Black', '#000000'), ('White', '#FFFFFF'), ('Red', '#FF0000'),
    ('Blue', '#0000FF'), ('Green', '#008000');

-- Currencies ------------------------------------------------------------
INSERT INTO currencies (code, name, symbol, rate_to_base, is_base) VALUES
    ('USD', 'US Dollar',    '$', 1.000000, 1),
    ('EUR', 'Euro',         '€', 1.080000, 0),
    ('TRY', 'Turkish Lira', '₺', 0.031000, 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), symbol = VALUES(symbol);

-- Cash boxes (one per currency) ----------------------------------------
INSERT IGNORE INTO cash_boxes (name, currency, balance) VALUES
    ('Main Cash USD', 'USD', 0),
    ('Main Cash EUR', 'EUR', 0),
    ('Main Cash TRY', 'TRY', 0);

-- Settings --------------------------------------------------------------
INSERT INTO settings (skey, svalue) VALUES
    ('company_name', 'EFA FASHION'),
    ('company_email', ''),
    ('company_phone', ''),
    ('company_address', ''),
    ('company_website', ''),
    ('base_currency', 'USD'),
    ('default_lang', 'en'),
    ('receipt_prefix', 'FIS-EFA-')
ON DUPLICATE KEY UPDATE svalue = VALUES(svalue);
