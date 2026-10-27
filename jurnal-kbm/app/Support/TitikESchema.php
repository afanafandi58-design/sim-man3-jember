<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures titik-E columns exist when artisan migrate is unavailable on hosting.
 */
class TitikESchema
{
    private static bool $checked = false;

    public static function ensure(): void
    {
        if (self::$checked) {
            return;
        }

        self::$checked = true;

        if (! Schema::hasTable('jurnals')) {
            return;
        }

        if (! Schema::hasColumn('jurnals', 'guru_pengganti')) {
            Schema::table('jurnals', function (Blueprint $table) {
                $table->string('guru_pengganti', 150)->nullable()->after('status_kbm');
            });
        }
    }
}
