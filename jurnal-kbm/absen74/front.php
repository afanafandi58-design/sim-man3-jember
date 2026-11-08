<?php

require __DIR__.'/lib.php';

try {
absen_boot();

try {
    absen_migrate();
} catch (Exception $e) {
    absen_fail_db();
}

$method = absen_method();
$path = absen_path();

if ($method === 'POST' && absen_unggahan_kosong_karena_ukuran() && ($path === '/absen-digital/scan' || preg_match('#^/absen-digital/k/[A-Za-z0-9]+$#', $path))) {
    $kembali = '/absen-digital/scan';
    if (preg_match('#^/absen-digital/k/([A-Za-z0-9]+)$#', $path, $unggahan)) {
        $kodeUnggahan = absen_kode_bersih($unggahan[1]);
        $kembali = $kodeUnggahan === '' ? '/absen-digital/scan' : '/absen-digital/k/'.rawurlencode($kodeUnggahan);
    }
    absen_flash_error(array('foto' => 'Foto terlalu besar dan tidak dikirim. Ambil ulang; foto diperkecil otomatis di perangkat sebelum disimpan.'), $kembali);
}

if ($method === 'POST') {
    absen_csrf();
}

if ($path === '/login' && $method === 'GET') {
    $current = absen_user();
    if ($current && $current['role'] === 'admin') {
        absen_redirect('/absen-digital/kegiatan');
    }
    absen_render('login', array('title' => 'Masuk pengelola'));
    exit;
}

if ($path === '/login' && $method === 'POST') {
    $ipMasuk = absen_klien_ip();
    if (!absen_batas('masuk', $ipMasuk, 20, 900, false)) {
        absen_flash_error(array('email' => 'Terlalu banyak percobaan masuk. Tunggu beberapa menit lalu coba lagi.'), '/login');
    }
    $email = trim(isset($_POST['email']) ? (string) $_POST['email'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $stmt = absen_db()->prepare('SELECT id, password, is_active, role FROM users WHERE email = ?');
    $stmt->execute(array($email));
    $row = $stmt->fetch();
    $ok = $row && (int) $row['is_active'] === 1 && $row['role'] === 'admin' && is_string($row['password']) && $row['password'] !== '' && password_verify($password, $row['password']);
    if (!$ok) {
        absen_batas('masuk', $ipMasuk, 20, 900, true);
        absen_flash_error(array('email' => 'Email atau kata sandi tidak sesuai.'), '/login');
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $row['id'];
    absen_redirect('/absen-digital/kegiatan');
}

if ($path === '/logout' && $method === 'POST') {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        $habis = time() - 42000;
        if (PHP_VERSION_ID >= 70300) {
            setcookie(session_name(), '', array(
                'expires' => $habis,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => isset($params['samesite']) ? $params['samesite'] : 'Lax',
            ));
        } else {
            setcookie(session_name(), '', $habis, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
    }
    session_destroy();
    absen_redirect('/absen-digital/scan');
}

if ($path === '/' || $path === '/absen-digital') {
    absen_redirect('/absen-digital/scan');
}

if ($path === '/absen-digital/peserta' && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    absen_render('peserta', array(
        'title' => 'Peserta',
        'rows' => absen_peserta(),
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/peserta' && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $hasil = absen_tambah_peserta(isset($_POST['name']) ? $_POST['name'] : '', isset($_POST['nip']) ? $_POST['nip'] : '');
    if (isset($hasil['errors'])) {
        absen_flash_error($hasil['errors'], '/absen-digital/peserta');
    }
    $pesan = $hasil['name'].' ditambahkan. Nama ini langsung bisa dipilih di halaman absen.';
    absen_flash_success($pesan, '/absen-digital/peserta');
}

if ($path === '/absen-digital/kegiatan' && $method === 'GET') {
    $user = absen_require_user();
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) {
        $page = 1;
    }
    $per = 15;
    $total = (int) absen_db()->query('SELECT COUNT(*) FROM absen_kegiatans')->fetchColumn();
    $pages = max(1, (int) ceil($total / $per));
    if ($page > $pages) {
        $page = $pages;
    }
    $stmt = absen_db()->prepare('SELECT k.*, (SELECT COUNT(*) FROM absen_kehadirans h WHERE h.kegiatan_id = k.id) AS hadir FROM absen_kegiatans k ORDER BY k.tanggal DESC, k.id DESC LIMIT '.$per.' OFFSET '.(($page - 1) * $per));
    $stmt->execute();
    absen_render('kegiatan_index', array(
        'title' => 'Kegiatan',
        'rows' => $stmt->fetchAll(),
        'page' => $page,
        'pages' => $pages,
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/kegiatan-baru' && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    absen_render('kegiatan_form', array(
        'title' => 'Kegiatan baru',
        'kegiatan' => array('id' => '', 'nama' => '', 'jenis' => 'rapat', 'tanggal' => date('Y-m-d'), 'waktu' => '07:00', 'waktu_mulai' => '07:00', 'waktu_selesai' => '15:00', 'tempat' => '', 'keterangan' => '', 'lokasi_lat' => '', 'lokasi_lng' => '', 'lokasi_mode' => 'titik'),
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/kegiatan' && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $data = absen_valid_kegiatan_input();
    if (isset($data['errors'])) {
        absen_flash_error($data['errors'], '/absen-digital/kegiatan-baru');
    }
    $now = date('Y-m-d H:i:s');
    $stmt = absen_db()->prepare('INSERT INTO absen_kegiatans (nama, jenis, tanggal, waktu, waktu_mulai, waktu_selesai, tempat, keterangan, lokasi_lat, lokasi_lng, lokasi_mode, kode, dibuat_oleh, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($data['nama'], $data['jenis'], $data['tanggal'], $data['waktu'], $data['waktu_mulai'], $data['waktu_selesai'], $data['tempat'], $data['keterangan'], $data['lokasi_lat'], $data['lokasi_lng'], $data['lokasi_mode'], absen_kode_baru(), (int) $user['id'], $now, $now));
    absen_flash_success('Kegiatan tersimpan. Kode QR siap dipakai.', '/absen-digital/kegiatan/'.absen_db()->lastInsertId());
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)$#', $path, $m) && $method === 'GET') {
    $user = absen_require_user();
    $kegiatan = absen_kegiatan($m[1]);
    $hadir = absen_db()->prepare('SELECT * FROM absen_kehadirans WHERE kegiatan_id = ? ORDER BY created_at');
    $hadir->execute(array($kegiatan['id']));
    $foto = absen_db()->prepare('SELECT * FROM absen_fotos WHERE kegiatan_id = ? ORDER BY id DESC');
    $foto->execute(array($kegiatan['id']));
    $gangguanStmt = absen_db()->prepare('SELECT * FROM absen_gangguan WHERE kegiatan_id = ? ORDER BY created_at DESC');
    $gangguanStmt->execute(array($kegiatan['id']));
    absen_render('kegiatan_show', array(
        'title' => $kegiatan['nama'],
        'kegiatan' => $kegiatan,
        'hadir' => $hadir->fetchAll(),
        'fotos' => $foto->fetchAll(),
        'gangguan' => $gangguanStmt->fetchAll(),
        'authUser' => $user,
    ));
    exit;
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/ubah$#', $path, $m) && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    absen_render('kegiatan_form', array(
        'title' => 'Ubah kegiatan',
        'kegiatan' => absen_kegiatan($m[1]),
        'authUser' => $user,
    ));
    exit;
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)$#', $path, $m) && $method === 'PUT') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $data = absen_valid_kegiatan_input();
    if (isset($data['errors'])) {
        absen_flash_error($data['errors'], '/absen-digital/kegiatan/'.$kegiatan['id'].'/ubah');
    }
    $stmt = absen_db()->prepare('UPDATE absen_kegiatans SET nama=?, jenis=?, tanggal=?, waktu=?, waktu_mulai=?, waktu_selesai=?, tempat=?, keterangan=?, lokasi_lat=?, lokasi_lng=?, lokasi_mode=?, updated_at=? WHERE id=?');
    $stmt->execute(array($data['nama'], $data['jenis'], $data['tanggal'], $data['waktu'], $data['waktu_mulai'], $data['waktu_selesai'], $data['tempat'], $data['keterangan'], $data['lokasi_lat'], $data['lokasi_lng'], $data['lokasi_mode'], date('Y-m-d H:i:s'), $kegiatan['id']));
    absen_flash_success('Data kegiatan diperbarui.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/hapus$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $paths = absen_db()->prepare('SELECT foto_path, ttd_path FROM absen_kehadirans WHERE kegiatan_id = ?');
    $paths->execute(array($kegiatan['id']));
    foreach ($paths->fetchAll() as $row) {
        absen_unlink_rel($row['foto_path']);
        absen_unlink_rel($row['ttd_path']);
    }
    $fotos = absen_db()->prepare('SELECT path FROM absen_fotos WHERE kegiatan_id = ?');
    $fotos->execute(array($kegiatan['id']));
    foreach ($fotos->fetchAll() as $row) {
        absen_unlink_rel($row['path']);
    }
    absen_unlink_rel($kegiatan['hasil_file_path']);
    $del = absen_db()->prepare('DELETE FROM absen_kegiatans WHERE id = ?');
    $del->execute(array($kegiatan['id']));
    absen_flash_success('Kegiatan dan data hadirnya dihapus.', '/absen-digital/kegiatan');
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/reset-kunci$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $peserta = absen_find_peserta(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
    $kembali = '/absen-digital/kegiatan/'.$kegiatan['id'];
    if (!$peserta) {
        absen_flash_error(array('user_id' => 'Peserta tidak ditemukan.'), $kembali);
    }
    absen_reset_kunci_user($kegiatan['id'], $peserta['id']);
    absen_flash_success('Kunci perangkat '.$peserta['name'].' dibuka. Data absen yang sudah tersimpan tidak dihapus. Peserta ini bisa absen lagi.', $kembali);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/bersih-tanda$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $stmt = absen_db()->prepare('DELETE FROM absen_gangguan WHERE kegiatan_id = ? AND user_id IS NULL');
    $stmt->execute(array($kegiatan['id']));
    absen_flash_success('Tanda tanpa nama dihapus. Data absen yang sudah tersimpan tidak diubah.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/foto$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $count = absen_db()->prepare('SELECT COUNT(*) FROM absen_fotos WHERE kegiatan_id = ?');
    $count->execute(array($kegiatan['id']));
    if ((int) $count->fetchColumn() >= 12) {
        absen_flash_error(array('foto' => 'Maksimal 12 foto dokumentasi per kegiatan.'), '/absen-digital/kegiatan/'.$kegiatan['id']);
    }
    $stored = absen_store_upload(isset($_FILES['foto']) ? $_FILES['foto'] : null, 'absen-digital/dokumentasi', array('jpg', 'jpeg', 'png', 'webp'), 4 * 1024 * 1024, true);
    if (isset($stored['empty'])) {
        absen_flash_error(array('foto' => 'Pilih foto yang akan diunggah.'), '/absen-digital/kegiatan/'.$kegiatan['id']);
    }
    if (isset($stored['error'])) {
        absen_flash_error(array('foto' => $stored['error']), '/absen-digital/kegiatan/'.$kegiatan['id']);
    }
    $now = date('Y-m-d H:i:s');
    $stmt = absen_db()->prepare('INSERT INTO absen_fotos (kegiatan_id, path, nama_asli, keterangan, diunggah_oleh, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $ket = absen_post_text('keterangan', 150);
    $stmt->execute(array($kegiatan['id'], $stored['path'], $stored['original'], $ket === '' ? null : $ket, (int) $user['id'], $now, $now));
    absen_flash_success('Foto dokumentasi tersimpan.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/foto/(\d+)/hapus$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $stmt = absen_db()->prepare('SELECT * FROM absen_fotos WHERE id = ? AND kegiatan_id = ?');
    $stmt->execute(array((int) $m[2], $kegiatan['id']));
    $foto = $stmt->fetch();
    if (!$foto) {
        http_response_code(404);
        echo 'Foto tidak ditemukan.';
        exit;
    }
    absen_unlink_rel($foto['path']);
    $del = absen_db()->prepare('DELETE FROM absen_fotos WHERE id = ?');
    $del->execute(array($foto['id']));
    absen_flash_success('Foto dokumentasi dihapus.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/hasil$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    $drive = trim(isset($_POST['hasil_drive_url']) ? (string) $_POST['hasil_drive_url'] : '');
    $catatan = absen_post_text('hasil_catatan', 2000);
    if ($drive !== '' && !absen_drive_ok($drive)) {
        absen_flash_error(array('hasil_drive_url' => 'Link harus HTTPS dari Google Drive atau Google Dokumen.'), '/absen-digital/kegiatan/'.$kegiatan['id']);
    }
    $filePath = $kegiatan['hasil_file_path'];
    $fileName = $kegiatan['hasil_file_nama'];
    if (isset($_FILES['hasil_file']) && $_FILES['hasil_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $stored = absen_store_upload($_FILES['hasil_file'], 'absen-digital/hasil', array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'), 10 * 1024 * 1024, false);
        if (isset($stored['error'])) {
            absen_flash_error(array('hasil_file' => $stored['error']), '/absen-digital/kegiatan/'.$kegiatan['id']);
        }
        absen_unlink_rel($filePath);
        $filePath = $stored['path'];
        $fileName = $stored['original'];
    }
    if ($drive === '' && !$filePath) {
        absen_flash_error(array('hasil_drive_url' => 'Isi link Google Drive atau unggah berkas hasil rapat. Keduanya boleh diisi sekaligus.'), '/absen-digital/kegiatan/'.$kegiatan['id']);
    }
    $stmt = absen_db()->prepare('UPDATE absen_kegiatans SET hasil_drive_url=?, hasil_file_path=?, hasil_file_nama=?, hasil_catatan=?, updated_at=? WHERE id=?');
    $stmt->execute(array($drive === '' ? null : $drive, $filePath, $fileName, $catatan === '' ? null : $catatan, date('Y-m-d H:i:s'), $kegiatan['id']));
    absen_flash_success('Hasil rapat tersimpan.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)/hasil-berkas/hapus$#', $path, $m) && $method === 'POST') {
    $user = absen_require_user();
    absen_require_admin($user);
    $kegiatan = absen_kegiatan($m[1]);
    absen_unlink_rel($kegiatan['hasil_file_path']);
    $stmt = absen_db()->prepare('UPDATE absen_kegiatans SET hasil_file_path=NULL, hasil_file_nama=NULL, updated_at=? WHERE id=?');
    $stmt->execute(array(date('Y-m-d H:i:s'), $kegiatan['id']));
    absen_flash_success('Berkas hasil rapat dihapus. Link Google Drive tidak diubah.', '/absen-digital/kegiatan/'.$kegiatan['id']);
}

if ($path === '/absen-digital/scan' && $method === 'GET') {
    $kode = absen_normalize_kode(isset($_GET['kode']) ? $_GET['kode'] : '');
    if ($kode !== '') {
        absen_redirect('/absen-digital/k/'.rawurlencode($kode));
    }
    absen_render('scan', array(
        'title' => 'Absen',
        'kode' => '',
        'kegiatan' => null,
        'kodeTidakDitemukan' => false,
        'peserta' => array(),
        'sudahHadir' => array(),
        'rentangBlokir' => '',
        'pesanPerangkat' => '',
        'halamanKegiatan' => false,
    ));
    exit;
}

if (preg_match('#^/absen-digital/k/([A-Za-z0-9]+)$#', $path, $m) && $method === 'GET') {
    $kode = absen_normalize_kode($m[1]);
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
    $stmt->execute(array($kode));
    $kegiatan = $stmt->fetch();
    $sudah = array();
    if ($kegiatan) {
        $list = absen_db()->prepare('SELECT id, user_id, nama, created_at FROM absen_kehadirans WHERE kegiatan_id = ? AND kunci_aktif = 1 ORDER BY nama');
        $list->execute(array($kegiatan['id']));
        $sudah = $list->fetchAll();
    }
    absen_render('scan', array(
        'title' => $kegiatan ? $kegiatan['nama'] : 'Absen',
        'kode' => $kode,
        'kegiatan' => $kegiatan,
        'kodeTidakDitemukan' => !$kegiatan,
        'peserta' => $kegiatan ? absen_peserta() : array(),
        'sudahHadir' => $sudah,
        'rentangBlokir' => $kegiatan ? absen_rentang_blokir($kegiatan) : '',
        'pesanPerangkat' => $kegiatan ? absen_pesan_perangkat($kegiatan) : '',
        'halamanKegiatan' => true,
    ));
    exit;
}

if (preg_match('#^/absen-digital/k/([A-Za-z0-9]+)/sidik$#', $path, $m) && $method === 'POST') {
    $kode = absen_normalize_kode($m[1]);
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
    $stmt->execute(array($kode));
    $kegiatan = $stmt->fetch();
    $terkunci = $kegiatan && absen_android() && absen_perangkat_terkunci($kegiatan['id']);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'terkunci' => $terkunci,
        'pesan' => $terkunci ? absen_pesan_perangkat_terpakai() : '',
    ));
    exit;
}

if (preg_match('#^/absen-digital/k/([A-Za-z0-9]+)/gangguan$#', $path, $m) && $method === 'POST') {
    $kode = absen_normalize_kode($m[1]);
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
    $stmt->execute(array($kode));
    $kegiatan = $stmt->fetch();
    header('Content-Type: application/json; charset=utf-8');
    if (!$kegiatan || !absen_batas('gangguan', absen_klien_ip(), 40, 600, true)) {
        echo json_encode(array('ok' => false));
        exit;
    }
    $pesanIzin = array(
        'Sidik perangkat tidak terbaca.' => 'tidak_terbaca',
        'Lokasi tidak terbaca.' => 'tidak_terbaca',
        'Pemeriksaan perangkat terlalu lama.' => 'tidak_terbaca',
    );
    $pesan = isset($_POST['keterangan']) ? trim((string) $_POST['keterangan']) : '';
    if (!isset($pesanIzin[$pesan])) {
        echo json_encode(array('ok' => false));
        exit;
    }
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $nama = '';
    if ($userId > 0) {
        $peserta = absen_find_peserta($userId);
        if (!$peserta) {
            $userId = 0;
        } else {
            $nama = $peserta['name'];
        }
    }
    if ($userId < 1) {
        echo json_encode(array('ok' => true));
        exit;
    }
    absen_catat_gangguan($kegiatan['id'], $userId, $nama, $pesanIzin[$pesan], $pesan);
    echo json_encode(array('ok' => true));
    exit;
}

if (preg_match('#^/absen-digital/k/([A-Za-z0-9]+)$#', $path, $m) && $method === 'POST') {
    $_POST['kode'] = absen_normalize_kode($m[1]);
    $path = '/absen-digital/scan';
}

if ($path === '/absen-digital/scan' && $method === 'POST') {
    $kode = absen_normalize_kode(isset($_POST['kode']) ? $_POST['kode'] : '');
    if ($kode === '') {
        absen_flash_error(array('kode' => 'Buka tautan kegiatan atau tempel kodenya.'), '/absen-digital/scan');
    }
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
    $stmt->execute(array($kode));
    $kegiatan = $stmt->fetch();
    if (!$kegiatan) {
        absen_flash_error(array('kode' => 'Kode kegiatan tidak ditemukan.'), '/absen-digital/scan');
    }
    absen_simpan_publik($kegiatan);
}

if ($path === '/absen-digital/absen') {
    $kode = absen_normalize_kode(isset($_GET['kode']) ? $_GET['kode'] : '');
    absen_redirect($kode === '' ? '/absen-digital/scan' : '/absen-digital/scan?kode='.rawurlencode($kode));
}

if (preg_match('#^/absen-digital/berkas/(foto|ttd|dokumentasi|hasil)/(\d+)$#', $path, $m) && $method === 'GET') {
    $user = absen_require_user();
    $jenis = $m[1];
    $id = (int) $m[2];
    if ($jenis === 'hasil') {
        absen_require_admin($user);
        $kegiatan = absen_kegiatan($id);
        if (!$kegiatan['hasil_file_path']) {
            http_response_code(404);
            echo 'Berkas tidak ditemukan.';
            exit;
        }
        absen_send_file($kegiatan['hasil_file_path'], $kegiatan['hasil_file_nama'] ? $kegiatan['hasil_file_nama'] : 'hasil', false);
    }
    if ($jenis === 'dokumentasi') {
        absen_require_admin($user);
        $stmt = absen_db()->prepare('SELECT * FROM absen_fotos WHERE id = ?');
        $stmt->execute(array($id));
        $foto = $stmt->fetch();
        if (!$foto) {
            http_response_code(404);
            echo 'Berkas tidak ditemukan.';
            exit;
        }
        absen_send_file($foto['path'], $foto['nama_asli'] ? $foto['nama_asli'] : 'dokumentasi', true);
    }
    $stmt = absen_db()->prepare('SELECT * FROM absen_kehadirans WHERE id = ?');
    $stmt->execute(array($id));
    $hadir = $stmt->fetch();
    if (!$hadir) {
        http_response_code(404);
        echo 'Berkas tidak ditemukan.';
        exit;
    }
    $owner = absen_is_admin($user) || (int) $hadir['user_id'] === (int) $user['id'] || (int) $hadir['dicatat_oleh'] === (int) $user['id'];
    if (!$owner) {
        http_response_code(403);
        echo 'Anda tidak memiliki akses ke halaman ini.';
        exit;
    }
    $rel = $jenis === 'foto' ? $hadir['foto_path'] : $hadir['ttd_path'];
    absen_send_file($rel, $jenis === 'foto' ? 'foto-kehadiran' : 'tanda-tangan', true);
}

if ($path === '/absen-digital/ringkasan' && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    $filters = absen_filters();
    if ($filters['errors']) {
        $_SESSION['_errors'] = $filters['errors'];
    }
    $params = array();
    $where = absen_where_kegiatan($filters, $params);
    $stmt = absen_db()->prepare('SELECT k.*, (SELECT COUNT(*) FROM absen_kehadirans h WHERE h.kegiatan_id = k.id) AS hadir FROM absen_kegiatans k '.$where.' ORDER BY k.tanggal DESC, k.nama');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $perJenis = array();
    $totalHadir = 0;
    foreach ($rows as $row) {
        $n = (int) $row['hadir'];
        $totalHadir += $n;
        if (!isset($perJenis[$row['jenis']])) {
            $perJenis[$row['jenis']] = 0;
        }
        $perJenis[$row['jenis']] += $n;
    }
    $semua = absen_db()->query('SELECT id, nama, tanggal FROM absen_kegiatans ORDER BY tanggal DESC')->fetchAll();
    absen_render('ringkasan', array(
        'title' => 'Ringkasan',
        'filters' => $filters,
        'rows' => $rows,
        'perJenis' => $perJenis,
        'totalHadir' => $totalHadir,
        'semuaKegiatan' => $semua,
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/cetak' && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    $filters = absen_filters();
    absen_render('cetak', array(
        'title' => 'Laporan',
        'filters' => $filters,
        'semuaKegiatan' => absen_db()->query('SELECT id, nama, tanggal FROM absen_kegiatans ORDER BY tanggal DESC')->fetchAll(),
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/cetak/halaman' && $method === 'GET') {
    $user = absen_require_user();
    absen_require_admin($user);
    $filters = absen_filters();
    if ($filters['errors']) {
        $_SESSION['_errors'] = $filters['errors'];
        absen_redirect('/absen-digital/cetak');
    }
    $params = array();
    $where = absen_where_kegiatan($filters, $params);
    $sql = 'SELECT h.*, k.nama AS kegiatan_nama, k.jenis, k.tanggal, k.waktu, k.waktu_mulai, k.waktu_selesai, k.tempat, k.kode
        FROM absen_kehadirans h
        JOIN absen_kegiatans k ON k.id = h.kegiatan_id
        WHERE h.kegiatan_id IN (SELECT id FROM absen_kegiatans '.$where.')
        ORDER BY k.tanggal, k.id, h.nama';
    $stmt = absen_db()->prepare($sql);
    $stmt->execute($params);
    $groups = array();
    foreach ($stmt->fetchAll() as $row) {
        $groups[$row['kegiatan_id']][] = $row;
    }
    absen_render('cetak_halaman', array(
        'title' => 'Daftar hadir',
        'filters' => $filters,
        'groups' => $groups,
        'authUser' => $user,
    ), false);
    exit;
}

http_response_code(404);
echo 'Halaman tidak ditemukan.';
} catch (Throwable $e) {
    error_log('absen gagal: '.get_class($e));
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Permintaan tidak bisa diproses.';
}
