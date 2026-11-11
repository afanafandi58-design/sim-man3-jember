<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_perbaikan_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwal_perbaikan_requests', 'old_values')) {
                $table->json('old_values')->nullable()->after('alasan');
            }
            if (! Schema::hasColumn('jadwal_perbaikan_requests', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_perbaikan_requests', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_perbaikan_requests', 'new_values')) {
                $table->dropColumn('new_values');
            }
            if (Schema::hasColumn('jadwal_perbaikan_requests', 'old_values')) {
                $table->dropColumn('old_values');
            }
        });
    }
};
