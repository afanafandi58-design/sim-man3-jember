<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeKelas extends Model
{
    protected $table = 'kode_kelas';

    protected $fillable = [
        'kode_kelas',
        'kelas',
        'rombel',
    ];

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kode_kelas', 'kode_kelas');
    }
}
