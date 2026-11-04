<?php

/**
 * Gerbang masuk.
 * PHP di bawah 8.0, atau folder vendor Laravel yang tidak ada,
 * dilayani runtime Absen Digital di absen74/ (aman untuk PHP 7.4).
 * PHP 8.3 dengan vendor tetap membuka aplikasi Laravel.
 */
$absenFront = __DIR__.'/../absen74/front.php';
$laravelAutoload = __DIR__.'/../vendor/autoload.php';

if ((PHP_VERSION_ID < 80000 || ! is_file($laravelAutoload)) && is_file($absenFront)) {
    require $absenFront;
    exit;
}

require __DIR__.'/laravel.php';
