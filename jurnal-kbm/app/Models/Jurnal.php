<?php

namespace App\Models;

use App\Services\OperationalDateService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    /**
     * Status KBM
     */
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_BELUM = 'Belum Selesai';

    protected $fillable = [
        'jadwal_id',
        'tanggal',
        'materi',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'hadir',
        'izin',
        'sakit',
        'alpha',
        'catatan',
        'status_kbm',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function absensiSiswas()
    {
        return $this->hasMany(AbsensiSiswa::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(JurnalAttachment::class)->orderBy('created_at');
    }

    public function isLocked(): bool
    {
        $today = app(OperationalDateService::class)->today();

        return ! Carbon::parse($this->tanggal)->isSameDay($today);
    }

    public static function dokumentasiLabelForCount(int $count): string
    {
        return match (min($count, JurnalAttachment::MAX_PER_JURNAL)) {
            0 => '📷 Tidak Ada',
            1 => '📷 1 Foto',
            2 => '📷 2 Foto',
            default => '📷 3 Foto',
        };
    }

    public function dokumentasiLabel(): string
    {
        $count = $this->attachments_count
            ?? ($this->relationLoaded('attachments') ? $this->attachments->count() : $this->attachments()->count());

        return self::dokumentasiLabelForCount((int) $count);
    }

    public function jamKeLabel(): string
    {
        return app(\App\Services\JurnalJamSelectionService::class)->jamKeLabel($this);
    }

    public function jpCount(): int
    {
        return app(\App\Services\JurnalJamSelectionService::class)->jpCount($this);
    }

    public function effectiveJamMulai(): string
    {
        return app(\App\Services\JurnalJamSelectionService::class)->effectiveJamMulai($this);
    }

    public function effectiveJamSelesai(): string
    {
        return app(\App\Services\JurnalJamSelectionService::class)->effectiveJamSelesai($this);
    }
}