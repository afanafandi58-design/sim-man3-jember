<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('jurnals', function (Blueprint $table) {

        $table->id();

        // Relasi ke jadwal
        $table->foreignId('jadwal_id')
              ->constrained()
              ->onDelete('cascade');

        // Tanggal mengajar
        $table->date('tanggal');

        // Materi pembelajaran
        $table->text('materi');

        // Jam pelajaran
        $table->integer('jam_ke');

        // Kehadiran
        $table->integer('hadir')->default(0);
        $table->integer('izin')->default(0);
        $table->integer('sakit')->default(0);
        $table->integer('alpha')->default(0);

        // Catatan guru
        $table->text('catatan')->nullable();

        // Status KBM
        $table->enum('status_kbm', [
            'Selesai',
            'Belum Selesai',
            'Guru Pengganti',
            'Libur'
        ])->default('Selesai');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};