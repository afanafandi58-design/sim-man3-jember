<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class Sekolah
{
    public static function get(): array
    {
        $row = Database::pdo()->query('SELECT * FROM sekolah ORDER BY id ASC LIMIT 1')->fetch();
        return $row ? $row : [
            'nama' => 'MAN 3 JEMBER',
            'nsm' => '',
            'npsn' => '',
            'alamat' => '',
            'tahun_angkatan_default' => '',
        ];
    }

    public static function save(array $data): void
    {
        $pdo = Database::pdo();
        $existing = $pdo->query('SELECT id FROM sekolah ORDER BY id ASC LIMIT 1')->fetch();
        if ($existing) {
            $stmt = $pdo->prepare('UPDATE sekolah SET nama=?, nsm=?, npsn=?, alamat=?, tahun_angkatan_default=? WHERE id=?');
            $stmt->execute([
                $data['nama'],
                $data['nsm'],
                $data['npsn'],
                $data['alamat'],
                $data['tahun_angkatan_default'],
                $existing['id'],
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO sekolah (nama, nsm, npsn, alamat, tahun_angkatan_default) VALUES (?,?,?,?,?)');
            $stmt->execute([
                $data['nama'],
                $data['nsm'],
                $data['npsn'],
                $data['alamat'],
                $data['tahun_angkatan_default'],
            ]);
        }
    }
}
