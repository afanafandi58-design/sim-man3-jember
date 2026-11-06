<h1>Siswa</h1>
<form method="post" action="<?= e(url('admin/siswa/simpan')) ?>" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
  <label>Nama pengguna<input name="username" required maxlength="32" value="<?= e((string) ($edit['username'] ?? '')) ?>"></label>
  <label>Nama<input name="nama" required maxlength="120" value="<?= e((string) ($edit['nama'] ?? '')) ?>"></label>
  <label>NIS (opsional)<input name="nis" maxlength="32" value="<?= e((string) ($edit['nis'] ?? '')) ?>"></label>
  <label>Kata sandi<?= $edit ? ' (kosongkan jika tidak diubah)' : '' ?><input name="sandi" type="password" autocomplete="new-password" <?= $edit ? '' : 'required' ?>></label>
  <label class="centang"><input type="checkbox" name="aktif" value="1" <?= !isset($edit) || (int) $edit['aktif'] === 1 ? 'checked' : '' ?>> Aktif</label>
  <button type="submit"><?= $edit ? 'Simpan perubahan' : 'Tambah siswa' ?></button>
</form>
<table>
  <thead><tr><th>Nama</th><th>Pengguna</th><th>NIS</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($daftar as $siswa): ?>
    <tr>
      <td><?= e((string) $siswa['nama']) ?></td>
      <td><?= e((string) $siswa['username']) ?></td>
      <td><?= e((string) $siswa['nis']) ?></td>
      <td><?= (int) $siswa['aktif'] === 1 ? 'Aktif' : 'Nonaktif' ?></td>
      <td class="aksi-kecil">
        <a href="<?= e(url('admin/siswa', ['id' => $siswa['id'], 'hal' => $hal])) ?>">Ubah</a>
        <form method="post" action="<?= e(url('admin/siswa/hapus')) ?>" onsubmit="return confirm('Hapus siswa dan semua percobaannya?')">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $siswa['id'] ?>">
          <button class="teks bahaya" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if ($daftar === []): ?><tr><td colspan="5">Belum ada siswa.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php $halaman = (int) ceil(max(1, $jumlah) / $per); if ($halaman > 1): ?>
<p class="halaman">
  <?php if ($hal > 1): ?><a href="<?= e(url('admin/siswa', ['hal' => $hal - 1])) ?>">Sebelumnya</a><?php endif; ?>
  Halaman <?= (int) $hal ?> dari <?= $halaman ?>
  <?php if ($hal < $halaman): ?><a href="<?= e(url('admin/siswa', ['hal' => $hal + 1])) ?>">Berikutnya</a><?php endif; ?>
</p>
<?php endif; ?>
