<?php
declare(strict_types=1);

final class Impor
{
    public const KOLOM = [
        'nomor', 'tipe', 'pertanyaan', 'audio',
        'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci',
        'kiri_1', 'kanan_1', 'kiri_2', 'kanan_2', 'kiri_3', 'kanan_3',
        'kiri_4', 'kanan_4', 'kiri_5', 'kanan_5',
    ];

    public static function bacaBerkas(string $jalur, string $nama): array
    {
        $ext = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
        if (in_array($ext, ['doc', 'docx'], true)) {
            throw new RuntimeException('Berkas Word tidak didukung. Gunakan templat Excel (.xlsx) atau CSV.');
        }
        if ($ext === 'xls') {
            throw new RuntimeException('Format .xls lama tidak didukung. Simpan sebagai .xlsx atau .csv.');
        }
        if ($ext === 'csv' || $ext === 'txt') {
            return self::bacaCsv($jalur);
        }
        if ($ext === 'xlsx') {
            return Xlsx::baca($jalur);
        }
        throw new RuntimeException('Format tidak dikenal. Gunakan .csv atau .xlsx.');
    }

    public static function artikan(array $tabel): array
    {
        if ($tabel === []) {
            throw new RuntimeException('Berkas kosong.');
        }
        $peta = self::petaKepala(array_shift($tabel), ['nomor', 'tipe', 'pertanyaan', 'kunci']);
        $soal = [];
        $nomorPakai = [];
        $galat = [];
        foreach ($tabel as $i => $baris) {
            if (self::barisKosong($baris)) {
                continue;
            }
            $barisNomor = $i + 2;
            try {
                $item = self::artikanBaris(self::ambil($baris, $peta));
                if (isset($nomorPakai[$item['nomor']])) {
                    throw new RuntimeException('Nomor ' . $item['nomor'] . ' dobel.');
                }
                $nomorPakai[$item['nomor']] = true;
                $soal[] = $item;
            } catch (RuntimeException $e) {
                $galat[] = 'Baris ' . $barisNomor . ': ' . $e->getMessage();
            }
        }
        if ($galat !== []) {
            throw new RuntimeException(implode(' ', array_slice($galat, 0, 8)));
        }
        if ($soal === []) {
            throw new RuntimeException('Tidak ada soal pada berkas.');
        }
        if (count($soal) > 300) {
            throw new RuntimeException('Paling banyak 300 soal sekali impor.');
        }
        return $soal;
    }

    public static function artikanKunci(array $tabel): array
    {
        if ($tabel === []) {
            throw new RuntimeException('Berkas kunci kosong.');
        }
        $peta = self::petaKepala(array_shift($tabel), ['nomor', 'kunci']);
        $hasil = [];
        $galat = [];
        foreach ($tabel as $i => $baris) {
            if (self::barisKosong($baris)) {
                continue;
            }
            try {
                $nilai = self::ambil($baris, $peta);
                $nomor = (int) ($nilai['nomor'] ?? 0);
                $kunci = trim((string) ($nilai['kunci'] ?? ''));
                if ($nomor < 1 || $kunci === '') {
                    throw new RuntimeException('Nomor dan kunci wajib diisi.');
                }
                $hasil[] = ['nomor' => $nomor, 'kunci' => $kunci];
            } catch (RuntimeException $e) {
                $galat[] = 'Baris ' . ($i + 2) . ': ' . $e->getMessage();
            }
        }
        if ($galat !== []) {
            throw new RuntimeException(implode(' ', array_slice($galat, 0, 8)));
        }
        if ($hasil === []) {
            throw new RuntimeException('Tidak ada kunci pada berkas.');
        }
        return $hasil;
    }

    public static function simpan(PDO $db, int $sesiId, array $soal, bool $timpa): array
    {
        $sendiri = !$db->inTransaction();
        if ($sendiri) {
            $db->beginTransaction();
        }
        try {
            if ($timpa) {
                $db->prepare('DELETE FROM soal WHERE sesi_id = ?')->execute([$sesiId]);
            }
            $peringatan = [];
            foreach ($soal as $item) {
                self::simpanSatu($db, $sesiId, $item, null);
                if ($item['audio'] !== '' && !audioAda($item['audio'])) {
                    $peringatan[] = 'Audio ' . $item['audio'] . ' pada nomor ' . $item['nomor'] . ' belum diunggah.';
                }
            }
            if ($sendiri) {
                $db->commit();
            }
            return ['jumlah' => count($soal), 'peringatan' => $peringatan];
        } catch (Throwable $e) {
            if ($sendiri && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function simpanSatu(PDO $db, int $sesiId, array $item, ?int $id): int
    {
        $cari = $db->prepare('SELECT id FROM soal WHERE sesi_id = ? AND nomor = ?');
        $cari->execute([$sesiId, $item['nomor']]);
        $pemilik = (int) ($cari->fetchColumn() ?: 0);
        $audio = $item['audio'] !== '' ? $item['audio'] : null;
        if ($id !== null) {
            if ($pemilik !== 0 && $pemilik !== $id) {
                throw new RuntimeException('Nomor soal sudah dipakai.');
            }
            $ada = $db->prepare('SELECT id FROM soal WHERE id = ? AND sesi_id = ?');
            $ada->execute([$id, $sesiId]);
            if (!$ada->fetchColumn()) {
                throw new RuntimeException('Soal tidak ditemukan.');
            }
            $db->prepare('UPDATE soal SET nomor = ?, tipe = ?, pertanyaan = ?, audio = ? WHERE id = ? AND sesi_id = ?')
                ->execute([$item['nomor'], $item['tipe'], $item['pertanyaan'], $audio, $id, $sesiId]);
            $soalId = $id;
        } elseif ($pemilik !== 0) {
            $db->prepare('UPDATE soal SET tipe = ?, pertanyaan = ?, audio = ? WHERE id = ?')
                ->execute([$item['tipe'], $item['pertanyaan'], $audio, $pemilik]);
            $soalId = $pemilik;
        } else {
            $db->prepare('INSERT INTO soal (sesi_id, nomor, tipe, pertanyaan, audio) VALUES (?, ?, ?, ?, ?)')
                ->execute([$sesiId, $item['nomor'], $item['tipe'], $item['pertanyaan'], $audio]);
            $soalId = (int) $db->lastInsertId();
        }
        $db->prepare('DELETE FROM pilihan WHERE soal_id = ?')->execute([$soalId]);
        $db->prepare('DELETE FROM pasangan WHERE soal_id = ?')->execute([$soalId]);
        if ($item['tipe'] === 'menjodohkan') {
            $masuk = $db->prepare('INSERT INTO pasangan (soal_id, kiri_kode, kiri_teks, kanan_kode, kanan_teks, urutan) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($item['pasangan'] as $i => $pasangan) {
                $masuk->execute([
                    $soalId,
                    $pasangan['kiri_kode'],
                    $pasangan['kiri_teks'],
                    $pasangan['kanan_kode'],
                    $pasangan['kanan_teks'],
                    $i + 1,
                ]);
            }
        } else {
            $masuk = $db->prepare('INSERT INTO pilihan (soal_id, kode, teks, benar, urutan) VALUES (?, ?, ?, ?, ?)');
            $kunci = array_fill_keys($item['kunci_pilihan'], true);
            foreach ($item['opsi'] as $i => $opsi) {
                $masuk->execute([$soalId, $opsi[0], $opsi[1], isset($kunci[$opsi[0]]) ? 1 : 0, $i + 1]);
            }
        }
        return $soalId;
    }

    public static function perbaruiKunci(PDO $db, int $sesiId, array $daftar): int
    {
        $sendiri = !$db->inTransaction();
        if ($sendiri) {
            $db->beginTransaction();
        }
        try {
            foreach ($daftar as $item) {
                $soal = $db->prepare('SELECT id, tipe FROM soal WHERE sesi_id = ? AND nomor = ?');
                $soal->execute([$sesiId, $item['nomor']]);
                $baris = $soal->fetch();
                if (!$baris) {
                    throw new RuntimeException('Soal nomor ' . $item['nomor'] . ' tidak ada pada sesi ini.');
                }
                self::terapkanKunci($db, (int) $baris['id'], (string) $baris['tipe'], (string) $item['kunci']);
            }
            if ($sendiri) {
                $db->commit();
            }
            return count($daftar);
        } catch (Throwable $e) {
            if ($sendiri && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function bacaCsv(string $jalur): array
    {
        $pegangan = fopen($jalur, 'rb');
        if ($pegangan === false) {
            throw new RuntimeException('Berkas CSV tidak bisa dibaca.');
        }
        $awal = (string) fgets($pegangan);
        $awal = preg_replace('/^\xEF\xBB\xBF/', '', $awal) ?? $awal;
        $koma = substr_count($awal, ',');
        $titikKoma = substr_count($awal, ';');
        $pemisah = $titikKoma > $koma ? ';' : ',';
        rewind($pegangan);
        $tabel = [];
        while (($baris = fgetcsv($pegangan, 0, $pemisah)) !== false) {
            if ($tabel === [] && isset($baris[0])) {
                $baris[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $baris[0]) ?? (string) $baris[0];
            }
            $tabel[] = array_map(static fn ($sel) => (string) $sel, $baris);
        }
        fclose($pegangan);
        return $tabel;
    }

    private static function artikanBaris(array $baris): array
    {
        $nomor = (int) ($baris['nomor'] ?? 0);
        if ($nomor < 1 || $nomor > 500) {
            throw new RuntimeException('Nomor soal tidak valid.');
        }
        $tipe = self::tipe((string) ($baris['tipe'] ?? ''));
        $pertanyaan = trim((string) ($baris['pertanyaan'] ?? ''));
        if ($pertanyaan === '') {
            throw new RuntimeException('Pertanyaan kosong.');
        }
        if (mb_strlen($pertanyaan) > 8000) {
            throw new RuntimeException('Pertanyaan terlalu panjang.');
        }
        $audio = trim((string) ($baris['audio'] ?? ''));
        if ($audio !== '' && !namaAudioAman($audio)) {
            throw new RuntimeException('Nama berkas audio tidak valid.');
        }
        $ekstensi = strtolower(pathinfo($audio, PATHINFO_EXTENSION));
        if ($audio !== '' && !in_array($ekstensi, ['mp3', 'ogg', 'wav', 'm4a'], true)) {
            throw new RuntimeException('Audio harus mp3, ogg, wav, atau m4a.');
        }
        if ($tipe === 'menjodohkan') {
            return [
                'nomor' => $nomor,
                'tipe' => $tipe,
                'pertanyaan' => $pertanyaan,
                'audio' => $audio,
                'opsi' => [],
                'kunci_pilihan' => [],
                'pasangan' => self::pasangan($baris),
            ];
        }
        $opsi = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $kode) {
            $teks = trim((string) ($baris['opsi_' . strtolower($kode)] ?? ''));
            if ($teks === '') {
                continue;
            }
            if (mb_strlen($teks) > 2000) {
                throw new RuntimeException('Teks pilihan terlalu panjang.');
            }
            $opsi[] = [$kode, $teks];
        }
        if (count($opsi) < 2) {
            throw new RuntimeException('Minimal dua pilihan.');
        }
        $kunci = self::pecahPilihan((string) ($baris['kunci'] ?? ''));
        $ada = array_column($opsi, 0);
        foreach ($kunci as $kode) {
            if (!in_array($kode, $ada, true)) {
                throw new RuntimeException('Kunci ' . $kode . ' tidak ada pada pilihan.');
            }
        }
        if ($tipe === 'pg' && count($kunci) !== 1) {
            throw new RuntimeException('Pilihan ganda memakai tepat satu kunci.');
        }
        if ($tipe === 'pgk' && count($kunci) < 2) {
            throw new RuntimeException('Pilihan ganda kompleks membutuhkan minimal dua kunci.');
        }
        return [
            'nomor' => $nomor,
            'tipe' => $tipe,
            'pertanyaan' => $pertanyaan,
            'audio' => $audio,
            'opsi' => $opsi,
            'kunci_pilihan' => $kunci,
            'pasangan' => [],
        ];
    }

    private static function pasangan(array $baris): array
    {
        $kiri = [];
        $kanan = [];
        for ($i = 1; $i <= 5; $i++) {
            $teksKiri = trim((string) ($baris['kiri_' . $i] ?? ''));
            $teksKanan = trim((string) ($baris['kanan_' . $i] ?? ''));
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
        $peta = self::pecahJodoh((string) ($baris['kunci'] ?? ''));
        if ($peta === []) {
            foreach ($kiri as $i => $item) {
                $peta[$item[0]] = $kanan[$i][0];
            }
        }
        $teksKiri = [];
        foreach ($kiri as $item) {
            $teksKiri[$item[0]] = $item[1];
        }
        $teksKanan = [];
        foreach ($kanan as $item) {
            $teksKanan[$item[0]] = $item[1];
        }
        if (count($peta) !== count($kiri) || count(array_unique($peta)) !== count($peta)) {
            throw new RuntimeException('Kunci menjodohkan harus memasangkan semua butir kiri, masing-masing sekali.');
        }
        $hasil = [];
        foreach ($teksKiri as $kode => $teks) {
            $tujuan = $peta[$kode] ?? '';
            if ($tujuan === '' || !isset($teksKanan[$tujuan])) {
                throw new RuntimeException('Kunci menjodohkan tidak cocok dengan butir.');
            }
            $hasil[] = [
                'kiri_kode' => $kode,
                'kiri_teks' => $teks,
                'kanan_kode' => $tujuan,
                'kanan_teks' => $teksKanan[$tujuan],
            ];
        }
        return $hasil;
    }

    public static function pecahPilihan(string $kunci): array
    {
        $bagian = preg_split('/[\s,;|]+/', strtoupper(trim($kunci))) ?: [];
        $hasil = [];
        foreach ($bagian as $kode) {
            if ($kode !== '') {
                $hasil[$kode] = $kode;
            }
        }
        return array_values($hasil);
    }

    public static function pecahJodoh(string $kunci): array
    {
        $kunci = trim($kunci);
        if ($kunci === '') {
            return [];
        }
        $peta = [];
        foreach (preg_split('/[;,]+/', $kunci) ?: [] as $bagian) {
            if (trim($bagian) === '') {
                continue;
            }
            if (!preg_match('/^\s*([A-Za-z0-9]+)\s*[-=:]\s*([A-Za-z0-9]+)\s*$/', $bagian, $cocok)) {
                throw new RuntimeException('Format kunci menjodohkan tidak dikenal. Contoh: 1-B;2-A');
            }
            $peta[strtoupper($cocok[1])] = strtoupper($cocok[2]);
        }
        return $peta;
    }

    private static function terapkanKunci(PDO $db, int $soalId, string $tipe, string $kunci): void
    {
        if ($tipe === 'menjodohkan') {
            $stmt = $db->prepare('SELECT kiri_kode, kiri_teks, kanan_kode, kanan_teks FROM pasangan WHERE soal_id = ? ORDER BY urutan, id');
            $stmt->execute([$soalId]);
            $baris = $stmt->fetchAll();
            $teksKiri = [];
            $teksKanan = [];
            foreach ($baris as $pasangan) {
                $teksKiri[(string) $pasangan['kiri_kode']] = (string) $pasangan['kiri_teks'];
                $teksKanan[(string) $pasangan['kanan_kode']] = (string) $pasangan['kanan_teks'];
            }
            $peta = self::pecahJodoh($kunci);
            if (count($peta) !== count($teksKiri) || count(array_unique($peta)) !== count($peta)) {
                throw new RuntimeException('Kunci menjodohkan tidak lengkap.');
            }
            $db->prepare('DELETE FROM pasangan WHERE soal_id = ?')->execute([$soalId]);
            $masuk = $db->prepare('INSERT INTO pasangan (soal_id, kiri_kode, kiri_teks, kanan_kode, kanan_teks, urutan) VALUES (?, ?, ?, ?, ?, ?)');
            $urutan = 1;
            foreach ($teksKiri as $kode => $teks) {
                $tujuan = $peta[$kode] ?? '';
                if ($tujuan === '' || !isset($teksKanan[$tujuan])) {
                    throw new RuntimeException('Kunci menjodohkan tidak cocok.');
                }
                $masuk->execute([$soalId, $kode, $teks, $tujuan, $teksKanan[$tujuan], $urutan]);
                $urutan++;
            }
            return;
        }
        $kode = self::pecahPilihan($kunci);
        if ($tipe === 'pg' && count($kode) !== 1) {
            throw new RuntimeException('Pilihan ganda memakai tepat satu kunci.');
        }
        if ($tipe === 'pgk' && count($kode) < 2) {
            throw new RuntimeException('Pilihan ganda kompleks membutuhkan minimal dua kunci.');
        }
        $ada = $db->prepare('SELECT id FROM pilihan WHERE soal_id = ? AND kode = ?');
        $tandai = $db->prepare('UPDATE pilihan SET benar = 1 WHERE soal_id = ? AND kode = ?');
        $db->prepare('UPDATE pilihan SET benar = 0 WHERE soal_id = ?')->execute([$soalId]);
        foreach ($kode as $satu) {
            $ada->execute([$soalId, $satu]);
            if (!$ada->fetchColumn()) {
                throw new RuntimeException('Kunci ' . $satu . ' tidak ada pada soal.');
            }
            $tandai->execute([$soalId, $satu]);
        }
    }

    private static function tipe(string $nilai): string
    {
        $nilai = strtolower(trim($nilai));
        $nilai = str_replace([' ', '_'], '', $nilai);
        return match ($nilai) {
            'pg', 'pilihanganda' => 'pg',
            'pgk', 'pilihangandakompleks', 'kompleks' => 'pgk',
            'menjodohkan', 'jodoh', 'matching' => 'menjodohkan',
            default => throw new RuntimeException('Tipe harus pg, pgk, atau menjodohkan.'),
        };
    }

    private static function petaKepala(array $kepala, array $wajib): array
    {
        $peta = [];
        foreach ($kepala as $i => $nama) {
            $nama = strtolower(trim((string) $nama));
            if ($nama !== '') {
                $peta[$nama] = $i;
            }
        }
        foreach ($wajib as $nama) {
            if (!array_key_exists($nama, $peta)) {
                throw new RuntimeException('Kolom ' . $nama . ' tidak ada. Unduh templatnya.');
            }
        }
        return $peta;
    }

    private static function ambil(array $baris, array $peta): array
    {
        $hasil = [];
        foreach ($peta as $nama => $indeks) {
            $hasil[$nama] = (string) ($baris[$indeks] ?? '');
        }
        return $hasil;
    }

    private static function barisKosong(array $baris): bool
    {
        foreach ($baris as $sel) {
            if (trim((string) $sel) !== '') {
                return false;
            }
        }
        return true;
    }
}
