<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jadwals')) {
            Schema::table('jadwals', function (Blueprint $table) {
                if (! Schema::hasColumn('jadwals', 'is_moving_class')) {
                    $table->boolean('is_moving_class')->default(false)->after('is_locked');
                }
                if (! Schema::hasColumn('jadwals', 'moving_kelas_nama')) {
                    $table->string('moving_kelas_nama', 150)->nullable()->after('is_moving_class');
                }
            });
        }

        if (! Schema::hasTable('jadwal_moving_siswas')) {
            Schema::create('jadwal_moving_siswas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['jadwal_id', 'siswa_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_moving_siswas');

        if (Schema::hasTable('jadwals')) {
            Schema::table('jadwals', function (Blueprint $table) {
                if (Schema::hasColumn('jadwals', 'moving_kelas_nama')) {
                    $table->dropColumn('moving_kelas_nama');
                }
                if (Schema::hasColumn('jadwals', 'is_moving_class')) {
                    $table->dropColumn('is_moving_class');
                }
            });
        }
    }
};
