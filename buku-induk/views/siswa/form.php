<?php
$s = $siswa ? $siswa : [];
$val = function ($key) use ($s) {
    return isset($s[$key]) ? $s[$key] : '';
};
?>
<div class="panel">
    <h2><?php echo $siswa ? 'Edit Siswa' : 'Tambah Siswa'; ?></h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="<?php echo e($action); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <div class="section-title">A. Identitas siswa</div>
            <div>
                <label>NIS Lokal *</label>
                <input type="text" name="nis_lokal" required value="<?php echo e($val('nis_lokal')); ?>">
            </div>
            <div>
                <label>NISN</label>
                <input type="text" name="nisn" value="<?php echo e($val('nisn')); ?>">
            </div>
            <div class="full">
                <label>Nama lengkap *</label>
                <input type="text" name="nama" required value="<?php echo e($val('nama')); ?>">
            </div>
            <div>
                <label>Jenis kelamin</label>
                <select name="jenis_kelamin">
                    <?php
                    $jk = (string) $val('jenis_kelamin');
                    $opts = ['', 'LAKI-LAKI', 'PEREMPUAN'];
                    foreach ($opts as $o) {
                        $label = $o === '' ? '—' : $o;
                        $sel = strtoupper($jk) === $o || ($o !== '' && strpos(strtoupper($jk), substr($o, 0, 4)) === 0) ? 'selected' : '';
                        echo '<option value="' . e($o) . '" ' . $sel . '>' . e($label) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Tempat lahir</label>
                <input type="text" name="tempat_lahir" value="<?php echo e($val('tempat_lahir')); ?>">
            </div>
            <div>
                <label>Tanggal lahir</label>
                <input type="date" name="tanggal_lahir" value="<?php echo e($val('tanggal_lahir')); ?>">
            </div>
            <div>
                <label>Agama</label>
                <input type="text" name="agama" value="<?php echo e($val('agama')); ?>">
            </div>
            <div class="full">
                <label>Alamat siswa</label>
                <textarea name="alamat"><?php echo e($val('alamat')); ?></textarea>
            </div>
            <div>
                <label>Status dalam keluarga</label>
                <input type="text" name="status_keluarga" value="<?php echo e($val('status_keluarga')); ?>">
            </div>
            <div>
                <label>Anak ke</label>
                <input type="number" name="anak_ke" min="1" value="<?php echo e((string) $val('anak_ke')); ?>">
            </div>
            <div>
                <label>Nomor HP</label>
                <input type="text" name="nomor_hp" value="<?php echo e($val('nomor_hp')); ?>">
            </div>

            <div class="section-title">B. Orang tua / wali</div>
            <div>
                <label>Nama ayah</label>
                <input type="text" name="nama_ayah" value="<?php echo e($val('nama_ayah')); ?>">
            </div>
            <div>
                <label>Agama ayah</label>
                <input type="text" name="agama_ayah" value="<?php echo e($val('agama_ayah')); ?>">
            </div>
            <div>
                <label>Pekerjaan ayah</label>
                <input type="text" name="pekerjaan_ayah" value="<?php echo e($val('pekerjaan_ayah')); ?>">
            </div>
            <div>
                <label>Nama ibu</label>
                <input type="text" name="nama_ibu" value="<?php echo e($val('nama_ibu')); ?>">
            </div>
            <div>
                <label>Agama ibu</label>
                <input type="text" name="agama_ibu" value="<?php echo e($val('agama_ibu')); ?>">
            </div>
            <div>
                <label>Pekerjaan ibu</label>
                <input type="text" name="pekerjaan_ibu" value="<?php echo e($val('pekerjaan_ibu')); ?>">
            </div>
            <div class="full">
                <label>Alamat orang tua</label>
                <textarea name="alamat_orang_tua"><?php echo e($val('alamat_orang_tua')); ?></textarea>
            </div>
            <div>
                <label>Nama wali</label>
                <input type="text" name="nama_wali" value="<?php echo e($val('nama_wali')); ?>">
            </div>
            <div>
                <label>Agama wali</label>
                <input type="text" name="agama_wali" value="<?php echo e($val('agama_wali')); ?>">
            </div>
            <div>
                <label>Pekerjaan wali</label>
                <input type="text" name="pekerjaan_wali" value="<?php echo e($val('pekerjaan_wali')); ?>">
            </div>
            <div>
                <label>HP wali</label>
                <input type="text" name="hp_wali" value="<?php echo e($val('hp_wali')); ?>">
            </div>
            <div class="full">
                <label>Alamat wali</label>
                <textarea name="alamat_wali"><?php echo e($val('alamat_wali')); ?></textarea>
            </div>

            <div class="section-title">C. Masuk madrasah</div>
            <div>
                <label>Sekolah / madrasah asal</label>
                <input type="text" name="sekolah_asal" value="<?php echo e($val('sekolah_asal')); ?>">
            </div>
            <div>
                <label>Alamat sekolah asal</label>
                <input type="text" name="alamat_sekolah_asal" value="<?php echo e($val('alamat_sekolah_asal')); ?>">
            </div>
            <div>
                <label>Diterima di kelas (tingkat awal)</label>
                <input type="text" name="tingkat_awal" value="<?php echo e($val('tingkat_awal')); ?>" placeholder="mis. X.A">
            </div>
            <div>
                <label>Tanggal diterima</label>
                <input type="date" name="tanggal_terima" value="<?php echo e($val('tanggal_terima')); ?>">
            </div>
            <div>
                <label>Nomor ijazah</label>
                <input type="text" name="nomor_ijazah" value="<?php echo e($val('nomor_ijazah')); ?>">
            </div>
            <div>
                <label>Nilai ujian akhir</label>
                <input type="text" name="nilai_ujian_akhir" value="<?php echo e($val('nilai_ujian_akhir')); ?>">
            </div>
            <div>
                <label>Mutasi dari</label>
                <input type="text" name="mutasi_dari" value="<?php echo e($val('mutasi_dari')); ?>">
            </div>
            <div>
                <label>Mutasi diterima di kelas</label>
                <input type="text" name="mutasi_diterima_kelas" value="<?php echo e($val('mutasi_diterima_kelas')); ?>">
            </div>
            <div>
                <label>Tanggal mutasi masuk</label>
                <input type="date" name="mutasi_tanggal" value="<?php echo e($val('mutasi_tanggal')); ?>">
            </div>
            <div>
                <label>No. surat mutasi masuk</label>
                <input type="text" name="mutasi_surat_nomor" value="<?php echo e($val('mutasi_surat_nomor')); ?>">
            </div>

            <div class="section-title">D. Meninggalkan madrasah</div>
            <div>
                <label>Tanggal keluar</label>
                <input type="date" name="keluar_tanggal" value="<?php echo e($val('keluar_tanggal')); ?>">
            </div>
            <div>
                <label>Nomor surat mutasi keluar</label>
                <input type="text" name="keluar_surat_nomor" value="<?php echo e($val('keluar_surat_nomor')); ?>">
            </div>
            <div class="full">
                <label>Alasan</label>
                <textarea name="keluar_alasan"><?php echo e($val('keluar_alasan')); ?></textarea>
            </div>
            <div class="full">
                <label>Keterangan tambahan</label>
                <textarea name="keluar_keterangan"><?php echo e($val('keluar_keterangan')); ?></textarea>
            </div>
        </div>
        <div class="actions" style="margin-top:18px;">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="<?php echo e(url('/siswa')); ?>">Batal</a>
        </div>
    </form>
</div>
