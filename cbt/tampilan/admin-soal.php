<p><a href="<?= e(url('admin/sesi', ['mapel' => $sesi['mapel_id']])) ?>"><?= e((string) $sesi['nama_mapel']) ?></a></p>
<h1>Soal · <?= e((string) $sesi['nama']) ?></h1>
<p><a class="tombol" href="<?= e(url('admin/soal/form', ['sesi' => $sesi['id']])) ?>">Soal baru</a>
<a class="tombol" href="<?= e(url('admin/impor', ['sesi' => $sesi['id']])) ?>">Impor</a></p>
<table>
  <thead><tr><th>No</th><th>Tipe</th><th>Pertanyaan</th><th>Audio</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($daftar as $soal): ?>
    <tr>
      <td><?= (int) $soal['nomor'] ?></td>
      <td><?= e(labelTipe((string) $soal['tipe'])) ?></td>
      <td><?= e(mb_strimwidth((string) $soal['pertanyaan'], 0, 90, '…')) ?></td>
      <td><?= e((string) ($soal['audio'] ?? '')) ?></td>
      <td class="aksi-kecil">
        <a href="<?= e(url('admin/soal/form', ['sesi' => $sesi['id'], 'id' => $soal['id']])) ?>">Ubah</a>
        <form method="post" action="<?= e(url('admin/soal/hapus')) ?>" onsubmit="return confirm('Hapus soal ini?')">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
          <input type="hidden" name="id" value="<?= (int) $soal['id'] ?>">
          <button class="teks bahaya" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if ($daftar === []): ?><tr><td colspan="5">Belum ada soal.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php
$halaman = (int) ceil(max(1, $jumlah) / $per);
if ($halaman > 1): ?>
<p class="halaman">
  <?php if ($hal > 1): ?><a href="<?= e(url('admin/soal', ['sesi' => $sesi['id'], 'hal' => $hal - 1])) ?>">Sebelumnya</a><?php endif; ?>
  Halaman <?= (int) $hal ?> dari <?= $halaman ?>
  <?php if ($hal < $halaman): ?><a href="<?= e(url('admin/soal', ['sesi' => $sesi['id'], 'hal' => $hal + 1])) ?>">Berikutnya</a><?php endif; ?>
</p>
<?php endif; ?>
