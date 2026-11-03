<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absen_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis', 32);
            $table->date('tanggal');
            $table->string('waktu', 8)->nullable();
            $table->string('tempat')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('kode', 32)->unique();
            $table->string('hasil_drive_url', 500)->nullable();
            $table->string('hasil_file_path')->nullable();
            $table->string('hasil_file_nama')->nullable();
            $table->text('hasil_catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'jenis']);
        });

        Schema::create('absen_kehadirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('absen_kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nik', 16)->nullable();
            $table->string('nama');
            $table->string('metode', 16);
            $table->string('foto_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('ttd_path')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kegiatan_id', 'user_id']);
            $table->unique(['kegiatan_id', 'nik']);
        });

        Schema::create('absen_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('absen_kegiatans')->cascadeOnDelete();
            $table->string('path');
            $table->string('nama_asli')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absen_fotos');
        Schema::dropIfExists('absen_kehadirans');
        Schema::dropIfExists('absen_kegiatans');
    }
};
