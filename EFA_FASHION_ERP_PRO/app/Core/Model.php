<?php

namespace App\Core;

/**
 * Base model providing generic CRUD on a single table.
 * Subclasses set $table, $fillable and (optionally) $softDelete.
 */
abstract class Model
{
    protected static string $table = '';
    protected static array $fillable = [];
    protected static bool $softDelete = false;

    /** Return all rows (excluding soft-deleted unless $withTrashed). */
    public static function all(string $orderBy = 'id DESC', bool $withTrashed = false): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if (static::$softDelete && !$withTrashed) {
            $sql .= ' WHERE deleted_at IS NULL';
        }
        $sql .= ' ORDER BY ' . $orderBy;
        return Database::all($sql);
    }

    public static function find($id): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE id = ?';
        if (static::$softDelete) {
            $sql .= ' AND deleted_at IS NULL';
        }
        return Database::first($sql, [$id]);
    }

    /** Create a record from an associative array (filtered by $fillable). */
    public static function create(array $data)
    {
        $data = static::filter($data);
        $cols = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(', ', $cols) . ') VALUES (' . $placeholders . ')';
        Database::query($sql, array_values($data));
        return Database::lastInsertId();
    }

    public static function update($id, array $data): void
    {
        $data = static::filter($data);
        if (!$data) {
            return;
        }
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . ' SET ' . $set . ' WHERE id = ?';
        Database::query($sql, [...array_values($data), $id]);
    }

    /** Soft delete when enabled, otherwise hard delete. */
    public static function delete($id): void
    {
        if (static::$softDelete) {
            Database::query('UPDATE ' . static::$table . ' SET deleted_at = NOW() WHERE id = ?', [$id]);
        } else {
            Database::query('DELETE FROM ' . static::$table . ' WHERE id = ?', [$id]);
        }
    }

    public static function restore($id): void
    {
        if (static::$softDelete) {
            Database::query('UPDATE ' . static::$table . ' SET deleted_at = NULL WHERE id = ?', [$id]);
        }
    }

    public static function count(string $where = '', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;
        $conds = [];
        if (static::$softDelete) {
            $conds[] = 'deleted_at IS NULL';
        }
        if ($where !== '') {
            $conds[] = $where;
        }
        if ($conds) {
            $sql .= ' WHERE ' . implode(' AND ', $conds);
        }
        return (int) Database::scalar($sql, $params);
    }

    protected static function filter(array $data): array
    {
        if (!static::$fillable) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }

    public static function table(): string
    {
        return static::$table;
    }
}
