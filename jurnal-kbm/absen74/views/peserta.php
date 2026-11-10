<div class="card">
    <p class="muted">Nama baru langsung muncul di pilihan identitas. Peserta tidak memakai kata sandi. Absen yang sudah tersimpan tidak diubah.</p>
    <form id="form-tambah" method="post" action="/absen-digital/peserta">
        <?php echo absen_csrf_field(); ?>
        <p>
            <label for="name">Nama</label>
            <input id="name" name="name" maxlength="150" required value="<?php echo absen_e(absen_old('name')); ?>">
            <?php if (absen_error('name')) { ?><span class="error"><?php echo absen_e(absen_error('name')); ?></span><?php } ?>
        </p>
        <p>
            <label for="nip">NIP atau NIK</label>
            <input id="nip" name="nip" inputmode="numeric" maxlength="32" value="<?php echo absen_e(absen_old('nip')); ?>">
            <span class="muted">Boleh dikosongkan. Isi angka saja bila ada.</span>
            <?php if (absen_error('nip')) { ?><span class="error"><?php echo absen_e(absen_error('nip')); ?></span><?php } ?>
        </p>
    </form>
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
        <button class="btn btn-success" type="submit" form="form-tambah">Tambah peserta</button>
        <?php if ($rows) { ?>
            <form method="post" action="/absen-digital/peserta/hapus" data-konfirmasi="Hapus peserta yang dipilih dari daftar? Absen yang sudah tersimpan tidak dihapus." style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0">
                <?php echo absen_csrf_field(); ?>
                <label for="user_id" style="margin:0">Hapus peserta</label>
                <select id="user_id" name="user_id" required style="width:auto;min-width:12rem;flex:1">
                    <option value="">Pilih peserta</option>
                    <?php foreach ($rows as $row) { ?>
                        <option value="<?php echo (int) $row['id']; ?>"><?php echo absen_e($row['name']); ?></option>
                    <?php } ?>
                </select>
                <button class="btn btn-danger" type="submit">Hapus peserta</button>
            </form>
        <?php } ?>
    </div>
    <?php if (absen_error('user_id')) { ?><p class="error"><?php echo absen_e(absen_error('user_id')); ?></p><?php } ?>
    <p class="muted">Nama yang dihapus tidak muncul lagi di pilihan identitas. Absen, foto, tanda tangan, waktu, dan titik yang sudah tersimpan tetap ada. Akun pengelola tidak dihapus.</p>
</div>
<div class="card" style="padding:0">
    <div style="padding:16px"><strong>Daftar peserta (<?php echo count($rows); ?>)</strong></div>
    <table>
        <thead><tr><th>Nama</th><th>NIP atau NIK</th></tr></thead>
        <tbody>
        <?php if (!$rows) { ?><tr><td colspan="2">Belum ada peserta.</td></tr><?php } ?>
        <?php foreach ($rows as $row) { ?>
            <tr>
                <td><?php echo absen_e($row['name']); ?></td>
                <td><?php echo absen_e($row['nip'] ? $row['nip'] : '—'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
