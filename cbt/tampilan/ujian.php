<?php
declare(strict_types=1);
/** @var array $siswa */
/** @var array $info */
/** @var int $sisa */
/** @var string $csrf */
/** @var array $pengerjaan */
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Ujian · CBT MAN 3 Jember</title>
<link rel="stylesheet" href="<?= e(aset('aset/gaya.css')) ?>">
</head>
<body class="layar-ujian">
<div id="ujian"
  data-basis="<?= e(basis()) ?>"
  data-csrf="<?= e($csrf) ?>"
  data-pengerjaan="<?= (int) $pengerjaan['id'] ?>"
  data-sisa="<?= (int) $sisa ?>">
  <header class="kepala-ujian">
    <div>
      <strong><?= e((string) $info['nama_mapel']) ?></strong>
      <span><?= e((string) $siswa['nama']) ?></span>
    </div>
    <div id="waktu" class="waktu"><?= e(formatDetik((int) $sisa)) ?></div>
    <button type="button" id="btn-keluar" class="teks">Keluar</button>
  </header>
  <p id="spanduk" class="spanduk" hidden></p>
  <div class="badan-ujian">
    <aside id="daftar" class="daftar"></aside>
    <section>
      <article id="soal"></article>
      <div class="aksi">
        <button type="button" id="btn-kembali">Kembali</button>
        <button type="button" id="btn-ragu">Ragu-ragu</button>
        <button type="button" id="btn-lanjut">Lanjut</button>
        <button type="button" id="btn-kumpulkan" class="utama">Kumpulkan</button>
      </div>
    </section>
  </div>
</div>
<form id="form-keluar" method="post" action="<?= e(url('keluar')) ?>" hidden>
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
</form>
<noscript><p class="spanduk">Nyalakan JavaScript untuk mengerjakan ujian.</p></noscript>
<script src="<?= e(aset('aset/ujian.js')) ?>"></script>
</body>
</html>
