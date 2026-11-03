<div class="panel">
    <h2>Upload Excel Buku Induk</h2>
    <p style="color:var(--muted);margin-top:0;">Unggah file <strong>.xlsx / .xlsm</strong> berisi sheet <code>DATA_SISWA</code> (atau template flat dengan header Nis Lokal, NISN, NAMA, …). Data di-upsert berdasarkan NIS Lokal.</p>

    <form method="post" action="<?php echo e(url('/import')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div style="margin-bottom:14px;max-width:480px;">
            <label for="excel">File Excel</label>
            <input type="file" id="excel" name="excel" accept=".xlsx,.xlsm,.xls" required>
        </div>
        <button class="btn" type="submit">Impor sekarang</button>
    </form>

    <?php if (!empty($result) && is_array($result)): ?>
        <div style="margin-top:20px;">
            <h3 style="margin-bottom:8px;">Hasil impor</h3>
            <ul>
                <li>Total baris diproses: <?php echo (int) $result['total']; ?></li>
                <li>Sukses: <?php echo (int) $result['sukses']; ?> (baru: <?php echo (int) $result['created']; ?>, diperbarui: <?php echo (int) $result['updated']; ?>)</li>
                <li>Gagal: <?php echo (int) $result['gagal']; ?></li>
            </ul>
            <?php if (!empty($result['errors'])): ?>
                <div class="alert alert-error">
                    <?php foreach ($result['errors'] as $err): ?>
                        <div><?php echo e($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
