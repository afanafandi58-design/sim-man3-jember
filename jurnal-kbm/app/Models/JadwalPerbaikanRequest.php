<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPerbaikanRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** Langsung diterapkan guru (timpa slot jadwal, tanpa antrean admin). */
    public const STATUS_APPLIED = 'applied';

    protected $fillable = [
        'jadwal_id',
        'guru_id',
        'alasan',
        'old_values',
        'new_values',
        'status',
        'processed_by',
        'admin_catatan',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }
}
