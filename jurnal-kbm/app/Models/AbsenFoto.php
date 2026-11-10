<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AbsenFoto extends Model
{
    protected $fillable = [
        'kegiatan_id',
        'path',
        'nama_asli',
        'keterangan',
        'diunggah_oleh',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(AbsenKegiatan::class, 'kegiatan_id');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function deleteStoredFile(): void
    {
        if ($this->path) {
            Storage::disk('local')->delete($this->path);
        }
    }
}
