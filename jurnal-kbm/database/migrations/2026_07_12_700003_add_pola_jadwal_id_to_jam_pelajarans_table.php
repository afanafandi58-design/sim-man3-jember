<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jam_pelajarans') || ! Schema::hasTable('pola_jadwals')) {
            return;
        }

        if (! Schema::hasColumn('jam_pelajarans', 'pola_jadwal_id')) {
            Schema::table('jam_pelajarans', function (Blueprint $table) {
                $table->foreignId('pola_jadwal_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('pola_jadwals')
                    ->nullOnDelete();
            });
        }

        $regId = DB::table('pola_jadwals')->where('kode', 'REG')->value('id');

        if ($regId) {
            DB::table('jam_pelajarans')
                ->whereNull('pola_jadwal_id')
                ->update(['pola_jadwal_id' => $regId]);
        }

        $this->dropJamKeUniqueIfExists();

        if (! $this->compositeUniqueExists()) {
            Schema::table('jam_pelajarans', function (Blueprint $table) {
                $table->unique(['pola_jadwal_id', 'jam_ke'], 'jam_pelajarans_pola_jam_ke_unique');
            });
        }
    }

    public function down(): void
    {
        // Tidak menghapus kolom agar data UAT tetap aman.
    }

    private function dropJamKeUniqueIfExists(): void
    {
        try {
            Schema::table('jam_pelajarans', function (Blueprint $table) {
                $table->dropUnique(['jam_ke']);
            });
        } catch (\Throwable) {
            // Index sudah tidak ada.
        }
    }

    private function compositeUniqueExists(): bool
    {
        $indexes = DB::select("SHOW INDEX FROM jam_pelajarans WHERE Key_name = 'jam_pelajarans_pola_jam_ke_unique'");

        return count($indexes) > 0;
    }
};
