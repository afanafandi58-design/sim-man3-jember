<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalChangeLog extends Model
{
    protected $fillable = [
        'jadwal_id',
        'guru_id',
        'user_id',
        'action',
        'snapshot_before',
        'snapshot_after',
        'ip_address',
    ];

    protected $casts = [
        'snapshot_before' => 'array',
        'snapshot_after' => 'array',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
