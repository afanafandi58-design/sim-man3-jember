<div class="panel">
    <h2>Upload Excel Buku Induk</h2>
    <p style="color:var(--muted);margin-top:0;">Unggah file <strong>.xlsx / .xlsm</strong> berisi sheet <code>DATA_SISWA</code> (atau template flat dengan header Nis Lokal, NISN, NAMA, …). Data di-upsert berdasarkan NIS Lokal.</p>

    <div class="import-template-box" style="margin:0 0 18px;padding:14px 16px;border:1px solid var(--line);border-radius:12px;background:#f7faf6;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div>
                <strong style="color:var(--brand-dark);">Template resmi impor</strong>
                <div style="color:var(--muted);font-size:0.9rem;margin-top:2px;">Sheet <code>DATA_SISWA</code> · baris 1 header · baris 2 contoh pengisian</div>
            </div>
            <a class="btn btn-secondary" href="<?php echo e(url('/import/template')); ?>">Unduh template Excel (.xlsx)</a>
        </div>
        <div style="color:var(--muted);font-size:0.85rem;margin-bottom:6px;">Kolom yang diterima (<?php echo (int) count($template_columns); ?>):</div>
        <p class="import-columns" style="margin:0;font-size:0.88rem;line-height:1.55;color:var(--ink);">
            <?php echo e(implode(' · ', $template_columns)); ?>
        </p>
    </div>

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
