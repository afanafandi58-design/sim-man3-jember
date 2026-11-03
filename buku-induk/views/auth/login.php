<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Buku Induk MAN 3 Jember</title>
    <link rel="stylesheet" href="<?php echo e(url('/assets/css/app.css')); ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h1>Buku Induk</h1>
        <p>MAN 3 Jember — masuk sebagai admin</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo e(url('/login')); ?>">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button class="btn" type="submit" style="width:100%;margin-top:8px;">Masuk</button>
        </form>
    </div>
</div>
</body>
</html>
