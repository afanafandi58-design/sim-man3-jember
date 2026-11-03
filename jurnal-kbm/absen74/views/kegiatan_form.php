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
?>
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
        <p><label for="keterangan">Keterangan</label><textarea id="keterangan" name="keterangan" rows="3" maxlength="2000"><?php echo absen_e($val('keterangan')); ?></textarea></p>
        <button class="btn btn-success btn-lg" type="submit">Simpan</button>
        <a class="btn btn-lg" href="<?php echo $id ? '/absen-digital/kegiatan/'.$id : '/absen-digital/kegiatan'; ?>">Batal</a>
    </form>
</div>
