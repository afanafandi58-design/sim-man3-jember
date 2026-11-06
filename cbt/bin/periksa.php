<?php
declare(strict_types=1);

$akar = dirname(__DIR__);
require_once $akar . '/pustaka/Fungsi.php';
require_once $akar . '/pustaka/Penilaian.php';
require_once $akar . '/pustaka/WaktuUjian.php';
require_once $akar . '/pustaka/Xlsx.php';
require_once $akar . '/pustaka/Impor.php';

date_default_timezone_set('Asia/Jakarta');
$gagal = 0;

function uji(bool $kondisi, string $pesan): void
{
    global $gagal;
    if ($kondisi) {
        echo "  lulus  $pesan\n";
        return;
    }
    echo "  GAGAL  $pesan\n";
    $gagal++;
}

echo "Penilaian\n";
uji(Penilaian::pilihanGanda('', 'A') === 'kosong', 'pg kosong');
uji(Penilaian::pilihanGanda('a', 'A') === 'benar', 'pg benar');
uji(Penilaian::pilihanGanda('B', 'A') === 'salah', 'pg salah');
uji(Penilaian::pilihanGandaKompleks([], ['A', 'C']) === 'kosong', 'pgk kosong');
uji(Penilaian::pilihanGandaKompleks(['c', 'A'], ['A', 'C']) === 'benar', 'pgk himpunan sama');
uji(Penilaian::pilihanGandaKompleks(['A'], ['A', 'C']) === 'salah', 'pgk tidak lengkap');
uji(Penilaian::menjodohkan([], ['1' => 'B']) === 'kosong', 'jodoh kosong');
uji(Penilaian::menjodohkan(['1' => 'b', '2' => 'A'], ['1' => 'B', '2' => 'A']) === 'benar', 'jodoh benar');
uji(Penilaian::menjodohkan(['1' => 'A', '2' => 'A'], ['1' => 'B', '2' => 'A']) === 'salah', 'jodoh salah');
uji(Penilaian::nilai('pg', ['pilihan' => 'B'], ['pg' => 'B', 'pgk' => [], 'pasangan' => []]) === 'benar', 'nilai pg');

echo "Waktu\n";
$awal = strtotime('2026-11-05 10:00:00');
$baris = [
    'sisa_detik' => 1000,
    'checkpoint_pada' => '2026-11-05 10:00:00',
    'terakhir_ping' => '2026-11-05 10:00:00',
    'status' => 'berjalan',
];
$dekat = WaktuUjian::hitung($baris, $awal + 5);
uji($dekat['sisa_detik'] === 995 && $dekat['putus'] === false, 'detak 5 detik mengurangi waktu');
$jauh = WaktuUjian::hitung($baris, $awal + WaktuUjian::AMBANG_PUTUS + 5);
uji($jauh['sisa_detik'] === 1000 && $jauh['putus'] === true, 'putus tidak mengurangi jeda offline');
$barisJeda = $baris;
$barisJeda['status'] = 'jeda';
$jeda = WaktuUjian::hitung($barisJeda, $awal + 100);
uji($jeda['sisa_detik'] === 1000 && $jeda['putus'] === false, 'status jeda tidak berjalan');

echo "Impor\n";
$contoh = (string) file_get_contents($akar . '/.env.example');
uji(!preg_match('/^CBT_ADMIN_PASSWORD=\S/m', $contoh), 'contoh env tanpa kata sandi');
$soal = Impor::artikan(Impor::bacaCsv($akar . '/templat/soal.csv'));
uji(count($soal) === 3, 'templat berisi 3 soal');
uji($soal[0]['tipe'] === 'pg' && $soal[0]['kunci_pilihan'] === ['B'], 'templat pg');
uji($soal[1]['tipe'] === 'pgk' && $soal[1]['kunci_pilihan'] === ['B', 'D'], 'templat pgk');
uji($soal[2]['tipe'] === 'menjodohkan' && $soal[2]['audio'] === 'listening-contoh.mp3', 'templat menjodohkan dan audio');
uji($soal[2]['pasangan'][0]['kanan_kode'] === 'B' && $soal[2]['pasangan'][1]['kanan_kode'] === 'A', 'kunci menjodohkan');
$kunci = Impor::artikanKunci(Impor::bacaCsv($akar . '/templat/kunci.csv'));
uji(count($kunci) === 3 && $kunci[2]['kunci'] === '1-B;2-A', 'templat kunci');
$docx = tempnam(sys_get_temp_dir(), 'cbt');
file_put_contents((string) $docx, 'bukan word');
try {
    Impor::bacaBerkas((string) $docx, 'soal.docx');
    uji(false, 'word ditolak');
} catch (RuntimeException $e) {
    uji(str_contains($e->getMessage(), 'Word'), 'word ditolak');
}
unlink((string) $docx);

echo "Excel\n";
$tabel = Impor::bacaCsv($akar . '/templat/soal.csv');
$xlsx = tempnam(sys_get_temp_dir(), 'cbtxlsx');
file_put_contents((string) $xlsx, Xlsx::tulis($tabel));
$baca = Xlsx::baca((string) $xlsx);
unlink((string) $xlsx);
uji($baca[0][0] === 'nomor' && $baca[1][1] === 'pg' && $baca[3][3] === 'listening-contoh.mp3', 'excel bolak-balik');

echo "Basis data\n";
try {
    require_once $akar . '/pustaka/bootstrap.php';
    $env = Db::env();
    try {
        $db = Db::pdo();
    } catch (PDOException $e) {
        $server = Db::server();
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . $env['nama'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        Db::lupa();
        $db = Db::pdo();
    }
    jalankanSql($db, (string) file_get_contents($akar . '/sql/skema.sql'));
    $ada = [];
    foreach ($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $baris) {
        $ada[] = $baris[0];
    }
    foreach (['pengawas', 'siswa', 'mapel', 'sesi', 'soal', 'pilihan', 'pasangan', 'pengerjaan', 'jawaban'] as $tabel) {
        uji(in_array($tabel, $ada, true), 'tabel ' . $tabel);
    }
    uji((bool) $db->query("SHOW INDEX FROM jawaban WHERE Key_name = 'uniq_jawaban'")->fetch(), 'indeks jawaban');
    uji((bool) $db->query("SHOW INDEX FROM pengerjaan WHERE Key_name = 'uniq_siswa_sesi'")->fetch(), 'indeks pengerjaan');
    uji((bool) $db->query("SHOW INDEX FROM soal WHERE Key_name = 'idx_soal_sesi'")->fetch(), 'indeks soal');

    $kode = 'U' . strtoupper(bin2hex(random_bytes(3)));
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO mapel (kode, nama) VALUES (?, ?)')->execute([$kode, 'Mapel fiktif']);
        $mapelId = (int) $db->lastInsertId();
        $db->prepare("INSERT INTO sesi (mapel_id, nama, durasi_menit, status) VALUES (?, ?, 30, 'draf')")
            ->execute([$mapelId, 'Sesi uji']);
        $sesiId = (int) $db->lastInsertId();
        $hasilImpor = Impor::simpan($db, $sesiId, $soal, false);
        uji($hasilImpor['jumlah'] === 3, 'tiga tipe masuk ke skema');
        $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $db->prepare('INSERT INTO siswa (username, hash_sandi, nama) VALUES (?, ?, ?)')
            ->execute(['uji' . bin2hex(random_bytes(3)), $hash, 'Siswa Fiktif']);
        $siswaId = (int) $db->lastInsertId();
        $saat = date('Y-m-d H:i:s');
        $db->prepare('INSERT INTO pengerjaan (siswa_id, sesi_id, status, sisa_detik, checkpoint_pada, terakhir_ping) VALUES (?, ?, ?, 1800, ?, ?)')
            ->execute([$siswaId, $sesiId, 'berjalan', $saat, $saat]);
        $pengerjaanId = (int) $db->lastInsertId();
        $cari = $db->prepare('SELECT id, nomor FROM soal WHERE sesi_id = ?');
        $cari->execute([$sesiId]);
        $idSoal = [];
        foreach ($cari as $barisSoal) {
            $idSoal[(int) $barisSoal['nomor']] = (int) $barisSoal['id'];
        }
        $masuk = $db->prepare('INSERT INTO jawaban (pengerjaan_id, soal_id, isi, ragu) VALUES (?, ?, ?, 0)');
        $masuk->execute([$pengerjaanId, $idSoal[1], json_encode(['pilihan' => 'B'])]);
        $masuk->execute([$pengerjaanId, $idSoal[2], json_encode(['pilihan' => ['D', 'B']])]);
        $masuk->execute([$pengerjaanId, $idSoal[3], json_encode(['pasangan' => ['1' => 'B', '2' => 'A']])]);
        $nilai = Layanan::hitungSkor($db, $pengerjaanId);
        uji($nilai['benar'] === 3 && $nilai['salah'] === 0 && $nilai['kosong'] === 0, 'skor tiga tipe benar');
        $db->prepare('UPDATE jawaban SET isi = ? WHERE pengerjaan_id = ? AND soal_id = ?')
            ->execute([json_encode(['pilihan' => 'A']), $pengerjaanId, $idSoal[1]]);
        $nilai = Layanan::hitungSkor($db, $pengerjaanId);
        uji($nilai['benar'] === 2 && $nilai['salah'] === 1 && $nilai['kosong'] === 0, 'pg salah mengurangi skor');
        $db->prepare('DELETE FROM jawaban WHERE pengerjaan_id = ? AND soal_id = ?')->execute([$pengerjaanId, $idSoal[1]]);
        $nilai = Layanan::hitungSkor($db, $pengerjaanId);
        uji($nilai['benar'] === 2 && $nilai['salah'] === 0 && $nilai['kosong'] === 1, 'jawaban hilang dihitung kosong');
        $db->rollBack();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
    $sisaUji = $db->prepare('SELECT COUNT(*) FROM mapel WHERE kode = ?');
    $sisaUji->execute([$kode]);
    uji((int) $sisaUji->fetchColumn() === 0, 'data uji dibatalkan');
} catch (Throwable $e) {
    echo '  GAGAL basis data: ' . $e->getMessage() . "\n";
    $gagal++;
}

echo $gagal === 0 ? "Semua pemeriksaan lulus.\n" : "Ada $gagal kegagalan.\n";
exit($gagal === 0 ? 0 : 1);
