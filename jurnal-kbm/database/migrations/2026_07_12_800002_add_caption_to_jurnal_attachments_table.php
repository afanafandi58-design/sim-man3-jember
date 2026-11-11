<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jurnal_attachments')) {
            return;
        }

        Schema::table('jurnal_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('jurnal_attachments', 'caption')) {
                $table->string('caption', 100)->nullable()->after('nama_file');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('jurnal_attachments')) {
            return;
        }

        Schema::table('jurnal_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('jurnal_attachments', 'caption')) {
                $table->dropColumn('caption');
            }
        });
    }
};
