<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AbsenKegiatan extends Model
{
    /** @var array<string, string> */
    public const JENIS = [
        'rapat' => 'Rapat',
        'upacara' => 'Upacara',
        'diklat' => 'Diklat',
        'pembinaan' => 'Pembinaan',
        'lain' => 'Lainnya',
    ];

    protected $fillable = [
        'nama',
        'jenis',
        'tanggal',
        'waktu',
        'tempat',
        'keterangan',
        'kode',
        'hasil_drive_url',
        'hasil_file_path',
        'hasil_file_nama',
        'hasil_catatan',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function kehadirans(): HasMany
    {
        return $this->hasMany(AbsenKehadiran::class, 'kegiatan_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(AbsenFoto::class, 'kegiatan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function scanUrl(): string
    {
        return route('absen-digital.scan', ['kode' => $this->kode]);
    }

    public function tanggalLabel(): string
    {
        return $this->tanggal?->locale('id')->translatedFormat('d F Y') ?? '';
    }

    protected static function booted(): void
    {
        static::deleting(function (AbsenKegiatan $kegiatan) {
            $kegiatan->loadMissing(['kehadirans', 'fotos']);

            foreach ($kegiatan->kehadirans as $row) {
                $row->deleteStoredFiles();
            }

            foreach ($kegiatan->fotos as $foto) {
                $foto->deleteStoredFile();
            }

            if ($kegiatan->hasil_file_path) {
                Storage::disk('local')->delete($kegiatan->hasil_file_path);
            }
        });
    }
}
