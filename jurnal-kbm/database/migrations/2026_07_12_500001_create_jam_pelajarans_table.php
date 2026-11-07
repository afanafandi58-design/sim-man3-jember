<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration aman UAT — idempotent.
 *
 * Penyebab error SQLSTATE[42S02]:
 * Tabel jam_pelajarans direferensikan Model/Service Sprint 11.2,
 * tetapi migration sebelumnya belum dijalankan di database UAT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jam_pelajarans')) {
            Schema::create('jam_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('jam_ke')->unique();
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('jam_pelajarans', 'status')) {
            Schema::table('jam_pelajarans', function (Blueprint $table) {
                $table->string('status')->default('aktif')->after('jam_selesai');
            });

            DB::table('jam_pelajarans')->update(['status' => 'aktif']);
        }

        if (DB::table('jam_pelajarans')->count() === 0) {
            $now = now();
            $defaults = [
                ['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'status' => 'aktif'],
                ['jam_ke' => 2, 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00', 'status' => 'aktif'],
                ['jam_ke' => 3, 'jam_mulai' => '08:30:00', 'jam_selesai' => '09:15:00', 'status' => 'aktif'],
                ['jam_ke' => 4, 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:00:00', 'status' => 'aktif'],
                ['jam_ke' => 5, 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:00:00', 'status' => 'aktif'],
                ['jam_ke' => 6, 'jam_mulai' => '11:00:00', 'jam_selesai' => '11:45:00', 'status' => 'aktif'],
                ['jam_ke' => 7, 'jam_mulai' => '12:30:00', 'jam_selesai' => '13:15:00', 'status' => 'aktif'],
                ['jam_ke' => 8, 'jam_mulai' => '13:15:00', 'jam_selesai' => '14:00:00', 'status' => 'aktif'],
            ];

            foreach ($defaults as $row) {
                DB::table('jam_pelajarans')->insert([
                    ...$row,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak drop tabel agar data UAT tetap aman.
    }
};
