<?php if ($authUser['role'] === 'admin') { ?>
    <p><a class="btn btn-success" href="/absen-digital/kegiatan-baru">Tambah kegiatan</a></p>
<?php } ?>
<div class="card" style="padding:0">
    <table>
        <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Jenis</th><th>Hadir</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows) { ?><tr><td colspan="5">Belum ada kegiatan.</td></tr><?php } ?>
        <?php foreach ($rows as $row) { ?>
            <tr>
                <td><?php echo absen_e(absen_tanggal_label($row['tanggal'])); ?><?php $rentang = absen_rentang_label($row); if ($rentang !== '') { ?><div class="muted"><?php echo absen_e($rentang); ?></div><?php } ?></td>
                <td><?php echo absen_e($row['nama']); ?><div class="muted"><?php echo absen_e($row['kode']); ?></div></td>
                <td><?php echo absen_e(isset($jenisMap[$row['jenis']]) ? $jenisMap[$row['jenis']] : $row['jenis']); ?></td>
                <td><?php echo (int) $row['hadir']; ?></td>
                <td><a href="/absen-digital/kegiatan/<?php echo (int) $row['id']; ?>">Detail</a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php if ($pages > 1) { ?>
    <p>
        <?php if ($page > 1) { ?><a href="?page=<?php echo $page - 1; ?>">Sebelumnya</a><?php } ?>
        <span class="muted">Halaman <?php echo (int) $page; ?> / <?php echo (int) $pages; ?></span>
        <?php if ($page < $pages) { ?><a href="?page=<?php echo $page + 1; ?>">Berikutnya</a><?php } ?>
    </p>
<?php } ?>
