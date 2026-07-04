<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Category extends Model
{
    protected static string $table = 'categories';
    protected static array $fillable = ['name', 'parent_id'];
    protected static bool $softDelete = true;

    /** Categories with their product counts. */
    public static function withCounts(): array
    {
        return Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.deleted_at IS NULL) AS product_count
             FROM categories c WHERE c.deleted_at IS NULL ORDER BY c.name'
        );
    }
}
