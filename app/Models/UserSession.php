<?php

namespace App\Models;

use App\Core\Model;

class UserSession extends Model
{
    protected static string $table = 'wishlist_user_sessions';
    protected static string $primaryKey = 'id';


    public static function createSession(array $data): bool
    {
        $stmt = \App\Core\Database::query(
            "INSERT INTO " . static::$table . "
            (
                username,
                selector,
                token_hash,
                expires_at,
                created_at,
                ip_address,
                user_agent
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['username'],
                $data['selector'],
                $data['token_hash'],
                $data['expires_at'],
                $data['created_at'],
                $data['ip_address'],
                $data['user_agent']
            ]
        );

        return $stmt->affected_rows > 0;
    }


    public static function findBySelector(string $selector): ?array
    {
        $stmt = \App\Core\Database::query(
            "SELECT *
             FROM " . static::$table . "
             WHERE selector = ?
             AND expires_at > NOW()",
            [$selector]
        );

        $result = $stmt->get_result()->fetch_assoc();

        return $result ?: null;
    }


    public static function updateToken(
        int $sessionId,
        string $tokenHash
    ): bool
    {
        $stmt = \App\Core\Database::query(
            "UPDATE " . static::$table . "
             SET token_hash = ?,
                 last_used_at = NOW()
             WHERE id = ?",
            [
                $tokenHash,
                $sessionId
            ]
        );

        return $stmt->affected_rows > 0;
    }


    public static function deleteBySelector(string $selector): bool
    {
        $stmt = \App\Core\Database::query(
            "DELETE FROM " . static::$table . "
             WHERE selector = ?",
            [$selector]
        );

        return $stmt->affected_rows > 0;
    }


    public static function deleteByUsername(string $username): bool
    {
        $stmt = \App\Core\Database::query(
            "DELETE FROM " . static::$table . "
             WHERE username = ?",
            [$username]
        );

        return $stmt->affected_rows > 0;
    }
}