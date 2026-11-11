<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'IPA',
            'IPS',
            'PKN',
            'Sen Budaya',
            'PJOK',
            'Informatika',
            'Fisika',
            'Kimia',
            'Biologi',
            'Ekonomi',
            'Sejarah',
            'Geografi',
            'Sosiologi',
        ];

        $now = now();

        foreach ($defaults as $nama) {
            if (! DB::table('mata_pelajarans')->where('nama', $nama)->exists()) {
                DB::table('mata_pelajarans')->insert([
                    'nama' => $nama,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Data seed — tidak dihapus agar backward compatible.
    }
};
