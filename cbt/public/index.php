<?php
declare(strict_types=1);

try {
    require dirname(__DIR__) . '/pustaka/bootstrap.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'CBT belum dipasang. Salin cbt/.env.example menjadi cbt/.env, lalu jalankan php cbt/bin/pasang.php.';
    exit;
}

require CBT_AKAR . '/halaman/Admin.php';
require CBT_AKAR . '/halaman/Siswa.php';
require CBT_AKAR . '/halaman/Api.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

$rute = trim((string) ($_GET['r'] ?? 'masuk'), '/');
if ($rute === '') {
    $rute = 'masuk';
}
$metode = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    if (str_starts_with($rute, 'admin')) {
        tanganiAdmin($rute, $metode);
    } elseif (str_starts_with($rute, 'api/')) {
        tanganiApi($rute, $metode);
    } else {
        tanganiSiswa($rute, $metode);
    }
    http_response_code(404);
    bungkus('Tidak ditemukan', 'kosong', ['pesan' => 'Halaman tidak ditemukan.']);
} catch (PDOException $e) {
    catat('basis-data ' . $e->getCode());
    tanggapiGalat('Basis data sedang bermasalah.', str_starts_with($rute, 'api/'));
} catch (RuntimeException $e) {
    tanggapiGalat($e->getMessage(), str_starts_with($rute, 'api/'));
} catch (Throwable $e) {
    catat('galat');
    tanggapiGalat('Terjadi kesalahan.', str_starts_with($rute, 'api/'));
}

function tanggapiGalat(string $pesan, bool $api): never
{
    if ($api) {
        jsonKeluar(['ok' => false, 'pesan' => $pesan], 400);
    }
    http_response_code(400);
    $nav = !empty($_SESSION['pengawas_id']) ? 'admin' : 'siswa';
    bungkus('Tidak dapat diproses', 'kosong', ['pesan' => $pesan], $nav);
    exit;
}
