<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JamPelajaranSeeder extends Seeder
{
    /**
     * Seed Master Jam Pelajaran hanya jika tabel kosong.
     * Tidak menimpa data yang sudah ada (aman UAT).
     */
    public function run(): void
    {
        if (! Schema::hasTable('jam_pelajarans')) {
            return;
        }

        if (DB::table('jam_pelajarans')->count() > 0) {
            return;
        }

        $now = now();
        $defaults = [
            ['jam_ke' => 0, 'nama_sesi' => 'Pembiasaan', 'jam_mulai' => '06:30:00', 'jam_selesai' => '07:00:00', 'status' => 'aktif'],
            ['jam_ke' => 1, 'nama_sesi' => 'Jam Pelajaran 1', 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'status' => 'aktif'],
            ['jam_ke' => 2, 'nama_sesi' => 'Jam Pelajaran 2', 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00', 'status' => 'aktif'],
            ['jam_ke' => 3, 'nama_sesi' => 'Jam Pelajaran 3', 'jam_mulai' => '08:30:00', 'jam_selesai' => '09:15:00', 'status' => 'aktif'],
            ['jam_ke' => 4, 'nama_sesi' => 'Jam Pelajaran 4', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:00:00', 'status' => 'aktif'],
            ['jam_ke' => 5, 'nama_sesi' => 'Jam Pelajaran 5', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:00:00', 'status' => 'aktif'],
            ['jam_ke' => 6, 'nama_sesi' => 'Jam Pelajaran 6', 'jam_mulai' => '11:00:00', 'jam_selesai' => '11:45:00', 'status' => 'aktif'],
            ['jam_ke' => 7, 'nama_sesi' => 'Jam Pelajaran 7', 'jam_mulai' => '12:30:00', 'jam_selesai' => '13:15:00', 'status' => 'aktif'],
            ['jam_ke' => 8, 'nama_sesi' => 'Jam Pelajaran 8', 'jam_mulai' => '13:15:00', 'jam_selesai' => '14:00:00', 'status' => 'aktif'],
            ['jam_ke' => 9, 'nama_sesi' => 'Jam Pelajaran 9', 'jam_mulai' => '14:00:00', 'jam_selesai' => '14:45:00', 'status' => 'aktif'],
            ['jam_ke' => 10, 'nama_sesi' => 'Jam Pelajaran 10', 'jam_mulai' => '14:45:00', 'jam_selesai' => '15:30:00', 'status' => 'aktif'],
        ];

        foreach ($defaults as $row) {
            DB::table('jam_pelajarans')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
