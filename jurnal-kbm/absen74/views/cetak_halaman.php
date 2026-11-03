<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar hadir · MAN 3 Jember</title>
    <style>
        body { font-family: "Times New Roman", serif; color: #111; }
        h1, h2 { text-align: center; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 12px; }
        img { height: 48px; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<p class="no-print"><button onclick="window.print()">Cetak</button></p>
<h1>MAN 3 Jember</h1>
<h2>Daftar Hadir Absen Digital</h2>
<?php if (!$groups) { ?><p>Tidak ada kehadiran pada saringan ini.</p><?php } ?>
<?php foreach ($groups as $rows) { $head = $rows[0]; ?>
    <h3><?php echo absen_e($head['kegiatan_nama']); ?></h3>
    <p><?php echo absen_e(isset($jenisMap[$head['jenis']]) ? $jenisMap[$head['jenis']] : $head['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($head['tanggal'])); ?><?php $rentang = absen_rentang_label($head); echo $rentang !== '' ? ' · '.absen_e($rentang) : ''; ?><?php echo $head['tempat'] ? ' · '.absen_e($head['tempat']) : ''; ?></p>
    <table>
        <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Cara</th><th>Koordinat</th><th>Foto selfie</th></tr></thead>
        <tbody>
        <?php $n = 1; foreach ($rows as $row) { ?>
            <tr>
                <td><?php echo $n++; ?></td>
                <td><?php echo absen_e($row['nama']); ?></td>
                <td><?php echo absen_e($row['nik']); ?></td>
                <td><?php echo absen_e(absen_metode_label($row['metode'])); ?></td>
                <td><?php echo ($row['latitude'] !== null && $row['latitude'] !== '') ? absen_e($row['latitude'].', '.$row['longitude']) : ''; ?></td>
                <td><?php if ($row['foto_path']) { ?><img src="/absen-digital/berkas/foto/<?php echo (int) $row['id']; ?>" alt="Foto <?php echo absen_e($row['nama']); ?>"><?php } ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
</body>
</html>
