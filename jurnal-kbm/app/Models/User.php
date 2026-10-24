<?php

namespace App\Models;

use App\Models\Guru;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'guru_id',
        'is_active',
        'password_reset_at',
        'last_login_at',
    ];

    /**
     * Hidden Attribute
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'password_reset_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}