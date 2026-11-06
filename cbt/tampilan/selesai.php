<h1>Ujian selesai</h1>
<div class="kartu">
  <p><?= e((string) $pengerjaan['nama_mapel']) ?></p>
  <p class="skor"><?= e((string) $pengerjaan['skor']) ?></p>
  <ul class="ringkas">
    <li>Benar: <?= (int) $pengerjaan['jumlah_benar'] ?></li>
    <li>Salah: <?= (int) $pengerjaan['jumlah_salah'] ?></li>
    <li>Kosong: <?= (int) $pengerjaan['jumlah_kosong'] ?></li>
  </ul>
  <p>Hubungi pengawas jika perlu mengulang.</p>
</div>
