<?php
$tipeAktif = (string) ($soal['tipe'] ?? 'pg');
$teksPasangan = static function (array $pasangan, int $i, string $sisi): string {
    $baris = $pasangan[$i] ?? [];
    return e((string) ($baris[$sisi] ?? ''));
};
?>
<p><a href="<?= e(url('admin/soal', ['sesi' => $sesi['id']])) ?>">Daftar soal</a></p>
<h1><?= $soal ? 'Ubah soal' : 'Soal baru' ?></h1>
<form method="post" action="<?= e(url('admin/soal/simpan')) ?>" enctype="multipart/form-data" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
  <input type="hidden" name="id" value="<?= (int) ($soal['id'] ?? 0) ?>">
  <input type="hidden" name="audio_lama" value="<?= e((string) ($soal['audio'] ?? '')) ?>">
  <label>Nomor<input name="nomor" type="number" min="1" max="500" required value="<?= (int) $nomor ?>"></label>
  <label>Tipe
    <select name="tipe" id="tipe">
      <?php foreach (['pg' => 'Pilihan ganda', 'pgk' => 'Pilihan ganda kompleks', 'menjodohkan' => 'Menjodohkan'] as $kode => $label): ?>
        <option value="<?= e($kode) ?>" <?= $tipeAktif === $kode ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Pertanyaan<textarea name="pertanyaan" rows="4" required><?= e((string) ($soal['pertanyaan'] ?? '')) ?></textarea></label>
  <fieldset>
    <legend>Audio menyimak (Bahasa Arab / Bahasa Inggris)</legend>
    <?php if (!empty($soal['audio'])): ?><p>Berkas saat ini: <?= e((string) $soal['audio']) ?></p><?php endif; ?>
    <label>Unggah mp3, ogg, wav, atau m4a (maks. 8 MB)<input type="file" name="audio" accept=".mp3,.ogg,.wav,.m4a,audio/*"></label>
    <label class="centang"><input type="checkbox" name="hapus_audio" value="1"> Hapus audio</label>
  </fieldset>
  <div data-untuk="pg pgk">
    <?php foreach (['A', 'B', 'C', 'D', 'E'] as $kode): ?>
      <label>Pilihan <?= e($kode) ?><input name="opsi_<?= e(strtolower($kode)) ?>" maxlength="2000" value="<?= e((string) ($pilihan[$kode] ?? '')) ?>"></label>
    <?php endforeach; ?>
    <div data-untuk="pg">
      <p>Jawaban benar</p>
      <?php foreach (['A', 'B', 'C', 'D', 'E'] as $kode): ?>
        <label class="centang"><input type="radio" name="benar_pg" value="<?= e($kode) ?>" <?= in_array($kode, $benar, true) && $tipeAktif === 'pg' ? 'checked' : '' ?>> <?= e($kode) ?></label>
      <?php endforeach; ?>
    </div>
    <div data-untuk="pgk">
      <p>Jawaban benar (lebih dari satu)</p>
      <?php foreach (['A', 'B', 'C', 'D', 'E'] as $kode): ?>
        <label class="centang"><input type="checkbox" name="benar_pgk[]" value="<?= e($kode) ?>" <?= in_array($kode, $benar, true) && $tipeAktif === 'pgk' ? 'checked' : '' ?>> <?= e($kode) ?></label>
      <?php endforeach; ?>
    </div>
  </div>
  <div data-untuk="menjodohkan">
    <p>Setiap baris adalah pasangan yang benar. Butir kanan diacak saat ujian.</p>
    <?php for ($i = 0; $i < 5; $i++): ?>
      <div class="baris-form">
        <label>Kiri <?= $i + 1 ?><input name="kiri_<?= $i + 1 ?>" maxlength="1000" value="<?= $teksPasangan($pasangan, $i, 'kiri_teks') ?>"></label>
        <label>Kanan <?= $i + 1 ?><input name="kanan_<?= $i + 1 ?>" maxlength="1000" value="<?= $teksPasangan($pasangan, $i, 'kanan_teks') ?>"></label>
      </div>
    <?php endfor; ?>
  </div>
  <button type="submit">Simpan soal</button>
</form>
<script>
(function () {
  var tipe = document.getElementById('tipe');
  function segar() {
    document.querySelectorAll('[data-untuk]').forEach(function (el) {
      el.hidden = el.getAttribute('data-untuk').split(' ').indexOf(tipe.value) === -1;
    });
  }
  tipe.addEventListener('change', segar);
  segar();
})();
</script>
