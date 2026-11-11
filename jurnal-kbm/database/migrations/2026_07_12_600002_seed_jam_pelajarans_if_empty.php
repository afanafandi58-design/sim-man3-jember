<?php

use Database\Seeders\JamPelajaranSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jam_pelajarans')) {
            return;
        }

        (new JamPelajaranSeeder)->run();

        // Tambah Jam Ke 0 secara additive jika belum ada (tidak mengubah data valid).
        if (! DB::table('jam_pelajarans')->where('jam_ke', 0)->exists()) {
            $now = now();
            DB::table('jam_pelajarans')->insert([
                'jam_ke' => 0,
                'nama_sesi' => 'Pembiasaan',
                'jam_mulai' => '06:30:00',
                'jam_selesai' => '07:00:00',
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
