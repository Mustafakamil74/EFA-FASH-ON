<?php

namespace App\Core;

/**
 * Writes audit-trail entries for security-relevant actions.
 */
class Audit
{
    public static function log(string $action, ?string $entity = null, $entityId = null, ?string $description = null): void
    {
        try {
            Database::query(
                'INSERT INTO audit_logs (user_id, action, entity, entity_id, description, ip_address, user_agent)
                 VALUES (?,?,?,?,?,?,?)',
                [
                    Auth::id(),
                    $action,
                    $entity,
                    $entityId !== null ? (string) $entityId : null,
                    $description,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ]
            );
        } catch (\Throwable $e) {
            // Never let audit logging break the request.
        }
    }
}
