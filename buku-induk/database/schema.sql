-- Skema Buku Induk MAN 3 Jember (MySQL / MariaDB)
-- Untuk SQLite, aplikasi menjalankan DDL setara di Database.php

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama VARCHAR(120) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sekolah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(160) NOT NULL,
    nsm VARCHAR(32) NOT NULL DEFAULT '',
    npsn VARCHAR(32) NOT NULL DEFAULT '',
    alamat TEXT,
    tahun_angkatan_default VARCHAR(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS siswa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nis_lokal VARCHAR(32) NOT NULL UNIQUE,
    nisn VARCHAR(20) DEFAULT NULL,
    nama VARCHAR(160) NOT NULL,
    jenis_kelamin VARCHAR(20) DEFAULT NULL,
    tempat_lahir VARCHAR(100) DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,
    alamat TEXT,
    agama VARCHAR(40) DEFAULT NULL,
    status_keluarga VARCHAR(60) DEFAULT NULL,
    anak_ke INT DEFAULT NULL,
    nomor_hp VARCHAR(40) DEFAULT NULL,
    sekolah_asal VARCHAR(160) DEFAULT NULL,
    alamat_sekolah_asal TEXT,
    tanggal_terima DATE DEFAULT NULL,
    tingkat_awal VARCHAR(40) DEFAULT NULL,
    nama_ayah VARCHAR(120) DEFAULT NULL,
    nama_ibu VARCHAR(120) DEFAULT NULL,
    pekerjaan_ayah VARCHAR(120) DEFAULT NULL,
    pekerjaan_ibu VARCHAR(120) DEFAULT NULL,
    alamat_orang_tua TEXT,
    agama_ayah VARCHAR(40) DEFAULT NULL,
    agama_ibu VARCHAR(40) DEFAULT NULL,
    nama_wali VARCHAR(120) DEFAULT NULL,
    pekerjaan_wali VARCHAR(120) DEFAULT NULL,
    alamat_wali TEXT,
    agama_wali VARCHAR(40) DEFAULT NULL,
    hp_wali VARCHAR(40) DEFAULT NULL,
    nomor_ijazah VARCHAR(80) DEFAULT NULL,
    nilai_ujian_akhir VARCHAR(40) DEFAULT NULL,
    mutasi_dari VARCHAR(160) DEFAULT NULL,
    mutasi_diterima_kelas VARCHAR(40) DEFAULT NULL,
    mutasi_tanggal DATE DEFAULT NULL,
    mutasi_surat_nomor VARCHAR(80) DEFAULT NULL,
    keluar_tanggal DATE DEFAULT NULL,
    keluar_alasan TEXT,
    keluar_surat_nomor VARCHAR(80) DEFAULT NULL,
    keluar_keterangan TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_siswa_nama (nama),
    INDEX idx_siswa_nisn (nisn),
    INDEX idx_siswa_tingkat (tingkat_awal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS import_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    filename VARCHAR(255) NOT NULL,
    total INT NOT NULL DEFAULT 0,
    sukses INT NOT NULL DEFAULT 0,
    gagal INT NOT NULL DEFAULT 0,
    detail_json MEDIUMTEXT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
