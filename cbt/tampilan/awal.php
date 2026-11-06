<?php
declare(strict_types=1);
/** @var string $judul */
/** @var string $nav */
/** @var string $csrf */
/** @var string|null $flash */
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($judul) ?> · CBT MAN 3 Jember</title>
<link rel="stylesheet" href="<?= e(aset('aset/gaya.css')) ?>">
</head>
<body>
<header class="atas">
  <a class="merek" href="<?= e($nav === 'admin' ? url('admin/mapel') : url('masuk')) ?>">CBT MAN 3 Jember</a>
  <?php if ($nav === 'admin'): ?>
  <nav>
    <a href="<?= e(url('admin/mapel')) ?>">Mata pelajaran</a>
    <a href="<?= e(url('admin/siswa')) ?>">Siswa</a>
    <form method="post" action="<?= e(url('admin/keluar')) ?>">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <button type="submit" class="teks">Keluar</button>
    </form>
  </nav>
  <?php elseif ($nav === 'siswa' && !empty($_SESSION['siswa_id'])): ?>
  <form method="post" action="<?= e(url('keluar')) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <button type="submit" class="teks">Keluar</button>
  </form>
  <?php endif; ?>
</header>
<main class="isi">
<?php if (!empty($flash)): ?>
<p class="flash"><?= e($flash) ?></p>
<?php endif; ?>
