<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

function url(string $path = ''): string
{
    global $config;
    $base = isset($config['base_url']) ? rtrim((string) $config['base_url'], '/') : '';
    if ($base === '') {
        $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $dir = str_replace('\\', '/', dirname($script));
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            $base = '';
        } else {
            $base = rtrim($dir, '/');
        }
    }
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return $base === '' ? '/' : $base . '/';
    }
    return $base . $path;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }
    $val = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : '';
    $session = isset($_SESSION['_csrf']) ? (string) $_SESSION['_csrf'] : '';
    if ($token === '' || $session === '' || !hash_equals($session, $token)) {
        http_response_code(419);
        echo 'Sesi tidak valid (CSRF). Muat ulang halaman.';
        exit;
    }
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = dirname(__DIR__, 2) . '/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'View tidak ditemukan: ' . e($name);
        exit;
    }
    require $viewFile;
}

function format_tanggal_id($value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    if ($value instanceof DateTimeInterface) {
        $dt = $value;
    } else {
        $raw = trim((string) $value);
        if ($raw === '' || $raw === '0000-00-00') {
            return '';
        }
        $dt = DateTime::createFromFormat('Y-m-d', $raw);
        if (!$dt) {
            try {
                $dt = new DateTime($raw);
            } catch (Exception $e) {
                return $raw;
            }
        }
    }
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $m = (int) $dt->format('n');
    return $dt->format('d') . ' ' . $bulan[$m] . ' ' . $dt->format('Y');
}

function parse_tanggal_flexible($value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_numeric($value)) {
        // Excel serial date
        $unix = ((float) $value - 25569) * 86400;
        return gmdate('Y-m-d', (int) $unix);
    }
    $raw = trim((string) $value);
    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd.m.Y', 'Y/m/d'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat('!' . $fmt, $raw);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }
    try {
        $dt = new DateTime($raw);
        return $dt->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

function normalize_jk(?string $value): string
{
    $v = strtoupper(trim((string) $value));
    if ($v === 'L' || $v === 'LAKI-LAKI' || $v === 'LAKI LAKI' || $v === 'PRIA') {
        return 'LAKI-LAKI';
    }
    if ($v === 'P' || $v === 'PEREMPUAN' || $v === 'WANITA') {
        return 'PEREMPUAN';
    }
    return trim((string) $value);
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Silakan masuk terlebih dahulu.');
        redirect('/login');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => isset($_SESSION['username']) ? (string) $_SESSION['username'] : '',
        'nama' => isset($_SESSION['nama']) ? (string) $_SESSION['nama'] : '',
    ];
}
