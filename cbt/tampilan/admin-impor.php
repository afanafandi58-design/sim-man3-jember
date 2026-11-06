<p><a href="<?= e(url('admin/sesi', ['mapel' => $sesi['mapel_id']])) ?>"><?= e((string) $sesi['nama_mapel']) ?></a></p>
<h1>Impor soal</h1>
<p>Gunakan templat Excel atau CSV. Berkas Word tidak didukung.</p>
<p class="aksi">
  <a class="tombol" href="<?= e(url('admin/templat', ['jenis' => 'soal', 'format' => 'xlsx'])) ?>">Templat soal Excel</a>
  <a class="tombol" href="<?= e(url('admin/templat', ['jenis' => 'soal', 'format' => 'csv'])) ?>">Templat soal CSV</a>
  <a class="tombol" href="<?= e(url('admin/templat', ['jenis' => 'kunci', 'format' => 'xlsx'])) ?>">Templat kunci Excel</a>
  <a class="tombol" href="<?= e(url('admin/templat', ['jenis' => 'kunci', 'format' => 'csv'])) ?>">Templat kunci CSV</a>
</p>
<form method="post" action="<?= e(url('admin/impor', ['sesi' => $sesi['id']])) ?>" enctype="multipart/form-data" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
  <label>Berkas soal .xlsx atau .csv<input type="file" name="berkas" accept=".csv,.xlsx,text/csv" required></label>
  <label class="centang"><input type="checkbox" name="timpa" value="1"> Hapus soal lama yang tidak ada di berkas ini</label>
  <button type="submit">Impor soal</button>
</form>
<form method="post" action="<?= e(url('admin/kunci')) ?>" enctype="multipart/form-data" class="kartu">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="sesi_id" value="<?= (int) $sesi['id'] ?>">
  <label>Berkas kunci jawaban<input type="file" name="berkas" accept=".csv,.xlsx,text/csv" required></label>
  <button type="submit">Perbarui kunci</button>
</form>
<div class="kartu">
  <h2>Isi templat</h2>
  <ul>
    <li><code>tipe</code>: pg, pgk, atau menjodohkan.</li>
    <li><code>kunci</code> pilihan ganda: B. Kompleks: B,D. Menjodohkan: 1-B;2-A.</li>
    <li>Jika kunci menjodohkan kosong, urutan kolom dianggap pasangan yang benar.</li>
    <li><code>audio</code>: nama berkas setelah diunggah, misalnya lewat formulir soal. Pemutar siswa mengambil berkas statis.</li>
  </ul>
</div>
