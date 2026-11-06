<?php
declare(strict_types=1);

function e(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8');
}

function basis(): string
{
    return (string) ($GLOBALS['basis'] ?? '');
}

function url(string $rute, array $query = []): string
{
    $query = ['r' => $rute] + $query;
    return basis() . '/index.php?' . http_build_query($query);
}

function aset(string $jalur): string
{
    return basis() . '/' . ltrim($jalur, '/');
}

function arah(string $rute, array $query = []): never
{
    header('Location: ' . url($rute, $query));
    exit;
}

function simpanFlash(string $pesan): void
{
    $_SESSION['flash'] = $pesan;
}

function ambilFlash(): ?string
{
    $pesan = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_string($pesan) && $pesan !== '' ? $pesan : null;
}

function labelTipe(string $tipe): string
{
    return match ($tipe) {
        'pg' => 'Pilihan ganda',
        'pgk' => 'Pilihan ganda kompleks',
        'menjodohkan' => 'Menjodohkan',
        default => $tipe,
    };
}

function labelStatus(string $status): string
{
    return match ($status) {
        'draf' => 'Draf',
        'berjalan' => 'Berjalan',
        'jeda' => 'Jeda',
        'selesai' => 'Selesai',
        default => $status,
    };
}

function formatDetik(int $detik): string
{
    $detik = max(0, $detik);
    $jam = intdiv($detik, 3600);
    $menit = intdiv($detik % 3600, 60);
    $sisa = $detik % 60;
    if ($jam > 0) {
        return sprintf('%d:%02d:%02d', $jam, $menit, $sisa);
    }
    return sprintf('%02d:%02d', $menit, $sisa);
}

function catat(string $pesan): void
{
    $dir = CBT_AKAR . '/storage/log';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $pesan = str_replace(["\r", "\n"], ' ', $pesan);
    file_put_contents($dir . '/aplikasi.log', date('c') . ' ' . $pesan . PHP_EOL, FILE_APPEND);
}

function jsonKeluar(array $data, int $kode = 200): never
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function halamanKe(): int
{
    return max(1, (int) ($_GET['hal'] ?? 1));
}

function teks(string $nilai, int $maks, string $label): string
{
    $nilai = trim($nilai);
    if (mb_strlen($nilai) > $maks) {
        throw new RuntimeException($label . ' terlalu panjang.');
    }
    return $nilai;
}

function namaAudioAman(string $nama): bool
{
    return (bool) preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,119}$/', $nama)
        && !str_contains($nama, '..');
}

function jalurAudio(string $nama): string
{
    return CBT_PUBLIK . '/unggah/audio/' . $nama;
}

function audioAda(string $nama): bool
{
    return namaAudioAman($nama) && is_file(jalurAudio($nama));
}

function simpanAudioUnggah(array $berkas): string
{
    $galat = (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($galat === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($galat !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unggahan audio gagal.');
    }
    if (($berkas['size'] ?? 0) > 8 * 1024 * 1024) {
        throw new RuntimeException('Audio paling besar 8 MB.');
    }
    $ext = strtolower(pathinfo((string) $berkas['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp3', 'ogg', 'wav', 'm4a'], true)) {
        throw new RuntimeException('Audio harus mp3, ogg, wav, atau m4a.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $berkas['tmp_name']);
    $boleh = [
        'audio/mpeg', 'audio/mp3', 'audio/ogg', 'application/ogg',
        'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/mp4', 'audio/x-m4a',
    ];
    if (!in_array($mime, $boleh, true)) {
        throw new RuntimeException('Isi berkas bukan audio.');
    }
    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    $tujuan = jalurAudio($nama);
    if (!is_dir(dirname($tujuan)) || !move_uploaded_file((string) $berkas['tmp_name'], $tujuan)) {
        throw new RuntimeException('Folder audio tidak bisa ditulis. Periksa izin cbt/public/unggah/audio.');
    }
    return $nama;
}

function hapusAudioJikaTakDipakai(PDO $db, string $nama, int $kecualiSoalId): void
{
    if (!namaAudioAman($nama)) {
        return;
    }
    $stmt = $db->prepare('SELECT COUNT(*) FROM soal WHERE audio = ? AND id <> ?');
    $stmt->execute([$nama, $kecualiSoalId]);
    if ((int) $stmt->fetchColumn() === 0 && is_file(jalurAudio($nama))) {
        unlink(jalurAudio($nama));
    }
}

function jalankanSql(PDO $db, string $sql): void
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    $potong = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];
    foreach ($potong as $perintah) {
        $perintah = trim($perintah);
        if ($perintah !== '') {
            $db->exec($perintah);
        }
    }
}

function bacaJson(): array
{
    $mentah = file_get_contents('php://input');
    if ($mentah === false || trim($mentah) === '') {
        return [];
    }
    $data = json_decode($mentah, true);
    if (!is_array($data)) {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    return $data;
}

function kirimCsv(string $namaBerkas, array $baris): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $namaBerkas . '"');
    header('Cache-Control: no-store');
    echo "\xEF\xBB\xBF";
    $keluar = fopen('php://output', 'w');
    if ($keluar === false) {
        throw new RuntimeException('Unduhan gagal.');
    }
    foreach ($baris as $satu) {
        fputcsv($keluar, $satu);
    }
    fclose($keluar);
    exit;
}

function bungkus(string $judul, string $berkas, array $data = [], string $nav = 'siswa'): void
{
    $data['judul'] = $judul;
    $data['nav'] = $nav;
    $data['flash'] = ambilFlash();
    $data['csrf'] = Keamanan::token();
    extract($data, EXTR_SKIP);
    require CBT_AKAR . '/tampilan/awal.php';
    require CBT_AKAR . '/tampilan/' . $berkas . '.php';
    require CBT_AKAR . '/tampilan/akhir.php';
    exit;
}
