<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kelas');
            $table->string('rombel');
            $table->string('kode_kelas');
            $table->string('tahun_pelajaran');
            $table->enum('status', ['aktif', 'lulus', 'pindah'])->default('aktif');
            $table->timestamps();

            $table->index(['kelas', 'rombel', 'kode_kelas']);
            $table->index('status');
            $table->index('tahun_pelajaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
