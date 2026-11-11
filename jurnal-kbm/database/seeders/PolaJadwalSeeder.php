<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PolaJadwalSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('pola_jadwals')) {
            return;
        }

        if (DB::table('pola_jadwals')->count() > 0) {
            return;
        }

        $now = now();
        $defaults = [
            ['kode' => 'REG', 'nama' => 'Reguler Senin–Kamis', 'status' => 'aktif'],
            ['kode' => 'JUM', 'nama' => 'Jumat', 'status' => 'aktif'],
            ['kode' => 'RAM', 'nama' => 'Ramadhan', 'status' => 'aktif'],
            ['kode' => 'UJN', 'nama' => 'Ujian', 'status' => 'aktif'],
            ['kode' => 'KSM', 'nama' => 'Kegiatan Khusus', 'status' => 'aktif'],
        ];

        foreach ($defaults as $row) {
            DB::table('pola_jadwals')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
