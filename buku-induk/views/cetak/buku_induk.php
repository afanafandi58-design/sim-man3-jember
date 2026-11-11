<?php
/** @var array $siswa */
/** @var array $sekolah */
/** @var bool $isPdf */
if (!isset($isPdf)) {
    $isPdf = false;
}
$v = function ($key) use ($siswa) {
    return isset($siswa[$key]) && $siswa[$key] !== null ? (string) $siswa[$key] : '';
};
$nism = trim(($sekolah['nsm'] ?? '') . ($v('nis_lokal')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Induk — <?php echo e($v('nama')); ?></title>
    <style>
        /* Margin kiri 2 cm, kanan 1.5 cm; satu kotak foto 2×3 cm di kanan atas (blok A) */
        @page {
            size: A4;
            margin: 1.2cm 1.5cm 1.2cm 2cm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            color: #111;
            font-size: 11pt;
            margin: 0;
            padding: <?php echo $isPdf ? '0' : '16px 1.5cm 16px 2cm'; ?>;
        }
        .sheet { width: 100%; }
        h1 { text-align: center; font-size: 15pt; margin: 0 0 2px; text-transform: uppercase; }
        .sub { text-align: center; margin: 0 0 12px; font-size: 10.5pt; }
        table.meta { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        table.meta td { padding: 2px 0; vertical-align: top; }
        table.meta td.k { width: 7.2cm; } /* label meta dipersempit → nilai bergeser kiri */
        table.form { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
        table.form td { padding: 3px 4px; vertical-align: top; border-bottom: 1px dotted #999; }
        table.form td.num { width: 1.1cm; white-space: nowrap; }
        table.form td.label { width: 5.8cm; } /* sebelumnya ~42%; digeser kiri sesuai acuan */
        table.form td.val { width: auto; }
        /* Heading seksi rapat dengan baris data berikutnya (tanpa jarak kosong) */
        .section { margin: 10px 0 0 0; padding: 0; font-weight: bold; line-height: 1.25; }
        .block { margin: 0; padding: 0; }
        /* Layout 2 kolom mPDF-friendly: konten kiri + 1 kotak foto kanan (blok A saja) */
        table.siswa-wrap { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
        table.siswa-wrap > tbody > tr > td { vertical-align: top; padding: 0; border: none; }
        table.siswa-wrap td.siswa-main { width: auto; padding-right: 8px; }
        table.siswa-wrap td.siswa-foto { width: 2.15cm; text-align: right; }
        /* Satu kotak kosong foto (tempel manual) — 2 cm × 3 cm, kanan atas sejajar A. Siswa */
        .foto-box {
            display: block;
            width: 2cm;
            height: 3cm;
            border: 1px solid #111;
            margin: 0 0 0 auto;
            padding: 0;
            background: transparent;
        }
        .toolbar { margin: 0 0 16px -2cm; padding-left: 0; }
        .toolbar a, .toolbar button {
            font-family: Arial, sans-serif; font-size: 12px;
            padding: 8px 12px; margin-right: 6px; border: 1px solid #ccc; background: #f5f5f5;
            text-decoration: none; color: #111; cursor: pointer; border-radius: 4px;
        }
        @media print {
            .toolbar { display: none !important; }
            body { margin: 0; padding: 0; }
        }
        <?php if ($isPdf): ?>
        .toolbar { display: none !important; }
        <?php endif; ?>
    </style>
</head>
<body>
<?php if (!$isPdf): ?>
<div class="toolbar">
    <button type="button" onclick="window.print()">Cetak</button>
    <a href="<?php echo e(url('/cetak/' . (int)$siswa['id'] . '/pdf')); ?>">Unduh PDF</a>
    <a href="<?php echo e(url('/siswa')); ?>">Kembali</a>
</div>
<?php endif; ?>

<div class="sheet">
<h1>Buku Induk Siswa</h1>
<div class="sub"><?php echo e($sekolah['nama']); ?></div>

<table class="meta">
    <tr>
        <td class="k">Nomor Induk Siswa Madrasah</td>
        <td>: <strong><?php echo e($nism); ?></strong></td>
    </tr>
    <tr>
        <td class="k">NISN</td>
        <td>: <?php echo e($v('nisn')); ?></td>
    </tr>
</table>

<div class="block">
    <table class="siswa-wrap">
        <tr>
            <td class="siswa-main">
                <div class="section" style="margin-top:0">A. Siswa</div>
                <table class="form">
                    <tr><td class="num">1</td><td class="label">Nama lengkap</td><td class="val"><?php echo e($v('nama')); ?></td></tr>
                    <tr><td class="num">2</td><td class="label">Jenis kelamin</td><td class="val"><?php echo e($v('jenis_kelamin')); ?></td></tr>
                    <tr><td class="num">3</td><td class="label">Tempat, tanggal lahir</td><td class="val"><?php
                        $ttl = trim($v('tempat_lahir'));
                        $tgl = format_tanggal_id($v('tanggal_lahir'));
                        echo e($ttl . ($ttl && $tgl ? ', ' : '') . $tgl);
                    ?></td></tr>
                    <tr><td class="num">4</td><td class="label">Anak ke</td><td class="val"><?php echo e($v('anak_ke')); ?></td></tr>
                    <tr><td class="num">5</td><td class="label">Status dalam keluarga</td><td class="val"><?php echo e($v('status_keluarga')); ?></td></tr>
                    <tr><td class="num">6</td><td class="label">Alamat</td><td class="val"><?php echo e($v('alamat')); ?></td></tr>
                </table>
            </td>
            <td class="siswa-foto">
                <div class="foto-box" title="Tempel foto siswa"></div>
            </td>
        </tr>
    </table>
</div>

<div class="block">
    <div class="section">B. Orang tua / wali</div>
    <table class="form">
        <tr><td class="num">7.1</td><td class="label">Nama ayah</td><td class="val"><?php echo e($v('nama_ayah')); ?></td></tr>
        <tr><td class="num">7.2</td><td class="label">Agama ayah</td><td class="val"><?php echo e($v('agama_ayah') !== '' ? $v('agama_ayah') : $v('agama')); ?></td></tr>
        <tr><td class="num">7.3</td><td class="label">Pekerjaan ayah</td><td class="val"><?php echo e($v('pekerjaan_ayah')); ?></td></tr>
        <tr><td class="num">7.4</td><td class="label">Nama ibu</td><td class="val"><?php echo e($v('nama_ibu')); ?></td></tr>
        <tr><td class="num">7.5</td><td class="label">Agama ibu</td><td class="val"><?php echo e($v('agama_ibu')); ?></td></tr>
        <tr><td class="num">7.6</td><td class="label">Pekerjaan ibu</td><td class="val"><?php echo e($v('pekerjaan_ibu')); ?></td></tr>
        <tr><td class="num">7.7</td><td class="label">Alamat orang tua</td><td class="val"><?php echo e($v('alamat_orang_tua')); ?></td></tr>
        <tr><td class="num">7.8</td><td class="label">Telepon / HP</td><td class="val"><?php echo e($v('nomor_hp')); ?></td></tr>
        <tr><td class="num">8.1</td><td class="label">Nama wali</td><td class="val"><?php echo e($v('nama_wali')); ?></td></tr>
        <tr><td class="num">8.2</td><td class="label">Agama wali</td><td class="val"><?php echo e($v('agama_wali')); ?></td></tr>
        <tr><td class="num">8.3</td><td class="label">Pekerjaan wali</td><td class="val"><?php echo e($v('pekerjaan_wali')); ?></td></tr>
        <tr><td class="num">8.4</td><td class="label">Alamat wali</td><td class="val"><?php echo e($v('alamat_wali')); ?></td></tr>
        <tr><td class="num">8.5</td><td class="label">Telepon / HP wali</td><td class="val"><?php echo e($v('hp_wali')); ?></td></tr>
    </table>
</div>

<div class="block">
    <div class="section">C. Masuk madrasah ini</div>
    <table class="form">
        <tr><td class="num">9</td><td class="label">Dari sekolah / madrasah</td><td class="val"><?php echo e($v('sekolah_asal')); ?></td></tr>
        <tr><td class="num">10</td><td class="label">Alamat sekolah / madrasah</td><td class="val"><?php echo e($v('alamat_sekolah_asal')); ?></td></tr>
        <tr><td class="num">11</td><td class="label">Diterima di kelas</td><td class="val"><?php echo e($v('tingkat_awal')); ?></td></tr>
        <tr><td class="num">12</td><td class="label">Pada tanggal</td><td class="val"><?php echo e(format_tanggal_id($v('tanggal_terima'))); ?></td></tr>
        <tr><td class="num">13</td><td class="label">Nomor ijazah</td><td class="val"><?php echo e($v('nomor_ijazah')); ?></td></tr>
        <tr><td class="num">14</td><td class="label">Mutasi dari</td><td class="val"><?php echo e($v('mutasi_dari')); ?></td></tr>
        <tr><td class="num">15</td><td class="label">Diterima di kelas (mutasi)</td><td class="val"><?php echo e($v('mutasi_diterima_kelas')); ?></td></tr>
        <tr><td class="num">16</td><td class="label">Pada tanggal (mutasi)</td><td class="val"><?php echo e(format_tanggal_id($v('mutasi_tanggal'))); ?></td></tr>
        <tr><td class="num">17</td><td class="label">Surat mutasi nomor</td><td class="val"><?php echo e($v('mutasi_surat_nomor')); ?></td></tr>
        <tr><td class="num">18</td><td class="label">Nilai ujian akhir / ijazah SMP/MTs</td><td class="val"><?php echo e($v('nilai_ujian_akhir')); ?></td></tr>
    </table>
</div>

<div class="block">
    <div class="section">D. Meninggalkan madrasah</div>
    <table class="form">
        <tr><td class="num">19</td><td class="label">Pada tanggal</td><td class="val"><?php echo e(format_tanggal_id($v('keluar_tanggal'))); ?></td></tr>
        <tr><td class="num">20</td><td class="label">Alasan</td><td class="val"><?php echo e($v('keluar_alasan')); ?></td></tr>
        <tr><td class="num">21</td><td class="label">Nomor surat mutasi</td><td class="val"><?php echo e($v('keluar_surat_nomor')); ?></td></tr>
        <?php if ($v('keluar_keterangan') !== ''): ?>
        <tr><td class="num"></td><td class="label">Keterangan</td><td class="val"><?php echo e($v('keluar_keterangan')); ?></td></tr>
        <?php endif; ?>
    </table>
</div>
</div>
</body>
</html>
