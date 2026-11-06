<?php
declare(strict_types=1);

final class Layanan
{
    public static function sekarang(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function tokenBaru(PDO $db): string
    {
        $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $panjang = strlen($abjad) - 1;
        for ($coba = 0; $coba < 8; $coba++) {
            $token = '';
            for ($i = 0; $i < 6; $i++) {
                $token .= $abjad[random_int(0, $panjang)];
            }
            $ada = $db->prepare('SELECT id FROM sesi WHERE token = ?');
            $ada->execute([$token]);
            if (!$ada->fetchColumn()) {
                return $token;
            }
        }
        throw new RuntimeException('Token belum bisa dibuat. Coba lagi.');
    }

    public static function isi(mixed $isi): ?array
    {
        if ($isi === null) {
            return null;
        }
        if (is_array($isi)) {
            return $isi;
        }
        $data = json_decode((string) $isi, true);
        return is_array($data) ? $data : null;
    }

    public static function kunciSoal(PDO $db, array $soalId): array
    {
        $hasil = [];
        foreach ($soalId as $id) {
            $hasil[(int) $id] = ['pg' => '', 'pgk' => [], 'pasangan' => []];
        }
        if ($soalId === []) {
            return $hasil;
        }
        $in = implode(',', array_fill(0, count($soalId), '?'));
        $nilai = array_values($soalId);
        $pilihan = $db->prepare("SELECT soal_id, kode, benar FROM pilihan WHERE soal_id IN ($in) ORDER BY urutan, id");
        $pilihan->execute($nilai);
        foreach ($pilihan as $baris) {
            if ((int) $baris['benar'] !== 1) {
                continue;
            }
            $id = (int) $baris['soal_id'];
            $hasil[$id]['pg'] = (string) $baris['kode'];
            $hasil[$id]['pgk'][] = (string) $baris['kode'];
        }
        $pasangan = $db->prepare("SELECT soal_id, kiri_kode, kanan_kode FROM pasangan WHERE soal_id IN ($in) ORDER BY urutan, id");
        $pasangan->execute($nilai);
        foreach ($pasangan as $baris) {
            $hasil[(int) $baris['soal_id']]['pasangan'][(string) $baris['kiri_kode']] = (string) $baris['kanan_kode'];
        }
        return $hasil;
    }

    public static function hitungSkor(PDO $db, int $pengerjaanId): array
    {
        $sesi = $db->prepare('SELECT sesi_id FROM pengerjaan WHERE id = ?');
        $sesi->execute([$pengerjaanId]);
        $sesiId = (int) $sesi->fetchColumn();
        $soal = $db->prepare('SELECT id, nomor, tipe FROM soal WHERE sesi_id = ? ORDER BY nomor');
        $soal->execute([$sesiId]);
        $daftar = $soal->fetchAll();
        $kunci = self::kunciSoal($db, array_map(static fn (array $s): int => (int) $s['id'], $daftar));
        $jawaban = $db->prepare('SELECT soal_id, isi FROM jawaban WHERE pengerjaan_id = ?');
        $jawaban->execute([$pengerjaanId]);
        $peta = [];
        foreach ($jawaban as $baris) {
            $peta[(int) $baris['soal_id']] = self::isi($baris['isi']);
        }
        $benar = 0;
        $salah = 0;
        $kosong = 0;
        $rinci = [];
        foreach ($daftar as $item) {
            $id = (int) $item['id'];
            $hasil = Penilaian::nilai((string) $item['tipe'], $peta[$id] ?? null, $kunci[$id] ?? []);
            if ($hasil === 'benar') {
                $benar++;
            } elseif ($hasil === 'salah') {
                $salah++;
            } else {
                $kosong++;
            }
            $rinci[$id] = $hasil;
        }
        return ['skor' => $benar, 'benar' => $benar, 'salah' => $salah, 'kosong' => $kosong, 'rinci' => $rinci];
    }

    public static function selesaikan(PDO $db, int $pengerjaanId): array
    {
        $sendiri = !$db->inTransaction();
        if ($sendiri) {
            $db->beginTransaction();
        }
        $kunci = $db->prepare('SELECT * FROM pengerjaan WHERE id = ? FOR UPDATE');
        $kunci->execute([$pengerjaanId]);
        $baris = $kunci->fetch();
        if (!$baris) {
            if ($sendiri && $db->inTransaction()) {
                $db->rollBack();
            }
            throw new RuntimeException('Pengerjaan tidak ditemukan.');
        }
        if ($baris['status'] === 'selesai') {
            if ($sendiri) {
                $db->commit();
            }
            return $baris;
        }
        $nilai = self::hitungSkor($db, $pengerjaanId);
        $saat = self::sekarang();
        $db->prepare('UPDATE pengerjaan SET status = ?, sisa_detik = 0, checkpoint_pada = ?, terakhir_ping = ?, skor = ?, jumlah_benar = ?, jumlah_salah = ?, jumlah_kosong = ?, selesai_pada = ? WHERE id = ?')
            ->execute(['selesai', $saat, $saat, $nilai['skor'], $nilai['benar'], $nilai['salah'], $nilai['kosong'], $saat, $pengerjaanId]);
        if ($sendiri) {
            $db->commit();
        }
        $baris['status'] = 'selesai';
        $baris['sisa_detik'] = 0;
        $baris['skor'] = $nilai['skor'];
        $baris['jumlah_benar'] = $nilai['benar'];
        $baris['jumlah_salah'] = $nilai['salah'];
        $baris['jumlah_kosong'] = $nilai['kosong'];
        $baris['selesai_pada'] = $saat;
        return $baris;
    }

    public static function sinkronWaktu(PDO $db, int $pengerjaanId, int $siswaId, string $aksi): array
    {
        $sendiri = !$db->inTransaction();
        if ($sendiri) {
            $db->beginTransaction();
        }
        try {
            $kunci = $db->prepare('SELECT * FROM pengerjaan WHERE id = ? AND siswa_id = ? FOR UPDATE');
            $kunci->execute([$pengerjaanId, $siswaId]);
            $baris = $kunci->fetch();
            if (!$baris) {
                throw new RuntimeException('Sesi ujian tidak ditemukan.');
            }
            if ($baris['status'] !== 'selesai') {
                if (!self::sesiBerjalan($db, (int) $baris['sesi_id'])) {
                    $baris = self::selesaikan($db, (int) $baris['id']);
                } elseif (!($baris['status'] === 'jeda' && $aksi === 'detak')) {
                    $hitung = WaktuUjian::hitung($baris, time());
                    if ($hitung['sisa_detik'] <= 0) {
                        $baris = self::selesaikan($db, (int) $baris['id']);
                    } else {
                        $status = $aksi === 'jeda' ? 'jeda' : 'berjalan';
                        $saat = self::sekarang();
                        $db->prepare('UPDATE pengerjaan SET status = ?, sisa_detik = ?, checkpoint_pada = ?, terakhir_ping = ? WHERE id = ?')
                            ->execute([$status, $hitung['sisa_detik'], $saat, $saat, (int) $baris['id']]);
                        $baris['status'] = $status;
                        $baris['sisa_detik'] = $hitung['sisa_detik'];
                        $baris['checkpoint_pada'] = $saat;
                        $baris['terakhir_ping'] = $saat;
                    }
                }
            }
            if ($sendiri) {
                $db->commit();
            }
            return $baris;
        } catch (Throwable $e) {
            if ($sendiri && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function efektif(array $baris): array
    {
        $hitung = WaktuUjian::hitung($baris, time());
        $baris['sisa_efektif'] = $hitung['sisa_detik'];
        $baris['putus'] = $hitung['putus'];
        if ($baris['status'] === 'berjalan' && $hitung['putus']) {
            $baris['status_tampil'] = 'jeda';
        } else {
            $baris['status_tampil'] = (string) $baris['status'];
        }
        return $baris;
    }

    public static function sesiBerjalan(PDO $db, int $sesiId): bool
    {
        $stmt = $db->prepare('SELECT status FROM sesi WHERE id = ?');
        $stmt->execute([$sesiId]);
        return $stmt->fetchColumn() === 'berjalan';
    }

    public static function simpanJawaban(PDO $db, array $pengerjaan, array $daftar): int
    {
        if ($pengerjaan['status'] === 'selesai') {
            throw new RuntimeException('Ujian sudah dikumpulkan.');
        }
        if (!self::sesiBerjalan($db, (int) $pengerjaan['sesi_id'])) {
            self::selesaikan($db, (int) $pengerjaan['id']);
            throw new RuntimeException('Ujian sudah ditutup.');
        }
        $hitung = WaktuUjian::hitung($pengerjaan, time());
        if ($hitung['sisa_detik'] <= 0) {
            self::selesaikan($db, (int) $pengerjaan['id']);
            throw new RuntimeException('Waktu habis.');
        }
        if (count($daftar) > 40) {
            throw new RuntimeException('Terlalu banyak jawaban sekaligus.');
        }
        $idSoal = [];
        foreach ($daftar as $item) {
            $idSoal[] = (int) ($item['soal_id'] ?? 0);
        }
        $idSoal = array_values(array_unique(array_filter($idSoal)));
        if ($idSoal === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($idSoal), '?'));
        $soalStmt = $db->prepare("SELECT id, tipe FROM soal WHERE sesi_id = ? AND id IN ($in)");
        $soalStmt->execute(array_merge([(int) $pengerjaan['sesi_id']], $idSoal));
        $soal = [];
        foreach ($soalStmt as $baris) {
            $soal[(int) $baris['id']] = (string) $baris['tipe'];
        }
        $pilihan = self::kodePilihan($db, $idSoal);
        $jodoh = self::kodeJodoh($db, $idSoal);
        $masuk = $db->prepare('INSERT INTO jawaban (pengerjaan_id, soal_id, isi, ragu) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE isi = ?, ragu = ?');
        $jumlah = 0;
        $sendiri = !$db->inTransaction();
        if ($sendiri) {
            $db->beginTransaction();
        }
        try {
            foreach ($daftar as $item) {
                $soalId = (int) ($item['soal_id'] ?? 0);
                if (!isset($soal[$soalId])) {
                    throw new RuntimeException('Soal tidak termasuk ujian ini.');
                }
                $isi = $item['isi'] ?? [];
                if (!is_array($isi)) {
                    throw new RuntimeException('Jawaban tidak valid.');
                }
                $bersih = self::saringIsi($soal[$soalId], $isi, $pilihan[$soalId] ?? [], $jodoh[$soalId]['kiri'] ?? [], $jodoh[$soalId]['kanan'] ?? []);
                $json = json_encode($bersih, JSON_UNESCAPED_UNICODE);
                $ragu = !empty($item['ragu']) ? 1 : 0;
                $masuk->execute([(int) $pengerjaan['id'], $soalId, $json, $ragu, $json, $ragu]);
                $jumlah++;
            }
            if ($sendiri) {
                $db->commit();
            }
        } catch (Throwable $e) {
            if ($sendiri && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
        return $jumlah;
    }

    public static function paketSoal(PDO $db, array $pengerjaan, int $nomor): array
    {
        if ($pengerjaan['status'] !== 'selesai') {
            if (!self::sesiBerjalan($db, (int) $pengerjaan['sesi_id'])) {
                $pengerjaan = self::selesaikan($db, (int) $pengerjaan['id']);
            } else {
                $hitung = WaktuUjian::hitung($pengerjaan, time());
                if ($hitung['sisa_detik'] <= 0) {
                    $pengerjaan = self::selesaikan($db, (int) $pengerjaan['id']);
                } else {
                    $pengerjaan['sisa_detik'] = $hitung['sisa_detik'];
                }
            }
        }
        if ($pengerjaan['status'] === 'selesai') {
            return ['ok' => true, 'status' => 'selesai', 'sisa_detik' => 0];
        }
        $totalStmt = $db->prepare('SELECT COUNT(*) FROM soal WHERE sesi_id = ?');
        $totalStmt->execute([(int) $pengerjaan['sesi_id']]);
        $total = (int) $totalStmt->fetchColumn();
        if ($total < 1) {
            throw new RuntimeException('Sesi ini belum memiliki soal.');
        }
        $nomor = min(max(1, $nomor), $total);
        $soalStmt = $db->prepare('SELECT id, nomor, tipe, pertanyaan, audio FROM soal WHERE sesi_id = ? AND nomor = ?');
        $soalStmt->execute([(int) $pengerjaan['sesi_id'], $nomor]);
        $soal = $soalStmt->fetch();
        if (!$soal) {
            $soalStmt = $db->prepare('SELECT id, nomor, tipe, pertanyaan, audio FROM soal WHERE sesi_id = ? ORDER BY nomor LIMIT 1 OFFSET ?');
            $soalStmt->execute([(int) $pengerjaan['sesi_id'], $nomor - 1]);
            $soal = $soalStmt->fetch();
        }
        if (!$soal) {
            throw new RuntimeException('Soal tidak ditemukan.');
        }
        $jawabanStmt = $db->prepare('SELECT isi, ragu FROM jawaban WHERE pengerjaan_id = ? AND soal_id = ?');
        $jawabanStmt->execute([(int) $pengerjaan['id'], (int) $soal['id']]);
        $tersimpan = $jawabanStmt->fetch() ?: null;
        $audio = (string) ($soal['audio'] ?? '');
        $paket = [
            'ok' => true,
            'status' => $pengerjaan['status'],
            'sisa_detik' => (int) $pengerjaan['sisa_detik'],
            'id' => (int) $soal['id'],
            'nomor' => (int) $soal['nomor'],
            'total' => $total,
            'tipe' => (string) $soal['tipe'],
            'pertanyaan' => (string) $soal['pertanyaan'],
            'audio' => $audio !== '' && audioAda($audio) ? $audio : null,
            'audio_hilang' => $audio !== '' && !audioAda($audio),
            'pilihan' => [],
            'kiri' => [],
            'kanan' => [],
            'jawaban' => $tersimpan ? self::isi($tersimpan['isi']) : null,
            'ragu' => $tersimpan ? (int) $tersimpan['ragu'] : 0,
            'daftar' => self::daftarNomor($db, (int) $pengerjaan['id'], (int) $pengerjaan['sesi_id']),
        ];
        if ($soal['tipe'] === 'menjodohkan') {
            $pasangan = $db->prepare('SELECT kiri_kode, kiri_teks, kanan_kode, kanan_teks FROM pasangan WHERE soal_id = ? ORDER BY urutan, id');
            $pasangan->execute([(int) $soal['id']]);
            $kiri = [];
            $kanan = [];
            foreach ($pasangan as $baris) {
                $kiri[] = ['kode' => (string) $baris['kiri_kode'], 'teks' => (string) $baris['kiri_teks']];
                $kanan[] = ['kode' => (string) $baris['kanan_kode'], 'teks' => (string) $baris['kanan_teks']];
            }
            shuffle($kanan);
            $paket['kiri'] = $kiri;
            $paket['kanan'] = $kanan;
        } else {
            $pilihan = $db->prepare('SELECT kode, teks FROM pilihan WHERE soal_id = ? ORDER BY urutan, id');
            $pilihan->execute([(int) $soal['id']]);
            foreach ($pilihan as $baris) {
                $paket['pilihan'][] = ['kode' => (string) $baris['kode'], 'teks' => (string) $baris['teks']];
            }
        }
        return $paket;
    }

    public static function daftarNomor(PDO $db, int $pengerjaanId, int $sesiId): array
    {
        $stmt = $db->prepare('SELECT s.id, s.nomor, s.tipe, j.isi, j.ragu
            FROM soal s
            LEFT JOIN jawaban j ON j.soal_id = s.id AND j.pengerjaan_id = ?
            WHERE s.sesi_id = ?
            ORDER BY s.nomor');
        $stmt->execute([$pengerjaanId, $sesiId]);
        $daftar = [];
        foreach ($stmt as $baris) {
            $isi = self::isi($baris['isi']);
            $daftar[] = [
                'id' => (int) $baris['id'],
                'nomor' => (int) $baris['nomor'],
                'tipe' => (string) $baris['tipe'],
                'ragu' => (int) ($baris['ragu'] ?? 0),
                'terjawab' => self::terisi($isi) ? 1 : 0,
            ];
        }
        return $daftar;
    }

    public static function analisis(PDO $db, int $sesiId): array
    {
        $pesertaStmt = $db->prepare('SELECT id FROM pengerjaan WHERE sesi_id = ?');
        $pesertaStmt->execute([$sesiId]);
        $peserta = array_map(static fn (array $b): int => (int) $b['id'], $pesertaStmt->fetchAll());
        $soalStmt = $db->prepare('SELECT id, nomor, tipe, pertanyaan FROM soal WHERE sesi_id = ? ORDER BY nomor');
        $soalStmt->execute([$sesiId]);
        $soal = $soalStmt->fetchAll();
        $kunci = self::kunciSoal($db, array_map(static fn (array $s): int => (int) $s['id'], $soal));
        $peta = [];
        if ($peserta !== []) {
            $in = implode(',', array_fill(0, count($peserta), '?'));
            $jawaban = $db->prepare("SELECT pengerjaan_id, soal_id, isi FROM jawaban WHERE pengerjaan_id IN ($in)");
            $jawaban->execute($peserta);
            foreach ($jawaban as $baris) {
                $peta[(int) $baris['pengerjaan_id']][(int) $baris['soal_id']] = self::isi($baris['isi']);
            }
        }
        $baris = [];
        foreach ($soal as $item) {
            $benar = 0;
            $salah = 0;
            $kosong = 0;
            foreach ($peserta as $pid) {
                $hasil = Penilaian::nilai((string) $item['tipe'], $peta[$pid][(int) $item['id']] ?? null, $kunci[(int) $item['id']] ?? []);
                if ($hasil === 'benar') {
                    $benar++;
                } elseif ($hasil === 'salah') {
                    $salah++;
                } else {
                    $kosong++;
                }
            }
            $jumlah = count($peserta);
            $baris[] = [
                'nomor' => (int) $item['nomor'],
                'tipe' => (string) $item['tipe'],
                'pertanyaan' => (string) $item['pertanyaan'],
                'benar' => $benar,
                'salah' => $salah,
                'kosong' => $kosong,
                'persen' => $jumlah > 0 ? (int) round($benar * 100 / $jumlah) : 0,
            ];
        }
        $urut = $baris;
        usort($urut, static function (array $a, array $b): int {
            return [$a['persen'], -$a['salah'], $a['nomor']] <=> [$b['persen'], -$b['salah'], $b['nomor']];
        });
        $tersulit = [];
        if (count($peserta) > 0) {
            foreach (array_slice($urut, 0, 3) as $i => $item) {
                $tersulit[$item['nomor']] = $i + 1;
            }
        }
        return [
            'peserta' => count($peserta),
            'soal' => $baris,
            'tersulit' => $tersulit,
        ];
    }

    public static function teksJawaban(?array $isi, string $tipe): string
    {
        if ($isi === null) {
            return '';
        }
        if ($tipe === 'pg') {
            return (string) ($isi['pilihan'] ?? '');
        }
        if ($tipe === 'pgk') {
            $pilihan = $isi['pilihan'] ?? [];
            return is_array($pilihan) ? implode(',', $pilihan) : '';
        }
        $pasangan = $isi['pasangan'] ?? [];
        if (!is_array($pasangan)) {
            return '';
        }
        $bagian = [];
        foreach ($pasangan as $kiri => $kanan) {
            if ((string) $kanan !== '') {
                $bagian[] = $kiri . '-' . $kanan;
            }
        }
        return implode(';', $bagian);
    }

    public static function teksKunci(array $kunci, string $tipe): string
    {
        if ($tipe === 'pg') {
            return (string) ($kunci['pg'] ?? '');
        }
        if ($tipe === 'pgk') {
            return implode(',', $kunci['pgk'] ?? []);
        }
        $bagian = [];
        foreach ($kunci['pasangan'] ?? [] as $kiri => $kanan) {
            $bagian[] = $kiri . '-' . $kanan;
        }
        return implode(';', $bagian);
    }

    private static function terisi(?array $isi): bool
    {
        if ($isi === null) {
            return false;
        }
        if (isset($isi['pilihan']) && is_string($isi['pilihan'])) {
            return $isi['pilihan'] !== '';
        }
        if (isset($isi['pilihan']) && is_array($isi['pilihan'])) {
            return $isi['pilihan'] !== [];
        }
        if (isset($isi['pasangan']) && is_array($isi['pasangan'])) {
            foreach ($isi['pasangan'] as $kanan) {
                if (trim((string) $kanan) !== '') {
                    return true;
                }
            }
        }
        return false;
    }

    private static function saringIsi(string $tipe, array $isi, array $kodePilihan, array $kiri, array $kanan): array
    {
        if ($tipe === 'pg') {
            $pilihan = strtoupper(trim((string) ($isi['pilihan'] ?? '')));
            if ($pilihan !== '' && !in_array($pilihan, $kodePilihan, true)) {
                throw new RuntimeException('Pilihan tidak dikenal.');
            }
            return ['pilihan' => $pilihan];
        }
        if ($tipe === 'pgk') {
            $pilihan = $isi['pilihan'] ?? [];
            if (!is_array($pilihan)) {
                $pilihan = [];
            }
            $bersih = [];
            foreach ($pilihan as $kode) {
                $kode = strtoupper(trim((string) $kode));
                if ($kode === '') {
                    continue;
                }
                if (!in_array($kode, $kodePilihan, true)) {
                    throw new RuntimeException('Pilihan tidak dikenal.');
                }
                $bersih[$kode] = $kode;
            }
            return ['pilihan' => array_values($bersih)];
        }
        $pasangan = $isi['pasangan'] ?? [];
        if (!is_array($pasangan)) {
            $pasangan = [];
        }
        $bersih = [];
        foreach ($kiri as $kode) {
            $nilai = strtoupper(trim((string) ($pasangan[$kode] ?? '')));
            if ($nilai !== '' && !in_array($nilai, $kanan, true)) {
                throw new RuntimeException('Pasangan tidak dikenal.');
            }
            $bersih[$kode] = $nilai;
        }
        return ['pasangan' => $bersih];
    }

    private static function kodePilihan(PDO $db, array $soalId): array
    {
        if ($soalId === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($soalId), '?'));
        $stmt = $db->prepare("SELECT soal_id, kode FROM pilihan WHERE soal_id IN ($in)");
        $stmt->execute(array_values($soalId));
        $hasil = [];
        foreach ($stmt as $baris) {
            $hasil[(int) $baris['soal_id']][] = (string) $baris['kode'];
        }
        return $hasil;
    }

    private static function kodeJodoh(PDO $db, array $soalId): array
    {
        if ($soalId === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($soalId), '?'));
        $stmt = $db->prepare("SELECT soal_id, kiri_kode, kanan_kode FROM pasangan WHERE soal_id IN ($in)");
        $stmt->execute(array_values($soalId));
        $hasil = [];
        foreach ($stmt as $baris) {
            $id = (int) $baris['soal_id'];
            $hasil[$id]['kiri'][] = (string) $baris['kiri_kode'];
            $hasil[$id]['kanan'][] = (string) $baris['kanan_kode'];
        }
        return $hasil;
    }
}
