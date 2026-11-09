<?php
$admin = $authUser['role'] === 'admin';
$scanUrl = absen_tautan_absen($kegiatan);
?>
<p class="muted"><?php echo absen_e(isset($jenisMap[$kegiatan['jenis']]) ? $jenisMap[$kegiatan['jenis']] : $kegiatan['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?><?php $rentang = absen_rentang_label($kegiatan); echo $rentang !== '' ? ' · absen '.absen_e($rentang) : ''; ?></p>
<?php if ($rentang === '') { ?><div class="alert alert-warning">Rentang waktu absen belum diatur. Peserta belum bisa menyimpan absen.</div><?php } ?>
<p class="muted">Setiap perangkat Android hanya dapat menyimpan satu absen pada kegiatan ini. Kunci tetap ada walaupun cache atau cookie dihapus.</p>
<?php if ($admin) { ?><p><a class="btn" href="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/ubah">Ubah</a></p><?php } ?>
<div class="row">
    <div>
        <div class="card" style="text-align:center">
            <h2 id="nama-qr"><?php echo absen_e($kegiatan['nama']); ?></h2>
            <p class="muted"><?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?><?php echo $rentang !== '' ? ' · absen '.absen_e($rentang) : ''; ?></p>
            <div id="qrcode" data-text="<?php echo absen_e($scanUrl); ?>"></div>
            <p>QR ini berisi tautan absen untuk kegiatan di atas. Nama dan rentang waktu mengikuti data terbaru.</p>
            <p><a class="btn btn-success" href="<?php echo absen_e($scanUrl); ?>">Buka halaman absen</a></p>
            <p class="muted">Peserta dapat memindai QR atau membuka tautan yang sama. Memindai tidak wajib.</p>
            <p><strong>Kode:</strong> <span style="font-size:24px"><?php echo absen_e($kegiatan['kode']); ?></span></p>
        </div>
        <div class="card">
            <p><strong>Tempat:</strong> <?php echo absen_e($kegiatan['tempat'] ? $kegiatan['tempat'] : '—'); ?></p>
            <?php $pusat = absen_lokasi_kegiatan($kegiatan); ?>
            <?php if (!absen_lokasi_wajib($kegiatan)) { ?>
                <p><strong>Aturan lokasi:</strong> Bebas, absen di mana saja</p>
                <p class="muted">Peserta boleh absen dari mana saja. GPS tidak menolak absen.</p>
            <?php } else { ?>
                <p><strong>Titik lokasi:</strong> <?php echo $pusat ? absen_e($pusat[0].', '.$pusat[1]) : 'Belum ditentukan'; ?></p>
                <p class="muted">Peserta hanya bisa absen dalam radius 75 meter dari titik lokasi.</p>
            <?php } ?>
            <p><strong>Keterangan:</strong> <?php echo absen_e($kegiatan['keterangan'] ? $kegiatan['keterangan'] : '—'); ?></p>
        </div>
    </div>
    <div>
        <div class="card" style="padding:0">
            <div style="padding:16px"><strong>Daftar hadir (<?php echo count($hadir); ?>)</strong></div>
            <table>
                <thead><tr><th>Nama</th><th>Cara</th><th>Waktu</th><?php if ($admin) { ?><th>Lokasi</th><th>Bukti</th><th>Status</th><?php } ?></tr></thead>
                <tbody>
                <?php if (!$hadir) { ?><tr><td colspan="<?php echo $admin ? 6 : 3; ?>">Belum ada yang hadir.</td></tr><?php } ?>
                <?php
                $userAktif = array();
                foreach ($hadir as $row) {
                    if (!empty($row['user_id']) && (string) $row['kunci_aktif'] === '1') {
                        $userAktif[(int) $row['user_id']] = true;
                    }
                }
                foreach ($hadir as $row) {
                    $kunciAktif = (string) $row['kunci_aktif'] === '1';
                ?>
                    <tr>
                        <td><?php echo absen_e($row['nama']); ?><?php if ($row['nik']) { ?><div class="muted"><?php echo absen_e($row['nik']); ?></div><?php } ?></td>
                        <td><?php echo absen_e(absen_metode_label($row['metode'])); ?></td>
                        <td><?php echo absen_e($row['created_at']); ?></td>
                        <?php if ($admin) { ?>
                            <td><?php echo ($row['latitude'] !== null && $row['latitude'] !== '') ? absen_e($row['latitude'].', '.$row['longitude']) : '—'; ?></td>
                            <td>
                                <?php if ($row['foto_path']) { ?><a href="/absen-digital/berkas/foto/<?php echo (int) $row['id']; ?>" target="_blank" rel="noopener">Foto</a><?php } ?>
                                <?php if ($row['ttd_path']) { ?><a href="/absen-digital/berkas/ttd/<?php echo (int) $row['id']; ?>" target="_blank" rel="noopener">TTD</a><?php } ?>
                            </td>
                            <td>
                                <?php if ($kunciAktif) { ?>
                                    <span class="tanda tanda-kunci">Perangkat terkunci</span>
                                    <?php if (!empty($row['user_id'])) { ?>
                                        <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/reset-kunci" data-konfirmasi="Buka kunci perangkat untuk peserta ini? Data absen yang sudah tersimpan tidak dihapus.">
                                            <?php echo absen_csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo (int) $row['user_id']; ?>">
                                            <button class="btn" type="submit">Reset kunci</button>
                                        </form>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="tanda tanda-buka">Kunci dibuka, data tersimpan</span>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($admin) {
            if (!isset($gangguan) || !is_array($gangguan)) {
                $gangguan = array();
            }
            $masalah = array();
            foreach ($gangguan as $item) {
                $uid = !empty($item['user_id']) ? (int) $item['user_id'] : 0;
                if ($uid > 0 && isset($userAktif[$uid])) {
                    continue;
                }
                $masalah[] = $item;
            }
        ?>
            <?php $belumAbsen = absen_daftar_belum_absen($kegiatan['id']); ?>
            <div class="card">
                <strong>Yang belum absen (<?php echo count($belumAbsen); ?>)</strong>
                <p class="muted">Daftar ini hanya peserta yang belum berhasil absen pada kegiatan ini. Centang satu atau beberapa nama, lalu reset supaya Android mereka bisa mencoba lagi. Yang sudah hadir tidak ada di daftar ini dan tidak ikut direset. Absen, foto, tanda tangan, nama, waktu, dan titik yang sudah tersimpan tidak diubah.</p>
                <?php if (!$belumAbsen) { ?><p>Semua peserta sudah absen.</p><?php } else { ?>
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/reset-belum" data-konfirmasi="Reset nama yang dipilih supaya bisa mencoba absen lagi? Yang sudah hadir tidak diubah.">
                        <?php echo absen_csrf_field(); ?>
                        <div style="max-height:420px;overflow:auto">
                            <table>
                                <thead><tr><th>Pilih</th><th>Yang belum absen</th><th>Keadaan</th></tr></thead>
                                <tbody>
                                <?php foreach ($belumAbsen as $orang) { ?>
                                    <tr>
                                        <td><input type="checkbox" name="pilihan[]" value="<?php echo (int) $orang['id']; ?>" aria-label="Pilih <?php echo absen_e($orang['name']); ?>" style="width:22px;height:22px"></td>
                                        <td><?php echo absen_e($orang['name']); ?><?php if (!empty($orang['nip'])) { ?><div class="muted"><?php echo absen_e($orang['nip']); ?></div><?php } ?></td>
                                        <td><?php echo absen_e($orang['keterangan'] ? $orang['keterangan'] : 'Belum absen'); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <p><button class="btn btn-danger" type="submit">Reset yang dipilih</button></p>
                    </form>
                <?php } ?>
            </div>
            <div class="card">
                <strong>Tidak terbaca atau gagal masuk</strong>
                <p class="muted">Catatan peserta yang belum berhasil absen. Reset namanya lewat daftar Yang belum absen. Yang sudah hadir tidak ikut.</p>
                <?php
                $tanpaNama = 0;
                $tandaLama = array('Lokasi tidak terbaca.', 'Sidik perangkat tidak terbaca.', 'Pemeriksaan perangkat terlalu lama.');
                foreach ($masalah as $item) {
                    if (empty($item['user_id']) && (empty($item['keterangan']) || in_array($item['keterangan'], $tandaLama, true))) {
                        $tanpaNama++;
                    }
                }
                if ($tanpaNama > 0) { ?>
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/bersih-tanda" data-konfirmasi="Hapus tanda yang belum terhubung ke nama? Data absen yang sudah tersimpan tidak diubah.">
                        <?php echo absen_csrf_field(); ?>
                        <button class="btn" type="submit">Hapus tanda tanpa nama</button>
                    </form>
                <?php } ?>
                <?php if (!$masalah) { ?><p>Tidak ada peserta yang gagal masuk.</p><?php } ?>
                <?php if ($masalah) { ?>
                    <table>
                        <thead><tr><th>Nama</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($masalah as $item) { ?>
                            <tr>
                                <td><?php echo absen_e($item['nama'] ? $item['nama'] : 'Perangkat belum terhubung ke nama'); ?></td>
                                <td>
                                    <span class="tanda tanda-gagal"><?php echo $item['jenis'] === 'tidak_terbaca' ? 'Tidak terbaca' : 'Gagal masuk'; ?></span>
                                    <?php if ($item['keterangan']) { ?><div class="muted"><?php echo absen_e($item['keterangan']); ?></div><?php } ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['user_id'])) { ?>
                                        <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/reset-belum" data-konfirmasi="Reset peserta ini supaya bisa mencoba absen lagi? Yang sudah hadir tidak diubah.">
                                            <?php echo absen_csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo (int) $item['user_id']; ?>">
                                            <button class="btn" type="submit">Reset</button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
            <div class="card">
                <strong>Foto dokumentasi</strong>
                <?php if (absen_error('foto')) { ?><p class="error"><?php echo absen_e(absen_error('foto')); ?></p><?php } ?>
                <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/foto" enctype="multipart/form-data">
                    <?php echo absen_csrf_field(); ?>
                    <p><label for="foto">Foto</label><input id="foto" type="file" name="foto" accept="image/*" capture="environment" required></p>
                    <p><label for="keterangan-foto">Keterangan</label><input id="keterangan-foto" name="keterangan" maxlength="150"></p>
                    <button class="btn btn-success" type="submit">Unggah</button>
                </form>
                <?php foreach ($fotos as $foto) { ?>
                    <p>
                        <a href="/absen-digital/berkas/dokumentasi/<?php echo (int) $foto['id']; ?>" target="_blank" rel="noopener"><?php echo absen_e($foto['keterangan'] ? $foto['keterangan'] : 'Foto dokumentasi'); ?></a>
                    </p>
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/foto/<?php echo (int) $foto['id']; ?>/hapus" data-konfirmasi="Hapus foto ini?">
                        <?php echo absen_csrf_field(); ?>
                        <button class="btn" type="submit">Hapus foto</button>
                    </form>
                <?php } ?>
            </div>
            <div class="card">
                <strong>Hasil rapat</strong>
                <?php if (absen_error('hasil_drive_url')) { ?><p class="error"><?php echo absen_e(absen_error('hasil_drive_url')); ?></p><?php } ?>
                <?php if (absen_error('hasil_file')) { ?><p class="error"><?php echo absen_e(absen_error('hasil_file')); ?></p><?php } ?>
                <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/hasil" enctype="multipart/form-data">
                    <?php echo absen_csrf_field(); ?>
                    <p><label for="hasil_drive_url">Link Google Drive</label><input id="hasil_drive_url" type="url" name="hasil_drive_url" placeholder="https://drive.google.com/..." value="<?php echo absen_e(absen_old('hasil_drive_url', $kegiatan['hasil_drive_url'])); ?>"></p>
                    <p><label for="hasil_file">Berkas di hosting</label><input id="hasil_file" type="file" name="hasil_file">
                        <?php if ($kegiatan['hasil_file_path']) { ?><a href="/absen-digital/berkas/hasil/<?php echo (int) $kegiatan['id']; ?>"><?php echo absen_e($kegiatan['hasil_file_nama'] ? $kegiatan['hasil_file_nama'] : 'Unduh berkas'); ?></a><?php } ?>
                    </p>
                    <p><label for="hasil_catatan">Catatan</label><textarea id="hasil_catatan" name="hasil_catatan" rows="3" maxlength="2000"><?php echo absen_e(absen_old('hasil_catatan', $kegiatan['hasil_catatan'])); ?></textarea></p>
                    <button class="btn btn-success" type="submit">Simpan hasil</button>
                </form>
                <?php if ($kegiatan['hasil_file_path']) { ?>
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/hasil-berkas/hapus" data-konfirmasi="Hapus berkas hasil rapat dari hosting?">
                        <?php echo absen_csrf_field(); ?>
                        <button class="btn" type="submit">Hapus berkas hosting</button>
                    </form>
                <?php } ?>
            </div>
            <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/hapus" data-konfirmasi="Hapus kegiatan ini beserta daftar hadirnya?">
                <?php echo absen_csrf_field(); ?>
                <button class="btn btn-danger" type="submit">Hapus kegiatan</button>
            </form>
        <?php } elseif ($kegiatan['hasil_drive_url'] && absen_drive_ok($kegiatan['hasil_drive_url'])) { ?>
            <div class="card"><a href="<?php echo absen_e($kegiatan['hasil_drive_url']); ?>" target="_blank" rel="noopener">Hasil di Google Drive</a></div>
        <?php } ?>
    </div>
</div>
