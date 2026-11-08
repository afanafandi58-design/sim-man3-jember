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
<p><strong>Daftar peserta (<?php echo count($rows); ?>)</strong></p>
<p class="muted petunjuk-kartu">Ketuk tidak diperlukan. Geser kartu untuk melihat nama lain.</p>
<div class="dek dek-layar">
    <?php if (!$rows) { ?><div class="kartu"><p class="kartu-judul">Belum ada peserta.</p></div><?php } ?>
    <?php foreach ($rows as $row) { ?>
        <article class="kartu">
            <p class="kartu-judul"><?php echo absen_e($row['name']); ?></p>
            <p class="kartu-meta"><?php echo absen_e($row['nip'] ? $row['nip'] : 'NIP atau NIK belum diisi'); ?></p>
        </article>
    <?php } ?>
</div>
