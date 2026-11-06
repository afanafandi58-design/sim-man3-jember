<?php
declare(strict_types=1);

final class Db
{
    private static ?PDO $pdo = null;

    public static function env(): array
    {
        $env = $GLOBALS['cbt_env'] ?? [];
        $nama = (string) ($env['CBT_DB_NAME'] ?? '');
        $host = (string) ($env['CBT_DB_HOST'] ?? '127.0.0.1');
        $user = (string) ($env['CBT_DB_USER'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $nama)) {
            throw new RuntimeException('CBT_DB_NAME tidak valid.');
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            throw new RuntimeException('CBT_DB_HOST tidak valid.');
        }
        if ($user === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $user)) {
            throw new RuntimeException('CBT_DB_USER tidak valid.');
        }
        return [
            'host' => $host,
            'port' => max(1, (int) ($env['CBT_DB_PORT'] ?? 3306)),
            'nama' => $nama,
            'user' => $user,
            'sandi' => (string) ($env['CBT_DB_PASS'] ?? ''),
        ];
    }

    public static function server(): PDO
    {
        $env = self::env();
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $env['host'], $env['port']);
        return self::buat($dsn, $env);
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $env = self::env();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $env['host'],
            $env['port'],
            $env['nama']
        );
        self::$pdo = self::buat($dsn, $env);
        return self::$pdo;
    }

    public static function lupa(): void
    {
        self::$pdo = null;
    }

    private static function buat(string $dsn, array $env): PDO
    {
        return new PDO($dsn, $env['user'], $env['sandi'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
