<?php
declare(strict_types=1);

require dirname(__DIR__) . '/pustaka/bootstrap.php';

$env = $GLOBALS['cbt_env'];
$username = strtolower(trim((string) ($env['CBT_ADMIN_USER'] ?? '')));
$sandi = (string) ($env['CBT_ADMIN_PASSWORD'] ?? '');
$nama = trim((string) ($env['CBT_ADMIN_NAMA'] ?? 'Admin CBT'));
if (!preg_match('/^[a-z0-9._-]{3,64}$/', $username)) {
    fwrite(STDERR, "Isi CBT_ADMIN_USER di .env.\n");
    exit(1);
}
if (strlen($sandi) < 8) {
    fwrite(STDERR, "Isi CBT_ADMIN_PASSWORD di .env (minimal 8 karakter). Jangan menyimpan kata sandi di git.\n");
    exit(1);
}

$db = Db::pdo();
$hash = password_hash($sandi, PASSWORD_DEFAULT);
$ada = $db->prepare('SELECT id FROM pengawas WHERE username = ?');
$ada->execute([$username]);
if ($ada->fetchColumn()) {
    $db->prepare('UPDATE pengawas SET hash_sandi = ?, nama = ? WHERE username = ?')->execute([$hash, $nama, $username]);
    echo "Pengawas diperbarui.\n";
} else {
    $db->prepare('INSERT INTO pengawas (username, hash_sandi, nama) VALUES (?, ?, ?)')->execute([$username, $hash, $nama]);
    echo "Pengawas dibuat.\n";
}
echo "Kata sandi tidak ditampilkan. Akun hanya ada di basis data server.\n";
