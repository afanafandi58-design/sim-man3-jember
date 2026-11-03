<div class="hero">
    <h1><?php echo e($sekolah['nama']); ?></h1>
    <p>Kelola data Buku Induk secara daring — input manual, impor Excel, dan cetak/PDF lembar induk siswa.</p>
</div>

<div class="grid">
    <div class="stat">
        <div class="label">Total siswa</div>
        <div class="value"><?php echo (int) $totalSiswa; ?></div>
    </div>
    <div class="stat">
        <div class="label">NSM</div>
        <div class="value" style="font-size:1.1rem;margin-top:10px;"><?php echo e($sekolah['nsm']); ?></div>
    </div>
    <div class="stat">
        <div class="label">NPSN</div>
        <div class="value" style="font-size:1.1rem;margin-top:10px;"><?php echo e($sekolah['npsn']); ?></div>
    </div>
</div>

<div class="panel">
    <h2>Aksi cepat</h2>
    <div class="actions">
        <a class="btn" href="<?php echo e(url('/siswa/create')); ?>">Tambah siswa</a>
        <a class="btn btn-secondary" href="<?php echo e(url('/import')); ?>">Upload Excel</a>
        <a class="btn btn-secondary" href="<?php echo e(url('/siswa')); ?>">Lihat daftar</a>
    </div>
    <?php if (!empty($tingkatList)): ?>
        <p style="margin-top:16px;color:var(--muted);font-size:0.92rem;">
            Tingkat terdaftar:
            <?php echo e(implode(', ', $tingkatList)); ?>
        </p>
    <?php endif; ?>
</div>
