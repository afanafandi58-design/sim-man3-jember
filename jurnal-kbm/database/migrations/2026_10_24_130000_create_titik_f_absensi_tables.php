<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wali_kelas')) {
            Schema::create('wali_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
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
                $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
                $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha'])->default('Alpha');
                $table->string('keterangan', 255)->nullable();
                $table->string('sumber', 30)->default('online'); // online|manual|jurnal
                $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('bukti_path', 255)->nullable();
                $table->string('bukti_caption', 150)->nullable();
                $table->foreignId('jurnal_id')->nullable()->constrained('jurnals')->nullOnDelete();
                $table->timestamps();
                $table->unique(['tanggal', 'siswa_id'], 'absensi_harians_tanggal_siswa_unique');
                $table->index(['tanggal', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
        Schema::dropIfExists('wali_kelas');
    }
};
