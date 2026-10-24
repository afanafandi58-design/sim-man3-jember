<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jam_pelajarans') || ! Schema::hasTable('pola_jadwals')) {
            return;
        }

        $jumId = DB::table('pola_jadwals')->where('kode', 'JUM')->value('id');

        if (! $jumId) {
            return;
        }

        if (DB::table('jam_pelajarans')->where('pola_jadwal_id', $jumId)->exists()) {
            return;
        }

        $now = now();
        $defaults = [
            ['jam_ke' => 0, 'nama_sesi' => 'Tadarus', 'jam_mulai' => '06:45:00', 'jam_selesai' => '07:00:00'],
            ['jam_ke' => 1, 'nama_sesi' => 'Jam Pelajaran 1', 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00'],
            ['jam_ke' => 2, 'nama_sesi' => 'Jam Pelajaran 2', 'jam_mulai' => '07:40:00', 'jam_selesai' => '08:20:00'],
            ['jam_ke' => 3, 'nama_sesi' => 'Jam Pelajaran 3', 'jam_mulai' => '08:20:00', 'jam_selesai' => '09:00:00'],
            ['jam_ke' => 4, 'nama_sesi' => 'Jam Pelajaran 4', 'jam_mulai' => '09:00:00', 'jam_selesai' => '09:40:00'],
            ['jam_ke' => 5, 'nama_sesi' => 'Jam Pelajaran 5', 'jam_mulai' => '09:55:00', 'jam_selesai' => '10:35:00'],
            ['jam_ke' => 6, 'nama_sesi' => 'Jam Pelajaran 6', 'jam_mulai' => '10:35:00', 'jam_selesai' => '11:15:00'],
        ];

        foreach ($defaults as $row) {
            if (DB::table('jam_pelajarans')->where('pola_jadwal_id', $jumId)->where('jam_ke', $row['jam_ke'])->exists()) {
                continue;
            }

            DB::table('jam_pelajarans')->insert([
                'pola_jadwal_id' => $jumId,
                'jam_ke' => $row['jam_ke'],
                'nama_sesi' => $row['nama_sesi'],
                'jam_mulai' => $row['jam_mulai'],
                'jam_selesai' => $row['jam_selesai'],
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Tidak menghapus data.
    }
};
