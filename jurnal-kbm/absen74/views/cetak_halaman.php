<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan absen · MAN 3 Jember</title>
    <style>
        body { font-family: "Times New Roman", serif; color: #111; margin: 24px; }
        h1, h2, h3 { text-align: center; margin: 0; }
        h1 { font-size: 22px; letter-spacing: 1px; }
        h2 { font-size: 18px; margin-top: 4px; }
        .sub { text-align: center; margin: 4px 0 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 12px; vertical-align: middle; }
        img { height: 42px; }
        .ttd-kepala { margin-top: 28px; width: 280px; margin-left: auto; text-align: center; page-break-inside: avoid; }
        .ruang { height: 90px; }
        .garis { border-bottom: 1px solid #111; height: 18px; margin: 0 24px; }
        .grup { page-break-after: always; }
        .grup:last-child { page-break-after: auto; }
        @page { size: A4 portrait; margin: 14mm; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
<p class="no-print"><button onclick="window.print()">Cetak laporan</button></p>
<?php if (!$groups) { ?><p>Tidak ada kehadiran pada saringan ini.</p><?php } ?>
<?php foreach ($groups as $rows) { $head = $rows[0]; ?>
    <section class="grup">
        <h1>MAN 3 JEMBER</h1>
        <h2>Laporan Absen</h2>
        <p class="sub">Daftar hadir untuk ditandatangani Kepala Madrasah</p>
        <h3><?php echo absen_e($head['kegiatan_nama']); ?></h3>
        <p><?php echo absen_e(isset($jenisMap[$head['jenis']]) ? $jenisMap[$head['jenis']] : $head['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($head['tanggal'])); ?><?php $rentang = absen_rentang_label($head); echo $rentang !== '' ? ' · '.absen_e($rentang) : ''; ?><?php echo $head['tempat'] ? ' · '.absen_e($head['tempat']) : ''; ?></p>
        <table>
            <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Waktu</th><th>Tanda tangan</th></tr></thead>
            <tbody>
            <?php $n = 1; foreach ($rows as $row) { ?>
                <tr>
                    <td><?php echo $n++; ?></td>
                    <td><?php echo absen_e($row['nama']); ?></td>
                    <td><?php echo absen_e($row['nik']); ?></td>
                    <td><?php echo absen_e($row['created_at']); ?></td>
                    <td><?php if ($row['ttd_path']) { ?><img src="/absen-digital/berkas/ttd/<?php echo (int) $row['id']; ?>" alt="Tanda tangan <?php echo absen_e($row['nama']); ?>"><?php } ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <p>Jumlah hadir: <?php echo count($rows); ?></p>
        <div class="ttd-kepala">
            <p>Jember, <?php echo absen_e(absen_tanggal_label(date('Y-m-d'))); ?></p>
            <p>Mengetahui,<br>Kepala MAN 3 Jember</p>
            <div class="ruang"></div>
            <div class="garis"></div>
            <p>Tanda tangan kepala</p>
        </div>
    </section>
<?php } ?>
</body>
</html>
