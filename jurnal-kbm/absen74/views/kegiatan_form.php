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
            <p><label for="waktu">Waktu</label><input id="waktu" type="time" name="waktu" value="<?php echo absen_e($val('waktu')); ?>"></p>
        </div>
        <p><label for="tempat">Tempat</label><input id="tempat" name="tempat" maxlength="150" value="<?php echo absen_e($val('tempat')); ?>"></p>
        <p><label for="keterangan">Keterangan</label><textarea id="keterangan" name="keterangan" rows="3" maxlength="2000"><?php echo absen_e($val('keterangan')); ?></textarea></p>
        <button class="btn btn-success btn-lg" type="submit">Simpan</button>
        <a class="btn btn-lg" href="<?php echo $id ? '/absen-digital/kegiatan/'.$id : '/absen-digital/kegiatan'; ?>">Batal</a>
    </form>
</div>
