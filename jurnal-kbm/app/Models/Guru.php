<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_guru',
        'nama',
        'nama_tampilan',
        'username_suffix',
        'nip',
        'mapel',
        'jumlah_jam_mengajar',
    ];

    public function displayName(): string
    {
        return $this->nama_tampilan ?: $this->nama;
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}
