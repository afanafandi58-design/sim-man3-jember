<h1>Token ujian</h1>
<p>Halo, <?= e((string) $siswa['nama']) ?>. Masukkan token yang dibacakan pengawas.</p>
<form method="post" action="<?= e(url('token')) ?>" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <label>Token<input name="token" autocomplete="off" required maxlength="16" autocapitalize="characters"></label>
  <button type="submit">Mulai</button>
</form>
