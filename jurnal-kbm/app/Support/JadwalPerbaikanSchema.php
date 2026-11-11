<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan kolom jejak perbaikan (snapshot lama/baru) ada di production.
 */
class JadwalPerbaikanSchema
{
    public static function ensure(): void
    {
        if (! Schema::hasTable('jadwal_perbaikan_requests')) {
            return;
        }

        Schema::table('jadwal_perbaikan_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwal_perbaikan_requests', 'old_values')) {
                $table->json('old_values')->nullable()->after('alasan');
            }
            if (! Schema::hasColumn('jadwal_perbaikan_requests', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
        });
    }
}
