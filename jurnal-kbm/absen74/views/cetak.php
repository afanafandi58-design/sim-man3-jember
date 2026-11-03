<div class="card">
    <form method="get" action="/absen-digital/cetak/halaman" target="_blank">
        <div class="row">
            <p><label for="tanggal_dari">Dari</label><input id="tanggal_dari" type="date" name="tanggal_dari" value="<?php echo absen_e($filters['tanggal_dari']); ?>"></p>
            <p><label for="tanggal_sampai">Sampai</label><input id="tanggal_sampai" type="date" name="tanggal_sampai" value="<?php echo absen_e($filters['tanggal_sampai']); ?>"></p>
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
        <p class="muted">Laporan ini disusun agar dapat dicetak dan ditandatangani kepala madrasah.</p>
        <button class="btn btn-success btn-lg" type="submit">Buka laporan absen</button>
    </form>
</div>
