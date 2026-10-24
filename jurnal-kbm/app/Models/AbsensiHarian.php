<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AbsensiHarian extends Model
{
    public const STATUS_HADIR = 'Hadir';

    public const STATUS_SAKIT = 'Sakit';

    public const STATUS_IZIN = 'Izin';

    public const STATUS_ALPHA = 'Alpha';

    public const STATUSES = [
        self::STATUS_HADIR,
        self::STATUS_SAKIT,
        self::STATUS_IZIN,
        self::STATUS_ALPHA,
    ];

    public const TIDAK_MASUK = [
        self::STATUS_SAKIT,
        self::STATUS_IZIN,
        self::STATUS_ALPHA,
    ];

    protected $fillable = [
        'tanggal',
        'siswa_id',
        'status',
        'keterangan',
        'sumber',
        'reported_by',
        'bukti_path',
        'bukti_caption',
        'jurnal_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function hasBukti(): bool
    {
        return filled($this->bukti_path);
    }

    public function buktiUrl(): ?string
    {
        if (! $this->hasBukti()) {
            return null;
        }

        return Storage::disk('public')->url($this->bukti_path);
    }

    public function isTidakMasuk(): bool
    {
        return in_array($this->status, self::TIDAK_MASUK, true);
    }
}
