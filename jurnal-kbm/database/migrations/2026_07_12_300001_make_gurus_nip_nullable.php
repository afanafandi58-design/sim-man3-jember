<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE gurus MODIFY nip VARCHAR(255) NULL');

        DB::table('gurus')
            ->where('nip', '')
            ->update(['nip' => null]);
    }

    public function down(): void
    {
        DB::table('gurus')
            ->whereNull('nip')
            ->limit(1)
            ->update(['nip' => '']);

        DB::statement('ALTER TABLE gurus MODIFY nip VARCHAR(255) NOT NULL');
    }
};
