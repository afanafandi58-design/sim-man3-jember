<?php
$id = isset($kegiatan['id']) ? (int) $kegiatan['id'] : 0;
$action = $id ? '/absen-digital/kegiatan/'.$id : '/absen-digital/kegiatan';
$val = function ($key) use ($kegiatan) {
    $old = absen_old($key, null);
    if ($old !== null && $old !== '') {
        return $old;
    }
    return isset($kegiatan[$key]) ? $kegiatan[$key] : '';
};
$modeLokasi = (string) $val('lokasi_mode');
if ($modeLokasi !== 'bebas') {
    $modeLokasi = 'titik';
}
?>
<link rel="stylesheet" href="/vendor/leaflet.css">
<div class="card">
    <form method="post" action="<?php echo absen_e($action); ?>">
        <?php echo absen_csrf_field(); ?>
        <?php if ($id) { ?><input type="hidden" name="_method" value="PUT"><?php } ?>
        <p><label for="nama">Nama kegiatan</label><input id="nama" name="nama" required maxlength="150" value="<?php echo absen_e($val('nama')); ?>"><?php if (absen_error('nama')) { ?><span class="error"><?php echo absen_e(absen_error('nama')); ?></span><?php } ?></p>
        <div class="row">
            <p><label for="jenis">Jenis</label>
                <select id="jenis" name="jenis" required>
                    <?php foreach ($jenisMap as $key => $label) { ?>
                        <option value="<?php echo absen_e($key); ?>" <?php echo (string) $val('jenis') === (string) $key ? 'selected' : ''; ?>><?php echo absen_e($label); ?></option>
                    <?php } ?>
                </select>
            </p>
            <p><label for="tanggal">Tanggal</label><input id="tanggal" type="date" name="tanggal" required value="<?php echo absen_e($val('tanggal')); ?>"></p>
            <p><label for="waktu_mulai">Jam mulai absen</label><input id="waktu_mulai" type="time" name="waktu_mulai" required value="<?php echo absen_e($val('waktu_mulai')); ?>"><?php if (absen_error('waktu_mulai')) { ?><span class="error"><?php echo absen_e(absen_error('waktu_mulai')); ?></span><?php } ?></p>
            <p><label for="waktu_selesai">Jam selesai absen</label><input id="waktu_selesai" type="time" name="waktu_selesai" required value="<?php echo absen_e($val('waktu_selesai')); ?>"><?php if (absen_error('waktu_selesai')) { ?><span class="error"><?php echo absen_e(absen_error('waktu_selesai')); ?></span><?php } ?></p>
        </div>
        <p><label for="tempat">Tempat</label><input id="tempat" name="tempat" maxlength="150" value="<?php echo absen_e($val('tempat')); ?>"></p>
        <p><span id="label-lokasi">Aturan lokasi</span></p>
        <div class="pilihan-lokasi" role="radiogroup" aria-labelledby="label-lokasi">
            <label><input type="radio" name="lokasi_mode" value="titik" <?php echo $modeLokasi === 'titik' ? 'checked' : ''; ?>> <span>Wajib titik lokasi, radius 75 meter</span></label>
            <label><input type="radio" name="lokasi_mode" value="bebas" <?php echo $modeLokasi === 'bebas' ? 'checked' : ''; ?>> <span>Bebas, absen di mana saja</span></label>
        </div>
        <?php if (absen_error('lokasi_mode')) { ?><p class="error"><?php echo absen_e(absen_error('lokasi_mode')); ?></p><?php } ?>
        <p id="catatan-bebas" class="muted" <?php echo $modeLokasi === 'bebas' ? '' : 'hidden'; ?>>Peserta boleh absen dari mana saja. GPS tidak menolak absen.</p>
        <div id="blok-peta" <?php echo $modeLokasi === 'bebas' ? 'hidden' : ''; ?>>
            <p><label for="peta-lokasi">Titik lokasi kegiatan</label>
                <span class="muted">Klik peta atau geser pin. Lingkaran menunjukkan radius 75 meter. Tombol GPS memindahkan pin ke posisi perangkat. Koordinat terisi dari peta.</span>
            </p>
            <div id="peta-lokasi"></div>
            <p>
                <button type="button" class="btn btn-success" id="btn-lokasi">Ambil titik dari GPS</button>
                <span id="lokasi-status" class="muted">Klik peta untuk meletakkan pin.</span>
            </p>
            <div class="row">
                <p><label for="latitude">Lintang</label><input id="latitude" name="lokasi_lat" type="text" inputmode="decimal" readonly <?php echo $modeLokasi === 'titik' ? 'required' : ''; ?> value="<?php echo absen_e($val('lokasi_lat')); ?>" placeholder="Dari peta"><?php if (absen_error('lokasi_lat')) { ?><span class="error"><?php echo absen_e(absen_error('lokasi_lat')); ?></span><?php } ?></p>
                <p><label for="longitude">Bujur</label><input id="longitude" name="lokasi_lng" type="text" inputmode="decimal" readonly <?php echo $modeLokasi === 'titik' ? 'required' : ''; ?> value="<?php echo absen_e($val('lokasi_lng')); ?>" placeholder="Dari peta"></p>
            </div>
        </div>
        <p><label for="keterangan">Keterangan</label><textarea id="keterangan" name="keterangan" rows="3" maxlength="2000"><?php echo absen_e($val('keterangan')); ?></textarea></p>
        <button class="btn btn-success btn-lg" type="submit">Simpan</button>
        <a class="btn btn-lg" href="<?php echo $id ? '/absen-digital/kegiatan/'.$id : '/absen-digital/kegiatan'; ?>">Batal</a>
    </form>
</div>
<script src="/vendor/leaflet.js"></script>
