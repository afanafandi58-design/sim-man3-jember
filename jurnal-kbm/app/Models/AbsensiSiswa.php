<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiSiswa extends Model
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

    protected $fillable = [
        'jurnal_id',
        'jadwal_id',
        'guru_id',
        'siswa_id',
        'status',
        'keterangan',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
