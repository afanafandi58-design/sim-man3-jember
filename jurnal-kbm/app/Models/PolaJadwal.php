<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolaJadwal extends Model
{
    public const KODE_REG = 'REG';
    public const KODE_JUM = 'JUM';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $table = 'pola_jadwals';

    protected $fillable = [
        'kode',
        'nama',
        'status',
    ];

    public function jamPelajarans(): HasMany
    {
        return $this->hasMany(JamPelajaran::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function getLabelAttribute(): string
    {
        return $this->kode.' — '.$this->nama;
    }
}
