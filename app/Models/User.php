<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'wishlist_users';
    protected static string $primaryKey = 'id';

    public static function findByUsernameOrEmail(string $identifier): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE username = ? OR email = ? OR unverified_email = ?", [$identifier, $identifier, $identifier]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function findBySessionId(string $sessionId): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE session = ? AND session_expiration > NOW()", [$sessionId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function updateSession(int $userId, ?string $sessionId, ?string $expiration): bool
    {
        return self::update($userId, ['session' => $sessionId, 'session_expiration' => $expiration]);
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE email = ?", [$email]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function findByVerificationToken(string $token): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE verification_token = ? AND verification_expires_at > NOW()", [$token]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function findByResetPasswordKey(string $key): ?array
    {
        $now = date("Y-m-d H:i:s");
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE reset_password_key = ? AND reset_password_expiration > ?", [$key, $now]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function where(string $column, string $operator, $value): array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ?", [$value]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function whereEqual(string $column, $value): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " WHERE {$column} = ?", [$value]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function paginate(int $perPage, int $offset): array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM " . static::$table . " ORDER BY id DESC LIMIT ? OFFSET ?", [$perPage, $offset]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function count(): int
    {
        $stmt = \App\Core\Database::query("SELECT COUNT(*) as count FROM " . static::$table);
        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['count'];
    }

    public static function findProfileForAll(): array
    {
        $stmt = \App\Core\Database::query("SELECT username, name, profile_picture FROM " . static::$table);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getNameAndProfilePictureByUsername(string $username): ?array
    {
        return $this->queryBuilder
            ->columns(['name', 'profile_picture'])
            ->where('username', $username)
            ->first();
    }

    public static function updateProfilePicture(string $username, string $filename): bool
    {
        $model = new self();
        return $model->queryBuilder
            ->columns(['profile_picture'])
            ->params([$filename])
            ->where('username', $username)
            ->update();
    }

    public static function updateFeatureUpdateSeen(string $username, string $status): bool
    {
        $model = new self();
        return $model->queryBuilder
            ->columns(['feature_update_seen'])
            ->params([$status])
            ->where('username', $username)
            ->update();
    }
}