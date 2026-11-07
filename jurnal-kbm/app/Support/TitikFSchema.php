<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TitikFSchema
{
    private static bool $checked = false;

    public static function ensure(): void
    {
        if (self::$checked) {
            return;
        }
        self::$checked = true;

        if (! Schema::hasTable('wali_kelas')) {
            Schema::create('wali_kelas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_id');
                $table->string('kelas', 50);
                $table->string('rombel', 50)->nullable();
                $table->string('tahun_pelajaran', 20)->nullable();
                $table->timestamps();
                $table->unique(['guru_id', 'kelas', 'rombel', 'tahun_pelajaran'], 'wali_kelas_unique');
            });
        }

        if (! Schema::hasTable('absensi_harians')) {
            Schema::create('absensi_harians', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->unsignedBigInteger('siswa_id');
                $table->string('status', 20)->default('Alpha');
                $table->string('keterangan', 255)->nullable();
                $table->string('sumber', 30)->default('online');
                $table->unsignedBigInteger('reported_by')->nullable();
                $table->string('bukti_path', 255)->nullable();
                $table->string('bukti_caption', 150)->nullable();
                $table->unsignedBigInteger('jurnal_id')->nullable();
                $table->timestamps();
                $table->unique(['tanggal', 'siswa_id'], 'absensi_harians_tanggal_siswa_unique');
                $table->index(['tanggal', 'status']);
            });
        }
    }
}
