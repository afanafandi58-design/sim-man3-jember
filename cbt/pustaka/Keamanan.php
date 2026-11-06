<?php
declare(strict_types=1);

final class Keamanan
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    public static function sahkan(): void
    {
        $kirim = (string) ($_POST['csrf'] ?? '');
        $simpan = (string) ($_SESSION['csrf'] ?? '');
        if ($kirim === '' || $simpan === '' || !hash_equals($simpan, $kirim)) {
            throw new RuntimeException('Sesi formulir tidak valid. Muat ulang halaman.');
        }
    }

    public static function sahkanApi(): void
    {
        $kirim = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $simpan = (string) ($_SESSION['csrf'] ?? '');
        if ($kirim === '' || $simpan === '' || !hash_equals($simpan, $kirim)) {
            throw new RuntimeException('Sesi ujian tidak valid. Muat ulang halaman.');
        }
    }
}
