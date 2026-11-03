<?php

require __DIR__.'/lib.php';

absen_boot();

try {
    absen_migrate();
} catch (Exception $e) {
    absen_fail_db();
}

$method = absen_method();
$path = absen_path();

if ($method === 'POST') {
    absen_csrf();
}

if ($path === '/login' && $method === 'GET') {
    if (absen_user()) {
        absen_redirect('/absen-digital/kegiatan');
    }
    absen_render('login', array('title' => 'Masuk'));
    exit;
}

if ($path === '/login' && $method === 'POST') {
    $email = trim(isset($_POST['email']) ? (string) $_POST['email'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $stmt = absen_db()->prepare('SELECT id, password, is_active, role FROM users WHERE email = ?');
    $stmt->execute(array($email));
    $row = $stmt->fetch();
    $ok = $row && (int) $row['is_active'] === 1 && in_array($row['role'], absen_roles_absen(), true) && password_verify($password, $row['password']);
    if (!$ok) {
        absen_flash_error(array('email' => 'Email atau kata sandi tidak sesuai.'), '/login');
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $row['id'];
    absen_redirect('/absen-digital/kegiatan');
}

if ($path === '/logout' && $method === 'POST') {
    $_SESSION = array();
    session_destroy();
    absen_redirect('/login');
}

if ($path === '/' || $path === '/absen-digital') {
    $user = absen_require_user();
    absen_redirect('/absen-digital/kegiatan');
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
        'kegiatan' => array('id' => '', 'nama' => '', 'jenis' => 'rapat', 'tanggal' => date('Y-m-d'), 'waktu' => '', 'tempat' => '', 'keterangan' => ''),
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
    $stmt = absen_db()->prepare('INSERT INTO absen_kegiatans (nama, jenis, tanggal, waktu, tempat, keterangan, kode, dibuat_oleh, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($data['nama'], $data['jenis'], $data['tanggal'], $data['waktu'], $data['tempat'], $data['keterangan'], absen_kode_baru(), (int) $user['id'], $now, $now));
    absen_flash_success('Kegiatan tersimpan. Kode QR siap dipakai.', '/absen-digital/kegiatan/'.absen_db()->lastInsertId());
}

if (preg_match('#^/absen-digital/kegiatan/(\d+)$#', $path, $m) && $method === 'GET') {
    $user = absen_require_user();
    $kegiatan = absen_kegiatan($m[1]);
    $hadir = absen_db()->prepare('SELECT * FROM absen_kehadirans WHERE kegiatan_id = ? ORDER BY created_at');
    $hadir->execute(array($kegiatan['id']));
    $foto = absen_db()->prepare('SELECT * FROM absen_fotos WHERE kegiatan_id = ? ORDER BY id DESC');
    $foto->execute(array($kegiatan['id']));
    absen_render('kegiatan_show', array(
        'title' => $kegiatan['nama'],
        'kegiatan' => $kegiatan,
        'hadir' => $hadir->fetchAll(),
        'fotos' => $foto->fetchAll(),
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
    $stmt = absen_db()->prepare('UPDATE absen_kegiatans SET nama=?, jenis=?, tanggal=?, waktu=?, tempat=?, keterangan=?, updated_at=? WHERE id=?');
    $stmt->execute(array($data['nama'], $data['jenis'], $data['tanggal'], $data['waktu'], $data['tempat'], $data['keterangan'], date('Y-m-d H:i:s'), $kegiatan['id']));
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
    $user = absen_require_user();
    $kode = absen_normalize_kode(isset($_GET['kode']) ? $_GET['kode'] : '');
    $kegiatan = null;
    $sudah = array();
    if ($kode !== '') {
        $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
        $stmt->execute(array($kode));
        $kegiatan = $stmt->fetch();
        if ($kegiatan) {
            $list = absen_db()->prepare('SELECT id, user_id, nama, created_at FROM absen_kehadirans WHERE kegiatan_id = ? ORDER BY nama');
            $list->execute(array($kegiatan['id']));
            $sudah = $list->fetchAll();
        }
    }
    absen_render('scan', array(
        'title' => 'Scan Barcode',
        'kode' => $kode,
        'kegiatan' => $kegiatan,
        'kodeTidakDitemukan' => $kode !== '' && !$kegiatan,
        'peserta' => absen_peserta(),
        'sudahHadir' => $sudah,
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/scan' && $method === 'POST') {
    $user = absen_require_user();
    $kode = absen_normalize_kode(isset($_POST['kode']) ? $_POST['kode'] : '');
    if ($kode === '') {
        absen_flash_error(array('kode' => 'Kode hasil scan wajib diisi.'), '/absen-digital/scan');
    }
    $stmt = absen_db()->prepare('SELECT * FROM absen_kegiatans WHERE kode = ?');
    $stmt->execute(array($kode));
    $kegiatan = $stmt->fetch();
    if (!$kegiatan) {
        absen_flash_error(array('kode' => 'Kode kegiatan tidak ditemukan.'), '/absen-digital/scan');
    }
    $peserta = absen_find_peserta(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
    if (!$peserta) {
        absen_flash_error(array('user_id' => 'Pilih pengguna yang aktif.'), '/absen-digital/scan?kode='.rawurlencode($kegiatan['kode']));
    }
    if (absen_sudah_hadir($kegiatan['id'], $peserta['id'], null)) {
        absen_flash_error(array('user_id' => $peserta['name'].' sudah tercatat hadir.'), '/absen-digital/scan?kode='.rawurlencode($kegiatan['kode']));
    }
    $now = date('Y-m-d H:i:s');
    $ins = absen_db()->prepare('INSERT INTO absen_kehadirans (kegiatan_id, user_id, nama, metode, dicatat_oleh, created_at, updated_at) VALUES (?, ?, ?, "qr", ?, ?, ?)');
    $ins->execute(array($kegiatan['id'], $peserta['id'], $peserta['name'], (int) $user['id'], $now, $now));
    absen_flash_success('Kehadiran '.$peserta['name'].' tercatat lewat scan.', '/absen-digital/scan?kode='.rawurlencode($kegiatan['kode']));
}

if ($path === '/absen-digital/absen' && $method === 'GET') {
    $user = absen_require_user();
    $kegiatan = absen_db()->query('SELECT id, nama, jenis, tanggal FROM absen_kegiatans ORDER BY tanggal DESC, nama')->fetchAll();
    $riwayatStmt = absen_db()->prepare('SELECT h.*, k.nama AS kegiatan_nama, k.tanggal FROM absen_kehadirans h JOIN absen_kegiatans k ON k.id = h.kegiatan_id WHERE h.user_id = ? OR h.dicatat_oleh = ? ORDER BY h.id DESC LIMIT 15');
    $riwayatStmt->execute(array((int) $user['id'], (int) $user['id']));
    absen_render('form', array(
        'title' => 'Absen Saya',
        'kegiatans' => $kegiatan,
        'peserta' => absen_peserta(),
        'riwayat' => $riwayatStmt->fetchAll(),
        'authUser' => $user,
    ));
    exit;
}

if ($path === '/absen-digital/absen' && $method === 'POST') {
    $user = absen_require_user();
    $kegiatanId = isset($_POST['kegiatan_id']) ? (int) $_POST['kegiatan_id'] : 0;
    $mode = isset($_POST['mode_peserta']) ? (string) $_POST['mode_peserta'] : '';
    $stmt = absen_db()->prepare('SELECT id FROM absen_kegiatans WHERE id = ?');
    $stmt->execute(array($kegiatanId));
    if (!$stmt->fetch()) {
        absen_flash_error(array('kegiatan_id' => 'Pilih kegiatan.'), '/absen-digital/absen');
    }
    if ($mode !== 'user' && $mode !== 'nik') {
        absen_flash_error(array('mode_peserta' => 'Pilih cara mencatat peserta.'), '/absen-digital/absen');
    }
    $userId = null;
    $nik = null;
    $nama = '';
    if ($mode === 'user') {
        $peserta = absen_find_peserta(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
        if (!$peserta) {
            absen_flash_error(array('user_id' => 'Pilih pengguna yang aktif.'), '/absen-digital/absen');
        }
        $userId = (int) $peserta['id'];
        $nama = $peserta['name'];
    } else {
        $nik = preg_replace('/\s+/', '', isset($_POST['nik']) ? (string) $_POST['nik'] : '');
        $nama = absen_post_text('nama', 150);
        if (!preg_match('/^[0-9]{16}$/', $nik)) {
            absen_flash_error(array('nik' => 'NIK harus 16 digit angka.'), '/absen-digital/absen');
        }
        if ($nama === '') {
            absen_flash_error(array('nama' => 'Nama peserta wajib diisi jika memakai NIK.'), '/absen-digital/absen');
        }
    }
    if (absen_sudah_hadir($kegiatanId, $userId, $nik)) {
        absen_flash_error(array('kegiatan_id' => 'Peserta ini sudah tercatat hadir pada kegiatan tersebut.'), '/absen-digital/absen');
    }
    $lat = isset($_POST['latitude']) ? (string) $_POST['latitude'] : '';
    $lng = isset($_POST['longitude']) ? (string) $_POST['longitude'] : '';
    if (!is_numeric($lat) || (float) $lat < -90 || (float) $lat > 90 || !is_numeric($lng) || (float) $lng < -180 || (float) $lng > 180) {
        absen_flash_error(array('latitude' => 'Titik lokasi wajib diisi. Gunakan GPS atau isi lintang dan bujur secara manual.'), '/absen-digital/absen');
    }
    $foto = absen_store_upload(isset($_FILES['foto']) ? $_FILES['foto'] : null, 'absen-digital/foto', array('jpg', 'jpeg', 'png', 'webp'), 4 * 1024 * 1024, true);
    if (isset($foto['empty'])) {
        absen_flash_error(array('foto' => 'Unggah foto kehadiran.'), '/absen-digital/absen');
    }
    if (isset($foto['error'])) {
        absen_flash_error(array('foto' => $foto['error']), '/absen-digital/absen');
    }
    $ttd = absen_decode_ttd(isset($_POST['ttd']) ? $_POST['ttd'] : '');
    if (isset($ttd['error'])) {
        absen_unlink_rel($foto['path']);
        absen_flash_error(array('ttd' => $ttd['error']), '/absen-digital/absen');
    }
    try {
        $now = date('Y-m-d H:i:s');
        $ins = absen_db()->prepare('INSERT INTO absen_kehadirans (kegiatan_id, user_id, nik, nama, metode, foto_path, latitude, longitude, ttd_path, dicatat_oleh, created_at, updated_at) VALUES (?, ?, ?, ?, "form", ?, ?, ?, ?, ?, ?, ?)');
        $ins->execute(array($kegiatanId, $userId, $nik, $nama, $foto['path'], $lat, $lng, $ttd['path'], (int) $user['id'], $now, $now));
    } catch (Exception $e) {
        absen_unlink_rel($foto['path']);
        absen_unlink_rel($ttd['path']);
        absen_flash_error(array('kegiatan_id' => 'Absen gagal disimpan. Periksa apakah peserta sudah tercatat.'), '/absen-digital/absen');
    }
    absen_flash_success('Absen tersimpan untuk '.$nama.'.', '/absen-digital/absen');
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
        'title' => 'Cetak',
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
    $sql = 'SELECT h.*, k.nama AS kegiatan_nama, k.jenis, k.tanggal, k.waktu, k.tempat, k.kode
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
