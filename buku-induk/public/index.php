<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/Helpers/bootstrap.php';

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = $path === null ? '/' : $path;

// Strip base path if app is in subdirectory
$scriptDir = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''));
if ($scriptDir !== '/' && $scriptDir !== '' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
    if ($path === '' || $path === false) {
        $path = '/';
    }
}
$path = '/' . ltrim($path, '/');
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

$routes = [
    'GET /' => ['App\\Controllers\\DashboardController', 'index'],
    'GET /login' => ['App\\Controllers\\AuthController', 'loginForm'],
    'POST /login' => ['App\\Controllers\\AuthController', 'login'],
    'GET /logout' => ['App\\Controllers\\AuthController', 'logout'],
    'POST /logout' => ['App\\Controllers\\AuthController', 'logout'],

    'GET /siswa' => ['App\\Controllers\\SiswaController', 'index'],
    'GET /siswa/create' => ['App\\Controllers\\SiswaController', 'createForm'],
    'POST /siswa/store' => ['App\\Controllers\\SiswaController', 'store'],

    'GET /import' => ['App\\Controllers\\ImportController', 'form'],
    'POST /import' => ['App\\Controllers\\ImportController', 'upload'],

    'GET /settings' => ['App\\Controllers\\SettingsController', 'form'],
    'POST /settings' => ['App\\Controllers\\SettingsController', 'save'],
];

$key = $method . ' ' . $path;
if (isset($routes[$key])) {
    $handler = $routes[$key];
    $controller = new $handler[0]();
    $controller->{$handler[1]}();
    exit;
}

// Dynamic routes
if (preg_match('#^/siswa/edit/(\d+)$#', $path, $m) && $method === 'GET') {
    (new App\Controllers\SiswaController())->editForm((int) $m[1]);
    exit;
}
if (preg_match('#^/siswa/update/(\d+)$#', $path, $m) && $method === 'POST') {
    (new App\Controllers\SiswaController())->update((int) $m[1]);
    exit;
}
if (preg_match('#^/siswa/delete/(\d+)$#', $path, $m) && $method === 'POST') {
    (new App\Controllers\SiswaController())->destroy((int) $m[1]);
    exit;
}
if (preg_match('#^/cetak/(\d+)$#', $path, $m) && $method === 'GET') {
    (new App\Controllers\CetakController())->show((int) $m[1]);
    exit;
}
if (preg_match('#^/cetak/(\d+)/pdf$#', $path, $m) && $method === 'GET') {
    (new App\Controllers\CetakController())->pdf((int) $m[1]);
    exit;
}

http_response_code(404);
echo 'Halaman tidak ditemukan.';
