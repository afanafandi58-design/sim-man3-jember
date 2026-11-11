<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'kelas',
        'mapel',
        'hari',
        'pola_jadwal_id',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'tahun_pelajaran',
        'semester',
        'is_locked',
        'is_moving_class',
        'moving_kelas_nama',
        'archived_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_moving_class' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isMovingClass(): bool
    {
        return (bool) $this->is_moving_class;
    }

    /**
     * Relasi ke Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function movingSiswaLinks()
    {
        return $this->hasMany(JadwalMovingSiswa::class);
    }

    public function movingSiswas()
    {
        return $this->belongsToMany(Siswa::class, 'jadwal_moving_siswas')
            ->withTimestamps();
    }

    public function polaJadwal()
    {
        return $this->belongsTo(PolaJadwal::class);
    }

    /**
     * Relasi ke Jurnal
     */
    public function jurnals()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function perbaikanRequests()
    {
        return $this->hasMany(JadwalPerbaikanRequest::class);
    }

    public function pendingPerbaikanRequest()
    {
        return $this->hasOne(JadwalPerbaikanRequest::class)
            ->where('status', JadwalPerbaikanRequest::STATUS_PENDING)
            ->latestOfMany();
    }

    public function jamRangeLabel(): string
    {
        return app(\App\Services\JurnalJamSelectionService::class)->jamRangeLabelForJadwal($this);
    }
}