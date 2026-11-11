<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalReset extends Model
{
    protected $fillable = [
        'guru_id',
        'user_id',
        'tahun_pelajaran',
        'semester',
        'slot_count',
        'jadwal_ids',
        'catatan',
    ];

    protected $casts = [
        'jadwal_ids' => 'array',
        'slot_count' => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
