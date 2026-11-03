<div class="card">
    <p>Pindai barcode, ketuk nama kegiatan, pilih identitas, unggah foto, lalu tanda tangan. Satu kegiatan hanya bisa diabsen dari satu perangkat Android. Satu identitas hanya sekali. Peserta tidak memakai email atau kata sandi.</p>
    <button type="button" class="btn btn-success btn-lg" id="btn-kamera">Buka kamera</button>
    <p id="scan-status" class="muted"></p>
    <video id="scan-video" class="absen-video" playsinline muted></video>
    <p><label for="scan-file">Atau pilih gambar barcode</label><input id="scan-file" type="file" accept="image/*" capture="environment"></p>
    <form method="get" action="/absen-digital/scan" id="form-cari-kode">
        <p><label for="kode-input">Kode atau hasil scan</label><input id="kode-input" name="kode" value="<?php echo absen_e($kode); ?>" placeholder="Tempel kode atau URL" autocomplete="off"></p>
        <button class="btn btn-success" type="submit">Cari kegiatan</button>
        <?php if (absen_error('kode')) { ?><span class="error"><?php echo absen_e(absen_error('kode')); ?></span><?php } ?>
    </form>
</div>
<?php if ($kodeTidakDitemukan) { ?><div class="alert alert-warning">Kode kegiatan tidak ditemukan.</div><?php } ?>
<?php if ($kegiatan) {
    $buka = absen_error('user_id') !== '' || absen_error('foto') !== '' || absen_error('ttd') !== '' || absen_error('rentang') !== '';
    $dipilih = (string) absen_old('user_id');
?>
    <div class="card">
        <p class="muted"><?php echo absen_e(isset($jenisMap[$kegiatan['jenis']]) ? $jenisMap[$kegiatan['jenis']] : $kegiatan['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?><?php $rentangLabel = absen_rentang_label($kegiatan); echo $rentangLabel !== '' ? ' · '.absen_e($rentangLabel) : ''; ?></p>
        <?php if ($rentangBlokir !== '') { ?>
            <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
            <div class="alert alert-warning"><?php echo absen_e($rentangBlokir); ?></div>
        <?php } elseif (!empty($pesanPerangkat)) { ?>
            <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
            <div class="alert alert-danger"><?php echo absen_e($pesanPerangkat); ?></div>
        <?php } else { ?>
            <p class="muted">Ketuk nama kegiatan untuk memilih identitas.</p>
            <button type="button" class="btn btn-success btn-lg" id="btn-kegiatan"><?php echo absen_e($kegiatan['nama']); ?></button>
            <div id="langkah-absen" <?php echo $buka ? '' : 'hidden'; ?>>
                <?php if (absen_error('user_id')) { ?><p class="error"><?php echo absen_e(absen_error('user_id')); ?></p><?php } ?>
                <?php if (absen_error('foto')) { ?><p class="error"><?php echo absen_e(absen_error('foto')); ?></p><?php } ?>
                <?php if (absen_error('ttd')) { ?><p class="error"><?php echo absen_e(absen_error('ttd')); ?></p><?php } ?>
                <?php if (absen_error('rentang')) { ?><p class="error"><?php echo absen_e(absen_error('rentang')); ?></p><?php } ?>
                <?php if (absen_error('perangkat')) { ?><p class="error"><?php echo absen_e(absen_error('perangkat')); ?></p><?php } ?>
                <form method="post" action="/absen-digital/scan" enctype="multipart/form-data" id="form-absen-scan">
                    <?php echo absen_csrf_field(); ?>
                    <input type="hidden" name="kode" value="<?php echo absen_e($kegiatan['kode']); ?>">
                    <p><label for="saring-identitas">Pilih identitas</label>
                        <input id="saring-identitas" type="search" autocomplete="off" placeholder="Saring <?php echo count($peserta); ?> nama">
                    </p>
                    <div id="daftar-identitas">
                        <?php
                        $sudahId = array();
                        foreach ($sudahHadir as $hadir) {
                            if (!empty($hadir['user_id'])) {
                                $sudahId[(int) $hadir['user_id']] = true;
                            }
                        }
                        foreach ($peserta as $orang) {
                            $sudah = isset($sudahId[(int) $orang['id']]);
                        ?>
                            <label class="identitas">
                                <input type="radio" name="user_id" value="<?php echo (int) $orang['id']; ?>" <?php echo !$sudah && $dipilih === (string) $orang['id'] ? 'checked' : ''; ?> <?php echo $sudah ? 'disabled' : 'required'; ?>>
                                <span><?php echo absen_e($orang['name']); ?><?php echo $sudah ? ' (sudah absen)' : ''; ?></span>
                            </label>
                        <?php } ?>
                    </div>
                    <p id="nama-terpilih" class="muted">Belum ada identitas dipilih.</p>
                    <p><label for="foto">Upload foto</label><input id="foto" type="file" name="foto" accept="image/*" capture="user" required><span class="muted">Absen tidak bisa disimpan tanpa foto. Maksimal 4 MB.</span></p>
                    <p><span id="ttd-label">Tanda tangan di layar</span>
                        <span class="ttd-kotak" id="ttd-kotak"><canvas id="ttd-canvas" width="640" height="320" aria-labelledby="ttd-label"></canvas></span>
                        <input type="hidden" name="ttd" id="ttd-input">
                        <span class="muted">Gambar dengan jari di dalam kotak. Halaman tidak ikut tergulung.</span>
                        <button type="button" class="btn btn-lg" id="ttd-clear">Hapus tanda tangan</button>
                        <span id="ttd-error" class="error"></span>
                    </p>
                    <button class="btn btn-success btn-lg" type="submit">Simpan</button>
                </form>
            </div>
            <script>
            (function () {
                var tombol = document.getElementById('btn-kegiatan');
                var langkah = document.getElementById('langkah-absen');
                var saring = document.getElementById('saring-identitas');
                var daftar = document.getElementById('daftar-identitas');
                var status = document.getElementById('nama-terpilih');
                if (tombol && langkah) {
                    tombol.addEventListener('click', function () {
                        langkah.hidden = false;
                        try {
                            document.dispatchEvent(new Event('ttd-buka'));
                        } catch (err) {
                            var legacy = document.createEvent('Event');
                            legacy.initEvent('ttd-buka', true, true);
                            document.dispatchEvent(legacy);
                        }
                        if (langkah.scrollIntoView) {
                            langkah.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                }
                if (saring && daftar) {
                    saring.addEventListener('input', function () {
                        var q = saring.value.toLowerCase().trim();
                        var labels = daftar.querySelectorAll('.identitas');
                        for (var i = 0; i < labels.length; i++) {
                            var nama = labels[i].textContent.toLowerCase();
                            labels[i].style.display = q === '' || nama.indexOf(q) !== -1 ? 'flex' : 'none';
                        }
                    });
                }
                if (daftar && status) {
                    daftar.addEventListener('change', function (event) {
                        var target = event.target;
                        if (!target || target.name !== 'user_id') {
                            return;
                        }
                        var label = target.parentNode ? target.parentNode.textContent : '';
                        status.textContent = 'Terpilih: ' + label.replace(/\s+/g, ' ').trim();
                    });
                }
            })();
            </script>
        <?php } ?>
        <?php if ($sudahHadir) { ?>
            <p><strong>Sudah hadir</strong></p>
            <ul><?php foreach ($sudahHadir as $row) { ?><li><?php echo absen_e($row['nama']); ?></li><?php } ?></ul>
        <?php } ?>
    </div>
<?php } ?>
