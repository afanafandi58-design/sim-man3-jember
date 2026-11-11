<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jadwals') || ! Schema::hasTable('pola_jadwals')) {
            return;
        }

        if (! Schema::hasColumn('jadwals', 'pola_jadwal_id')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->foreignId('pola_jadwal_id')
                    ->nullable()
                    ->after('hari')
                    ->constrained('pola_jadwals')
                    ->nullOnDelete();
            });
        }

        $polaIds = DB::table('pola_jadwals')->pluck('id', 'kode');

        if ($polaIds->isEmpty()) {
            return;
        }

        $regId = $polaIds->get('REG');
        $jumId = $polaIds->get('JUM');

        if ($jumId) {
            DB::table('jadwals')
                ->whereNull('pola_jadwal_id')
                ->where('hari', 'Jumat')
                ->update(['pola_jadwal_id' => $jumId]);
        }

        if ($regId) {
            DB::table('jadwals')
                ->whereNull('pola_jadwal_id')
                ->update(['pola_jadwal_id' => $regId]);
        }
    }

    public function down(): void
    {
        // Tidak menghapus kolom agar data UAT tetap aman.
    }
};
