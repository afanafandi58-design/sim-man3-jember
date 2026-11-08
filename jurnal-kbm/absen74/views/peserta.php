<div class="card">
    <p class="muted">Nama baru langsung muncul di pilihan identitas. Peserta tidak memakai kata sandi. Absen yang sudah tersimpan tidak diubah.</p>
    <form method="post" action="/absen-digital/peserta">
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
        <button class="btn btn-success" type="submit">Tambah peserta</button>
    </form>
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
