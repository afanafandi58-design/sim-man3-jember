<div class="panel">
    <h2>Pengaturan Madrasah</h2>
    <form method="post" action="<?php echo e(url('/settings')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <div class="full">
                <label>Nama madrasah</label>
                <input type="text" name="nama" required value="<?php echo e($sekolah['nama']); ?>">
            </div>
            <div>
                <label>NSM</label>
                <input type="text" name="nsm" value="<?php echo e($sekolah['nsm']); ?>">
            </div>
            <div>
                <label>NPSN</label>
                <input type="text" name="npsn" value="<?php echo e($sekolah['npsn']); ?>">
            </div>
            <div class="full">
                <label>Alamat</label>
                <textarea name="alamat"><?php echo e($sekolah['alamat']); ?></textarea>
            </div>
            <div>
                <label>Tahun angkatan default</label>
                <input type="text" name="tahun_angkatan_default" value="<?php echo e($sekolah['tahun_angkatan_default']); ?>">
            </div>
        </div>
        <div class="actions" style="margin-top:16px;">
            <button class="btn" type="submit">Simpan</button>
        </div>
    </form>
</div>
