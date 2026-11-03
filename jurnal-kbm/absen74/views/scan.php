<div class="card">
    <button type="button" class="btn btn-success btn-lg" id="btn-kamera">Buka kamera</button>
    <p id="scan-status" class="muted"></p>
    <video id="scan-video" class="absen-video" playsinline muted></video>
    <p><label for="scan-file">Atau pilih gambar QR</label><input id="scan-file" type="file" accept="image/*" capture="environment"></p>
    <form method="get" action="/absen-digital/scan" id="form-cari-kode">
        <p><label for="kode-input">Kode atau hasil scan</label><input id="kode-input" name="kode" value="<?php echo absen_e($kode); ?>" placeholder="Tempel kode atau URL" autocomplete="off"></p>
        <button class="btn btn-success" type="submit">Cari kegiatan</button>
        <?php if (absen_error('kode')) { ?><span class="error"><?php echo absen_e(absen_error('kode')); ?></span><?php } ?>
    </form>
</div>
<?php if ($kodeTidakDitemukan) { ?><div class="alert alert-warning">Kode kegiatan tidak ditemukan.</div><?php } ?>
<?php if ($kegiatan) { ?>
    <div class="card">
        <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
        <p class="muted"><?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?> · <?php echo absen_e($kegiatan['kode']); ?></p>
        <?php if (absen_error('user_id')) { ?><p class="error"><?php echo absen_e(absen_error('user_id')); ?></p><?php } ?>
        <form method="post" action="/absen-digital/scan">
            <?php echo absen_csrf_field(); ?>
            <input type="hidden" name="kode" value="<?php echo absen_e($kegiatan['kode']); ?>">
            <p><label for="scan-user">Pengguna yang hadir</label>
                <select id="scan-user" name="user_id" required>
                    <?php foreach ($peserta as $orang) { ?>
                        <option value="<?php echo (int) $orang['id']; ?>" <?php echo (int) $orang['id'] === (int) $authUser['id'] ? 'selected' : ''; ?>><?php echo absen_e($orang['name']); ?></option>
                    <?php } ?>
                </select>
            </p>
            <button class="btn btn-success btn-lg" type="submit">Catat hadir</button>
        </form>
        <?php if ($sudahHadir) { ?>
            <p><strong>Sudah hadir</strong></p>
            <ul><?php foreach ($sudahHadir as $row) { ?><li><?php echo absen_e($row['nama']); ?></li><?php } ?></ul>
        <?php } ?>
    </div>
<?php } ?>
