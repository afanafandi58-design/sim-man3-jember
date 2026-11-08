<?php

function absen_env_path()
{
    $override = getenv('ABSEN_ENV_FILE');
    if (is_string($override) && $override !== '') {
        return $override;
    }

    return dirname(__DIR__).'/.env';
}

function absen_env($key, $default = '')
{
    global $ABSEN_ENV;
    if (!isset($ABSEN_ENV[$key])) {
        return $default;
    }

    return $ABSEN_ENV[$key];
}

function absen_load_env()
{
    global $ABSEN_ENV;
    $ABSEN_ENV = array();
    $path = absen_env_path();
    if (!is_file($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        $len = strlen($val);
        if ($len >= 2) {
            $quote = $val[0];
            if (($quote === '"' || $quote === "'") && substr($val, -1) === $quote) {
                $val = substr($val, 1, -1);
            }
        }
        $ABSEN_ENV[$key] = $val;
    }
}

function absen_root()
{
    return dirname(__DIR__);
}

function absen_storage_root()
{
    return absen_root().'/storage/app';
}

function absen_https()
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443') {
        return true;
    }

    return false;
}

function absen_header_aman()
{
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), camera=(self), microphone=()');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https://tile.openstreetmap.org; font-src 'self' data:; connect-src 'self'");
    if (absen_https()) {
        header('Strict-Transport-Security: max-age=15552000');
    }
}

function absen_boot()
{
    date_default_timezone_set('Asia/Jakarta');
    absen_load_env();
    absen_header_aman();
    absen_lindungi_penyimpanan();
    $secure = absen_https();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params(array(
                'lifetime' => 0,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ));
        } else {
            session_set_cookie_params(0, '/', '', $secure, true);
        }
        session_start();
    }
    if (empty($_SESSION['_token']) || !is_string($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(16));
    }
}

function absen_db()
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = 'mysql:host='.absen_env('DB_HOST', 'localhost')
        .';port='.absen_env('DB_PORT', '3306')
        .';dbname='.absen_env('DB_DATABASE')
        .';charset=utf8mb4';
    $pdo = new PDO($dsn, absen_env('DB_USERNAME'), absen_env('DB_PASSWORD'), array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));

    return $pdo;
}

function absen_fail_db()
{
    http_response_code(500);
    echo 'Aplikasi belum terhubung ke basis data.';
    exit;
}

function absen_migrate()
{
    $db = absen_db();
    $statements = array(
        'CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(150) NOT NULL,
            nip VARCHAR(32) NULL,
            email VARCHAR(190) NULL,
            password VARCHAR(255) NULL,
            role VARCHAR(32) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY users_email_unique (email),
            UNIQUE KEY users_nip_unique (nip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS absen_kegiatans (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nama VARCHAR(150) NOT NULL,
            jenis VARCHAR(32) NOT NULL,
            tanggal DATE NOT NULL,
            waktu VARCHAR(8) NULL,
            waktu_mulai VARCHAR(5) NULL,
            waktu_selesai VARCHAR(5) NULL,
            tempat VARCHAR(150) NULL,
            keterangan TEXT NULL,
            lokasi_lat DECIMAL(10,7) NULL,
            lokasi_lng DECIMAL(10,7) NULL,
            lokasi_mode VARCHAR(8) NOT NULL DEFAULT \'titik\',
            kode VARCHAR(32) NOT NULL,
            perangkat_id VARCHAR(64) NULL,
            hasil_drive_url VARCHAR(500) NULL,
            hasil_file_path VARCHAR(255) NULL,
            hasil_file_nama VARCHAR(255) NULL,
            hasil_catatan TEXT NULL,
            dibuat_oleh INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY absen_kegiatans_kode_unique (kode),
            KEY absen_kegiatans_tanggal_jenis (tanggal, jenis),
            CONSTRAINT absen_kegiatans_dibuat_oleh_fk FOREIGN KEY (dibuat_oleh) REFERENCES users (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS absen_kehadirans (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            kegiatan_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NULL,
            nik VARCHAR(32) NULL,
            nama VARCHAR(150) NOT NULL,
            metode VARCHAR(16) NOT NULL,
            foto_path VARCHAR(255) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            ttd_path VARCHAR(255) NULL,
            perangkat_id VARCHAR(64) NULL,
            kunci_aktif TINYINT(1) NULL DEFAULT 1,
            dicatat_oleh INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY absen_kehadirans_user_aktif (kegiatan_id, user_id, kunci_aktif),
            UNIQUE KEY absen_kehadirans_nik_aktif (kegiatan_id, nik, kunci_aktif),
            CONSTRAINT absen_kehadirans_kegiatan_fk FOREIGN KEY (kegiatan_id) REFERENCES absen_kegiatans (id) ON DELETE CASCADE,
            CONSTRAINT absen_kehadirans_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
            CONSTRAINT absen_kehadirans_pencatat_fk FOREIGN KEY (dicatat_oleh) REFERENCES users (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS absen_fotos (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            kegiatan_id INT UNSIGNED NOT NULL,
            path VARCHAR(255) NOT NULL,
            nama_asli VARCHAR(150) NULL,
            keterangan VARCHAR(150) NULL,
            diunggah_oleh INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            CONSTRAINT absen_fotos_kegiatan_fk FOREIGN KEY (kegiatan_id) REFERENCES absen_kegiatans (id) ON DELETE CASCADE,
            CONSTRAINT absen_fotos_pengunggah_fk FOREIGN KEY (diunggah_oleh) REFERENCES users (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    );
    foreach ($statements as $sql) {
        $db->exec($sql);
    }
    absen_upgrade_schema();
    absen_drop_login_roles();
    absen_seed_users();
    absen_import_orang();
}

function absen_column_meta($table, $column)
{
    $allowed = array('users' => true, 'absen_kegiatans' => true, 'absen_kehadirans' => true);
    if (!isset($allowed[$table])) {
        return null;
    }
    $stmt = absen_db()->prepare('SHOW COLUMNS FROM `'.$table.'` LIKE ?');
    $stmt->execute(array($column));
    $row = $stmt->fetch();

    return $row ? $row : null;
}

function absen_upgrade_schema()
{
    $db = absen_db();
    $email = absen_column_meta('users', 'email');
    if ($email && strtoupper((string) $email['Null']) !== 'YES') {
        $db->exec('ALTER TABLE users MODIFY email VARCHAR(190) NULL');
    }
    $password = absen_column_meta('users', 'password');
    if ($password && strtoupper((string) $password['Null']) !== 'YES') {
        $db->exec('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
    }
    if (!absen_column_meta('users', 'nip')) {
        $db->exec('ALTER TABLE users ADD COLUMN nip VARCHAR(32) NULL AFTER name');
    }
    $idx = $db->query("SHOW INDEX FROM users WHERE Key_name = 'users_nip_unique'")->fetch();
    if (!$idx) {
        try {
            $db->exec('ALTER TABLE users ADD UNIQUE KEY users_nip_unique (nip)');
        } catch (Exception $e) {
            if (!$db->query("SHOW INDEX FROM users WHERE Key_name = 'users_nip_unique'")->fetch()) {
                throw $e;
            }
        }
    }
    if (!absen_column_meta('absen_kegiatans', 'waktu_mulai')) {
        $db->exec('ALTER TABLE absen_kegiatans ADD COLUMN waktu_mulai VARCHAR(5) NULL AFTER waktu');
    }
    if (!absen_column_meta('absen_kegiatans', 'waktu_selesai')) {
        $db->exec('ALTER TABLE absen_kegiatans ADD COLUMN waktu_selesai VARCHAR(5) NULL AFTER waktu_mulai');
    }
    $db->exec("UPDATE absen_kegiatans SET waktu_mulai = SUBSTRING(waktu, 1, 5) WHERE (waktu_mulai IS NULL OR waktu_mulai = '') AND waktu IS NOT NULL AND waktu <> ''");
    $nik = absen_column_meta('absen_kehadirans', 'nik');
    if ($nik && stripos((string) $nik['Type'], 'varchar(32)') === false) {
        $db->exec('ALTER TABLE absen_kehadirans MODIFY nik VARCHAR(32) NULL');
    }
    if (!absen_column_meta('absen_kegiatans', 'perangkat_id')) {
        $db->exec('ALTER TABLE absen_kegiatans ADD COLUMN perangkat_id VARCHAR(64) NULL AFTER kode');
    }
    if (!absen_column_meta('absen_kehadirans', 'perangkat_id')) {
        $db->exec('ALTER TABLE absen_kehadirans ADD COLUMN perangkat_id VARCHAR(64) NULL AFTER ttd_path');
    }
    if (!absen_column_meta('absen_kegiatans', 'lokasi_lat')) {
        $db->exec('ALTER TABLE absen_kegiatans ADD COLUMN lokasi_lat DECIMAL(10,7) NULL AFTER keterangan');
    }
    if (!absen_column_meta('absen_kegiatans', 'lokasi_lng')) {
        $db->exec('ALTER TABLE absen_kegiatans ADD COLUMN lokasi_lng DECIMAL(10,7) NULL AFTER lokasi_lat');
    }
    if (!absen_column_meta('absen_kegiatans', 'lokasi_mode')) {
        $db->exec("ALTER TABLE absen_kegiatans ADD COLUMN lokasi_mode VARCHAR(8) NOT NULL DEFAULT 'titik' AFTER lokasi_lng");
    }
    $db->exec("UPDATE absen_kehadirans h INNER JOIN absen_kegiatans k ON k.id = h.kegiatan_id SET h.perangkat_id = k.perangkat_id WHERE (h.perangkat_id IS NULL OR h.perangkat_id = '') AND k.perangkat_id IS NOT NULL AND k.perangkat_id <> ''");
    if (!absen_column_meta('absen_kehadirans', 'kunci_aktif')) {
        $db->exec('ALTER TABLE absen_kehadirans ADD COLUMN kunci_aktif TINYINT(1) NULL DEFAULT 1 AFTER perangkat_id');
    }
    $idxKegiatan = $db->query("SHOW INDEX FROM absen_kehadirans WHERE Key_name = 'absen_kehadirans_kegiatan_idx'")->fetch();
    if (!$idxKegiatan) {
        $db->exec('ALTER TABLE absen_kehadirans ADD INDEX absen_kehadirans_kegiatan_idx (kegiatan_id)');
    }
    $idxLamaUser = $db->query("SHOW INDEX FROM absen_kehadirans WHERE Key_name = 'absen_kehadirans_user_unique'")->fetch();
    if ($idxLamaUser) {
        $db->exec('ALTER TABLE absen_kehadirans DROP INDEX absen_kehadirans_user_unique');
    }
    $idxLamaNik = $db->query("SHOW INDEX FROM absen_kehadirans WHERE Key_name = 'absen_kehadirans_nik_unique'")->fetch();
    if ($idxLamaNik) {
        $db->exec('ALTER TABLE absen_kehadirans DROP INDEX absen_kehadirans_nik_unique');
    }
    $idxUser = $db->query("SHOW INDEX FROM absen_kehadirans WHERE Key_name = 'absen_kehadirans_user_aktif'")->fetch();
    if (!$idxUser) {
        $db->exec('ALTER TABLE absen_kehadirans ADD UNIQUE KEY absen_kehadirans_user_aktif (kegiatan_id, user_id, kunci_aktif)');
    }
    $idxNik = $db->query("SHOW INDEX FROM absen_kehadirans WHERE Key_name = 'absen_kehadirans_nik_aktif'")->fetch();
    if (!$idxNik) {
        $db->exec('ALTER TABLE absen_kehadirans ADD UNIQUE KEY absen_kehadirans_nik_aktif (kegiatan_id, nik, kunci_aktif)');
    }
    $db->exec('CREATE TABLE IF NOT EXISTS absen_gangguan (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        kegiatan_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NULL,
        nama VARCHAR(150) NULL,
        perangkat_id VARCHAR(64) NULL,
        jenis VARCHAR(16) NOT NULL,
        keterangan VARCHAR(180) NULL,
        created_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY absen_gangguan_kegiatan (kegiatan_id, user_id),
        CONSTRAINT absen_gangguan_kegiatan_fk FOREIGN KEY (kegiatan_id) REFERENCES absen_kegiatans (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->exec('DELETE FROM absen_gangguan WHERE user_id IS NULL');
}

function absen_drop_login_roles()
{
    absen_db()->exec("DELETE FROM users WHERE role IN ('guru', 'staff', 'kepala')");
}

function absen_seed_users()
{
    $email = trim(absen_env('ABSEN_ADMIN_EMAIL'));
    $password = absen_env('ABSEN_ADMIN_PASSWORD');
    $name = trim(absen_env('ABSEN_ADMIN_NAME'));
    if ($name === '') {
        $name = 'Admin Absen';
    }
    if ($email === '' || $password === '') {
        return;
    }
    $db = absen_db();
    $check = $db->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute(array($email));
    if ($check->fetch()) {
        return;
    }
    $now = date('Y-m-d H:i:s');
    $insert = $db->prepare('INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?)');
    $insert->execute(array($name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin', $now, $now));
}

function absen_import_orang()
{
    $path = __DIR__.'/data/orang.php';
    if (!is_file($path)) {
        return;
    }
    $rows = include $path;
    if (!is_array($rows)) {
        return;
    }
    $db = absen_db();
    $now = date('Y-m-d H:i:s');
    $byNip = $db->prepare('SELECT id FROM users WHERE nip = ?');
    $byName = $db->prepare("SELECT id FROM users WHERE role = 'user' AND nip IS NULL AND name = ?");
    $update = $db->prepare('UPDATE users SET name = ?, role = ?, is_active = 1, updated_at = ? WHERE id = ?');
    $insert = $db->prepare('INSERT INTO users (name, nip, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, NULL, NULL, ?, 1, ?, ?)');
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = preg_replace('/\s+/', ' ', trim(isset($row['name']) ? (string) $row['name'] : ''));
        $nip = preg_replace('/\s+/', '', trim(isset($row['nip']) ? (string) $row['nip'] : ''));
        if ($name === '') {
            continue;
        }
        if ($nip !== '') {
            $byNip->execute(array($nip));
            $found = $byNip->fetch();
        } else {
            $byName->execute(array($name));
            $found = $byName->fetch();
        }
        if ($found) {
            $update->execute(array($name, 'user', $now, $found['id']));
            continue;
        }
        $insert->execute(array($name, $nip === '' ? null : $nip, 'user', $now, $now));
    }
}

function absen_jenis()
{
    return array(
        'rapat' => 'Rapat',
        'upacara' => 'Upacara',
        'diklat' => 'Diklat',
        'pembinaan' => 'Pembinaan',
        'lain' => 'Lainnya',
    );
}

function absen_peran()
{
    return array(
        'admin' => 'Admin',
        'user' => 'Pengguna',
    );
}

function absen_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function absen_path()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = '/';
    }
    if ($path !== '/' && substr($path, -1) === '/') {
        $path = substr($path, 0, -1);
    }

    return $path;
}

function absen_method()
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    if ($method === 'POST' && isset($_POST['_method'])) {
        $override = strtoupper((string) $_POST['_method']);
        if ($override === 'PUT' || $override === 'DELETE') {
            return $override;
        }
    }

    return $method;
}

function absen_csrf()
{
    $sent = isset($_POST['_token']) ? (string) $_POST['_token'] : '';
    $known = isset($_SESSION['_token']) ? (string) $_SESSION['_token'] : '';
    if ($known === '' || !hash_equals($known, $sent)) {
        http_response_code(419);
        echo 'Sesi berakhir. Muat ulang halaman lalu coba lagi.';
        exit;
    }
}

function absen_csrf_field()
{
    return '<input type="hidden" name="_token" value="'.absen_e($_SESSION['_token']).'">';
}

function absen_path_aman($path)
{
    if (!is_string($path)) {
        return '/';
    }
    if (strlen($path) > 2048 || preg_match('/[\x00-\x1F\x7F\\\\]/', $path)) {
        return '/';
    }
    if ($path === '' || $path[0] !== '/' || (isset($path[1]) && $path[1] === '/')) {
        return '/';
    }
    if (strpos($path, '://') !== false) {
        return '/';
    }

    return $path;
}

function absen_redirect($path)
{
    header('Location: '.absen_path_aman($path));
    exit;
}

function absen_back($fallback)
{
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $parts = parse_url($ref);
    if (is_array($parts) && isset($parts['host']) && strcasecmp($parts['host'], $host) === 0 && isset($parts['path']) && $parts['path'][0] === '/') {
        $path = $parts['path'];
        if (!empty($parts['query'])) {
            $path .= '?'.$parts['query'];
        }

        return absen_path_aman($path);
    }

    return absen_path_aman($fallback);
}

function absen_flash_error($errors, $fallback)
{
    $_SESSION['_errors'] = $errors;
    $old = $_POST;
    unset($old['password'], $old['ttd'], $old['_token']);
    $_SESSION['_old'] = $old;
    absen_redirect(absen_back($fallback));
}

function absen_flash_success($message, $path)
{
    $_SESSION['_success'] = $message;
    absen_redirect($path);
}

function absen_old($key, $default = '')
{
    if (isset($_SESSION['_old']) && is_array($_SESSION['_old']) && array_key_exists($key, $_SESSION['_old'])) {
        return $_SESSION['_old'][$key];
    }

    return $default;
}

function absen_error($key)
{
    if (isset($_SESSION['_errors'][$key])) {
        return (string) $_SESSION['_errors'][$key];
    }

    return '';
}

function absen_success()
{
    return isset($_SESSION['_success']) ? (string) $_SESSION['_success'] : '';
}

function absen_end_flash()
{
    unset($_SESSION['_old'], $_SESSION['_errors'], $_SESSION['_success']);
}

function absen_user()
{
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $stmt = absen_db()->prepare('SELECT id, name, email, role, is_active FROM users WHERE id = ?');
    $stmt->execute(array((int) $_SESSION['uid']));
    $user = $stmt->fetch();
    if (!$user || (int) $user['is_active'] !== 1) {
        return null;
    }

    return $user;
}

function absen_roles_absen()
{
    return array('admin');
}

function absen_require_user()
{
    $user = absen_user();
    if (!$user || $user['role'] !== 'admin') {
        absen_redirect('/login');
    }

    return $user;
}

function absen_is_admin($user)
{
    return $user && $user['role'] === 'admin';
}

function absen_require_admin($user)
{
    if (!absen_is_admin($user)) {
        http_response_code(403);
        echo 'Anda tidak memiliki akses ke halaman ini.';
        exit;
    }
}

function absen_render($view, $data, $layout = true)
{
    $data['jenisMap'] = absen_jenis();
    $data['peranMap'] = absen_peran();
    $data['authUser'] = isset($data['authUser']) ? $data['authUser'] : absen_user();
    extract($data, EXTR_SKIP);
    ob_start();
    include __DIR__.'/views/'.$view.'.php';
    $isi = ob_get_clean();
    if (!$layout) {
        echo $isi;
        absen_end_flash();

        return;
    }
    include __DIR__.'/views/layout.php';
    absen_end_flash();
}

function absen_tanggal_label($ymd)
{
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $ts = strtotime((string) $ymd);
    if (!$ts) {
        return (string) $ymd;
    }
    $n = (int) date('n', $ts);

    return (int) date('j', $ts).' '.$bulan[$n].' '.date('Y', $ts);
}

function absen_base_url()
{
    $host = isset($_SERVER['HTTP_HOST']) ? trim((string) $_SERVER['HTTP_HOST']) : '';
    $hostOnly = strtolower((string) preg_replace('/:\d+$/', '', $host));
    $cfg = trim(absen_env('APP_URL'));
    $cfgHost = '';
    if ($cfg !== '') {
        $parsedCfg = parse_url($cfg);
        if (is_array($parsedCfg) && isset($parsedCfg['host'])) {
            $cfgHost = strtolower((string) $parsedCfg['host']);
        }
    }
    $allowed = array(
        'absendigital.man3jember.sch.id' => true,
        'localhost' => true,
        '127.0.0.1' => true,
    );
    if ($cfgHost !== '') {
        $allowed[$cfgHost] = true;
    }
    if ($hostOnly !== '' && isset($allowed[$hostOnly])) {
        return (absen_https() ? 'https' : 'http').'://'.$host;
    }
    $cfg = trim(absen_env('APP_URL'));
    if ($cfg !== '') {
        return rtrim($cfg, '/');
    }

    return 'http://localhost';
}

function absen_tautan_absen($kegiatan)
{
    $kode = isset($kegiatan['kode']) ? absen_normalize_kode($kegiatan['kode']) : '';

    return absen_base_url().'/absen-digital/k/'.rawurlencode($kode);
}

function absen_uuid()
{
    $bytes = random_bytes(16);
    $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
    $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

function absen_safe_name($name, $fallback)
{
    $name = basename(str_replace('\\', '/', (string) $name));
    $name = preg_replace('/[^\p{L}\p{N}._ -]/u', '', $name);
    $name = trim((string) $name);
    if ($name === '' || $name === '.' || $name === '..') {
        return $fallback;
    }
    if (function_exists('mb_substr')) {
        return mb_substr($name, 0, 150);
    }

    return substr($name, 0, 150);
}

function absen_rel_aman($path)
{
    if (!is_string($path) || $path === '' || strlen($path) > 255) {
        return '';
    }
    if (strpos($path, "\0") !== false || strpos($path, '\\') !== false || strpos($path, '..') !== false) {
        return '';
    }
    if (strpos($path, 'absen-digital/') !== 0) {
        return '';
    }
    if (!preg_match('#^absen-digital/(foto|ttd|dokumentasi|hasil)/[A-Za-z0-9._-]+$#', $path)) {
        return '';
    }

    return $path;
}

function absen_file_penuh($path)
{
    $rel = absen_rel_aman($path);
    if ($rel === '') {
        return '';
    }
    $root = realpath(absen_storage_root());
    if ($root === false) {
        return '';
    }
    $full = realpath($root.'/'.$rel);
    if ($full === false || !is_file($full)) {
        return '';
    }
    $prefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    if (strpos($full, $prefix) !== 0) {
        return '';
    }

    return $full;
}

function absen_assert_rel($path)
{
    if (absen_rel_aman($path) === '') {
        http_response_code(404);
        echo 'Berkas tidak ditemukan.';
        exit;
    }
}

function absen_unlink_rel($path)
{
    $full = absen_file_penuh($path);
    if ($full !== '') {
        unlink($full);
    }
}

function absen_kode_baru()
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($alphabet) - 1;
    do {
        $kode = '';
        for ($i = 0; $i < 8; $i++) {
            $kode .= $alphabet[random_int(0, $max)];
        }
        $stmt = absen_db()->prepare('SELECT id FROM absen_kegiatans WHERE kode = ?');
        $stmt->execute(array($kode));
    } while ($stmt->fetch());

    return $kode;
}

function absen_normalize_kode($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    $parts = parse_url($raw);
    if (is_array($parts) && isset($parts['query'])) {
        $query = array();
        parse_str($parts['query'], $query);
        if (!empty($query['kode']) && is_string($query['kode'])) {
            return absen_kode_bersih($query['kode']);
        }
    }
    if (preg_match('/(?:^|[?&])kode=([A-Za-z0-9]+)/', $raw, $matches) === 1) {
        return absen_kode_bersih($matches[1]);
    }
    $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : $raw;
    if (preg_match('#/absen-digital/k/([A-Za-z0-9]+)#', $path, $matches) === 1) {
        return absen_kode_bersih($matches[1]);
    }

    return absen_kode_bersih($raw);
}

function absen_kode_bersih($kode)
{
    $kode = strtoupper(trim((string) $kode));
    if (!preg_match('/^[A-Z0-9]{4,32}$/', $kode)) {
        return '';
    }

    return $kode;
}

function absen_drive_ok($url)
{
    $url = trim((string) $url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    if (stripos($url, 'https://') !== 0) {
        return false;
    }
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host)) {
        return false;
    }
    $host = strtolower($host);
    $allowed = array('drive.google.com', 'docs.google.com', 'forms.gle', 'forms.google.com');
    foreach ($allowed as $item) {
        $suffix = '.'.$item;
        if ($host === $item || substr($host, -strlen($suffix)) === $suffix) {
            return true;
        }
    }

    return false;
}

function absen_kegiatan($id)
{
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE id = ?');
    $stmt->execute(array((int) $id));
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo 'Kegiatan tidak ditemukan.';
        exit;
    }

    return $row;
}

function absen_peserta()
{
    return absen_db()->query("SELECT id, name, nip FROM users WHERE is_active = 1 AND role = 'user' ORDER BY name")->fetchAll();
}

function absen_nama_peserta($value)
{
    $name = preg_replace('/\s+/', ' ', trim((string) $value));
    if (function_exists('mb_substr')) {
        return mb_substr($name, 0, 150);
    }

    return substr($name, 0, 150);
}

function absen_tambah_peserta($name, $nip)
{
    $name = absen_nama_peserta($name);
    $nip = preg_replace('/\s+/', '', trim((string) $nip));
    $errors = array();
    if ($name === '') {
        $errors['name'] = 'Isi nama peserta.';
    }
    if ($nip !== '' && preg_match('/^\d{1,32}$/', $nip) !== 1) {
        $errors['nip'] = 'NIP atau NIK hanya angka, paling banyak 32 digit.';
    }
    if ($errors) {
        return array('errors' => $errors);
    }
    $db = absen_db();
    $byName = $db->prepare("SELECT name FROM users WHERE role = 'user' AND LOWER(name) = LOWER(?) LIMIT 1");
    $byName->execute(array($name));
    $samaNama = $byName->fetch();
    if ($samaNama) {
        $errors['name'] = 'Nama ini sudah ada: '.$samaNama['name'].'.';
    }
    if ($nip !== '') {
        $byNip = $db->prepare('SELECT name FROM users WHERE nip = ? LIMIT 1');
        $byNip->execute(array($nip));
        $samaNip = $byNip->fetch();
        if ($samaNip) {
            $errors['nip'] = 'NIP atau NIK ini sudah dipakai oleh '.$samaNip['name'].'.';
        }
    }
    if ($errors) {
        return array('errors' => $errors);
    }
    $now = date('Y-m-d H:i:s');
    try {
        $insert = $db->prepare('INSERT INTO users (name, nip, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, NULL, NULL, ?, 1, ?, ?)');
        $insert->execute(array($name, $nip === '' ? null : $nip, 'user', $now, $now));
    } catch (Exception $e) {
        return array('errors' => array('nip' => 'NIP atau NIK ini sudah dipakai. Peserta tidak ditambahkan.'));
    }

    return array('name' => $name, 'nip' => $nip);
}

function absen_find_peserta($id)
{
    $stmt = absen_db()->prepare("SELECT id, name, nip, role FROM users WHERE id = ? AND is_active = 1 AND role = 'user'");
    $stmt->execute(array((int) $id));
    $row = $stmt->fetch();

    return $row ? $row : null;
}

function absen_sudah_hadir($kegiatanId, $userId, $nik)
{
    if ($userId) {
        $stmt = absen_db()->prepare('SELECT id FROM absen_kehadirans WHERE kegiatan_id = ? AND user_id = ? AND kunci_aktif = 1');
        $stmt->execute(array((int) $kegiatanId, (int) $userId));

        return (bool) $stmt->fetch();
    }
    if ($nik) {
        $stmt = absen_db()->prepare('SELECT id FROM absen_kehadirans WHERE kegiatan_id = ? AND nik = ? AND kunci_aktif = 1');
        $stmt->execute(array((int) $kegiatanId, $nik));

        return (bool) $stmt->fetch();
    }

    return false;
}

function absen_post_text($key, $max)
{
    $value = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }

    return substr($value, 0, $max);
}

function absen_jam($value)
{
    $value = trim((string) $value);
    if (!preg_match('/^(\d{2}):(\d{2})/', $value, $match)) {
        return '';
    }
    $hour = (int) $match[1];
    $minute = (int) $match[2];
    if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
        return '';
    }

    return sprintf('%02d:%02d', $hour, $minute);
}

function absen_rentang_label($kegiatan)
{
    $mulai = absen_jam(isset($kegiatan['waktu_mulai']) ? $kegiatan['waktu_mulai'] : '');
    $selesai = absen_jam(isset($kegiatan['waktu_selesai']) ? $kegiatan['waktu_selesai'] : '');
    if ($mulai === '' || $selesai === '') {
        return '';
    }

    return $mulai.'–'.$selesai;
}

function absen_rentang_blokir($kegiatan)
{
    $mulai = absen_jam(isset($kegiatan['waktu_mulai']) ? $kegiatan['waktu_mulai'] : '');
    $selesai = absen_jam(isset($kegiatan['waktu_selesai']) ? $kegiatan['waktu_selesai'] : '');
    if ($mulai === '' || $selesai === '') {
        return 'Admin belum menentukan rentang waktu absen.';
    }
    $tanggal = isset($kegiatan['tanggal']) ? (string) $kegiatan['tanggal'] : '';
    if (strlen($tanggal) > 10) {
        $tanggal = substr($tanggal, 0, 10);
    }
    $label = $mulai.'–'.$selesai;
    if ($tanggal !== date('Y-m-d')) {
        return 'Absen hanya bisa pada '.absen_tanggal_label($tanggal).' pukul '.$label.'.';
    }
    $now = date('H:i');
    if ($now < $mulai || $now > $selesai) {
        return 'Di luar rentang waktu absen ('.$label.').';
    }

    return '';
}

function absen_metode_label($metode)
{
    if ($metode === 'qr' || $metode === 'barcode') {
        return 'Barcode';
    }

    return 'Formulir';
}

function absen_android()
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

    return stripos($ua, 'Android') !== false;
}

function absen_id_perangkat_valid($id)
{
    return is_string($id) && preg_match('/^[a-f0-9]{32}(?:[a-f0-9]{32})?$/', $id) === 1;
}

function absen_perangkat_id()
{
    $id = isset($_COOKIE['absen_perangkat']) ? strtolower((string) $_COOKIE['absen_perangkat']) : '';
    if (!absen_id_perangkat_valid($id)) {
        return '';
    }

    return $id;
}

function absen_sidik_mentah()
{
    if (!isset($_POST['sidik'])) {
        return '';
    }
    $raw = trim((string) $_POST['sidik']);
    if (strlen($raw) < 20 || strlen($raw) > 800 || !preg_match('/\Av1\|[\x20-\x7E]{10,780}\z/', $raw)) {
        return '';
    }
    if (substr_count($raw, '|') < 8) {
        return '';
    }

    return $raw;
}

function absen_sidik_id()
{
    $raw = absen_sidik_mentah();
    if ($raw === '') {
        return '';
    }

    return hash('sha256', $raw);
}

function absen_set_perangkat_cookie($id)
{
    if (!absen_id_perangkat_valid((string) $id)) {
        return;
    }
    $secure = absen_https();
    if (PHP_VERSION_ID >= 70300) {
        setcookie('absen_perangkat', $id, array(
            'expires' => time() + 365 * 24 * 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    } else {
        setcookie('absen_perangkat', $id, time() + 365 * 24 * 3600, '/', '', $secure, true);
    }
    $_COOKIE['absen_perangkat'] = $id;
}

function absen_pesan_perangkat_terpakai()
{
    return 'Perangkat Android ini sudah dipakai untuk absen pada kegiatan ini. Perangkat yang sama tidak bisa mengabsenkan nama lain.';
}

function absen_perangkat_sudah($kegiatanId, $perangkatId)
{
    if ($perangkatId === '') {
        return false;
    }
    $stmt = absen_db()->prepare('SELECT id FROM absen_kehadirans WHERE kegiatan_id = ? AND perangkat_id = ? AND kunci_aktif = 1 LIMIT 1');
    $stmt->execute(array((int) $kegiatanId, $perangkatId));

    return (bool) $stmt->fetch();
}

function absen_perangkat_terkunci($kegiatanId)
{
    $sidik = absen_sidik_id();
    if ($sidik !== '' && absen_perangkat_sudah($kegiatanId, $sidik)) {
        return true;
    }
    $cookie = absen_perangkat_id();
    if ($cookie !== '' && absen_perangkat_sudah($kegiatanId, $cookie)) {
        return true;
    }

    return false;
}

function absen_catat_gangguan($kegiatanId, $userId, $nama, $jenis, $keterangan)
{
    $jenis = $jenis === 'tidak_terbaca' ? 'tidak_terbaca' : 'gagal';
    $keterangan = trim((string) $keterangan);
    if (function_exists('mb_substr')) {
        $keterangan = mb_substr($keterangan, 0, 180);
    } else {
        $keterangan = substr($keterangan, 0, 180);
    }
    $userId = (int) $userId;
    if ($userId < 1) {
        return;
    }
    $sidik = absen_sidik_id();
    $now = date('Y-m-d H:i:s');
    $db = absen_db();
    $ada = null;
    if ($userId > 0) {
        $cek = $db->prepare('SELECT id FROM absen_gangguan WHERE kegiatan_id = ? AND user_id = ? LIMIT 1');
        $cek->execute(array((int) $kegiatanId, $userId));
        $ada = $cek->fetch();
    } else {
        $cek = $db->prepare('SELECT id FROM absen_gangguan WHERE kegiatan_id = ? AND user_id IS NULL AND perangkat_id = ? LIMIT 1');
        $cek->execute(array((int) $kegiatanId, $sidik));
        $ada = $cek->fetch();
    }
    $nama = trim((string) $nama);
    if ($nama === '') {
        $nama = null;
    }
    $perangkat = $sidik === '' ? null : $sidik;
    if ($ada) {
        $upd = $db->prepare('UPDATE absen_gangguan SET nama=?, perangkat_id=?, jenis=?, keterangan=?, created_at=? WHERE id=?');
        $upd->execute(array($nama, $perangkat, $jenis, $keterangan, $now, $ada['id']));

        return;
    }
    $ins = $db->prepare('INSERT INTO absen_gangguan (kegiatan_id, user_id, nama, perangkat_id, jenis, keterangan, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $ins->execute(array((int) $kegiatanId, $userId > 0 ? $userId : null, $nama, $perangkat, $jenis, $keterangan, $now));
}

function absen_hapus_gangguan_user($kegiatanId, $userId)
{
    $stmt = absen_db()->prepare('DELETE FROM absen_gangguan WHERE kegiatan_id = ? AND user_id = ?');
    $stmt->execute(array((int) $kegiatanId, (int) $userId));
}

function absen_tolak_absen($kegiatan, $errors, $back)
{
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $pesan = '';
    $jenis = 'gagal';
    if (is_array($errors)) {
        foreach ($errors as $key => $text) {
            $pesan = (string) $text;
            if (($key === 'lokasi' || $key === 'perangkat') && stripos($pesan, 'tidak dikenali') !== false) {
                $jenis = 'tidak_terbaca';
            }
            if ($key === 'lokasi' && stripos($pesan, 'belum terbaca') !== false) {
                $jenis = 'tidak_terbaca';
            }
            if ($key === 'perangkat' && stripos($pesan, 'tidak dikenali') !== false) {
                $jenis = 'tidak_terbaca';
            }
            break;
        }
    }
    $nama = '';
    if ($userId > 0) {
        $peserta = absen_find_peserta($userId);
        if ($peserta) {
            $nama = $peserta['name'];
        }
    }
    absen_catat_gangguan($kegiatan['id'], $userId, $nama, $jenis, $pesan);
    absen_flash_error($errors, $back);
}

function absen_reset_kunci_user($kegiatanId, $userId)
{
    $kegiatanId = (int) $kegiatanId;
    $userId = (int) $userId;
    $stmt = absen_db()->prepare('UPDATE absen_kehadirans SET kunci_aktif = NULL, updated_at = ? WHERE kegiatan_id = ? AND user_id = ? AND kunci_aktif = 1');
    $stmt->execute(array(date('Y-m-d H:i:s'), $kegiatanId, $userId));
    $terbuka = $stmt->rowCount() > 0;
    absen_hapus_gangguan_user($kegiatanId, $userId);

    return $terbuka;
}

function absen_pesan_perangkat($kegiatan)
{
    if (!absen_android()) {
        return 'Absen dilakukan dari perangkat Android. Perangkat ini bukan Android.';
    }
    if (absen_perangkat_terkunci($kegiatan['id'])) {
        return absen_pesan_perangkat_terpakai();
    }

    return '';
}

function absen_lokasi_kegiatan($kegiatan)
{
    $lat = isset($kegiatan['lokasi_lat']) ? $kegiatan['lokasi_lat'] : null;
    $lng = isset($kegiatan['lokasi_lng']) ? $kegiatan['lokasi_lng'] : null;
    if ($lat === null || $lat === '' || $lng === null || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
        return null;
    }

    return array((float) $lat, (float) $lng);
}

function absen_lokasi_wajib($kegiatan)
{
    $mode = isset($kegiatan['lokasi_mode']) ? (string) $kegiatan['lokasi_mode'] : 'titik';

    return $mode !== 'bebas';
}

function absen_jarak_meter($lat1, $lng1, $lat2, $lng2)
{
    $bumi = 6371000;
    $p1 = deg2rad((float) $lat1);
    $p2 = deg2rad((float) $lat2);
    $dp = deg2rad((float) $lat2 - (float) $lat1);
    $dl = deg2rad((float) $lng2 - (float) $lng1);
    $a = sin($dp / 2) * sin($dp / 2) + cos($p1) * cos($p2) * sin($dl / 2) * sin($dl / 2);
    $c = 2 * atan2(sqrt($a), sqrt(max(0, 1 - $a)));

    return $bumi * $c;
}

function absen_lokasi_dilaporkan_palsu()
{
    if (!isset($_POST['lokasi_palsu'])) {
        return false;
    }
    $nilai = strtolower(trim((string) $_POST['lokasi_palsu']));

    return in_array($nilai, array('1', 'true', 'ya', 'palsu'), true);
}

function absen_koordinat_peserta()
{
    $lat = isset($_POST['latitude']) ? trim((string) $_POST['latitude']) : '';
    $lng = isset($_POST['longitude']) ? trim((string) $_POST['longitude']) : '';
    if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
        return null;
    }
    $lat = (float) $lat;
    $lng = (float) $lng;
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return null;
    }

    return array($lat, $lng);
}

function absen_klien_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '0';
    }

    return $ip;
}

function absen_batas($nama, $kunci, $maks, $detik, $catat)
{
    $maks = (int) $maks;
    $detik = (int) $detik;
    if ($maks < 1 || $detik < 1) {
        return true;
    }
    $dir = absen_storage_root().'/absen-digital/_batas';
    if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
        return true;
    }
    $file = $dir.'/'.hash('sha256', $nama.'|'.$kunci);
    $fh = @fopen($file, 'c+');
    if (!$fh) {
        return true;
    }
    if (!flock($fh, LOCK_EX)) {
        fclose($fh);

        return true;
    }
    $raw = stream_get_contents($fh);
    $now = time();
    $hits = array();
    if (is_string($raw) && $raw !== '') {
        foreach (explode(',', $raw) as $part) {
            $ts = (int) $part;
            if ($ts > 0 && $ts > $now - $detik) {
                $hits[] = $ts;
            }
        }
    }
    $boleh = count($hits) < $maks;
    if ($catat && $boleh) {
        $hits[] = $now;
    }
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, implode(',', $hits));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    return $boleh;
}

function absen_simpan_publik($kegiatan)
{
    $back = '/absen-digital/k/'.rawurlencode(absen_normalize_kode($kegiatan['kode']));
    $ip = absen_klien_ip();
    if (!absen_batas('absen-ip', $ip, 150, 600, true)) {
        absen_tolak_absen($kegiatan, array('user_id' => 'Terlalu banyak percobaan absen dari jaringan ini. Tunggu beberapa menit lalu coba lagi.'), $back);
    }
    $perangkat = absen_sidik_id();
    if ($perangkat === '') {
        $perangkat = absen_perangkat_id();
    }
    if ($perangkat !== '' && !absen_batas('absen-perangkat', $perangkat, 25, 600, true)) {
        absen_tolak_absen($kegiatan, array('perangkat' => 'Terlalu banyak percobaan dari perangkat ini. Tunggu beberapa menit lalu coba lagi.'), $back);
    }
    $blokir = absen_rentang_blokir($kegiatan);
    if ($blokir !== '') {
        absen_tolak_absen($kegiatan, array('rentang' => $blokir), $back);
    }
    $pesanPerangkat = absen_pesan_perangkat($kegiatan);
    if ($pesanPerangkat !== '') {
        absen_tolak_absen($kegiatan, array('perangkat' => $pesanPerangkat), $back);
    }
    if (absen_sidik_id() === '') {
        absen_tolak_absen($kegiatan, array('perangkat' => 'Perangkat tidak dikenali. Muat ulang halaman absen, lalu coba lagi.'), $back);
    }
    $peserta = absen_find_peserta(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
    if (!$peserta) {
        absen_tolak_absen($kegiatan, array('user_id' => 'Pilih satu identitas.'), $back);
    }
    if (absen_sudah_hadir($kegiatan['id'], $peserta['id'], null)) {
        absen_tolak_absen($kegiatan, array('user_id' => $peserta['name'].' sudah absen pada kegiatan ini. Satu identitas hanya bisa absen sekali.'), $back);
    }
    if (absen_lokasi_wajib($kegiatan)) {
        $pusat = absen_lokasi_kegiatan($kegiatan);
        if ($pusat === null) {
            absen_tolak_absen($kegiatan, array('lokasi' => 'Titik lokasi kegiatan belum ditentukan. Absen belum bisa disimpan.'), $back);
        }
        if (absen_lokasi_dilaporkan_palsu()) {
            absen_tolak_absen($kegiatan, array('lokasi' => 'Lokasi perangkat terdeteksi sebagai GPS palsu. Absen ditolak. Matikan mock location, lalu coba lagi.'), $back);
        }
        $pesertaLokasi = absen_koordinat_peserta();
        if ($pesertaLokasi === null) {
            absen_tolak_absen($kegiatan, array('lokasi' => 'Lokasi perangkat belum terbaca. Izinkan GPS, lalu coba lagi.'), $back);
        }
        if (absen_jarak_meter($pusat[0], $pusat[1], $pesertaLokasi[0], $pesertaLokasi[1]) > 75) {
            absen_tolak_absen($kegiatan, array('lokasi' => 'Posisi Anda di luar radius 75 meter dari titik lokasi kegiatan. Absen ditolak.'), $back);
        }
        $latVal = $pesertaLokasi[0];
        $lngVal = $pesertaLokasi[1];
    } else {
        $pesertaLokasi = absen_lokasi_dilaporkan_palsu() ? null : absen_koordinat_peserta();
        $latVal = $pesertaLokasi ? $pesertaLokasi[0] : null;
        $lngVal = $pesertaLokasi ? $pesertaLokasi[1] : null;
    }
    $foto = absen_store_upload(isset($_FILES['foto']) ? $_FILES['foto'] : null, 'absen-digital/foto', array('jpg', 'jpeg', 'png', 'webp'), 1024 * 1024, true);
    if (isset($foto['empty'])) {
        absen_tolak_absen($kegiatan, array('foto' => 'Absen tidak bisa disimpan tanpa foto selfie.'), $back);
    }
    if (isset($foto['error'])) {
        absen_tolak_absen($kegiatan, array('foto' => $foto['error']), $back);
    }
    $ttd = absen_decode_ttd(isset($_POST['ttd']) ? $_POST['ttd'] : '');
    if (isset($ttd['error'])) {
        absen_unlink_rel($foto['path']);
        absen_tolak_absen($kegiatan, array('ttd' => $ttd['error']), $back);
    }
    $nip = isset($peserta['nip']) ? trim((string) $peserta['nip']) : '';
    $db = absen_db();
    try {
        $db->beginTransaction();
        $kunci = $db->prepare('SELECT id FROM absen_kegiatans WHERE id = ? FOR UPDATE');
        $kunci->execute(array($kegiatan['id']));
        $pakai = absen_sidik_id();
        if ($pakai === '' || absen_perangkat_terkunci($kegiatan['id'])) {
            $db->rollBack();
            absen_unlink_rel($foto['path']);
            absen_unlink_rel($ttd['path']);
            $pesanKunci = $pakai === ''
                ? 'Perangkat tidak dikenali. Muat ulang halaman absen, lalu coba lagi.'
                : absen_pesan_perangkat_terpakai();
            absen_tolak_absen($kegiatan, array('perangkat' => $pesanKunci), $back);
        }
        if (absen_sudah_hadir($kegiatan['id'], $peserta['id'], null)) {
            $db->rollBack();
            absen_unlink_rel($foto['path']);
            absen_unlink_rel($ttd['path']);
            absen_tolak_absen($kegiatan, array('user_id' => $peserta['name'].' sudah absen pada kegiatan ini. Satu identitas hanya bisa absen sekali.'), $back);
        }
        $now = date('Y-m-d H:i:s');
        $ins = $db->prepare('INSERT INTO absen_kehadirans (kegiatan_id, user_id, nik, nama, metode, foto_path, latitude, longitude, ttd_path, perangkat_id, kunci_aktif, dicatat_oleh, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NULL, ?, ?)');
        $ins->execute(array(
            $kegiatan['id'],
            $peserta['id'],
            $nip === '' ? null : $nip,
            $peserta['name'],
            'barcode',
            $foto['path'],
            $latVal,
            $lngVal,
            $ttd['path'],
            $pakai,
            $now,
            $now,
        ));
        $bersih = $db->prepare('DELETE FROM absen_gangguan WHERE kegiatan_id = ? AND user_id = ?');
        $bersih->execute(array($kegiatan['id'], $peserta['id']));
        $db->commit();
        absen_set_perangkat_cookie($pakai);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        absen_unlink_rel($foto['path']);
        absen_unlink_rel($ttd['path']);
        absen_tolak_absen($kegiatan, array('user_id' => 'Absen gagal disimpan. Satu identitas hanya bisa absen sekali pada kegiatan ini.'), $back);
    }
    absen_flash_success('Absen '.$peserta['name'].' tersimpan.', $back);
}

function absen_valid_kegiatan_input()
{
    $jenis = absen_jenis();
    $nama = absen_post_text('nama', 150);
    $jenisKey = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';
    $tanggal = isset($_POST['tanggal']) ? (string) $_POST['tanggal'] : '';
    $mulai = absen_jam(isset($_POST['waktu_mulai']) ? $_POST['waktu_mulai'] : '');
    $selesai = absen_jam(isset($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : '');
    $tempat = absen_post_text('tempat', 150);
    $keterangan = absen_post_text('keterangan', 2000);
    $lat = isset($_POST['lokasi_lat']) ? trim((string) $_POST['lokasi_lat']) : '';
    $lng = isset($_POST['lokasi_lng']) ? trim((string) $_POST['lokasi_lng']) : '';
    $modeLokasi = isset($_POST['lokasi_mode']) ? (string) $_POST['lokasi_mode'] : 'titik';
    $errors = array();
    if ($modeLokasi !== 'titik' && $modeLokasi !== 'bebas') {
        $errors['lokasi_mode'] = 'Pilih aturan lokasi kegiatan.';
    }
    if ($nama === '') {
        $errors['nama'] = 'Nama kegiatan wajib diisi.';
    }
    if (!isset($jenis[$jenisKey])) {
        $errors['jenis'] = 'Pilih jenis kegiatan.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || strtotime($tanggal) === false) {
        $errors['tanggal'] = 'Tanggal kegiatan wajib diisi.';
    }
    if ($mulai === '') {
        $errors['waktu_mulai'] = 'Jam mulai wajib diisi.';
    }
    if ($selesai === '') {
        $errors['waktu_selesai'] = 'Jam selesai wajib diisi.';
    }
    if ($mulai !== '' && $selesai !== '' && $selesai <= $mulai) {
        $errors['waktu_selesai'] = 'Jam selesai harus lebih akhir dari jam mulai pada hari yang sama.';
    }
    $koordinatValid = is_numeric($lat) && is_numeric($lng) && (float) $lat >= -90 && (float) $lat <= 90 && (float) $lng >= -180 && (float) $lng <= 180;
    if ($modeLokasi === 'bebas') {
        $lat = null;
        $lng = null;
    } elseif ($lat === '' || $lng === '') {
        $errors['lokasi_lat'] = 'Titik lokasi kegiatan wajib diisi.';
    } elseif (!$koordinatValid) {
        $errors['lokasi_lat'] = 'Titik lokasi tidak valid. Isi lintang dan bujur yang benar.';
    }
    if ($errors) {
        return array('errors' => $errors);
    }

    return array(
        'nama' => $nama,
        'jenis' => $jenisKey,
        'tanggal' => $tanggal,
        'waktu' => $mulai,
        'waktu_mulai' => $mulai,
        'waktu_selesai' => $selesai,
        'tempat' => $tempat === '' ? null : $tempat,
        'keterangan' => $keterangan === '' ? null : $keterangan,
        'lokasi_lat' => $lat,
        'lokasi_lng' => $lng,
        'lokasi_mode' => $modeLokasi === 'bebas' ? 'bebas' : 'titik',
    );
}

function absen_unggahan_kosong_karena_ukuran()
{
    $length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($length < 1048576) {
        return false;
    }

    return empty($_POST) && empty($_FILES);
}

function absen_jenis_gambar($path)
{
    $info = @getimagesize($path);
    if (!is_array($info) || empty($info[0]) || empty($info[1]) || empty($info[2])) {
        return '';
    }
    $lebar = (int) $info[0];
    $tinggi = (int) $info[1];
    if ($lebar < 1 || $tinggi < 1 || $lebar > 8000 || $tinggi > 8000 || ($lebar * $tinggi) > 24000000) {
        return '';
    }
    $map = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
    );
    if (defined('IMAGETYPE_WEBP')) {
        $map[IMAGETYPE_WEBP] = 'webp';
    }
    if (!isset($map[$info[2]])) {
        return '';
    }

    return $map[$info[2]];
}

function absen_tulis_gambar($src, $dest, $jenis)
{
    $gambar = null;
    if ($jenis === 'jpg') {
        $gambar = @imagecreatefromjpeg($src);
    } elseif ($jenis === 'png') {
        $gambar = @imagecreatefrompng($src);
    } elseif ($jenis === 'webp' && function_exists('imagecreatefromwebp')) {
        $gambar = @imagecreatefromwebp($src);
    }
    if (!$gambar) {
        return false;
    }
    $ok = false;
    if ($jenis === 'jpg') {
        $ok = imagejpeg($gambar, $dest, 85);
    } elseif ($jenis === 'png') {
        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);
        $ok = imagepng($gambar, $dest, 6);
    } elseif ($jenis === 'webp' && function_exists('imagewebp')) {
        $ok = imagewebp($gambar, $dest, 85);
    }
    imagedestroy($gambar);

    return $ok && is_file($dest);
}

function absen_berkas_diterima($path, $ext)
{
    $fh = @fopen($path, 'rb');
    $head = $fh ? (string) fread($fh, 16) : '';
    if ($fh) {
        fclose($fh);
    }
    if ($head === '') {
        return false;
    }
    $awal = ltrim($head);
    if (stripos($awal, '<?php') === 0 || stripos($awal, '<html') === 0 || stripos($awal, '<svg') === 0 || stripos($awal, '<script') === 0 || stripos($awal, '<!doctype') === 0) {
        return false;
    }
    if ($ext === 'pdf') {
        return strncmp($head, '%PDF', 4) === 0;
    }
    if ($ext === 'zip' || $ext === 'docx' || $ext === 'xlsx' || $ext === 'pptx') {
        return strncmp($head, "PK\x03\x04", 4) === 0 || strncmp($head, "PK\x05\x06", 4) === 0 || strncmp($head, "PK\x07\x08", 4) === 0;
    }
    if ($ext === 'doc' || $ext === 'xls' || $ext === 'ppt') {
        return strncmp($head, "\xD0\xCF\x11\xE0", 4) === 0;
    }
    if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'webp') {
        $jenis = absen_jenis_gambar($path);
        if ($jenis === '') {
            return false;
        }
        if ($ext === 'jpg' || $ext === 'jpeg') {
            return $jenis === 'jpg';
        }

        return $jenis === $ext;
    }

    return false;
}

function absen_store_upload($file, $directory, $allowedExt, $maxBytes, $mustImage)
{
    if (!is_array($file) || !isset($file['error']) || !isset($file['tmp_name'])) {
        return array('error' => 'Berkas tidak ditemukan.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return array('empty' => true);
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return array('error' => $mustImage
            ? 'Foto terlalu besar. Ambil ulang; foto diperkecil otomatis di perangkat sebelum dikirim.'
            : 'Berkas terlalu besar.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return array('error' => 'Gagal mengunggah berkas.');
    }
    if ((int) $file['size'] > $maxBytes) {
        if ($mustImage && $maxBytes <= 1048576) {
            return array('error' => 'Foto terlalu besar. Ambil ulang; foto diperkecil otomatis di perangkat sebelum dikirim.');
        }

        return array('error' => $mustImage ? 'Ukuran foto maksimal 4 MB.' : 'Berkas hasil rapat maksimal 10 MB.');
    }
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', (string) $ext);
    if (!in_array($ext, $allowedExt, true) || !absen_berkas_diterima($file['tmp_name'], $ext)) {
        return array('error' => $mustImage ? 'Berkas harus berupa gambar JPEG, PNG, atau WebP.' : 'Tipe berkas tidak didukung. Gunakan PDF, dokumen kantor, gambar, atau ZIP.');
    }
    $jenisGambar = '';
    if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'webp') {
        $jenisGambar = absen_jenis_gambar($file['tmp_name']);
        if ($jenisGambar === '' || ($mustImage && !in_array($jenisGambar, array('jpg', 'png', 'webp'), true))) {
            return array('error' => 'Berkas harus berupa gambar JPEG, PNG, atau WebP.');
        }
    }
    $dir = absen_storage_root().'/'.trim($directory, '/');
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return array('error' => 'Folder penyimpanan tidak bisa dibuat.');
    }
    $simpanExt = $jenisGambar !== '' ? $jenisGambar : $ext;
    $rel = trim($directory, '/').'/'.absen_uuid().'.'.$simpanExt;
    $dest = absen_storage_root().'/'.$rel;
    if ($jenisGambar !== '') {
        if (!absen_tulis_gambar($file['tmp_name'], $dest, $jenisGambar)) {
            if (is_file($dest)) {
                unlink($dest);
            }

            return array('error' => 'Berkas harus berupa gambar JPEG, PNG, atau WebP.');
        }
    } elseif (!move_uploaded_file($file['tmp_name'], $dest)) {
        return array('error' => 'Berkas tidak tersimpan.');
    }
    if (absen_rel_aman($rel) === '') {
        if (is_file($dest)) {
            unlink($dest);
        }

        return array('error' => 'Berkas tidak tersimpan.');
    }

    return array(
        'path' => $rel,
        'original' => absen_safe_name($file['name'], $mustImage ? 'foto' : 'berkas'),
    );
}

function absen_decode_ttd($dataUrl)
{
    $prefix = 'data:image/png;base64,';
    if (strpos((string) $dataUrl, $prefix) !== 0) {
        return array('error' => 'Tanda tangan harus digambar di layar.');
    }
    $raw = base64_decode(substr($dataUrl, strlen($prefix)), true);
    if ($raw === false || strlen($raw) < 80 || strlen($raw) > 700000 || strpos($raw, "\x89PNG\r\n\x1a\n") !== 0) {
        return array('error' => 'Tanda tangan tidak valid atau terlalu besar.');
    }
    if (!function_exists('imagecreatefromstring')) {
        return array('error' => 'Server belum mendukung pembacaan tanda tangan.');
    }
    $image = @imagecreatefromstring($raw);
    if ($image === false) {
        return array('error' => 'Tanda tangan tidak dapat dibaca.');
    }
    $ink = 0;
    $width = imagesx($image);
    $height = imagesy($image);
    if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096) {
        imagedestroy($image);

        return array('error' => 'Tanda tangan tidak valid atau terlalu besar.');
    }
    for ($y = 0; $y < $height; $y += 3) {
        for ($x = 0; $x < $width; $x += 3) {
            $color = imagecolorat($image, $x, $y);
            $red = ($color >> 16) & 0xFF;
            $green = ($color >> 8) & 0xFF;
            $blue = $color & 0xFF;
            $alpha = ($color >> 24) & 0x7F;
            if ($alpha < 100 && $red < 90 && $green < 90 && $blue < 90) {
                $ink++;
            }
            if ($ink >= 8) {
                break 2;
            }
        }
    }
    if ($ink < 8) {
        imagedestroy($image);

        return array('error' => 'Tanda tangan masih kosong. Gambar di kotak yang tersedia.');
    }
    $dir = absen_storage_root().'/absen-digital/ttd';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return array('error' => 'Folder penyimpanan tidak bisa dibuat.');
    }
    $rel = 'absen-digital/ttd/'.absen_uuid().'.png';
    $dest = absen_storage_root().'/'.$rel;
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $tersimpan = imagepng($image, $dest, 6);
    imagedestroy($image);
    if (!$tersimpan || absen_rel_aman($rel) === '') {
        if (is_file($dest)) {
            unlink($dest);
        }

        return array('error' => 'Tanda tangan tidak tersimpan.');
    }

    return array('path' => $rel);
}

function absen_filters()
{
    $jenis = absen_jenis();
    $has = isset($_GET['tanggal_dari']) || isset($_GET['tanggal_sampai']) || isset($_GET['jenis']) || isset($_GET['kegiatan_id']);
    $dari = $has ? trim((string) (isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '')) : date('Y-m-01');
    $sampai = $has ? trim((string) (isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : '')) : date('Y-m-d');
    $jenisKey = isset($_GET['jenis']) ? (string) $_GET['jenis'] : '';
    $kegiatanId = isset($_GET['kegiatan_id']) ? (int) $_GET['kegiatan_id'] : 0;
    $errors = array();
    if ($dari !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
        $errors['tanggal_dari'] = 'Tanggal dari tidak valid.';
    }
    if ($sampai !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
        $errors['tanggal_sampai'] = 'Tanggal sampai tidak valid.';
    }
    if ($jenisKey !== '' && !isset($jenis[$jenisKey])) {
        $errors['jenis'] = 'Jenis kegiatan tidak valid.';
    }
    if (!$errors && $dari !== '' && $sampai !== '' && $sampai < $dari) {
        $errors['tanggal_sampai'] = 'Tanggal sampai tidak boleh lebih awal dari tanggal dari.';
    }
    if (!$errors && $dari !== '' && $sampai !== '') {
        $start = strtotime($dari);
        $end = strtotime($sampai);
        if ($start && $end && (($end - $start) / 86400) > 366) {
            $errors['tanggal_sampai'] = 'Rentang tanggal maksimal 366 hari.';
        }
    }
    if ($kegiatanId > 0) {
        $stmt = absen_db()->prepare('SELECT id FROM absen_kegiatans WHERE id = ?');
        $stmt->execute(array($kegiatanId));
        if (!$stmt->fetch()) {
            $kegiatanId = 0;
        }
    }

    return array(
        'errors' => $errors,
        'tanggal_dari' => $dari,
        'tanggal_sampai' => $sampai,
        'jenis' => $jenisKey,
        'kegiatan_id' => $kegiatanId,
    );
}

function absen_where_kegiatan($filters, &$params)
{
    $sql = ' WHERE 1=1';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['tanggal_dari'])) {
        $sql .= ' AND tanggal >= ?';
        $params[] = $filters['tanggal_dari'];
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['tanggal_sampai'])) {
        $sql .= ' AND tanggal <= ?';
        $params[] = $filters['tanggal_sampai'];
    }
    if ($filters['jenis'] !== '') {
        $sql .= ' AND jenis = ?';
        $params[] = $filters['jenis'];
    }
    if ($filters['kegiatan_id'] > 0) {
        $sql .= ' AND id = ?';
        $params[] = $filters['kegiatan_id'];
    }

    return $sql;
}

function absen_lindungi_penyimpanan()
{
    $dir = absen_storage_root();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return;
    }
    $ht = $dir.'/.htaccess';
    if (!is_file($ht)) {
        @file_put_contents($ht, "Options -Indexes\nRequire all denied\nDeny from all\n");
    }
}

function absen_send_file($rel, $name, $inline)
{
    $full = absen_file_penuh($rel);
    if ($full === '') {
        http_response_code(404);
        echo 'Berkas tidak ditemukan.';
        exit;
    }
    $mime = 'application/octet-stream';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $full) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if (is_string($detected) && $detected !== '') {
            $mime = $detected;
        }
    }
    $gambar = array('image/jpeg' => true, 'image/png' => true, 'image/webp' => true, 'image/gif' => true);
    $berbahaya = (isset($gambar[$mime]) === false) && (
        strpos($mime, 'html') !== false
        || strpos($mime, 'xml') !== false
        || strpos($mime, 'svg') !== false
        || strpos($mime, 'php') !== false
        || strpos($mime, 'javascript') !== false
    );
    if ($berbahaya) {
        $mime = 'application/octet-stream';
    }
    $tampil = $inline && isset($gambar[$mime]);
    $safe = str_replace(array('"', "\r", "\n"), '', absen_safe_name($name, 'berkas'));
    header('Content-Security-Policy: default-src \'none\'; sandbox');
    header('X-Content-Type-Options: nosniff');
    header('Content-Type: '.$mime);
    header('Content-Disposition: '.($tampil ? 'inline' : 'attachment').'; filename="'.$safe.'"');
    header('Content-Length: '.filesize($full));
    readfile($full);
    exit;
}
