<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jam_pelajarans') && ! Schema::hasColumn('jam_pelajarans', 'nama_sesi')) {
            Schema::table('jam_pelajarans', function (Blueprint $table) {
                $table->string('nama_sesi', 100)->nullable()->after('jam_ke');
            });
        }
    }

    public function down(): void
    {
        // Tidak drop kolom agar data UAT tetap aman.
    }
};
