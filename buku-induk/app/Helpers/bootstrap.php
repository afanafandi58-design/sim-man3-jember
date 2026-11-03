<?php

declare(strict_types=1);

$configLocal = dirname(__DIR__, 2) . '/config/config.local.php';
$configExample = dirname(__DIR__, 2) . '/config/config.example.php';

if (is_file($configLocal)) {
    $config = require $configLocal;
} else {
    $config = require $configExample;
}

date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, $len));
    $file = $baseDir . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$vendorAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
}

require_once __DIR__ . '/functions.php';

App\Helpers\Database::init($config);
App\Helpers\Database::migrate();

return $config;
