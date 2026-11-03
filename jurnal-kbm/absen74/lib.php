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

function absen_boot()
{
    absen_load_env();
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
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
    if (empty($_SESSION['_token'])) {
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
            email VARCHAR(190) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(32) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY users_email_unique (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS absen_kegiatans (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nama VARCHAR(150) NOT NULL,
            jenis VARCHAR(32) NOT NULL,
            tanggal DATE NOT NULL,
            waktu VARCHAR(8) NULL,
            tempat VARCHAR(150) NULL,
            keterangan TEXT NULL,
            kode VARCHAR(32) NOT NULL,
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
            nik VARCHAR(16) NULL,
            nama VARCHAR(150) NOT NULL,
            metode VARCHAR(16) NOT NULL,
            foto_path VARCHAR(255) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            ttd_path VARCHAR(255) NULL,
            dicatat_oleh INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY absen_kehadirans_user_unique (kegiatan_id, user_id),
            UNIQUE KEY absen_kehadirans_nik_unique (kegiatan_id, nik),
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
    absen_seed_users();
}

function absen_seed_users()
{
    $rows = array(
        array('ABSEN_ADMIN_EMAIL', 'ABSEN_ADMIN_PASSWORD', 'ABSEN_ADMIN_NAME', 'admin', 'Admin Absen'),
        array('ABSEN_GURU_EMAIL', 'ABSEN_GURU_PASSWORD', 'ABSEN_GURU_NAME', 'guru', 'Guru Absen'),
        array('ABSEN_STAFF_EMAIL', 'ABSEN_STAFF_PASSWORD', 'ABSEN_STAFF_NAME', 'staff', 'Staff Absen'),
    );
    $now = date('Y-m-d H:i:s');
    $db = absen_db();
    foreach ($rows as $row) {
        $email = trim(absen_env($row[0]));
        $password = absen_env($row[1]);
        $name = trim(absen_env($row[2]));
        if ($name === '') {
            $name = $row[4];
        }
        if ($email === '' || $password === '') {
            continue;
        }
        $check = $db->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute(array($email));
        if ($check->fetch()) {
            continue;
        }
        $insert = $db->prepare('INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?)');
        $insert->execute(array($name, $email, password_hash($password, PASSWORD_DEFAULT), $row[3], $now, $now));
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
        'guru' => 'Guru',
        'staff' => 'Staff',
        'kepala' => 'Kepala',
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

function absen_redirect($path)
{
    if (!is_string($path) || $path === '' || $path[0] !== '/') {
        $path = '/';
    }
    header('Location: '.$path);
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

        return $path;
    }

    return $fallback;
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
    return array('admin', 'guru', 'staff', 'kepala');
}

function absen_require_user()
{
    $user = absen_user();
    if (!$user || !in_array($user['role'], absen_roles_absen(), true)) {
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
    $cfg = trim(absen_env('APP_URL'));
    if ($cfg !== '') {
        return rtrim($cfg, '/');
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

    return ($https ? 'https' : 'http').'://'.$host;
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

function absen_assert_rel($path)
{
    if (!is_string($path) || strpos($path, 'absen-digital/') !== 0 || strpos($path, '..') !== false) {
        http_response_code(404);
        echo 'Berkas tidak ditemukan.';
        exit;
    }
}

function absen_unlink_rel($path)
{
    if (!is_string($path) || $path === '' || strpos($path, 'absen-digital/') !== 0 || strpos($path, '..') !== false) {
        return;
    }
    $full = absen_storage_root().'/'.$path;
    if (is_file($full)) {
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
            return strtoupper(trim($query['kode']));
        }
    }
    if (preg_match('/(?:^|[?&])kode=([A-Za-z0-9]+)/', $raw, $matches) === 1) {
        return strtoupper($matches[1]);
    }

    return strtoupper($raw);
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
    $roles = absen_roles_absen();
    $marks = implode(',', array_fill(0, count($roles), '?'));
    $stmt = absen_db()->prepare('SELECT id, name, role FROM users WHERE is_active = 1 AND role IN ('.$marks.') ORDER BY name');
    $stmt->execute($roles);

    return $stmt->fetchAll();
}

function absen_find_peserta($id)
{
    $stmt = absen_db()->prepare("SELECT id, name, role FROM users WHERE id = ? AND is_active = 1 AND role IN ('admin','guru','staff','kepala')");
    $stmt->execute(array((int) $id));
    $row = $stmt->fetch();

    return $row ? $row : null;
}

function absen_sudah_hadir($kegiatanId, $userId, $nik)
{
    if ($userId) {
        $stmt = absen_db()->prepare('SELECT id FROM absen_kehadirans WHERE kegiatan_id = ? AND user_id = ?');
        $stmt->execute(array((int) $kegiatanId, (int) $userId));

        return (bool) $stmt->fetch();
    }
    if ($nik) {
        $stmt = absen_db()->prepare('SELECT id FROM absen_kehadirans WHERE kegiatan_id = ? AND nik = ?');
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

function absen_valid_kegiatan_input()
{
    $jenis = absen_jenis();
    $nama = absen_post_text('nama', 150);
    $jenisKey = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';
    $tanggal = isset($_POST['tanggal']) ? (string) $_POST['tanggal'] : '';
    $waktu = isset($_POST['waktu']) ? trim((string) $_POST['waktu']) : '';
    $tempat = absen_post_text('tempat', 150);
    $keterangan = absen_post_text('keterangan', 2000);
    $errors = array();
    if ($nama === '') {
        $errors['nama'] = 'Nama kegiatan wajib diisi.';
    }
    if (!isset($jenis[$jenisKey])) {
        $errors['jenis'] = 'Pilih jenis kegiatan.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || strtotime($tanggal) === false) {
        $errors['tanggal'] = 'Tanggal kegiatan wajib diisi.';
    }
    if ($waktu !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $waktu)) {
        $errors['waktu'] = 'Format waktu tidak valid.';
    }
    if ($errors) {
        return array('errors' => $errors);
    }
    if (strlen($waktu) > 5) {
        $waktu = substr($waktu, 0, 5);
    }

    return array(
        'nama' => $nama,
        'jenis' => $jenisKey,
        'tanggal' => $tanggal,
        'waktu' => $waktu === '' ? null : $waktu,
        'tempat' => $tempat === '' ? null : $tempat,
        'keterangan' => $keterangan === '' ? null : $keterangan,
    );
}

function absen_store_upload($file, $directory, $allowedExt, $maxBytes, $mustImage)
{
    if (!is_array($file) || !isset($file['error'])) {
        return array('error' => 'Berkas tidak ditemukan.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return array('empty' => true);
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('error' => 'Gagal mengunggah berkas.');
    }
    if ((int) $file['size'] > $maxBytes) {
        return array('error' => $mustImage ? 'Ukuran foto maksimal 4 MB.' : 'Berkas hasil rapat maksimal 10 MB.');
    }
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext);
    if (!in_array($ext, $allowedExt, true)) {
        return array('error' => $mustImage ? 'Berkas harus berupa gambar.' : 'Tipe berkas tidak didukung. Gunakan PDF, dokumen kantor, gambar, atau ZIP.');
    }
    if ($mustImage) {
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return array('error' => 'Berkas harus berupa gambar.');
        }
    }
    $dir = absen_storage_root().'/'.trim($directory, '/');
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return array('error' => 'Folder penyimpanan tidak bisa dibuat.');
    }
    $rel = trim($directory, '/').'/'.absen_uuid().'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'], absen_storage_root().'/'.$rel)) {
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
    imagedestroy($image);
    if ($ink < 8) {
        return array('error' => 'Tanda tangan masih kosong. Gambar di kotak yang tersedia.');
    }
    $dir = absen_storage_root().'/absen-digital/ttd';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return array('error' => 'Folder penyimpanan tidak bisa dibuat.');
    }
    $rel = 'absen-digital/ttd/'.absen_uuid().'.png';
    if (file_put_contents(absen_storage_root().'/'.$rel, $raw) === false) {
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

function absen_send_file($rel, $name, $inline)
{
    absen_assert_rel($rel);
    $full = absen_storage_root().'/'.$rel;
    if (!is_file($full)) {
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
    $safe = str_replace(array('"', "\r", "\n"), '', absen_safe_name($name, 'berkas'));
    header('Content-Type: '.$mime);
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.$safe.'"');
    header('Content-Length: '.filesize($full));
    readfile($full);
    exit;
}
