-- Skema CBT MAN 3 Jember untuk MariaDB/MySQL (XAMPP, PHP 8).
-- Tidak memuat akun, token, atau data siswa.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS pengawas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  hash_sandi VARCHAR(255) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_pengawas_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siswa (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  hash_sandi VARCHAR(255) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  nis VARCHAR(32) NOT NULL DEFAULT '',
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_siswa_username (username),
  KEY idx_siswa_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mapel (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode VARCHAR(32) NOT NULL,
  nama VARCHAR(160) NOT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_mapel_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sesi (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  mapel_id INT UNSIGNED NOT NULL,
  nama VARCHAR(160) NOT NULL,
  durasi_menit SMALLINT UNSIGNED NOT NULL,
  status ENUM('draf','berjalan','selesai') NOT NULL DEFAULT 'draf',
  token VARCHAR(16) DEFAULT NULL,
  dimulai_pada DATETIME DEFAULT NULL,
  selesai_pada DATETIME DEFAULT NULL,
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_sesi_token (token),
  KEY idx_sesi_mapel (mapel_id, status),
  CONSTRAINT fk_sesi_mapel FOREIGN KEY (mapel_id) REFERENCES mapel (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS soal (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sesi_id INT UNSIGNED NOT NULL,
  nomor SMALLINT UNSIGNED NOT NULL,
  tipe ENUM('pg','pgk','menjodohkan') NOT NULL,
  pertanyaan TEXT NOT NULL,
  audio VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_soal_nomor (sesi_id, nomor),
  KEY idx_soal_sesi (sesi_id, nomor),
  CONSTRAINT fk_soal_sesi FOREIGN KEY (sesi_id) REFERENCES sesi (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pilihan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  soal_id INT UNSIGNED NOT NULL,
  kode VARCHAR(8) NOT NULL,
  teks TEXT NOT NULL,
  benar TINYINT(1) NOT NULL DEFAULT 0,
  urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pilihan_soal (soal_id, urutan),
  CONSTRAINT fk_pilihan_soal FOREIGN KEY (soal_id) REFERENCES soal (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pasangan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  soal_id INT UNSIGNED NOT NULL,
  kiri_kode VARCHAR(8) NOT NULL,
  kiri_teks TEXT NOT NULL,
  kanan_kode VARCHAR(8) NOT NULL,
  kanan_teks TEXT NOT NULL,
  urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pasangan_soal (soal_id, urutan),
  CONSTRAINT fk_pasangan_soal FOREIGN KEY (soal_id) REFERENCES soal (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pengerjaan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  siswa_id INT UNSIGNED NOT NULL,
  sesi_id INT UNSIGNED NOT NULL,
  status ENUM('berjalan','jeda','selesai') NOT NULL DEFAULT 'berjalan',
  sisa_detik INT UNSIGNED NOT NULL,
  checkpoint_pada DATETIME NOT NULL,
  terakhir_ping DATETIME NOT NULL,
  skor DECIMAL(8,2) NOT NULL DEFAULT 0,
  jumlah_benar SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  jumlah_salah SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  jumlah_kosong SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  selesai_pada DATETIME DEFAULT NULL,
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_siswa_sesi (siswa_id, sesi_id),
  KEY idx_pengerjaan_sesi (sesi_id, status),
  CONSTRAINT fk_pengerjaan_siswa FOREIGN KEY (siswa_id) REFERENCES siswa (id),
  CONSTRAINT fk_pengerjaan_sesi FOREIGN KEY (sesi_id) REFERENCES sesi (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jawaban (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pengerjaan_id INT UNSIGNED NOT NULL,
  soal_id INT UNSIGNED NOT NULL,
  isi JSON NOT NULL,
  ragu TINYINT(1) NOT NULL DEFAULT 0,
  diubah_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_jawaban (pengerjaan_id, soal_id),
  KEY idx_jawaban_soal (soal_id),
  CONSTRAINT fk_jawaban_pengerjaan FOREIGN KEY (pengerjaan_id) REFERENCES pengerjaan (id) ON DELETE CASCADE,
  CONSTRAINT fk_jawaban_soal FOREIGN KEY (soal_id) REFERENCES soal (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
