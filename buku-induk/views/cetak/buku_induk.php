<?php
/** @var array $siswa */
/** @var array $sekolah */
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
        body { font-family: "Times New Roman", Times, serif; color: #111; font-size: 12pt; margin: 24px; }
        h1 { text-align: center; font-size: 16pt; margin: 0 0 4px; text-transform: uppercase; }
        .sub { text-align: center; margin: 0 0 18px; font-size: 11pt; }
        table.meta { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        table.meta td { padding: 2px 0; }
        table.form { width: 100%; border-collapse: collapse; }
        table.form td { padding: 4px 6px; vertical-align: top; border-bottom: 1px dotted #999; }
        table.form td.num { width: 28px; }
        table.form td.label { width: 42%; }
        .section { margin-top: 14px; font-weight: bold; }
        .toolbar { margin-bottom: 16px; }
        .toolbar a, .toolbar button {
            font-family: Arial, sans-serif; font-size: 12px;
            padding: 8px 12px; margin-right: 6px; border: 1px solid #ccc; background: #f5f5f5;
            text-decoration: none; color: #111; cursor: pointer; border-radius: 4px;
        }
        @media print { .toolbar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
<div class="toolbar">
    <button type="button" onclick="window.print()">Cetak</button>
    <a href="<?php echo e(url('/cetak/' . (int)$siswa['id'] . '/pdf')); ?>">Unduh PDF</a>
    <a href="<?php echo e(url('/siswa')); ?>">Kembali</a>
</div>

<h1>Buku Induk Siswa</h1>
<div class="sub"><?php echo e($sekolah['nama']); ?></div>

<table class="meta">
    <tr>
        <td style="width:45%;">Nomor Induk Siswa Madrasah</td>
        <td>: <strong><?php echo e($nism); ?></strong></td>
    </tr>
    <tr>
        <td>NISN</td>
        <td>: <?php echo e($v('nisn')); ?></td>
    </tr>
</table>

<div class="section">A. Siswa</div>
<table class="form">
    <tr><td class="num">1</td><td class="label">Nama lengkap</td><td><?php echo e($v('nama')); ?></td></tr>
    <tr><td class="num">2</td><td class="label">Jenis kelamin</td><td><?php echo e($v('jenis_kelamin')); ?></td></tr>
    <tr><td class="num">3</td><td class="label">Tempat, tanggal lahir</td><td><?php
        $ttl = trim($v('tempat_lahir'));
        $tgl = format_tanggal_id($v('tanggal_lahir'));
        echo e($ttl . ($ttl && $tgl ? ', ' : '') . $tgl);
    ?></td></tr>
    <tr><td class="num">4</td><td class="label">Anak ke</td><td><?php echo e($v('anak_ke')); ?></td></tr>
    <tr><td class="num">5</td><td class="label">Status dalam keluarga</td><td><?php echo e($v('status_keluarga')); ?></td></tr>
    <tr><td class="num">6</td><td class="label">Alamat</td><td><?php echo e($v('alamat')); ?></td></tr>
</table>

<div class="section">B. Orang tua / wali</div>
<table class="form">
    <tr><td class="num">7.1</td><td class="label">Nama ayah</td><td><?php echo e($v('nama_ayah')); ?></td></tr>
    <tr><td class="num">7.2</td><td class="label">Agama ayah</td><td><?php echo e($v('agama_ayah') !== '' ? $v('agama_ayah') : $v('agama')); ?></td></tr>
    <tr><td class="num">7.3</td><td class="label">Pekerjaan ayah</td><td><?php echo e($v('pekerjaan_ayah')); ?></td></tr>
    <tr><td class="num">7.4</td><td class="label">Nama ibu</td><td><?php echo e($v('nama_ibu')); ?></td></tr>
    <tr><td class="num">7.5</td><td class="label">Agama ibu</td><td><?php echo e($v('agama_ibu')); ?></td></tr>
    <tr><td class="num">7.6</td><td class="label">Pekerjaan ibu</td><td><?php echo e($v('pekerjaan_ibu')); ?></td></tr>
    <tr><td class="num">7.7</td><td class="label">Alamat orang tua</td><td><?php echo e($v('alamat_orang_tua')); ?></td></tr>
    <tr><td class="num">7.8</td><td class="label">Telepon / HP</td><td><?php echo e($v('nomor_hp')); ?></td></tr>
    <tr><td class="num">8.1</td><td class="label">Nama wali</td><td><?php echo e($v('nama_wali')); ?></td></tr>
    <tr><td class="num">8.2</td><td class="label">Agama wali</td><td><?php echo e($v('agama_wali')); ?></td></tr>
    <tr><td class="num">8.3</td><td class="label">Pekerjaan wali</td><td><?php echo e($v('pekerjaan_wali')); ?></td></tr>
    <tr><td class="num">8.4</td><td class="label">Alamat wali</td><td><?php echo e($v('alamat_wali')); ?></td></tr>
    <tr><td class="num">8.5</td><td class="label">Telepon / HP wali</td><td><?php echo e($v('hp_wali')); ?></td></tr>
</table>

<div class="section">C. Masuk madrasah ini</div>
<table class="form">
    <tr><td class="num">9</td><td class="label">Dari sekolah / madrasah</td><td><?php echo e($v('sekolah_asal')); ?></td></tr>
    <tr><td class="num">10</td><td class="label">Alamat sekolah / madrasah</td><td><?php echo e($v('alamat_sekolah_asal')); ?></td></tr>
    <tr><td class="num">11</td><td class="label">Diterima di kelas</td><td><?php echo e($v('tingkat_awal')); ?></td></tr>
    <tr><td class="num">12</td><td class="label">Pada tanggal</td><td><?php echo e(format_tanggal_id($v('tanggal_terima'))); ?></td></tr>
    <tr><td class="num">13</td><td class="label">Nomor ijazah</td><td><?php echo e($v('nomor_ijazah')); ?></td></tr>
    <tr><td class="num">14</td><td class="label">Mutasi dari</td><td><?php echo e($v('mutasi_dari')); ?></td></tr>
    <tr><td class="num">15</td><td class="label">Diterima di kelas (mutasi)</td><td><?php echo e($v('mutasi_diterima_kelas')); ?></td></tr>
    <tr><td class="num">16</td><td class="label">Pada tanggal (mutasi)</td><td><?php echo e(format_tanggal_id($v('mutasi_tanggal'))); ?></td></tr>
    <tr><td class="num">17</td><td class="label">Surat mutasi nomor</td><td><?php echo e($v('mutasi_surat_nomor')); ?></td></tr>
    <tr><td class="num">18</td><td class="label">Nilai ujian akhir / ijazah SMP/MTs</td><td><?php echo e($v('nilai_ujian_akhir')); ?></td></tr>
</table>

<div class="section">D. Meninggalkan madrasah</div>
<table class="form">
    <tr><td class="num">19</td><td class="label">Pada tanggal</td><td><?php echo e(format_tanggal_id($v('keluar_tanggal'))); ?></td></tr>
    <tr><td class="num">20</td><td class="label">Alasan</td><td><?php echo e($v('keluar_alasan')); ?></td></tr>
    <tr><td class="num">21</td><td class="label">Nomor surat mutasi</td><td><?php echo e($v('keluar_surat_nomor')); ?></td></tr>
    <?php if ($v('keluar_keterangan') !== ''): ?>
    <tr><td class="num"></td><td class="label">Keterangan</td><td><?php echo e($v('keluar_keterangan')); ?></td></tr>
    <?php endif; ?>
</table>
</body>
</html>
