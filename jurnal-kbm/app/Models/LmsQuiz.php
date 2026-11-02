<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LmsQuiz extends Model
{
    protected $fillable = [
        'guru_id',
        'jadwal_id',
        'kelas',
        'mapel',
        'tahun_pelajaran',
        'judul',
        'deskripsi',
        'drive_url',
        'due_at',
        'is_published',
        'share_token',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (LmsQuiz $model) {
            if (! $model->share_token) {
                $model->share_token = Str::random(48);
            }
        });
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }
}
