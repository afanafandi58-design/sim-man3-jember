<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda slot moving class + daftar siswa gabungan dari rombel existing.
 * Tidak mengganti / menghapus data siswa atau rombel.
 */
class JadwalMovingSchema
{
    private static bool $checked = false;

    public static function ensure(): void
    {
        if (self::$checked) {
            return;
        }

        self::$checked = true;

        if (! Schema::hasTable('jadwals')) {
            return;
        }

        if (! Schema::hasColumn('jadwals', 'is_moving_class')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->boolean('is_moving_class')->default(false);
            });
        }

        if (! Schema::hasColumn('jadwals', 'moving_kelas_nama')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->string('moving_kelas_nama', 150)->nullable();
            });
        }

        if (! Schema::hasTable('jadwal_moving_siswas')) {
            Schema::create('jadwal_moving_siswas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_id');
                $table->unsignedBigInteger('siswa_id');
                $table->timestamps();
                $table->unique(['jadwal_id', 'siswa_id']);
                $table->index('jadwal_id');
                $table->index('siswa_id');
            });
        }
    }
}
