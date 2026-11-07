<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('jam_ke')->unique();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00'],
            ['jam_ke' => 2, 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00'],
            ['jam_ke' => 3, 'jam_mulai' => '08:30:00', 'jam_selesai' => '09:15:00'],
            ['jam_ke' => 4, 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:00:00'],
            ['jam_ke' => 5, 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:00:00'],
            ['jam_ke' => 6, 'jam_mulai' => '11:00:00', 'jam_selesai' => '11:45:00'],
            ['jam_ke' => 7, 'jam_mulai' => '12:30:00', 'jam_selesai' => '13:15:00'],
            ['jam_ke' => 8, 'jam_mulai' => '13:15:00', 'jam_selesai' => '14:00:00'],
        ];

        foreach ($defaults as $row) {
            DB::table('jam_pelajarans')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_pelajarans');
    }
};
