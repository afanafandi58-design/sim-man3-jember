<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    public static function attempt(string $username, string $password): ?array
    {
        $user = self::findByUsername($username);
        if (!$user) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
