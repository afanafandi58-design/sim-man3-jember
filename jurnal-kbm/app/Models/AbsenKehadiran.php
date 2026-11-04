<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AbsenKehadiran extends Model
{
    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'nik',
        'nama',
        'metode',
        'foto_path',
        'latitude',
        'longitude',
        'ttd_path',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(AbsenKegiatan::class, 'kegiatan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function metodeLabel(): string
    {
        return $this->metode === 'qr' ? 'Scan QR' : 'Formulir';
    }

    public function koordinatLabel(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return $this->latitude.', '.$this->longitude;
    }

    public function mapsUrl(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://maps.google.com/?q='.$this->latitude.','.$this->longitude;
    }

    public function deleteStoredFiles(): void
    {
        $paths = array_filter([$this->foto_path, $this->ttd_path]);

        if ($paths !== []) {
            Storage::disk('local')->delete($paths);
        }
    }
}
