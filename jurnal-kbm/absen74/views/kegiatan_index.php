<?php if ($authUser['role'] === 'admin') { ?>
    <p><a class="btn btn-success" href="/absen-digital/kegiatan-baru">Tambah kegiatan</a></p>
<?php } ?>
<?php if (!$rows) { ?><div class="kartu"><p class="kartu-judul">Belum ada kegiatan.</p></div><?php } ?>
<div class="dek dek-layar">
<?php foreach ($rows as $row) {
    $rentang = absen_rentang_label($row);
    $jenis = isset($jenisMap[$row['jenis']]) ? $jenisMap[$row['jenis']] : $row['jenis'];
?>
    <a class="kartu" href="/absen-digital/kegiatan/<?php echo (int) $row['id']; ?>">
        <p class="kartu-judul"><?php echo absen_e($row['nama']); ?></p>
        <p class="kartu-meta"><?php echo absen_e(absen_tanggal_label($row['tanggal'])); ?><?php echo $rentang !== '' ? ' · '.absen_e($rentang) : ''; ?></p>
        <p class="kartu-meta"><?php echo absen_e($jenis); ?> · <?php echo (int) $row['hadir']; ?> hadir<?php echo !absen_lokasi_wajib($row) ? ' · bebas lokasi' : (absen_lokasi_kegiatan($row) ? '' : ' · titik lokasi belum diisi'); ?></p>
        <p class="kartu-aksi"><span class="btn">Buka kegiatan</span></p>
    </a>
<?php } ?>
</div>
<?php if ($pages > 1) { ?>
    <p>
        <?php if ($page > 1) { ?><a class="btn" href="?page=<?php echo $page - 1; ?>">Sebelumnya</a><?php } ?>
        <span class="muted">Halaman <?php echo (int) $page; ?> / <?php echo (int) $pages; ?></span>
        <?php if ($page < $pages) { ?><a class="btn" href="?page=<?php echo $page + 1; ?>">Berikutnya</a><?php } ?>
    </p>
<?php } ?>
