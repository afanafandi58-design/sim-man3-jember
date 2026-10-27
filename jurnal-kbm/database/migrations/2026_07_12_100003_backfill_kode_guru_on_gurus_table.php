<?php

use App\Models\Guru;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $maxNumber = Guru::query()
            ->whereNotNull('kode_guru')
            ->pluck('kode_guru')
            ->map(fn (string $kode) => (int) preg_replace('/\D/', '', $kode))
            ->max() ?? 0;

        Guru::query()
            ->whereNull('kode_guru')
            ->orderBy('id')
            ->each(function (Guru $guru) use (&$maxNumber) {
                $maxNumber++;
                $guru->update([
                    'kode_guru' => 'G'.str_pad((string) $maxNumber, 3, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        //
    }
};
