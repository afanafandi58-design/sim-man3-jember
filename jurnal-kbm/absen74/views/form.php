<?php if (!$kegiatans) { ?><div class="alert alert-warning">Belum ada kegiatan. Admin perlu membuat kegiatan terlebih dahulu.</div><?php } ?>
<div class="card">
    <form method="post" action="/absen-digital/absen" enctype="multipart/form-data" id="form-absen">
        <?php echo absen_csrf_field(); ?>
        <div class="row">
            <p><label for="jenis-filter">Jenis kegiatan</label>
                <select id="jenis-filter">
                    <option value="">Semua jenis</option>
                    <?php foreach ($jenisMap as $key => $label) { ?><option value="<?php echo absen_e($key); ?>"><?php echo absen_e($label); ?></option><?php } ?>
                </select>
            </p>
            <p><label for="kegiatan-id">Kegiatan</label>
                <select id="kegiatan-id" name="kegiatan_id" required>
                    <option value="">Pilih kegiatan</option>
                    <?php foreach ($kegiatans as $row) { ?>
                        <option value="<?php echo (int) $row['id']; ?>" data-jenis="<?php echo absen_e($row['jenis']); ?>" <?php echo (string) absen_old('kegiatan_id') === (string) $row['id'] ? 'selected' : ''; ?>><?php echo absen_e(absen_tanggal_label($row['tanggal']).' — '.$row['nama']); ?></option>
                    <?php } ?>
                </select>
                <?php if (absen_error('kegiatan_id')) { ?><span class="error"><?php echo absen_e(absen_error('kegiatan_id')); ?></span><?php } ?>
            </p>
        </div>
        <?php $mode = absen_old('mode_peserta', 'user'); ?>
        <p><label>Peserta</label>
            <label><input type="radio" name="mode_peserta" value="user" <?php echo $mode === 'user' ? 'checked' : ''; ?>> Pilih pengguna</label>
            <label><input type="radio" name="mode_peserta" value="nik" <?php echo $mode === 'nik' ? 'checked' : ''; ?>> Isi NIK</label>
        </p>
        <div id="box-user">
            <p><label for="user-id">Pengguna</label>
                <select id="user-id" name="user_id">
                    <?php foreach ($peserta as $orang) { ?>
                        <option value="<?php echo (int) $orang['id']; ?>" <?php echo (string) absen_old('user_id', $authUser['id']) === (string) $orang['id'] ? 'selected' : ''; ?>><?php echo absen_e($orang['name'].' ('.(isset($peranMap[$orang['role']]) ? $peranMap[$orang['role']] : $orang['role']).')'); ?></option>
                    <?php } ?>
                </select>
                <?php if (absen_error('user_id')) { ?><span class="error"><?php echo absen_e(absen_error('user_id')); ?></span><?php } ?>
            </p>
        </div>
        <div class="row" id="box-nik">
            <p><label for="nik">NIK</label><input id="nik" name="nik" inputmode="numeric" maxlength="16" value="<?php echo absen_e(absen_old('nik')); ?>" placeholder="16 digit"><?php if (absen_error('nik')) { ?><span class="error"><?php echo absen_e(absen_error('nik')); ?></span><?php } ?></p>
            <p><label for="nama">Nama</label><input id="nama" name="nama" maxlength="150" value="<?php echo absen_e(absen_old('nama')); ?>"><?php if (absen_error('nama')) { ?><span class="error"><?php echo absen_e(absen_error('nama')); ?></span><?php } ?></p>
        </div>
        <p><label for="foto">Foto kehadiran</label><input id="foto" type="file" name="foto" accept="image/*" capture="environment" required><span class="muted">Ambil dari kamera atau pilih berkas gambar. Maksimal 4 MB.</span><?php if (absen_error('foto')) { ?><span class="error"><?php echo absen_e(absen_error('foto')); ?></span><?php } ?></p>
        <p><label>Titik lokasi</label>
            <button type="button" class="btn btn-success btn-lg" id="btn-lokasi">Ambil lokasi GPS</button>
            <span id="lokasi-status" class="muted">Jika GPS tidak tersedia atau ditolak, isi lintang dan bujur di bawah.</span>
        </p>
        <div class="row">
            <p><label for="latitude">Lintang</label><input id="latitude" name="latitude" type="number" step="any" inputmode="decimal" required value="<?php echo absen_e(absen_old('latitude')); ?>" placeholder="-8.170000"><?php if (absen_error('latitude')) { ?><span class="error"><?php echo absen_e(absen_error('latitude')); ?></span><?php } ?></p>
            <p><label for="longitude">Bujur</label><input id="longitude" name="longitude" type="number" step="any" inputmode="decimal" required value="<?php echo absen_e(absen_old('longitude')); ?>" placeholder="113.700000"></p>
        </div>
        <p id="lokasi-tampil"></p>
        <p><label>Tanda tangan</label>
            <canvas id="ttd-canvas" aria-label="Kanvas tanda tangan"></canvas>
            <input type="hidden" name="ttd" id="ttd-input">
            <button type="button" class="btn btn-lg" id="ttd-clear">Hapus tanda tangan</button>
            <span id="ttd-error" class="error"><?php echo absen_e(absen_error('ttd')); ?></span>
        </p>
        <button class="btn btn-success btn-lg" type="submit">Simpan absen</button>
    </form>
</div>
<?php if ($riwayat) { ?>
    <div class="card" style="padding:0">
        <div style="padding:16px"><strong>Riwayat terbaru</strong></div>
        <table>
            <thead><tr><th>Kegiatan</th><th>Nama</th><th>Cara</th></tr></thead>
            <tbody>
            <?php foreach ($riwayat as $row) { ?>
                <tr>
                    <td><?php echo absen_e(absen_tanggal_label($row['tanggal']).' — '.$row['kegiatan_nama']); ?></td>
                    <td><?php echo absen_e($row['nama']); ?></td>
                    <td><?php echo $row['metode'] === 'qr' ? 'QR' : 'Formulir'; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
