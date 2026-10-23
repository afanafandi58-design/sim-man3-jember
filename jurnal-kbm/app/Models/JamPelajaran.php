<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamPelajaran extends Model
{
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $table = 'jam_pelajarans';

    protected $fillable = [
        'pola_jadwal_id',
        'jam_ke',
        'nama_sesi',
        'jam_mulai',
        'jam_selesai',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_AKTIF,
    ];

    public function polaJadwal(): BelongsTo
    {
        return $this->belongsTo(PolaJadwal::class);
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function getLabelAttribute(): string
    {
        $label = 'Jam Ke '.$this->jam_ke;

        if ($this->nama_sesi) {
            $label .= ' — '.$this->nama_sesi;
        }

        return $label;
    }
}
