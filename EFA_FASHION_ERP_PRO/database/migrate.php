<?php
/**
 * Database migration & seed runner.
 *
 * Usage:
 *   php database/migrate.php           # schema + seed + default admin
 *   php database/migrate.php --fresh   # DROP all tables first, then rebuild
 *
 * Reads DB credentials from .env (or .env.example defaults).
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;

Env::load(__DIR__ . '/../.env');

$fresh = in_array('--fresh', $argv, true);
$pdo   = Database::pdo();

echo "Connected to database '" . config('db.name') . "'.\n";

if ($fresh) {
    echo "Dropping existing tables (--fresh)...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $pdo->exec('DROP TABLE IF EXISTS `' . $t . '`');
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

function runSqlFile(PDO $pdo, string $file): void
{
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Cannot read {$file}");
    }
    // Execute the whole script (multi-statement) via PDO.
    $pdo->exec($sql);
    echo "  applied " . basename($file) . "\n";
}

echo "Applying schema...\n";
runSqlFile($pdo, __DIR__ . '/schema.sql');

echo "Applying seed data...\n";
runSqlFile($pdo, __DIR__ . '/seed.sql');

// Create / update the default Super Admin with a hashed password.
$admin = config('admin');
$roleId = Database::scalar("SELECT id FROM roles WHERE name = 'super_admin'");
if (!$roleId) {
    throw new RuntimeException('super_admin role missing after seed.');
}
$existing = Database::first('SELECT id FROM users WHERE username = ?', [$admin['username']]);
$hash = password_hash($admin['password'], PASSWORD_DEFAULT);
if ($existing) {
    Database::query(
        'UPDATE users SET name=?, email=?, password_hash=?, role_id=?, is_active=1, deleted_at=NULL WHERE id=?',
        [$admin['name'], $admin['email'], $hash, $roleId, $existing['id']]
    );
    echo "Updated existing Super Admin '{$admin['username']}'.\n";
} else {
    Database::query(
        'INSERT INTO users (role_id, name, username, email, password_hash, lang, theme) VALUES (?,?,?,?,?,?,?)',
        [$roleId, $admin['name'], $admin['username'], $admin['email'], $hash, config('app.default_lang', 'en'), 'light']
    );
    echo "Created Super Admin '{$admin['username']}'.\n";
}

echo "\nDone. Login with username '{$admin['username']}' / password '{$admin['password']}'.\n";
