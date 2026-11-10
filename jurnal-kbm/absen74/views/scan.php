<?php if (empty($halamanKegiatan)) { ?>
<div class="card">
    <p>Tempel tautan kegiatan, atau pindai barcode bila tautan belum dibuka.</p>
    <form method="get" action="/absen-digital/scan" id="form-cari-kode">
        <p><label for="kode-input">Tautan atau kode kegiatan</label><input id="kode-input" name="kode" value="<?php echo absen_e($kode); ?>" placeholder="Tempel tautan absen atau kode" autocomplete="off"></p>
        <button class="btn btn-success" type="submit">Buka kegiatan</button>
        <?php if (absen_error('kode')) { ?><span class="error"><?php echo absen_e(absen_error('kode')); ?></span><?php } ?>
    </form>
    <p class="muted">Pindai hanya bila tautan belum dibuka.</p>
    <button type="button" class="btn" id="btn-kamera">Pindai barcode</button>
    <p id="scan-status" class="muted"></p>
    <video id="scan-video" class="absen-video" playsinline muted></video>
    <p><label for="scan-file">Atau pilih gambar barcode</label><input id="scan-file" type="file" accept="image/*" capture="environment"></p>
</div>
<?php } ?>
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
        <?php } elseif (absen_lokasi_wajib($kegiatan) && absen_lokasi_kegiatan($kegiatan) === null) { ?>
            <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
            <div class="alert alert-warning">Titik lokasi kegiatan belum ditentukan. Absen belum bisa disimpan.</div>
        <?php } else {
            $lokasiBebas = !absen_lokasi_wajib($kegiatan);
        ?>
            <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
            <p class="muted"><?php echo $lokasiBebas ? 'Halaman kegiatan ini bisa dibuka dari tautan, tanpa memindai barcode. Absen boleh dari mana saja. GPS tidak menolak.' : 'Halaman kegiatan ini bisa dibuka dari tautan, tanpa memindai barcode. Absen hanya berlaku dalam radius 75 meter dari titik lokasi.'; ?></p>
            <div id="langkah-absen">
                <?php if (absen_error('user_id')) { ?><p class="error"><?php echo absen_e(absen_error('user_id')); ?></p><?php } ?>
                <?php if (absen_error('foto')) { ?><p class="error"><?php echo absen_e(absen_error('foto')); ?></p><?php } ?>
                <?php if (absen_error('ttd')) { ?><p class="error"><?php echo absen_e(absen_error('ttd')); ?></p><?php } ?>
                <?php if (absen_error('rentang')) { ?><p class="error"><?php echo absen_e(absen_error('rentang')); ?></p><?php } ?>
                <?php if (absen_error('perangkat')) { ?><p class="error"><?php echo absen_e(absen_error('perangkat')); ?></p><?php } ?>
                <?php if (absen_error('lokasi')) { ?><p class="error"><?php echo absen_e(absen_error('lokasi')); ?></p><?php } ?>
                <form method="post" action="/absen-digital/k/<?php echo absen_e(rawurlencode($kegiatan['kode'])); ?>" enctype="multipart/form-data" id="form-absen-scan" data-lokasi="<?php echo $lokasiBebas ? 'bebas' : 'wajib'; ?>">
                    <?php echo absen_csrf_field(); ?>
                    <input type="hidden" name="kode" value="<?php echo absen_e($kegiatan['kode']); ?>">
                    <input type="hidden" name="latitude" id="latitude" value="">
                    <input type="hidden" name="longitude" id="longitude" value="">
                    <input type="hidden" name="lokasi_palsu" id="lokasi-palsu" value="0">
                    <input type="hidden" name="sidik" id="sidik-perangkat" value="">
                    <p id="lokasi-status" class="muted"><?php echo $lokasiBebas ? 'Kegiatan ini bebas lokasi. Absen boleh dari mana saja. GPS tidak menolak.' : 'Ketuk Simpan untuk membaca lokasi. Absen hanya berlaku dalam radius 75 meter.'; ?></p>
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
                    <p><label for="foto">Upload foto</label><input id="foto" type="file" name="foto" accept="image/*" capture="user" required><span class="muted">Absen tidak bisa disimpan tanpa foto. Saat diambil atau dipilih, foto diperkecil di perangkat sebelum dikirim agar di bawah 1 MB. File asli yang besar tidak terkirim.</span><span id="foto-status" class="muted"></span><img id="foto-pratinjau" alt="Pratinjau foto yang akan dikirim" hidden style="max-width:100%;max-height:200px;margin-top:8px"></p>
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
            <ul><?php foreach ($sudahHadir as $row) { ?><li><?php echo absen_e($row['nama']); ?><?php if (!empty($row['catatan'])) { ?> <span class="muted">(<?php echo absen_e($row['catatan']); ?>)</span><?php } ?></li><?php } ?></ul>
        <?php } ?>
    </div>
<?php } ?>
