<form method="get" action="/absen-digital/ringkasan" class="card">
    <div class="row">
        <p><label for="tanggal_dari">Dari</label><input id="tanggal_dari" type="date" name="tanggal_dari" value="<?php echo absen_e($filters['tanggal_dari']); ?>"></p>
        <p><label for="tanggal_sampai">Sampai</label><input id="tanggal_sampai" type="date" name="tanggal_sampai" value="<?php echo absen_e($filters['tanggal_sampai']); ?>"><?php if (!empty($filters['errors']['tanggal_sampai'])) { ?><span class="error"><?php echo absen_e($filters['errors']['tanggal_sampai']); ?></span><?php } ?></p>
        <p><label for="jenis">Jenis</label>
            <select id="jenis" name="jenis">
                <option value="">Semua</option>
                <?php foreach ($jenisMap as $key => $label) { ?><option value="<?php echo absen_e($key); ?>" <?php echo $filters['jenis'] === $key ? 'selected' : ''; ?>><?php echo absen_e($label); ?></option><?php } ?>
            </select>
        </p>
        <p><label for="kegiatan_id">Kegiatan</label>
            <select id="kegiatan_id" name="kegiatan_id">
                <option value="">Semua</option>
                <?php foreach ($semuaKegiatan as $row) { ?><option value="<?php echo (int) $row['id']; ?>" <?php echo (int) $filters['kegiatan_id'] === (int) $row['id'] ? 'selected' : ''; ?>><?php echo absen_e(absen_tanggal_label($row['tanggal']).' — '.$row['nama']); ?></option><?php } ?>
            </select>
        </p>
    </div>
    <button class="btn btn-success" type="submit">Terapkan</button>
</form>
<div class="card">
    <p><strong><?php echo count($rows); ?></strong> kegiatan · <strong><?php echo (int) $totalHadir; ?></strong> kehadiran</p>
    <ul>
        <?php foreach ($jenisMap as $key => $label) { if (empty($perJenis[$key])) { continue; } ?>
            <li><?php echo absen_e($label); ?>: <?php echo (int) $perJenis[$key]; ?></li>
        <?php } ?>
    </ul>
</div>
<div class="dek dek-layar">
    <?php if (!$rows) { ?><div class="kartu"><p class="kartu-judul">Tidak ada kegiatan pada saringan ini.</p></div><?php } ?>
    <?php foreach ($rows as $row) { ?>
        <article class="kartu">
            <p class="kartu-judul"><?php echo absen_e($row['nama']); ?></p>
            <p class="kartu-meta"><?php echo absen_e(absen_tanggal_label($row['tanggal'])); ?> · <?php echo absen_e(isset($jenisMap[$row['jenis']]) ? $jenisMap[$row['jenis']] : $row['jenis']); ?></p>
            <p class="kartu-meta"><?php echo (int) $row['hadir']; ?> hadir</p>
            <p class="kartu-aksi">
                <a class="btn" href="/absen-digital/kegiatan/<?php echo (int) $row['id']; ?>">Detail</a>
                <a class="btn" href="/absen-digital/cetak/halaman?kegiatan_id=<?php echo (int) $row['id']; ?>" target="_blank" rel="noopener">Cetak</a>
            </p>
        </article>
    <?php } ?>
</div>
