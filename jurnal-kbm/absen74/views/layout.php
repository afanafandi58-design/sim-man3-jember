<?php
$aktif = absen_path();
$admin = isset($authUser) && $authUser && $authUser['role'] === 'admin';
if ($admin) {
    $menu = array(
        array('Absen', '/absen-digital/scan'),
        array('Kegiatan', '/absen-digital/kegiatan'),
        array('Peserta', '/absen-digital/peserta'),
        array('Ringkasan', '/absen-digital/ringkasan'),
        array('Laporan', '/absen-digital/cetak'),
    );
} else {
    $menu = array(
        array('Absen', '/absen-digital/scan'),
        array('Masuk pengelola', '/login'),
    );
}
$success = absen_success();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo absen_e($title); ?> · Absen Digital</title>
    <link rel="stylesheet" href="/css/absen-digital.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", sans-serif; background: #f4f6f5; color: #1c2b22; }
        a { color: #1b5e20; }
        .shell { display: flex; min-height: 100vh; }
        .side { width: 240px; background: #1b5e20; color: #fff; padding: 20px 14px; }
        .brand { font-weight: 700; font-size: 18px; margin: 0 8px 4px; }
        .school { margin: 0 8px 18px; opacity: .85; font-size: 13px; }
        .side a { display: block; color: #fff; text-decoration: none; padding: 10px 12px; border-radius: 8px; margin-bottom: 4px; }
        .side a.active, .side a:hover { background: rgba(255,255,255,.16); }
        .main { flex: 1; padding: 20px; min-width: 0; }
        .top { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 16px; }
        h1 { font-size: 26px; margin: 0; }
        .card { background: #fff; border-radius: 12px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #c9d4cc; border-radius: 8px; font: inherit; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        .row > * { flex: 1; min-width: 180px; }
        .btn { display: inline-block; border: 0; border-radius: 8px; padding: 10px 16px; font: inherit; cursor: pointer; text-decoration: none; background: #e8eee9; color: #1c2b22; }
        .btn-lg { padding: 12px 18px; font-size: 16px; }
        .btn-success { background: #2e7d32; color: #fff; }
        .btn-danger { background: #b71c1c; color: #fff; }
        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 12px; }
        .alert-success { background: #e8f5e9; }
        .alert-danger { background: #ffebee; }
        .alert-warning { background: #fff8e1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e4ebe6; vertical-align: top; }
        .muted { color: #5f6f64; }
        .catatan { background: #e8f5e9; border-radius: 8px; padding: 10px 12px; margin: 0 0 16px; font-size: 14px; }
        .error { color: #b71c1c; font-size: 14px; }
        .who { font-size: 14px; }
        .ttd-kotak { position: relative; z-index: 2; display: block; width: 100%; height: 320px; margin: 8px 0 12px; touch-action: none; }
        #ttd-canvas { position: relative; z-index: 3; width: 100%; height: 320px; touch-action: none; background: #fff; border: 2px dashed #2e7d32; border-radius: 12px; display: block; pointer-events: auto; user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; }
        #daftar-identitas { max-height: 360px; overflow: auto; border: 1px solid #c9d4cc; border-radius: 8px; margin: 8px 0 12px; }
        .identitas { display: flex; align-items: center; gap: 12px; padding: 12px; border-bottom: 1px solid #e4ebe6; font-weight: 600; }
        .identitas input { width: 22px; height: 22px; margin: 0; flex: 0 0 auto; }
        .pilihan-lokasi { display: flex; flex-direction: column; gap: 8px; margin: 8px 0 12px; }
        .pilihan-lokasi label { display: flex; align-items: flex-start; gap: 10px; font-weight: 600; }
        .pilihan-lokasi input { width: 22px; height: 22px; margin: 2px 0 0; flex: 0 0 auto; }
        #btn-kegiatan, #nama-qr { width: 100%; text-align: center; font-size: 28px; margin: 0 0 8px; }
        #qrcode { display: inline-block; margin: 8px auto; background: #fff; padding: 8px; }
        #peta-lokasi { height: 340px; border: 1px solid #c9d4cc; border-radius: 8px; margin: 8px 0 12px; background: #e8eee9; }
        .pin-absen { background: #b71c1c; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 0 0 2px #1b5e20; }
        .tanda { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 12px; font-weight: 700; }
        .tanda-kunci { background: #fff8e1; color: #8a6d00; }
        .tanda-gagal { background: #ffebee; color: #b71c1c; }
        .tanda-buka { background: #e8f5e9; color: #1b5e20; }
        @media (max-width: 800px) {
            .shell { display: block; }
            .side { width: auto; }
            .btn-lg { width: 100%; margin-bottom: 8px; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="side">
        <p class="brand">Absen Digital</p>
        <p class="school">MAN 3 Jember</p>
        <?php foreach ($menu as $item) { ?>
            <a class="<?php echo strpos($aktif, $item[1]) === 0 ? 'active' : ''; ?>" href="<?php echo absen_e($item[1]); ?>"><?php echo absen_e($item[0]); ?></a>
        <?php } ?>
    </aside>
    <main class="main">
        <div class="top">
            <h1><?php echo absen_e($title); ?></h1>
            <?php if ($admin) { ?>
                <form method="post" action="/logout" class="who">
                    <?php echo absen_csrf_field(); ?>
                    <span><?php echo absen_e($authUser['name']); ?></span>
                    <button class="btn" type="submit">Keluar</button>
                </form>
            <?php } ?>
        </div>
        <?php if (strpos($aktif, '/absen-digital') === 0) { ?>
            <p class="catatan">Catatan: Buka tautan atau QR. Nama kegiatan tampil di atas QR. Pilih identitas, foto di bawah 1 MB, lalu tanda tangan. Jika kegiatan memakai titik lokasi, GPS harus dalam 75 meter. Jika kegiatan bebas lokasi, absen boleh dari mana saja dan GPS tidak menolak. Absen hanya dalam rentang waktu. Satu Android hanya satu absen, lalu terkunci. Menghapus cache atau cookie tidak membuka kunci itu. Satu identitas hanya sekali.</p>
        <?php } ?>
        <?php if ($success !== '') { ?><div class="alert alert-success"><?php echo absen_e($success); ?></div><?php } ?>
        <?php echo $isi; ?>
    </main>
</div>
<script src="/vendor/absen-digital/qrcode.min.js"></script>
<script src="/js/absen-digital.js?v=proses2"></script>
</body>
</html>
