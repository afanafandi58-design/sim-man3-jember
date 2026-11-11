<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    public const STATUS_AKTIF = 'aktif';

    public const STATUS_MUTASI_MASUK = 'mutasi_masuk';

    public const STATUS_MUTASI_KELUAR = 'mutasi_keluar';

    public const STATUS_LULUS = 'lulus';

    public const STATUS_KELUAR = 'keluar';

    /** @deprecated Legacy import value — tetap didukung */
    public const STATUS_PINDAH = 'pindah';

    public const STATUS_NONAKTIF = 'nonaktif';

    public const STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_MUTASI_MASUK,
        self::STATUS_MUTASI_KELUAR,
        self::STATUS_LULUS,
        self::STATUS_KELUAR,
        self::STATUS_PINDAH,
        self::STATUS_NONAKTIF,
    ];

    public const STATUS_LABELS = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_MUTASI_MASUK => 'Mutasi Masuk',
        self::STATUS_MUTASI_KELUAR => 'Mutasi Keluar',
        self::STATUS_LULUS => 'Lulus',
        self::STATUS_KELUAR => 'Keluar',
        self::STATUS_PINDAH => 'Pindah',
        self::STATUS_NONAKTIF => 'Nonaktif',
    ];

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kelas',
        'rombel',
        'kode_kelas',
        'tahun_pelajaran',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function absensiSiswas(): HasMany
    {
        return $this->hasMany(AbsensiSiswa::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function kelasDisplay(): string
    {
        if ($this->rombel !== '') {
            return $this->rombel;
        }

        return trim($this->kelas.' '.$this->rombel) ?: ($this->kode_kelas ?: '-');
    }

    public function jenisKelaminLabel(): string
    {
        return match ($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'nis',
            'nisn',
            'nama',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'kelas',
            'rombel',
            'kode_kelas',
            'tahun_pelajaran',
            'status',
        ]);
    }
}
