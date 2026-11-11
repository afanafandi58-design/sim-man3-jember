<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('kode_guru', 10)->nullable()->unique()->after('id');
            $table->string('nama_tampilan')->nullable()->after('nama');
            $table->string('username_suffix', 5)->nullable()->after('nama_tampilan');
            $table->unsignedSmallInteger('jumlah_jam_mengajar')->default(0)->after('mapel');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['kode_guru', 'nama_tampilan', 'username_suffix', 'jumlah_jam_mengajar']);
        });
    }
};
