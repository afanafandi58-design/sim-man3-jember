<?php
/**
 * Salin file ini menjadi config.local.php dan isi nilai sebenarnya.
 * Jangan commit config.local.php atau secret ke repository.
 */
return [
    'app_name' => 'Buku Induk MAN 3 Jember',
    'base_url' => '', // kosong = auto-detect; contoh: https://bukuinduk.man3jember.sch.id
    'timezone' => 'Asia/Jakarta',

    // sqlite | mysql
    'db_driver' => 'sqlite',
    'sqlite_path' => __DIR__ . '/../storage/db/buku_induk.sqlite',

    'mysql' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'buku_induk',
        'username' => 'db_user',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    // Admin awal (hanya dipakai saat seed / install)
    'seed_admin' => [
        'username' => 'admin',
        'password' => 'ganti-password-ini',
        'nama' => 'Administrator',
    ],
];
