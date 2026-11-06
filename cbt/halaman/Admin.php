<?php
declare(strict_types=1);

function tanganiAdmin(string $rute, string $metode): void
{
    if ($rute === 'admin/masuk') {
        adminMasuk($metode);
        return;
    }
    if ($rute === 'admin/keluar') {
        if ($metode !== 'POST') {
            throw new RuntimeException('Permintaan tidak valid.');
        }
        Keamanan::sahkan();
        unset($_SESSION['pengawas_id']);
        arah('admin/masuk');
    }
    wajibPengawas();
    switch ($rute) {
        case 'admin/mapel':
            adminMapel();
            return;
        case 'admin/mapel/simpan':
            simpanMapel($metode);
            return;
        case 'admin/mapel/hapus':
            hapusMapel($metode);
            return;
        case 'admin/sesi':
            adminSesi();
            return;
        case 'admin/sesi/simpan':
            simpanSesi($metode);
            return;
        case 'admin/sesi/mulai':
            mulaiSesi($metode);
            return;
        case 'admin/sesi/selesai':
            selesaiSesi($metode);
            return;
        case 'admin/sesi/reset':
            resetSesi($metode);
            return;
        case 'admin/sesi/hapus':
            hapusSesi($metode);
            return;
        case 'admin/pengerjaan/reset':
            resetPengerjaan($metode);
            return;
        case 'admin/soal':
            adminSoal();
            return;
        case 'admin/soal/form':
            formSoal();
            return;
        case 'admin/soal/simpan':
            simpanSoal($metode);
            return;
        case 'admin/soal/hapus':
            hapusSoal($metode);
            return;
        case 'admin/impor':
            if ($metode === 'POST') {
                prosesImpor();
            }
            adminImpor();
            return;
        case 'admin/kunci':
            prosesKunci($metode);
            return;
        case 'admin/templat':
            unduhTemplat();
            return;
        case 'admin/siswa':
            adminSiswa();
            return;
        case 'admin/siswa/simpan':
            simpanSiswa($metode);
            return;
        case 'admin/siswa/hapus':
            hapusSiswa($metode);
            return;
        case 'admin/analisis':
            adminAnalisis();
            return;
        case 'admin/unduh':
            unduhData();
            return;
        default:
            return;
    }
}

function wajibPengawas(): array
{
    $id = (int) ($_SESSION['pengawas_id'] ?? 0);
    if ($id < 1) {
        arah('admin/masuk');
    }
    $stmt = Db::pdo()->prepare('SELECT * FROM pengawas WHERE id = ?');
    $stmt->execute([$id]);
    $pengawas = $stmt->fetch();
    if (!$pengawas) {
        unset($_SESSION['pengawas_id']);
        arah('admin/masuk');
    }
    return $pengawas;
}

function ambilMapel(PDO $db, int $id): array
{
    $stmt = $db->prepare('SELECT * FROM mapel WHERE id = ?');
    $stmt->execute([$id]);
    $baris = $stmt->fetch();
    if (!$baris) {
        throw new RuntimeException('Mata pelajaran tidak ditemukan.');
    }
    return $baris;
}

function ambilSesi(PDO $db, int $id): array
{
    $stmt = $db->prepare('SELECT s.*, m.nama AS nama_mapel, m.kode AS kode_mapel, m.aktif AS mapel_aktif FROM sesi s JOIN mapel m ON m.id = s.mapel_id WHERE s.id = ?');
    $stmt->execute([$id]);
    $baris = $stmt->fetch();
    if (!$baris) {
        throw new RuntimeException('Sesi tidak ditemukan.');
    }
    return $baris;
}

function idSesi(): int
{
    $id = (int) ($_POST['sesi_id'] ?? $_GET['sesi'] ?? 0);
    if ($id < 1) {
        throw new RuntimeException('Sesi tidak dipilih.');
    }
    return $id;
}

function adminMasuk(string $metode): void
{
    if (!empty($_SESSION['pengawas_id'])) {
        arah('admin/mapel');
    }
    if ($metode === 'POST') {
        Keamanan::sahkan();
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $sandi = (string) ($_POST['sandi'] ?? '');
        $stmt = Db::pdo()->prepare('SELECT * FROM pengawas WHERE username = ?');
        $stmt->execute([$username]);
        $pengawas = $stmt->fetch();
        if (!$pengawas || !password_verify($sandi, (string) $pengawas['hash_sandi'])) {
            simpanFlash('Nama pengguna atau kata sandi salah.');
            arah('admin/masuk');
        }
        session_regenerate_id(true);
        $_SESSION['pengawas_id'] = (int) $pengawas['id'];
        arah('admin/mapel');
    }
    bungkus('Masuk pengawas', 'admin-masuk', [], 'kosong');
}

function adminMapel(): void
{
    $db = Db::pdo();
    $edit = null;
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $edit = ambilMapel($db, $id);
    }
    $daftar = $db->query('SELECT * FROM mapel ORDER BY nama')->fetchAll();
    bungkus('Mata pelajaran', 'admin-mapel', ['daftar' => $daftar, 'edit' => $edit], 'admin');
}

function simpanMapel(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    try {
        $id = (int) ($_POST['id'] ?? 0);
        $kode = strtoupper(teks((string) ($_POST['kode'] ?? ''), 32, 'Kode'));
        $nama = teks((string) ($_POST['nama'] ?? ''), 160, 'Nama');
        if (!preg_match('/^[A-Z0-9_-]{2,32}$/', $kode) || $nama === '') {
            throw new RuntimeException('Kode dan nama mata pelajaran wajib diisi.');
        }
        $aktif = empty($_POST['aktif']) ? 0 : 1;
        $db = Db::pdo();
        if ($id > 0) {
            ambilMapel($db, $id);
            $db->prepare('UPDATE mapel SET kode = ?, nama = ?, aktif = ? WHERE id = ?')->execute([$kode, $nama, $aktif, $id]);
        } else {
            $db->prepare('INSERT INTO mapel (kode, nama, aktif) VALUES (?, ?, ?)')->execute([$kode, $nama, $aktif]);
        }
        simpanFlash('Mata pelajaran disimpan.');
    } catch (PDOException $e) {
        if ((string) $e->getCode() !== '23000') {
            throw $e;
        }
        simpanFlash('Kode mata pelajaran sudah dipakai.');
    } catch (RuntimeException $e) {
        simpanFlash($e->getMessage());
    }
    arah('admin/mapel');
}

function hapusMapel(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $id = (int) ($_POST['id'] ?? 0);
    $ada = Db::pdo()->prepare('SELECT COUNT(*) FROM sesi WHERE mapel_id = ?');
    $ada->execute([$id]);
    if ((int) $ada->fetchColumn() > 0) {
        simpanFlash('Hapus sesi ujian mata pelajaran ini terlebih dahulu.');
    } else {
        Db::pdo()->prepare('DELETE FROM mapel WHERE id = ?')->execute([$id]);
        simpanFlash('Mata pelajaran dihapus.');
    }
    arah('admin/mapel');
}

function adminSesi(): void
{
    $db = Db::pdo();
    $mapel = ambilMapel($db, (int) ($_GET['mapel'] ?? 0));
    $sesi = $db->prepare('SELECT * FROM sesi WHERE mapel_id = ? ORDER BY id DESC');
    $sesi->execute([(int) $mapel['id']]);
    $daftar = $sesi->fetchAll();
    $hitungSoal = $db->prepare('SELECT COUNT(*) FROM soal WHERE sesi_id = ?');
    $pesertaStmt = $db->prepare('SELECT p.*, s.nama, s.nis, s.username FROM pengerjaan p JOIN siswa s ON s.id = p.siswa_id WHERE p.sesi_id = ? ORDER BY s.nama');
    foreach ($daftar as $i => $baris) {
        $hitungSoal->execute([(int) $baris['id']]);
        $daftar[$i]['jumlah_soal'] = (int) $hitungSoal->fetchColumn();
        $pesertaStmt->execute([(int) $baris['id']]);
        $orang = [];
        foreach ($pesertaStmt->fetchAll() as $orangBaris) {
            $orang[] = Layanan::efektif($orangBaris);
        }
        $daftar[$i]['peserta'] = $orang;
    }
    bungkus($mapel['nama'], 'admin-sesi', ['mapel' => $mapel, 'daftar' => $daftar], 'admin');
}

function simpanSesi(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $mapelId = (int) ($_POST['mapel_id'] ?? 0);
    try {
        $db = Db::pdo();
        ambilMapel($db, $mapelId);
        $nama = teks((string) ($_POST['nama'] ?? ''), 160, 'Nama sesi');
        $durasi = (int) ($_POST['durasi_menit'] ?? 0);
        if ($nama === '' || $durasi < 1 || $durasi > 300) {
            throw new RuntimeException('Nama sesi dan durasi (1–300 menit) wajib diisi.');
        }
        $db->prepare('INSERT INTO sesi (mapel_id, nama, durasi_menit) VALUES (?, ?, ?)')->execute([$mapelId, $nama, $durasi]);
        simpanFlash('Sesi ujian dibuat.');
    } catch (RuntimeException $e) {
        simpanFlash($e->getMessage());
    }
    arah('admin/sesi', ['mapel' => $mapelId]);
}

function mulaiSesi(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $jumlah = $db->prepare('SELECT COUNT(*) FROM soal WHERE sesi_id = ?');
    $jumlah->execute([(int) $sesi['id']]);
    if ((int) $jumlah->fetchColumn() < 1) {
        simpanFlash('Tambah soal sebelum ujian dimulai.');
    } elseif ((int) $sesi['mapel_aktif'] !== 1) {
        simpanFlash('Mata pelajaran ini nonaktif.');
    } elseif ($sesi['status'] === 'berjalan') {
        simpanFlash('Ujian sudah berjalan.');
    } else {
        $token = Layanan::tokenBaru($db);
        $db->prepare("UPDATE sesi SET status = 'berjalan', token = ?, dimulai_pada = ?, selesai_pada = NULL WHERE id = ?")
            ->execute([$token, Layanan::sekarang(), (int) $sesi['id']]);
        simpanFlash('Ujian dimulai. Token tampil di halaman sesi.');
    }
    arah('admin/sesi', ['mapel' => (int) $sesi['mapel_id']]);
}

function selesaiSesi(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $db->prepare("UPDATE sesi SET status = 'selesai', selesai_pada = ? WHERE id = ?")
        ->execute([Layanan::sekarang(), (int) $sesi['id']]);
    simpanFlash('Ujian ditutup. Token disembunyikan.');
    arah('admin/sesi', ['mapel' => (int) $sesi['mapel_id']]);
}

function resetSesi(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $db->beginTransaction();
    try {
        $db->prepare('DELETE FROM pengerjaan WHERE sesi_id = ?')->execute([(int) $sesi['id']]);
        if ($sesi['status'] === 'berjalan') {
            $token = Layanan::tokenBaru($db);
            $db->prepare('UPDATE sesi SET token = ? WHERE id = ?')->execute([$token, (int) $sesi['id']]);
            $pesan = 'Seluruh percobaan dihapus. Token baru sudah terbit.';
        } else {
            $db->prepare("UPDATE sesi SET status = 'draf', token = NULL, dimulai_pada = NULL, selesai_pada = NULL WHERE id = ?")
                ->execute([(int) $sesi['id']]);
            $pesan = 'Sesi direset ke draf.';
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
    simpanFlash($pesan);
    arah('admin/sesi', ['mapel' => (int) $sesi['mapel_id']]);
}

function hapusSesi(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $db->beginTransaction();
    $db->prepare('DELETE FROM pengerjaan WHERE sesi_id = ?')->execute([(int) $sesi['id']]);
    $db->prepare('DELETE FROM sesi WHERE id = ?')->execute([(int) $sesi['id']]);
    $db->commit();
    simpanFlash('Sesi dihapus.');
    arah('admin/sesi', ['mapel' => (int) $sesi['mapel_id']]);
}

function resetPengerjaan(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $id = (int) ($_POST['pengerjaan_id'] ?? 0);
    $db->prepare('DELETE FROM pengerjaan WHERE id = ? AND sesi_id = ?')->execute([$id, (int) $sesi['id']]);
    simpanFlash('Percobaan siswa direset. Siswa dapat masuk lagi dengan token yang sama.');
    arah('admin/sesi', ['mapel' => (int) $sesi['mapel_id']]);
}

function adminSoal(): void
{
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_GET['sesi'] ?? 0));
    $per = 15;
    $hal = halamanKe();
    $jumlahStmt = $db->prepare('SELECT COUNT(*) FROM soal WHERE sesi_id = ?');
    $jumlahStmt->execute([(int) $sesi['id']]);
    $jumlah = (int) $jumlahStmt->fetchColumn();
    $offset = ($hal - 1) * $per;
    $stmt = $db->prepare('SELECT id, nomor, tipe, pertanyaan, audio FROM soal WHERE sesi_id = ? ORDER BY nomor LIMIT ? OFFSET ?');
    $stmt->bindValue(1, (int) $sesi['id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $per, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    bungkus('Soal', 'admin-soal', [
        'sesi' => $sesi,
        'daftar' => $stmt->fetchAll(),
        'hal' => $hal,
        'jumlah' => $jumlah,
        'per' => $per,
    ], 'admin');
}

function formSoal(): void
{
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_GET['sesi'] ?? 0));
    $id = (int) ($_GET['id'] ?? 0);
    $soal = null;
    $pilihan = [];
    $benar = [];
    $pasangan = [];
    $nomor = 1;
    if ($id > 0) {
        $stmt = $db->prepare('SELECT * FROM soal WHERE id = ? AND sesi_id = ?');
        $stmt->execute([$id, (int) $sesi['id']]);
        $soal = $stmt->fetch();
        if (!$soal) {
            throw new RuntimeException('Soal tidak ditemukan.');
        }
        $nomor = (int) $soal['nomor'];
        $opsi = $db->prepare('SELECT kode, teks, benar FROM pilihan WHERE soal_id = ? ORDER BY urutan');
        $opsi->execute([$id]);
        foreach ($opsi as $baris) {
            $pilihan[$baris['kode']] = $baris['teks'];
            if ((int) $baris['benar'] === 1) {
                $benar[] = $baris['kode'];
            }
        }
        $jodoh = $db->prepare('SELECT * FROM pasangan WHERE soal_id = ? ORDER BY urutan');
        $jodoh->execute([$id]);
        $pasangan = $jodoh->fetchAll();
    } else {
        $maks = $db->prepare('SELECT COALESCE(MAX(nomor), 0) FROM soal WHERE sesi_id = ?');
        $maks->execute([(int) $sesi['id']]);
        $nomor = (int) $maks->fetchColumn() + 1;
    }
    bungkus($soal ? 'Ubah soal' : 'Soal baru', 'admin-soal-form', [
        'sesi' => $sesi,
        'soal' => $soal,
        'pilihan' => $pilihan,
        'benar' => $benar,
        'pasangan' => $pasangan,
        'nomor' => $nomor,
    ], 'admin');
}

function simpanSoal(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $unggah = '';
    try {
        $item = itemDariForm();
        $unggah = (string) ($item['unggah'] ?? '');
        unset($item['unggah']);
        $id = (int) ($_POST['id'] ?? 0);
        $lama = (string) ($_POST['audio_lama'] ?? '');
        $soalId = Impor::simpanSatu($db, (int) $sesi['id'], $item, $id > 0 ? $id : null);
        if (namaAudioAman($lama) && $lama !== $item['audio']) {
            hapusAudioJikaTakDipakai($db, $lama, $soalId);
        }
        simpanFlash('Soal disimpan.');
    } catch (RuntimeException $e) {
        if ($unggah !== '' && is_file(jalurAudio($unggah))) {
            unlink(jalurAudio($unggah));
        }
        simpanFlash($e->getMessage());
    }
    arah('admin/soal', ['sesi' => (int) $sesi['id']]);
}

function itemDariForm(): array
{
    $tipe = (string) ($_POST['tipe'] ?? '');
    if (!in_array($tipe, ['pg', 'pgk', 'menjodohkan'], true)) {
        throw new RuntimeException('Tipe soal tidak valid.');
    }
    $nomor = (int) ($_POST['nomor'] ?? 0);
    $pertanyaan = teks((string) ($_POST['pertanyaan'] ?? ''), 8000, 'Pertanyaan');
    if ($nomor < 1 || $pertanyaan === '') {
        throw new RuntimeException('Nomor dan pertanyaan wajib diisi.');
    }
    $unggah = simpanAudioUnggah($_FILES['audio'] ?? []);
    $audio = $unggah;
    if ($audio === '' && empty($_POST['hapus_audio'])) {
        $lama = (string) ($_POST['audio_lama'] ?? '');
        if (namaAudioAman($lama)) {
            $audio = $lama;
        }
    }
    if ($tipe === 'menjodohkan') {
        $pasangan = [];
        $kiri = [];
        $kanan = [];
        for ($i = 1; $i <= 5; $i++) {
            $teksKiri = teks((string) ($_POST['kiri_' . $i] ?? ''), 1000, 'Butir kiri');
            $teksKanan = teks((string) ($_POST['kanan_' . $i] ?? ''), 1000, 'Butir kanan');
            if ($teksKiri === '' && $teksKanan === '') {
                continue;
            }
            if ($teksKiri === '' || $teksKanan === '') {
                throw new RuntimeException('Pasangan kiri dan kanan harus sama-sama diisi.');
            }
            $kiri[] = [(string) (count($kiri) + 1), $teksKiri];
            $kanan[] = [chr(ord('A') + count($kanan)), $teksKanan];
        }
        if (count($kiri) < 2) {
            throw new RuntimeException('Menjodohkan membutuhkan minimal dua pasangan.');
        }
        foreach ($kiri as $i => $item) {
            $pasangan[] = [
                'kiri_kode' => $item[0],
                'kiri_teks' => $item[1],
                'kanan_kode' => $kanan[$i][0],
                'kanan_teks' => $kanan[$i][1],
            ];
        }
        return [
            'nomor' => $nomor,
            'tipe' => $tipe,
            'pertanyaan' => $pertanyaan,
            'audio' => $audio,
            'opsi' => [],
            'kunci_pilihan' => [],
            'pasangan' => $pasangan,
            'unggah' => $unggah,
        ];
    }
    $opsi = [];
    foreach (['A', 'B', 'C', 'D', 'E'] as $kode) {
        $teksOpsi = teks((string) ($_POST['opsi_' . strtolower($kode)] ?? ''), 2000, 'Pilihan');
        if ($teksOpsi !== '') {
            $opsi[] = [$kode, $teksOpsi];
        }
    }
    if (count($opsi) < 2) {
        throw new RuntimeException('Minimal dua pilihan.');
    }
    if ($tipe === 'pg') {
        $kunci = [strtoupper(trim((string) ($_POST['benar_pg'] ?? '')))];
    } else {
        $mentah = $_POST['benar_pgk'] ?? [];
        $kunci = array_map(static fn ($k): string => strtoupper(trim((string) $k)), is_array($mentah) ? $mentah : []);
    }
    $kunci = array_values(array_filter($kunci, static fn (string $k): bool => $k !== ''));
    $ada = array_column($opsi, 0);
    foreach ($kunci as $kode) {
        if (!in_array($kode, $ada, true)) {
            throw new RuntimeException('Kunci tidak ada pada pilihan.');
        }
    }
    if ($tipe === 'pg' && count($kunci) !== 1) {
        throw new RuntimeException('Pilih satu jawaban benar.');
    }
    if ($tipe === 'pgk' && count($kunci) < 2) {
        throw new RuntimeException('Pilih minimal dua jawaban benar.');
    }
    return [
        'nomor' => $nomor,
        'tipe' => $tipe,
        'pertanyaan' => $pertanyaan,
        'audio' => $audio,
        'opsi' => $opsi,
        'kunci_pilihan' => $kunci,
        'pasangan' => [],
        'unggah' => $unggah,
    ];
}

function hapusSoal(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    $id = (int) ($_POST['id'] ?? 0);
    $audio = $db->prepare('SELECT audio FROM soal WHERE id = ? AND sesi_id = ?');
    $audio->execute([$id, (int) $sesi['id']]);
    $nama = (string) ($audio->fetchColumn() ?: '');
    $db->prepare('DELETE FROM soal WHERE id = ? AND sesi_id = ?')->execute([$id, (int) $sesi['id']]);
    if (namaAudioAman($nama)) {
        hapusAudioJikaTakDipakai($db, $nama, 0);
    }
    simpanFlash('Soal dihapus.');
    arah('admin/soal', ['sesi' => (int) $sesi['id']]);
}

function adminImpor(): void
{
    $sesi = ambilSesi(Db::pdo(), (int) ($_GET['sesi'] ?? $_POST['sesi_id'] ?? 0));
    bungkus('Impor soal', 'admin-impor', ['sesi' => $sesi], 'admin');
}

function prosesImpor(): void
{
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    try {
        $berkas = $_FILES['berkas'] ?? null;
        if (!is_array($berkas) || (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Pilih berkas CSV atau Excel.');
        }
        if ((int) $berkas['size'] > 2 * 1024 * 1024) {
            throw new RuntimeException('Berkas paling besar 2 MB.');
        }
        $tabel = Impor::bacaBerkas((string) $berkas['tmp_name'], (string) $berkas['name']);
        $soal = Impor::artikan($tabel);
        $hasil = Impor::simpan($db, (int) $sesi['id'], $soal, !empty($_POST['timpa']));
        $pesan = $hasil['jumlah'] . ' soal tersimpan.';
        if ($hasil['peringatan'] !== []) {
            $pesan .= ' ' . implode(' ', array_slice($hasil['peringatan'], 0, 4));
        }
        simpanFlash($pesan);
    } catch (RuntimeException $e) {
        simpanFlash($e->getMessage());
    }
    arah('admin/impor', ['sesi' => (int) $sesi['id']]);
}

function prosesKunci(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_POST['sesi_id'] ?? 0));
    try {
        $berkas = $_FILES['berkas'] ?? null;
        if (!is_array($berkas) || (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Pilih berkas kunci CSV atau Excel.');
        }
        $tabel = Impor::bacaBerkas((string) $berkas['tmp_name'], (string) $berkas['name']);
        $jumlah = Impor::perbaruiKunci($db, (int) $sesi['id'], Impor::artikanKunci($tabel));
        simpanFlash($jumlah . ' kunci diperbarui.');
    } catch (RuntimeException $e) {
        simpanFlash($e->getMessage());
    }
    arah('admin/impor', ['sesi' => (int) $sesi['id']]);
}

function unduhTemplat(): void
{
    $jenis = (string) ($_GET['jenis'] ?? 'soal');
    $format = (string) ($_GET['format'] ?? 'csv');
    if (!in_array($jenis, ['soal', 'kunci'], true) || !in_array($format, ['csv', 'xlsx'], true)) {
        throw new RuntimeException('Templat tidak dikenal.');
    }
    $jalur = CBT_AKAR . '/templat/' . $jenis . '.csv';
    $nama = ($jenis === 'kunci' ? 'templat-kunci' : 'templat-soal') . '.' . $format;
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nama . '"');
        header('Cache-Control: no-store');
        readfile($jalur);
        exit;
    }
    $bytes = Xlsx::tulis(Impor::bacaCsv($jalur));
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Cache-Control: no-store');
    echo $bytes;
    exit;
}

function adminSiswa(): void
{
    $db = Db::pdo();
    $per = 25;
    $hal = halamanKe();
    $jumlah = (int) $db->query('SELECT COUNT(*) FROM siswa')->fetchColumn();
    $offset = ($hal - 1) * $per;
    $stmt = $db->prepare('SELECT id, username, nama, nis, aktif FROM siswa ORDER BY nama LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $per, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $edit = null;
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $cari = $db->prepare('SELECT id, username, nama, nis, aktif FROM siswa WHERE id = ?');
        $cari->execute([$id]);
        $edit = $cari->fetch() ?: null;
    }
    bungkus('Siswa', 'admin-siswa', [
        'daftar' => $stmt->fetchAll(),
        'edit' => $edit,
        'hal' => $hal,
        'jumlah' => $jumlah,
        'per' => $per,
    ], 'admin');
}

function simpanSiswa(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    try {
        $id = (int) ($_POST['id'] ?? 0);
        $username = strtolower(teks((string) ($_POST['username'] ?? ''), 32, 'Nama pengguna'));
        $nama = teks((string) ($_POST['nama'] ?? ''), 120, 'Nama');
        $nis = teks((string) ($_POST['nis'] ?? ''), 32, 'NIS');
        $sandi = (string) ($_POST['sandi'] ?? '');
        $aktif = empty($_POST['aktif']) ? 0 : 1;
        if (!preg_match('/^[a-z0-9._]{3,32}$/', $username) || $nama === '') {
            throw new RuntimeException('Nama pengguna (huruf kecil, angka, titik) dan nama wajib diisi.');
        }
        $db = Db::pdo();
        if ($id > 0) {
            if ($sandi !== '' && strlen($sandi) < 6) {
                throw new RuntimeException('Kata sandi minimal 6 karakter.');
            }
            if ($sandi !== '') {
                $db->prepare('UPDATE siswa SET username = ?, nama = ?, nis = ?, aktif = ?, hash_sandi = ? WHERE id = ?')
                    ->execute([$username, $nama, $nis, $aktif, password_hash($sandi, PASSWORD_DEFAULT), $id]);
            } else {
                $db->prepare('UPDATE siswa SET username = ?, nama = ?, nis = ?, aktif = ? WHERE id = ?')
                    ->execute([$username, $nama, $nis, $aktif, $id]);
            }
        } else {
            if (strlen($sandi) < 6) {
                throw new RuntimeException('Kata sandi minimal 6 karakter.');
            }
            $db->prepare('INSERT INTO siswa (username, hash_sandi, nama, nis, aktif) VALUES (?, ?, ?, ?, ?)')
                ->execute([$username, password_hash($sandi, PASSWORD_DEFAULT), $nama, $nis, $aktif]);
        }
        simpanFlash('Siswa disimpan.');
    } catch (PDOException $e) {
        if ((string) $e->getCode() !== '23000') {
            throw $e;
        }
        simpanFlash('Nama pengguna sudah dipakai.');
    } catch (RuntimeException $e) {
        simpanFlash($e->getMessage());
    }
    arah('admin/siswa');
}

function hapusSiswa(string $metode): void
{
    if ($metode !== 'POST') {
        throw new RuntimeException('Permintaan tidak valid.');
    }
    Keamanan::sahkan();
    $id = (int) ($_POST['id'] ?? 0);
    $db = Db::pdo();
    $db->beginTransaction();
    $db->prepare('DELETE FROM pengerjaan WHERE siswa_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM siswa WHERE id = ?')->execute([$id]);
    $db->commit();
    simpanFlash('Siswa dan percobaannya dihapus.');
    arah('admin/siswa');
}

function adminAnalisis(): void
{
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_GET['sesi'] ?? 0));
    $analisis = Layanan::analisis($db, (int) $sesi['id']);
    bungkus('Analisis', 'admin-analisis', ['sesi' => $sesi, 'analisis' => $analisis], 'admin');
}

function unduhData(): void
{
    $db = Db::pdo();
    $sesi = ambilSesi($db, (int) ($_GET['sesi'] ?? 0));
    $jenis = (string) ($_GET['jenis'] ?? '');
    if ($jenis === 'analisis') {
        $analisis = Layanan::analisis($db, (int) $sesi['id']);
        $baris = [['nomor', 'tipe', 'benar', 'salah', 'kosong', 'persen_benar', 'peringkat_sulit', 'jumlah_peserta']];
        foreach ($analisis['soal'] as $soal) {
            $baris[] = [
                $soal['nomor'],
                labelTipe($soal['tipe']),
                $soal['benar'],
                $soal['salah'],
                $soal['kosong'],
                $soal['persen'],
                $analisis['tersulit'][$soal['nomor']] ?? '',
                $analisis['peserta'],
            ];
        }
        kirimCsv('analisis-' . $sesi['kode_mapel'] . '-sesi-' . $sesi['id'] . '.csv', $baris);
    }
    [$hasil, $jawaban] = rekapSesi($db, (int) $sesi['id']);
    if ($jenis === 'hasil') {
        array_unshift($hasil, ['nis', 'nama', 'username', 'status', 'skor', 'benar', 'salah', 'kosong', 'selesai']);
        kirimCsv('hasil-sesi-' . $sesi['id'] . '.csv', $hasil);
    }
    if ($jenis === 'jawaban') {
        array_unshift($jawaban, ['nis', 'nama', 'nomor', 'tipe', 'jawaban', 'kunci', 'hasil', 'ragu']);
        kirimCsv('jawaban-sesi-' . $sesi['id'] . '.csv', $jawaban);
    }
    throw new RuntimeException('Unduhan tidak dikenal.');
}

function rekapSesi(PDO $db, int $sesiId): array
{
    $soalStmt = $db->prepare('SELECT id, nomor, tipe FROM soal WHERE sesi_id = ? ORDER BY nomor');
    $soalStmt->execute([$sesiId]);
    $daftarSoal = $soalStmt->fetchAll();
    $kunci = Layanan::kunciSoal($db, array_map(static fn (array $s): int => (int) $s['id'], $daftarSoal));
    $orangStmt = $db->prepare('SELECT p.id, p.status, p.selesai_pada, s.nis, s.nama, s.username FROM pengerjaan p JOIN siswa s ON s.id = p.siswa_id WHERE p.sesi_id = ? ORDER BY s.nama, s.id');
    $orangStmt->execute([$sesiId]);
    $peserta = $orangStmt->fetchAll();
    $peta = [];
    if ($peserta !== []) {
        $ids = array_map(static fn (array $p): int => (int) $p['id'], $peserta);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $jawabanStmt = $db->prepare("SELECT pengerjaan_id, soal_id, isi, ragu FROM jawaban WHERE pengerjaan_id IN ($in)");
        $jawabanStmt->execute($ids);
        foreach ($jawabanStmt as $baris) {
            $peta[(int) $baris['pengerjaan_id']][(int) $baris['soal_id']] = $baris;
        }
    }
    $hasil = [];
    $jawaban = [];
    foreach ($peserta as $orang) {
        $benar = 0;
        $salah = 0;
        $kosong = 0;
        foreach ($daftarSoal as $soal) {
            $soalId = (int) $soal['id'];
            $baris = $peta[(int) $orang['id']][$soalId] ?? null;
            $isi = $baris ? Layanan::isi($baris['isi']) : null;
            $nilai = Penilaian::nilai((string) $soal['tipe'], $isi, $kunci[$soalId] ?? []);
            if ($nilai === 'benar') {
                $benar++;
            } elseif ($nilai === 'salah') {
                $salah++;
            } else {
                $kosong++;
            }
            $jawaban[] = [
                $orang['nis'],
                $orang['nama'],
                $soal['nomor'],
                labelTipe((string) $soal['tipe']),
                Layanan::teksJawaban($isi, (string) $soal['tipe']),
                Layanan::teksKunci($kunci[$soalId] ?? [], (string) $soal['tipe']),
                $nilai,
                $baris ? (int) $baris['ragu'] : 0,
            ];
        }
        $hasil[] = [
            $orang['nis'],
            $orang['nama'],
            $orang['username'],
            labelStatus((string) $orang['status']),
            $benar,
            $benar,
            $salah,
            $kosong,
            (string) ($orang['selesai_pada'] ?? ''),
        ];
    }
    return [$hasil, $jawaban];
}
