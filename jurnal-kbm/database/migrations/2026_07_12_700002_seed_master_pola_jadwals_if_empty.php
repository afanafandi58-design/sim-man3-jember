<?php

use Database\Seeders\PolaJadwalSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new PolaJadwalSeeder)->run();
    }

    public function down(): void
    {
        // Tidak menghapus data.
    }
};
