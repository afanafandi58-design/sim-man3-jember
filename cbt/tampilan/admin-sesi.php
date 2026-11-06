<p><a href="<?= e(url('admin/mapel')) ?>">Mata pelajaran</a></p>
<h1><?= e((string) $mapel['nama']) ?></h1>
<form method="post" action="<?= e(url('admin/sesi/simpan')) ?>" class="kartu baris-form">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="mapel_id" value="<?= (int) $mapel['id'] ?>">
  <label>Nama sesi<input name="nama" required maxlength="160"></label>
  <label>Durasi menit<input name="durasi_menit" type="number" min="1" max="300" value="60" required></label>
  <button type="submit">Buat sesi</button>
</form>
<?php if ($daftar === []): ?><p>Belum ada sesi.</p><?php endif; ?>
<?php foreach ($daftar as $sesi): ?>
<section class="kartu">
  <h2><?= e((string) $sesi['nama']) ?></h2>
  <p><?= e(labelStatus((string) $sesi['status'])) ?> · <?= (int) $sesi['durasi_menit'] ?> menit · <?= (int) $sesi['jumlah_soal'] ?> soal</p>
  <?php if ($sesi['status'] === 'berjalan' && $sesi['token']): ?>
    <p class="token-ujian">Token ujian <strong><?= e((string) $sesi['token']) ?></strong></p>
  <?php else: ?>
    <p class="redup">Token ditampilkan saat ujian berjalan.</p>
  <?php endif; ?>
  <div class="aksi">
    <a class="tombol" href="<?= e(url('admin/soal', ['sesi' => $sesi['id']])) ?>">Soal</a>
    <a class="tombol" href="<?= e(url('admin/impor', ['sesi' => $sesi['id']])) ?>">Impor</a>
    <a class="tombol" href="<?= e(url('admin/analisis', ['sesi' => $sesi['id']])) ?>">Hasil</a>
    <?php if ($sesi['status'] !== 'berjalan'): ?>
    <form method="post" action="<?= e(url('admin/sesi/mulai')) ?>">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
      <button type="submit">Mulai ujian</button>
    </form>
    <?php else: ?>
    <form method="post" action="<?= e(url('admin/sesi/selesai')) ?>">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
      <button type="submit">Tutup ujian</button>
    </form>
    <?php endif; ?>
    <form method="post" action="<?= e(url('admin/sesi/reset')) ?>" onsubmit="return confirm('Reset seluruh sesi? Semua percobaan dihapus. Jika ujian sedang berjalan, token diganti.')">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
      <button type="submit" class="bahaya">Reset sesi</button>
    </form>
    <form method="post" action="<?= e(url('admin/sesi/hapus')) ?>" onsubmit="return confirm('Hapus sesi ini beserta soalnya?')">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
      <button type="submit" class="teks bahaya">Hapus</button>
    </form>
  </div>
  <?php if ($sesi['peserta'] !== []): ?>
  <table>
    <thead><tr><th>Siswa</th><th>Status</th><th>Sisa</th><th>Skor</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($sesi['peserta'] as $orang): ?>
      <tr>
        <td><?= e((string) $orang['nama']) ?> <span class="redup"><?= e((string) $orang['username']) ?></span></td>
        <td><?= e(labelStatus((string) $orang['status_tampil'])) ?></td>
        <td><?= e(formatDetik((int) $orang['sisa_efektif'])) ?></td>
        <td><?= e((string) $orang['skor']) ?></td>
        <td>
          <form method="post" action="<?= e(url('admin/pengerjaan/reset')) ?>" onsubmit="return confirm('Reset percobaan siswa ini? Jawaban yang tersimpan akan dihapus.')">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
            <input type="hidden" name="pengerjaan_id" value="<?= (int) $orang['id'] ?>">
            <button type="submit" class="teks">Reset siswa</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
<?php endforeach; ?>
