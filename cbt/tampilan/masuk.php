<h1>Masuk ujian</h1>
<form method="post" action="<?= e(url('masuk')) ?>" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <label>Nama pengguna<input name="username" autocomplete="username" required maxlength="32"></label>
  <label>Kata sandi<input name="sandi" type="password" autocomplete="current-password" required></label>
  <button type="submit">Masuk</button>
</form>
<p class="redup"><a href="<?= e(url('admin/masuk')) ?>">Masuk pengawas</a></p>
