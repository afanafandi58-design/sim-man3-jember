<?php
declare(strict_types=1);

function tanganiApi(string $rute, string $metode): void
{
    $siswaId = (int) ($_SESSION['siswa_id'] ?? 0);
    if ($siswaId < 1) {
        jsonKeluar(['ok' => false, 'pesan' => 'Masuk kembali.', 'masuk' => false], 401);
    }
    if ($metode === 'POST') {
        Keamanan::sahkanApi();
    }
    $pengerjaan = pengerjaanAktif(true);
    $db = Db::pdo();
    if ($rute === 'api/soal' && $metode === 'GET') {
        jsonKeluar(Layanan::paketSoal($db, $pengerjaan, (int) ($_GET['nomor'] ?? 1)));
    }
    if ($metode !== 'POST') {
        jsonKeluar(['ok' => false, 'pesan' => 'Permintaan tidak valid.'], 405);
    }
    if ($rute === 'api/jawaban') {
        $data = bacaJson();
        $daftar = $data['jawaban'] ?? [];
        if (!is_array($daftar)) {
            throw new RuntimeException('Jawaban tidak valid.');
        }
        $jumlah = Layanan::simpanJawaban($db, $pengerjaan, $daftar);
        $segar = $db->prepare('SELECT status, sisa_detik, checkpoint_pada, terakhir_ping FROM pengerjaan WHERE id = ?');
        $segar->execute([(int) $pengerjaan['id']]);
        $baris = $segar->fetch() ?: ['status' => 'berjalan', 'sisa_detik' => 0, 'checkpoint_pada' => Layanan::sekarang(), 'terakhir_ping' => Layanan::sekarang()];
        $hitung = WaktuUjian::hitung($baris, time());
        jsonKeluar([
            'ok' => true,
            'jumlah' => $jumlah,
            'sisa_detik' => $hitung['sisa_detik'],
            'status' => $baris['status'],
        ]);
    }
    $aksi = match ($rute) {
        'api/detak' => 'detak',
        'api/jeda' => 'jeda',
        'api/lanjut' => 'lanjut',
        'api/selesai' => 'selesai',
        default => '',
    };
    if ($aksi === '') {
        jsonKeluar(['ok' => false, 'pesan' => 'Tidak ditemukan.'], 404);
    }
    if ($aksi === 'selesai') {
        $baris = Layanan::selesaikan($db, (int) $pengerjaan['id']);
    } else {
        $baris = Layanan::sinkronWaktu($db, (int) $pengerjaan['id'], (int) $pengerjaan['siswa_id'], $aksi);
    }
    jsonKeluar([
        'ok' => true,
        'sisa_detik' => (int) $baris['sisa_detik'],
        'status' => (string) $baris['status'],
    ]);
}
