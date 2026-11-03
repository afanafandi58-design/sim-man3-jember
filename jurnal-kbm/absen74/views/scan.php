<div class="card">
    <p>Pindai barcode kegiatan. Setelah barcode terbaca, cari nama lalu ambil foto selfie. Peserta tidak memakai email atau kata sandi.</p>
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
<?php if ($kegiatan) { ?>
    <div class="card">
        <h2><?php echo absen_e($kegiatan['nama']); ?></h2>
        <p class="muted"><?php echo absen_e(isset($jenisMap[$kegiatan['jenis']]) ? $jenisMap[$kegiatan['jenis']] : $kegiatan['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?><?php $rentangLabel = absen_rentang_label($kegiatan); echo $rentangLabel !== '' ? ' · '.absen_e($rentangLabel) : ''; ?></p>
        <?php if ($rentangBlokir !== '') { ?>
            <div class="alert alert-warning"><?php echo absen_e($rentangBlokir); ?></div>
        <?php } else { ?>
            <?php if (absen_error('user_id')) { ?><p class="error"><?php echo absen_e(absen_error('user_id')); ?></p><?php } ?>
            <?php if (absen_error('foto')) { ?><p class="error"><?php echo absen_e(absen_error('foto')); ?></p><?php } ?>
            <?php if (absen_error('latitude')) { ?><p class="error"><?php echo absen_e(absen_error('latitude')); ?></p><?php } ?>
            <?php if (absen_error('rentang')) { ?><p class="error"><?php echo absen_e(absen_error('rentang')); ?></p><?php } ?>
            <form method="post" action="/absen-digital/scan" enctype="multipart/form-data" id="form-absen-scan">
                <?php echo absen_csrf_field(); ?>
                <input type="hidden" name="kode" value="<?php echo absen_e($kegiatan['kode']); ?>">
                <input type="hidden" name="user_id" id="user-id" value="<?php echo absen_e(absen_old('user_id')); ?>">
                <p><label for="cari-nama">Cari nama</label>
                    <input id="cari-nama" type="search" autocomplete="off" placeholder="Ketik nama guru atau staf">
                </p>
                <ul id="hasil-nama" style="list-style:none;padding:0;margin:0 0 12px"></ul>
                <p id="nama-terpilih"><strong>Belum ada nama dipilih.</strong></p>
                <p><label for="foto">Foto selfie</label><input id="foto" type="file" name="foto" accept="image/*" capture="user" required><span class="muted">Absen tidak bisa disimpan tanpa foto selfie. Maksimal 4 MB.</span></p>
                <p><label>Titik lokasi</label>
                    <button type="button" class="btn btn-success btn-lg" id="btn-lokasi">Ambil lokasi GPS</button>
                    <span id="lokasi-status" class="muted">Jika GPS tidak tersedia atau ditolak, isi lintang dan bujur di bawah.</span>
                </p>
                <div class="row">
                    <p><label for="latitude">Lintang</label><input id="latitude" name="latitude" type="number" step="any" inputmode="decimal" required value="<?php echo absen_e(absen_old('latitude')); ?>" placeholder="-8.170000"></p>
                    <p><label for="longitude">Bujur</label><input id="longitude" name="longitude" type="number" step="any" inputmode="decimal" required value="<?php echo absen_e(absen_old('longitude')); ?>" placeholder="113.700000"></p>
                </div>
                <p id="lokasi-tampil"></p>
                <button class="btn btn-success btn-lg" type="submit">Simpan absen</button>
            </form>
            <script>
            (function () {
                var data = <?php echo json_encode(array_map(function ($orang) {
                    return array('id' => (int) $orang['id'], 'name' => $orang['name']);
                }, $peserta), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
                var input = document.getElementById('cari-nama');
                var list = document.getElementById('hasil-nama');
                var hidden = document.getElementById('user-id');
                var label = document.getElementById('nama-terpilih');
                var form = document.getElementById('form-absen-scan');
                function pilih(id, name) {
                    hidden.value = String(id);
                    label.innerHTML = '<strong>Terpilih: ' + name.replace(/[&<>"']/g, function (ch) {
                        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];
                    }) + '</strong>';
                    input.value = name;
                    list.innerHTML = '';
                }
                function render() {
                    var q = input.value.toLowerCase().trim();
                    list.innerHTML = '';
                    if (q.length < 1) {
                        return;
                    }
                    var shown = 0;
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].name.toLowerCase().indexOf(q) === -1) {
                            continue;
                        }
                        var item = document.createElement('li');
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'btn';
                        button.style.display = 'block';
                        button.style.width = '100%';
                        button.style.textAlign = 'left';
                        button.style.marginBottom = '6px';
                        button.textContent = data[i].name;
                        button.setAttribute('data-id', data[i].id);
                        button.addEventListener('click', function () {
                            pilih(this.getAttribute('data-id'), this.textContent);
                        });
                        item.appendChild(button);
                        list.appendChild(item);
                        shown++;
                        if (shown >= 8) {
                            break;
                        }
                    }
                }
                input.addEventListener('input', function () {
                    hidden.value = '';
                    label.innerHTML = '<strong>Belum ada nama dipilih.</strong>';
                    render();
                });
                form.addEventListener('submit', function (event) {
                    if (!hidden.value) {
                        event.preventDefault();
                        label.innerHTML = '<strong>Pilih nama dari hasil pencarian.</strong>';
                    }
                });
            })();
            </script>
        <?php } ?>
        <?php if ($sudahHadir) { ?>
            <p><strong>Sudah hadir</strong></p>
            <ul><?php foreach ($sudahHadir as $row) { ?><li><?php echo absen_e($row['nama']); ?></li><?php } ?></ul>
        <?php } ?>
    </div>
<?php } ?>
