<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE siswas MODIFY status ENUM(
            'aktif',
            'mutasi_masuk',
            'mutasi_keluar',
            'lulus',
            'keluar',
            'pindah',
            'nonaktif'
        ) NOT NULL DEFAULT 'aktif'");
    }

    public function down(): void
    {
        DB::statement("UPDATE siswas SET status = 'pindah' WHERE status NOT IN ('aktif', 'lulus', 'pindah')");

        DB::statement("ALTER TABLE siswas MODIFY status ENUM(
            'aktif',
            'lulus',
            'pindah'
        ) NOT NULL DEFAULT 'aktif'");
    }
};
