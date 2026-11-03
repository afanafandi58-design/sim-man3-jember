<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Siswa
{
    public static function fields(): array
    {
        return [
            'nis_lokal', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'alamat', 'agama', 'status_keluarga', 'anak_ke', 'nomor_hp',
            'sekolah_asal', 'alamat_sekolah_asal', 'tanggal_terima', 'tingkat_awal',
            'nama_ayah', 'nama_ibu', 'pekerjaan_ayah', 'pekerjaan_ibu', 'alamat_orang_tua',
            'agama_ayah', 'agama_ibu',
            'nama_wali', 'pekerjaan_wali', 'alamat_wali', 'agama_wali', 'hp_wali',
            'nomor_ijazah', 'nilai_ujian_akhir',
            'mutasi_dari', 'mutasi_diterima_kelas', 'mutasi_tanggal', 'mutasi_surat_nomor',
            'keluar_tanggal', 'keluar_alasan', 'keluar_surat_nomor', 'keluar_keterangan',
        ];
    }

    public static function count(?string $q = null, ?string $tingkat = null): int
    {
        $pdo = Database::pdo();
        $sql = 'SELECT COUNT(*) FROM siswa WHERE 1=1';
        $params = [];
        if ($q !== null && $q !== '') {
            $sql .= ' AND (nama LIKE ? OR nis_lokal LIKE ? OR nisn LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($tingkat !== null && $tingkat !== '') {
            $sql .= ' AND tingkat_awal = ?';
            $params[] = $tingkat;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function search(?string $q = null, ?string $tingkat = null, int $limit = 50, int $offset = 0): array
    {
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM siswa WHERE 1=1';
        $params = [];
        if ($q !== null && $q !== '') {
            $sql .= ' AND (nama LIKE ? OR nis_lokal LIKE ? OR nisn LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($tingkat !== null && $tingkat !== '') {
            $sql .= ' AND tingkat_awal = ?';
            $params[] = $tingkat;
        }
        $sql .= ' ORDER BY nis_lokal ASC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function tingkatList(): array
    {
        $pdo = Database::pdo();
        $rows = $pdo->query("SELECT DISTINCT tingkat_awal FROM siswa WHERE tingkat_awal IS NOT NULL AND tingkat_awal != '' ORDER BY tingkat_awal")->fetchAll(PDO::FETCH_COLUMN);
        return $rows ? $rows : [];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM siswa WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    public static function findByNis(string $nis): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM siswa WHERE nis_lokal = ?');
        $stmt->execute([$nis]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    public static function create(array $data): int
    {
        $fields = self::fields();
        $cols = [];
        $placeholders = [];
        $values = [];
        foreach ($fields as $f) {
            $cols[] = $f;
            $placeholders[] = '?';
            $values[] = array_key_exists($f, $data) ? $data[$f] : null;
        }
        $now = date('Y-m-d H:i:s');
        $cols[] = 'created_at';
        $cols[] = 'updated_at';
        $placeholders[] = '?';
        $placeholders[] = '?';
        $values[] = $now;
        $values[] = $now;

        $sql = 'INSERT INTO siswa (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($values);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = self::fields();
        $sets = [];
        $values = [];
        foreach ($fields as $f) {
            if (!array_key_exists($f, $data)) {
                continue;
            }
            $sets[] = $f . ' = ?';
            $values[] = $data[$f];
        }
        $sets[] = 'updated_at = ?';
        $values[] = date('Y-m-d H:i:s');
        $values[] = $id;
        $sql = 'UPDATE siswa SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($values);
    }

    public static function upsertByNis(array $data): string
    {
        $nis = isset($data['nis_lokal']) ? trim((string) $data['nis_lokal']) : '';
        if ($nis === '') {
            throw new \InvalidArgumentException('NIS Lokal wajib diisi');
        }
        $existing = self::findByNis($nis);
        if ($existing) {
            self::update((int) $existing['id'], $data);
            return 'updated';
        }
        self::create($data);
        return 'created';
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM siswa WHERE id = ?');
        $stmt->execute([$id]);
    }
}
