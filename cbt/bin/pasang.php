<?php
declare(strict_types=1);

require dirname(__DIR__) . '/pustaka/bootstrap.php';

$env = Db::env();
$server = Db::server();
try {
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS `' . $env['nama'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Basis data tidak dibuat otomatis. Pastikan {$env['nama']} sudah ada.\n");
}
Db::lupa();
$db = Db::pdo();
jalankanSql($db, (string) file_get_contents(dirname(__DIR__) . '/sql/skema.sql'));
echo "Skema siap di {$env['nama']}.\n";

if (!in_array('--contoh', $argv ?? [], true)) {
    echo "Lewati data contoh. Tambahkan --contoh untuk mapel dan soal fiktif.\n";
    exit(0);
}

jalankanSql($db, (string) file_get_contents(dirname(__DIR__) . '/sql/contoh.sql'));
$cari = $db->query("SELECT s.id FROM sesi s JOIN mapel m ON m.id = s.mapel_id WHERE m.kode = 'BIG' AND s.nama = 'Latihan fiktif'");
$sesiId = (int) ($cari->fetchColumn() ?: 0);
if ($sesiId === 0) {
    $mapel = (int) $db->query("SELECT id FROM mapel WHERE kode = 'BIG'")->fetchColumn();
    $db->prepare('INSERT INTO sesi (mapel_id, nama, durasi_menit, status) VALUES (?, ?, ?, ?)')
        ->execute([$mapel, 'Latihan fiktif', 30, 'draf']);
    $sesiId = (int) $db->lastInsertId();
}
$jumlah = (int) $db->query('SELECT COUNT(*) FROM soal WHERE sesi_id = ' . $sesiId)->fetchColumn();
if ($jumlah === 0) {
    $soal = Impor::artikan(Impor::bacaCsv(dirname(__DIR__) . '/templat/soal.csv'));
    $hasil = Impor::simpan($db, $sesiId, $soal, false);
    echo "Soal contoh: {$hasil['jumlah']}.\n";
    foreach ($hasil['peringatan'] as $peringatan) {
        echo $peringatan . "\n";
    }
} else {
    echo "Soal contoh sudah ada.\n";
}
echo "Data contoh fiktif siap. Tidak ada akun atau token.\n";
