<p><a href="<?= e(url('admin/sesi', ['mapel' => $sesi['mapel_id']])) ?>"><?= e((string) $sesi['nama_mapel']) ?></a></p>
<h1>Hasil · <?= e((string) $sesi['nama']) ?></h1>
<p>Peserta: <?= (int) $analisis['peserta'] ?></p>
<p class="aksi">
  <a class="tombol" href="<?= e(url('admin/unduh', ['sesi' => $sesi['id'], 'jenis' => 'hasil'])) ?>">Unduh hasil</a>
  <a class="tombol" href="<?= e(url('admin/unduh', ['sesi' => $sesi['id'], 'jenis' => 'jawaban'])) ?>">Unduh jawaban siswa</a>
  <a class="tombol" href="<?= e(url('admin/unduh', ['sesi' => $sesi['id'], 'jenis' => 'analisis'])) ?>">Unduh analisis</a>
</p>
<?php if ($analisis['tersulit'] !== []): ?>
<p>Soal tersulit:
<?php foreach ($analisis['tersulit'] as $nomor => $peringkat): ?>
  nomor <?= (int) $nomor ?><?= $peringkat < count($analisis['tersulit']) ? ',' : '' ?>
<?php endforeach; ?>
</p>
<?php endif; ?>
<table>
  <thead><tr><th>No</th><th>Tipe</th><th>Benar</th><th>Salah</th><th>Kosong</th><th>% benar</th></tr></thead>
  <tbody>
  <?php foreach ($analisis['soal'] as $soal): ?>
    <tr class="<?= isset($analisis['tersulit'][$soal['nomor']]) && $analisis['tersulit'][$soal['nomor']] === 1 ? 'tersulit' : '' ?>">
      <td><?= (int) $soal['nomor'] ?></td>
      <td><?= e(labelTipe((string) $soal['tipe'])) ?></td>
      <td><?= (int) $soal['benar'] ?></td>
      <td><?= (int) $soal['salah'] ?></td>
      <td><?= (int) $soal['kosong'] ?></td>
      <td><?= (int) $soal['persen'] ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if ($analisis['soal'] === []): ?><tr><td colspan="6">Belum ada soal.</td></tr><?php endif; ?>
  </tbody>
</table>
