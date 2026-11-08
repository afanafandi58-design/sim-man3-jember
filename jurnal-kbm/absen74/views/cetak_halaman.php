<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan absen · MAN 3 Jember</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; color: #111; margin: 0; background: #e8e8e8; }
        h1, h2, h3, p { margin: 0; }
        h1, h2, h3 { text-align: center; font-weight: bold; }
        h1 { font-size: 14pt; letter-spacing: 0.06em; }
        h2 { font-size: 12pt; margin-top: 0.4mm; }
        h3 { font-size: 11pt; margin-top: 1.5mm; }
        .sub { text-align: center; font-size: 9pt; margin-top: 0.6mm; }
        .meta { text-align: center; font-size: 9pt; margin: 1mm 0 2mm; }
        .lembar { width: 210mm; min-height: 297mm; margin: 8mm auto; padding: 10mm 11mm; background: #fff; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 1.5mm; }
        col.c-no { width: 7%; }
        col.c-nama { width: 36%; }
        col.c-nip { width: 22%; }
        col.c-waktu { width: 13%; }
        col.c-ttd { width: 22%; }
        th, td { border: 1px solid #222; padding: 0.5mm 1.2mm; font-size: 9pt; line-height: 1.15; vertical-align: middle; }
        th { font-size: 8pt; text-align: center; background: #f2f2f2; }
        td.no, td.waktu, td.ttd { text-align: center; }
        td.nama { overflow-wrap: anywhere; }
        td.nip { font-size: 8pt; white-space: nowrap; }
        thead { display: table-header-group; }
        tr, .ttd-kepala { page-break-inside: avoid; }
        td.ttd img { display: block; max-height: 8mm; max-width: 100%; height: auto; width: auto; margin: 0 auto; }
        .jumlah { font-size: 9pt; margin-top: 2mm; }
        .ttd-kepala { margin-top: 5mm; width: 62mm; margin-left: auto; text-align: center; font-size: 10pt; line-height: 1.25; }
        .ttd-kepala p { margin: 0; }
        .ruang { height: 16mm; }
        .garis { border-bottom: 1px solid #111; margin: 0 4mm 0.6mm; }
        .grup { page-break-after: always; }
        .grup:last-child { page-break-after: auto; }
        .no-print { width: 210mm; margin: 6mm auto 0; }
        .no-print button { font: 10pt sans-serif; padding: 4px 10px; }
        @page { size: A4; margin: 10mm 11mm; }
        @media print {
            body { background: #fff; }
            .no-print { display: none; }
            .lembar { width: auto; min-height: 0; margin: 0; padding: 0; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<p class="no-print"><button type="button" id="cetak-laporan">Cetak laporan</button></p>
<?php if (!$groups) { ?><div class="lembar"><p>Tidak ada kehadiran pada saringan ini.</p></div><?php } ?>
<?php foreach ($groups as $rows) { $head = $rows[0]; ?>
    <section class="grup">
        <div class="lembar">
            <h1>MAN 3 JEMBER</h1>
            <h2>Laporan Absen</h2>
            <p class="sub">Daftar hadir untuk ditandatangani Kepala Madrasah</p>
            <h3><?php echo absen_e($head['kegiatan_nama']); ?></h3>
            <p class="meta"><?php echo absen_e(isset($jenisMap[$head['jenis']]) ? $jenisMap[$head['jenis']] : $head['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($head['tanggal'])); ?><?php $rentang = absen_rentang_label($head); echo $rentang !== '' ? ' · '.absen_e($rentang) : ''; ?><?php echo $head['tempat'] ? ' · '.absen_e($head['tempat']) : ''; ?></p>
            <table>
                <colgroup>
                    <col class="c-no">
                    <col class="c-nama">
                    <col class="c-nip">
                    <col class="c-waktu">
                    <col class="c-ttd">
                </colgroup>
                <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Waktu</th><th>Tanda tangan</th></tr></thead>
                <tbody>
                <?php $n = 1; foreach ($rows as $row) {
                    $ts = strtotime((string) $row['created_at']);
                    $jam = $ts ? date('H:i', $ts) : (string) $row['created_at'];
                ?>
                    <tr>
                        <td class="no"><?php echo $n++; ?></td>
                        <td class="nama"><?php echo absen_e($row['nama']); ?></td>
                        <td class="nip"><?php echo absen_e($row['nik']); ?></td>
                        <td class="waktu"><?php echo absen_e($jam); ?></td>
                        <td class="ttd"><?php if ($row['ttd_path']) { ?><img src="/absen-digital/berkas/ttd/<?php echo (int) $row['id']; ?>" alt="Tanda tangan <?php echo absen_e($row['nama']); ?>"><?php } ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <p class="jumlah">Jumlah hadir: <?php echo count($rows); ?></p>
            <div class="ttd-kepala">
                <p>Jember, <?php echo absen_e(absen_tanggal_label(date('Y-m-d'))); ?></p>
                <p>Mengetahui,<br>Kepala MAN 3 Jember</p>
                <div class="ruang"></div>
                <div class="garis"></div>
                <p>Tanda tangan kepala</p>
            </div>
        </div>
    </section>
<?php } ?>
<script src="/js/absen-digital.js?v=lama1"></script>
</body>
</html>
