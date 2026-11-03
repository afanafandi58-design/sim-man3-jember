<?php
$user = current_user();
$currentPath = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(isset($title) ? $title . ' — ' : ''); ?>Buku Induk MAN 3 Jember</title>
    <link rel="stylesheet" href="<?php echo e(url('/assets/css/app.css')); ?>">
</head>
<body>
<div class="shell">
    <div class="topbar">
        <div class="brand">
            Buku Induk
            <small>MAN 3 Jember</small>
        </div>
        <nav class="nav">
            <a href="<?php echo e(url('/')); ?>" class="<?php echo strpos((string)$currentPath, '/siswa') === false && strpos((string)$currentPath, '/import') === false && strpos((string)$currentPath, '/settings') === false ? 'active' : ''; ?>">Dasbor</a>
            <a href="<?php echo e(url('/siswa')); ?>">Siswa</a>
            <a href="<?php echo e(url('/siswa/create')); ?>">Input Manual</a>
            <a href="<?php echo e(url('/import')); ?>">Upload Excel</a>
            <a href="<?php echo e(url('/settings')); ?>">Pengaturan</a>
            <a href="<?php echo e(url('/logout')); ?>">Keluar<?php echo $user ? ' (' . e($user['username']) . ')' : ''; ?></a>
        </nav>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php
    $contentFile = dirname(__DIR__) . '/' . $content . '.php';
    if ($content === 'dashboard') {
        require dirname(__DIR__) . '/dashboard.php';
    } else {
        require $contentFile;
    }
    ?>
</div>
</body>
</html>
