<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaliKelas extends Model
{
    protected $table = 'wali_kelas';

    protected $fillable = [
        'guru_id',
        'kelas',
        'rombel',
        'tahun_pelajaran',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
