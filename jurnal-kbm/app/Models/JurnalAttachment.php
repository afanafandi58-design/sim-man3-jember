<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JurnalAttachment extends Model
{
    public const MAX_PER_JURNAL = 3;

    public const THUMB_SUFFIX = '-thumb';

    public $timestamps = false;

    protected $fillable = [
        'jurnal_id',
        'nama_file',
        'caption',
        'path_file',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public static function deriveThumbPath(string $pathFile): string
    {
        $dot = strrpos($pathFile, '.');

        if ($dot === false) {
            return $pathFile.self::THUMB_SUFFIX;
        }

        return substr($pathFile, 0, $dot).self::THUMB_SUFFIX.substr($pathFile, $dot);
    }

    public function thumbPath(): string
    {
        return self::deriveThumbPath($this->path_file);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path_file);
    }

    public function thumbUrl(): string
    {
        $thumbPath = $this->thumbPath();

        if (Storage::disk('public')->exists($thumbPath)) {
            return Storage::disk('public')->url($thumbPath);
        }

        return $this->url();
    }

    public function absolutePath(): string
    {
        return Storage::disk('public')->path($this->path_file);
    }

    public function thumbAbsolutePath(): string
    {
        $thumbPath = $this->thumbPath();

        if (Storage::disk('public')->exists($thumbPath)) {
            return Storage::disk('public')->path($thumbPath);
        }

        return $this->absolutePath();
    }
}
