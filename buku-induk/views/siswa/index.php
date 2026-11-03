<div class="panel">
    <h2>Data Siswa</h2>
    <form class="toolbar" method="get" action="<?php echo e(url('/siswa')); ?>">
        <div class="grow">
            <label for="q">Cari nama / NIS / NISN</label>
            <input type="text" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Contoh: Ahmad atau 250001">
        </div>
        <div>
            <label for="tingkat">Tingkat</label>
            <select id="tingkat" name="tingkat">
                <option value="">Semua</option>
                <?php foreach ($tingkatList as $t): ?>
                    <option value="<?php echo e($t); ?>" <?php echo $tingkat === $t ? 'selected' : ''; ?>><?php echo e($t); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Filter</button>
        <a class="btn btn-secondary" href="<?php echo e(url('/siswa/create')); ?>">Tambah</a>
    </form>

    <table class="data">
        <thead>
        <tr>
            <th>NIS Lokal</th>
            <th>NISN</th>
            <th>Nama</th>
            <th>L/P</th>
            <th>Tingkat</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6">Belum ada data.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo e($row['nis_lokal']); ?></td>
                    <td><?php echo e($row['nisn']); ?></td>
                    <td><?php echo e($row['nama']); ?></td>
                    <td><?php echo e($row['jenis_kelamin']); ?></td>
                    <td><?php echo e($row['tingkat_awal']); ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-sm btn-secondary" href="<?php echo e(url('/siswa/edit/' . $row['id'])); ?>">Edit</a>
                            <a class="btn btn-sm" href="<?php echo e(url('/cetak/' . $row['id'])); ?>" target="_blank">Cetak</a>
                            <a class="btn btn-sm btn-secondary" href="<?php echo e(url('/cetak/' . $row['id'] . '/pdf')); ?>">PDF</a>
                            <form method="post" action="<?php echo e(url('/siswa/delete/' . $row['id'])); ?>" onsubmit="return confirm('Hapus data siswa ini?');" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    $pages = max(1, (int) ceil($total / $perPage));
    if ($pages > 1):
    ?>
        <div class="pager">
            <span>Halaman <?php echo (int) $page; ?> / <?php echo $pages; ?> (<?php echo (int) $total; ?> data)</span>
            <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-secondary" href="<?php echo e(url('/siswa?q=' . urlencode($q) . '&tingkat=' . urlencode($tingkat) . '&page=' . ($page - 1))); ?>">Sebelumnya</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
                <a class="btn btn-sm btn-secondary" href="<?php echo e(url('/siswa?q=' . urlencode($q) . '&tingkat=' . urlencode($tingkat) . '&page=' . ($page + 1))); ?>">Berikutnya</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
