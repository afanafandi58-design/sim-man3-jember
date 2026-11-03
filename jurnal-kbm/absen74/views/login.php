<div class="card" style="max-width:420px">
    <p class="muted">Masuk untuk membuka menu Absen Digital.</p>
    <?php if (absen_error('email') !== '') { ?><div class="alert alert-danger"><?php echo absen_e(absen_error('email')); ?></div><?php } ?>
    <form method="post" action="/login">
        <?php echo absen_csrf_field(); ?>
        <p><label for="email">Email</label><input id="email" name="email" type="email" required value="<?php echo absen_e(absen_old('email')); ?>"></p>
        <p><label for="password">Kata sandi</label><input id="password" name="password" type="password" required></p>
        <button class="btn btn-success btn-lg" type="submit">Masuk</button>
    </form>
</div>
