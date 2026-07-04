<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = [
        'role_id', 'name', 'username', 'email', 'password_hash', 'lang', 'theme', 'is_active',
    ];
    protected static bool $softDelete = true;

    public static function findByUsername(string $username): ?array
    {
        return Database::first('SELECT * FROM users WHERE username = ? AND deleted_at IS NULL', [$username]);
    }

    /** Users joined with their role label. */
    public static function withRoles(): array
    {
        return Database::all(
            'SELECT u.*, r.label AS role_label, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL ORDER BY u.id DESC'
        );
    }
}
