<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-archive jadwal aktif guru tanpa menghapus baris / jurnal terkait.
 */
class JadwalArchiveSchema
{
    public static function ensure(): void
    {
        if (Schema::hasTable('jadwals') && ! Schema::hasColumn('jadwals', 'archived_at')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('is_locked');
            });
        }

        if (! Schema::hasTable('jadwal_resets')) {
            Schema::create('jadwal_resets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tahun_pelajaran', 20)->nullable();
                $table->string('semester', 20)->nullable();
                $table->unsignedInteger('slot_count')->default(0);
                $table->json('jadwal_ids')->nullable();
                $table->string('catatan', 255)->nullable();
                $table->timestamps();
            });
        }
    }
}
