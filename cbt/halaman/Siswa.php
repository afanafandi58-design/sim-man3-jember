<?php
declare(strict_types=1);

function tanganiSiswa(string $rute, string $metode): void
{
    switch ($rute) {
        case 'masuk':
            siswaMasuk($metode);
            return;
        case 'keluar':
            if ($metode !== 'POST') {
                throw new RuntimeException('Permintaan tidak valid.');
            }
            Keamanan::sahkan();
            unset($_SESSION['siswa_id'], $_SESSION['pengerjaan_id'], $_SESSION['sesi_id']);
            arah('masuk');
        case 'token':
            siswaToken($metode);
            return;
        case 'ujian':
            siswaUjian();
            return;
        case 'selesai':
            siswaSelesai();
            return;
        default:
            return;
    }
}

function wajibSiswa(): array
{
    $id = (int) ($_SESSION['siswa_id'] ?? 0);
    if ($id < 1) {
        arah('masuk');
    }
    $stmt = Db::pdo()->prepare('SELECT * FROM siswa WHERE id = ? AND aktif = 1');
    $stmt->execute([$id]);
    $siswa = $stmt->fetch();
    if (!$siswa) {
        unset($_SESSION['siswa_id'], $_SESSION['pengerjaan_id'], $_SESSION['sesi_id']);
        arah('masuk');
    }
    return $siswa;
}

function pengerjaanAktif(bool $json = false): array
{
    $id = (int) ($_SESSION['pengerjaan_id'] ?? 0);
    $siswaId = (int) ($_SESSION['siswa_id'] ?? 0);
    if ($id < 1 || $siswaId < 1) {
        if ($json) {
            jsonKeluar(['ok' => false, 'pesan' => 'Token ujian belum dimasukkan.', 'masuk' => false], 401);
        }
        arah('token');
    }
    $stmt = Db::pdo()->prepare('SELECT * FROM pengerjaan WHERE id = ? AND siswa_id = ?');
    $stmt->execute([$id, $siswaId]);
    $baris = $stmt->fetch();
    if (!$baris) {
        unset($_SESSION['pengerjaan_id'], $_SESSION['sesi_id']);
        if ($json) {
            jsonKeluar(['ok' => false, 'pesan' => 'Percobaan tidak ditemukan. Masukkan token lagi.'], 404);
        }
        simpanFlash('Percobaan tidak ditemukan. Masukkan token lagi.');
        arah('token');
    }
    return $baris;
}

function siswaMasuk(string $metode): void
{
    if (!empty($_SESSION['siswa_id'])) {
        arah('token');
    }
    if ($metode === 'POST') {
        Keamanan::sahkan();
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $sandi = (string) ($_POST['sandi'] ?? '');
        $stmt = Db::pdo()->prepare('SELECT * FROM siswa WHERE username = ? AND aktif = 1');
        $stmt->execute([$username]);
        $siswa = $stmt->fetch();
        if (!$siswa || !password_verify($sandi, (string) $siswa['hash_sandi'])) {
            simpanFlash('Nama pengguna atau kata sandi salah.');
            arah('masuk');
        }
        session_regenerate_id(true);
        $_SESSION['siswa_id'] = (int) $siswa['id'];
        unset($_SESSION['pengerjaan_id'], $_SESSION['sesi_id']);
        arah('token');
    }
    bungkus('Masuk ujian', 'masuk');
}

function siswaToken(string $metode): void
{
    $siswa = wajibSiswa();
    if ($metode === 'POST') {
        Keamanan::sahkan();
        $token = strtoupper(preg_replace('/\s+/', '', (string) ($_POST['token'] ?? '')) ?? '');
        $stmt = Db::pdo()->prepare('SELECT s.* FROM sesi s WHERE s.token = ?');
        $stmt->execute([$token]);
        $sesi = $stmt->fetch();
        if (!$sesi || $sesi['status'] !== 'berjalan') {
            simpanFlash('Token tidak berlaku atau ujian belum berjalan.');
            arah('token');
        }
        $ada = Db::pdo()->prepare('SELECT * FROM pengerjaan WHERE siswa_id = ? AND sesi_id = ?');
        $ada->execute([(int) $siswa['id'], (int) $sesi['id']]);
        $pengerjaan = $ada->fetch();
        if ($pengerjaan && $pengerjaan['status'] === 'selesai') {
            simpanFlash('Ujian Anda sudah dikumpulkan. Hubungi pengawas jika perlu diulang.');
            arah('token');
        }
        if (!$pengerjaan) {
            $saat = Layanan::sekarang();
            $sisa = (int) $sesi['durasi_menit'] * 60;
            Db::pdo()->prepare('INSERT INTO pengerjaan (siswa_id, sesi_id, status, sisa_detik, checkpoint_pada, terakhir_ping) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([(int) $siswa['id'], (int) $sesi['id'], 'berjalan', $sisa, $saat, $saat]);
            $pengerjaanId = (int) Db::pdo()->lastInsertId();
        } else {
            $pengerjaanId = (int) $pengerjaan['id'];
        }
        $_SESSION['pengerjaan_id'] = $pengerjaanId;
        $_SESSION['sesi_id'] = (int) $sesi['id'];
        arah('ujian');
    }
    bungkus('Token ujian', 'token', ['siswa' => $siswa]);
}

function siswaUjian(): void
{
    $siswa = wajibSiswa();
    $pengerjaan = pengerjaanAktif();
    if ($pengerjaan['status'] === 'selesai') {
        arah('selesai');
    }
    $sesi = Db::pdo()->prepare('SELECT s.nama AS nama_sesi, m.nama AS nama_mapel FROM sesi s JOIN mapel m ON m.id = s.mapel_id WHERE s.id = ?');
    $sesi->execute([(int) $pengerjaan['sesi_id']]);
    $info = $sesi->fetch() ?: ['nama_mapel' => 'Ujian', 'nama_sesi' => ''];
    $hitung = WaktuUjian::hitung($pengerjaan, time());
    $csrf = Keamanan::token();
    $sisa = $hitung['sisa_detik'];
    require CBT_AKAR . '/tampilan/ujian.php';
    exit;
}

function siswaSelesai(): void
{
    $siswa = wajibSiswa();
    $id = (int) ($_SESSION['pengerjaan_id'] ?? 0);
    $stmt = Db::pdo()->prepare('SELECT p.*, m.nama AS nama_mapel FROM pengerjaan p JOIN sesi s ON s.id = p.sesi_id JOIN mapel m ON m.id = s.mapel_id WHERE p.id = ? AND p.siswa_id = ?');
    $stmt->execute([$id, (int) $siswa['id']]);
    $pengerjaan = $stmt->fetch();
    if (!$pengerjaan) {
        arah('token');
    }
    if ($pengerjaan['status'] !== 'selesai') {
        arah('ujian');
    }
    bungkus('Ujian selesai', 'selesai', ['siswa' => $siswa, 'pengerjaan' => $pengerjaan]);
}
