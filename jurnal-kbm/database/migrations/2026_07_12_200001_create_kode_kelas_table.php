<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kelas')->unique();
            $table->string('kelas');
            $table->string('rombel');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_kelas');
    }
};
