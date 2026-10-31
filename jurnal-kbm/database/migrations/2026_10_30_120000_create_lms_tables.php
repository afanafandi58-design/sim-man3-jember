<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lms_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->string('kelas');
            $table->string('mapel');
            $table->string('tahun_pelajaran')->nullable();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('drive_url', 2048);
            $table->boolean('is_published')->default(true);
            $table->string('share_token', 64)->unique();
            $table->timestamps();

            $table->index(['guru_id', 'kelas', 'mapel']);
        });

        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->string('kelas');
            $table->string('mapel');
            $table->string('tahun_pelajaran')->nullable();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('drive_url', 2048)->nullable();
            $table->dateTime('due_at')->nullable();
            $table->unsignedSmallInteger('max_score')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('share_token', 64)->unique();
            $table->timestamps();

            $table->index(['guru_id', 'kelas', 'mapel']);
        });

        Schema::create('lms_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('drive_url', 2048);
            $table->text('catatan')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['lms_assignment_id', 'siswa_id']);
        });

        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->string('kelas');
            $table->string('mapel');
            $table->string('tahun_pelajaran')->nullable();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('drive_url', 2048);
            $table->dateTime('due_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('share_token', 64)->unique();
            $table->timestamps();

            $table->index(['guru_id', 'kelas', 'mapel']);
        });

        Schema::create('lms_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->string('scope')->default('all');
            $table->string('kelas')->nullable();
            $table->string('judul');
            $table->text('isi');
            $table->string('drive_url', 2048)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['scope', 'kelas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_announcements');
        Schema::dropIfExists('lms_quizzes');
        Schema::dropIfExists('lms_submissions');
        Schema::dropIfExists('lms_assignments');
        Schema::dropIfExists('lms_materials');
        Schema::dropIfExists('lms_settings');
    }
};
