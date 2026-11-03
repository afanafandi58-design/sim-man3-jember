<?php
$admin = $authUser['role'] === 'admin';
$scanUrl = absen_base_url().'/absen-digital/scan?kode='.rawurlencode($kegiatan['kode']);
?>
<p class="muted"><?php echo absen_e(isset($jenisMap[$kegiatan['jenis']]) ? $jenisMap[$kegiatan['jenis']] : $kegiatan['jenis']); ?> · <?php echo absen_e(absen_tanggal_label($kegiatan['tanggal'])); ?><?php $rentang = absen_rentang_label($kegiatan); echo $rentang !== '' ? ' · absen '.absen_e($rentang) : ''; ?></p>
<?php if ($rentang === '') { ?><div class="alert alert-warning">Rentang waktu absen belum diatur. Peserta belum bisa menyimpan absen.</div><?php } ?>
<p class="muted">Setiap perangkat Android hanya dapat menyimpan satu absen pada kegiatan ini.</p>
<?php if ($admin) { ?><p><a class="btn" href="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/ubah">Ubah</a></p><?php } ?>
<div class="row">
    <div>
        <div class="card" style="text-align:center">
            <strong>Kode QR</strong>
            <div id="qrcode" data-text="<?php echo absen_e($scanUrl); ?>"></div>
            <p><strong>Kode:</strong> <span style="font-size:24px"><?php echo absen_e($kegiatan['kode']); ?></span></p>
            <p class="muted">Tampilkan barcode ini saat acara. Peserta mengetuk nama kegiatan, memilih identitas, mengunggah foto, lalu menandatangani di layar.</p>
        </div>
        <div class="card">
            <p><strong>Tempat:</strong> <?php echo absen_e($kegiatan['tempat'] ? $kegiatan['tempat'] : '—'); ?></p>
            <p><strong>Keterangan:</strong> <?php echo absen_e($kegiatan['keterangan'] ? $kegiatan['keterangan'] : '—'); ?></p>
        </div>
    </div>
    <div>
        <div class="card" style="padding:0">
            <div style="padding:16px"><strong>Daftar hadir (<?php echo count($hadir); ?>)</strong></div>
            <table>
                <thead><tr><th>Nama</th><th>Cara</th><th>Waktu</th><?php if ($admin) { ?><th>Lokasi</th><th>Bukti</th><?php } ?></tr></thead>
                <tbody>
                <?php if (!$hadir) { ?><tr><td colspan="5">Belum ada yang hadir.</td></tr><?php } ?>
                <?php foreach ($hadir as $row) { ?>
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
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($admin) { ?>
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
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/foto/<?php echo (int) $foto['id']; ?>/hapus" onsubmit="return confirm('Hapus foto ini?')">
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
                    <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/hasil-berkas/hapus" onsubmit="return confirm('Hapus berkas hasil rapat dari hosting?')">
                        <?php echo absen_csrf_field(); ?>
                        <button class="btn" type="submit">Hapus berkas hosting</button>
                    </form>
                <?php } ?>
            </div>
            <form method="post" action="/absen-digital/kegiatan/<?php echo (int) $kegiatan['id']; ?>/hapus" onsubmit="return confirm('Hapus kegiatan ini beserta daftar hadirnya?')">
                <?php echo absen_csrf_field(); ?>
                <button class="btn btn-danger" type="submit">Hapus kegiatan</button>
            </form>
        <?php } elseif ($kegiatan['hasil_drive_url']) { ?>
            <div class="card"><a href="<?php echo absen_e($kegiatan['hasil_drive_url']); ?>" target="_blank" rel="noopener">Hasil di Google Drive</a></div>
        <?php } ?>
    </div>
</div>
