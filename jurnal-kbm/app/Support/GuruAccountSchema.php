<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom teks password awal akun guru untuk cetak admin (bukan hash login).
 */
class GuruAccountSchema
{
    public static function ensure(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'password_awal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password_awal', 64)->nullable()->after('password');
            });
        }
    }
}
