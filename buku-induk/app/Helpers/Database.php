<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    /** @var PDO|null */
    private static $pdo = null;

    /** @var array */
    private static $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = isset(self::$config['db_driver']) ? self::$config['db_driver'] : 'sqlite';

        try {
            if ($driver === 'mysql') {
                $m = self::$config['mysql'];
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $m['host'],
                    (int) $m['port'],
                    $m['database'],
                    isset($m['charset']) ? $m['charset'] : 'utf8mb4'
                );
                self::$pdo = new PDO($dsn, $m['username'], $m['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } else {
                $path = isset(self::$config['sqlite_path'])
                    ? self::$config['sqlite_path']
                    : dirname(__DIR__, 2) . '/storage/db/buku_induk.sqlite';
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                self::$pdo = new PDO('sqlite:' . $path, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$pdo->exec('PRAGMA foreign_keys = ON');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo 'Gagal koneksi database. Periksa konfigurasi.';
            exit;
        }

        return self::$pdo;
    }

    public static function driver(): string
    {
        return isset(self::$config['db_driver']) ? (string) self::$config['db_driver'] : 'sqlite';
    }

    public static function migrate(): void
    {
        $pdo = self::pdo();
        $isMysql = self::driver() === 'mysql';

        if ($isMysql) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(64) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                nama VARCHAR(120) NOT NULL DEFAULT '',
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS sekolah (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(160) NOT NULL,
                nsm VARCHAR(32) NOT NULL DEFAULT '',
                npsn VARCHAR(32) NOT NULL DEFAULT '',
                alamat TEXT,
                tahun_angkatan_default VARCHAR(16) NOT NULL DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
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
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS import_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED DEFAULT NULL,
                filename VARCHAR(255) NOT NULL,
                total INT NOT NULL DEFAULT 0,
                sukses INT NOT NULL DEFAULT 0,
                gagal INT NOT NULL DEFAULT 0,
                detail_json MEDIUMTEXT,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                nama TEXT NOT NULL DEFAULT '',
                created_at TEXT NOT NULL
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS sekolah (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama TEXT NOT NULL,
                nsm TEXT NOT NULL DEFAULT '',
                npsn TEXT NOT NULL DEFAULT '',
                alamat TEXT,
                tahun_angkatan_default TEXT NOT NULL DEFAULT ''
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nis_lokal TEXT NOT NULL UNIQUE,
                nisn TEXT,
                nama TEXT NOT NULL,
                jenis_kelamin TEXT,
                tempat_lahir TEXT,
                tanggal_lahir TEXT,
                alamat TEXT,
                agama TEXT,
                status_keluarga TEXT,
                anak_ke INTEGER,
                nomor_hp TEXT,
                sekolah_asal TEXT,
                alamat_sekolah_asal TEXT,
                tanggal_terima TEXT,
                tingkat_awal TEXT,
                nama_ayah TEXT,
                nama_ibu TEXT,
                pekerjaan_ayah TEXT,
                pekerjaan_ibu TEXT,
                alamat_orang_tua TEXT,
                agama_ayah TEXT,
                agama_ibu TEXT,
                nama_wali TEXT,
                pekerjaan_wali TEXT,
                alamat_wali TEXT,
                agama_wali TEXT,
                hp_wali TEXT,
                nomor_ijazah TEXT,
                nilai_ujian_akhir TEXT,
                mutasi_dari TEXT,
                mutasi_diterima_kelas TEXT,
                mutasi_tanggal TEXT,
                mutasi_surat_nomor TEXT,
                keluar_tanggal TEXT,
                keluar_alasan TEXT,
                keluar_surat_nomor TEXT,
                keluar_keterangan TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS import_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                filename TEXT NOT NULL,
                total INTEGER NOT NULL DEFAULT 0,
                sukses INTEGER NOT NULL DEFAULT 0,
                gagal INTEGER NOT NULL DEFAULT 0,
                detail_json TEXT,
                created_at TEXT NOT NULL
            )");
        }

        self::seedDefaults();
    }

    private static function seedDefaults(): void
    {
        $pdo = self::pdo();
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $seed = isset(self::$config['seed_admin']) ? self::$config['seed_admin'] : [];
            $username = isset($seed['username']) ? $seed['username'] : 'admin';
            $password = isset($seed['password']) ? $seed['password'] : 'admin123';
            $nama = isset($seed['nama']) ? $seed['nama'] : 'Administrator';
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, nama, created_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $nama,
                date('Y-m-d H:i:s'),
            ]);
        }

        $countSekolah = (int) $pdo->query('SELECT COUNT(*) FROM sekolah')->fetchColumn();
        if ($countSekolah === 0) {
            $stmt = $pdo->prepare('INSERT INTO sekolah (nama, nsm, npsn, alamat, tahun_angkatan_default) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                'MAN 3 JEMBER',
                '131135090003',
                '20580293',
                'Jl. Jendral A. Yani no.76 Kec. Jombang Kab. Jember',
                '2025',
            ]);
        }
    }
}
