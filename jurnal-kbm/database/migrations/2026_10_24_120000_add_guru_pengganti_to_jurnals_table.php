<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jurnals', 'guru_pengganti')) {
            Schema::table('jurnals', function (Blueprint $table) {
                $table->string('guru_pengganti', 150)->nullable()->after('status_kbm');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jurnals', 'guru_pengganti')) {
            Schema::table('jurnals', function (Blueprint $table) {
                $table->dropColumn('guru_pengganti');
            });
        }
    }
};
