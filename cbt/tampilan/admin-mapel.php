<h1>Mata pelajaran</h1>
<form method="post" action="<?= e(url('admin/mapel/simpan')) ?>" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
  <label>Kode<input name="kode" required maxlength="32" value="<?= e((string) ($edit['kode'] ?? '')) ?>"></label>
  <label>Nama<input name="nama" required maxlength="160" value="<?= e((string) ($edit['nama'] ?? '')) ?>"></label>
  <label class="centang"><input type="checkbox" name="aktif" value="1" <?= !isset($edit) || (int) $edit['aktif'] === 1 ? 'checked' : '' ?>> Aktif</label>
  <button type="submit"><?= $edit ? 'Simpan perubahan' : 'Tambah' ?></button>
</form>
<table>
  <thead><tr><th>Kode</th><th>Nama</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($daftar as $mapel): ?>
    <tr>
      <td><?= e((string) $mapel['kode']) ?></td>
      <td><a href="<?= e(url('admin/sesi', ['mapel' => $mapel['id']])) ?>"><?= e((string) $mapel['nama']) ?></a></td>
      <td><?= (int) $mapel['aktif'] === 1 ? 'Aktif' : 'Nonaktif' ?></td>
      <td class="aksi-kecil">
        <a href="<?= e(url('admin/mapel', ['id' => $mapel['id']])) ?>">Ubah</a>
        <form method="post" action="<?= e(url('admin/mapel/hapus')) ?>" onsubmit="return confirm('Hapus mata pelajaran ini?')">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $mapel['id'] ?>">
          <button type="submit" class="teks bahaya">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if ($daftar === []): ?><tr><td colspan="4">Belum ada mata pelajaran.</td></tr><?php endif; ?>
  </tbody>
</table>
