<?php
declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    $pesan = 'CBT ini membutuhkan PHP 8.';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $pesan . PHP_EOL);
        exit(1);
    }
    http_response_code(500);
    echo $pesan;
    exit;
}

define('CBT_AKAR', dirname(__DIR__));
define('CBT_PUBLIK', CBT_AKAR . '/public');

require_once CBT_AKAR . '/pustaka/Fungsi.php';
require_once CBT_AKAR . '/pustaka/Keamanan.php';
require_once CBT_AKAR . '/pustaka/Db.php';
require_once CBT_AKAR . '/pustaka/Penilaian.php';
require_once CBT_AKAR . '/pustaka/WaktuUjian.php';
require_once CBT_AKAR . '/pustaka/Xlsx.php';
require_once CBT_AKAR . '/pustaka/Impor.php';
require_once CBT_AKAR . '/pustaka/Layanan.php';

date_default_timezone_set('Asia/Jakarta');

$jalurEnv = CBT_AKAR . '/.env';
if (!is_file($jalurEnv)) {
    throw new RuntimeException('Berkas .env tidak ada. Salin .env.example menjadi .env.');
}
$env = [];
foreach (file($jalurEnv, FILE_IGNORE_NEW_LINES) ?: [] as $baris) {
    $baris = trim($baris);
    if ($baris === '' || str_starts_with($baris, '#')) {
        continue;
    }
    $pos = strpos($baris, '=');
    if ($pos === false) {
        continue;
    }
    $kunci = trim(substr($baris, 0, $pos));
    $nilai = trim(substr($baris, $pos + 1));
    if (
        (str_starts_with($nilai, '"') && str_ends_with($nilai, '"'))
        || (str_starts_with($nilai, "'") && str_ends_with($nilai, "'"))
    ) {
        $nilai = substr($nilai, 1, -1);
    }
    $env[$kunci] = $nilai;
}
$GLOBALS['cbt_env'] = $env;

if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    $skrip = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $basis = rtrim(dirname($skrip), '/');
    $GLOBALS['basis'] = ($basis === '' || $basis === '/') ? '' : $basis;
    session_name('CBTMAN3');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => ($GLOBALS['basis'] === '' ? '/' : $GLOBALS['basis'] . '/'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
} else {
    $GLOBALS['basis'] = '';
}
