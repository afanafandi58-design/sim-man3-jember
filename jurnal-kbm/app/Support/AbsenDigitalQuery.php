<?php

namespace App\Support;

use App\Models\AbsenKegiatan;
use App\Models\AbsenKehadiran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AbsenDigitalQuery
{
    /** @var list<string> */
    public const PESERTA_ROLES = ['admin', 'guru', 'staff', 'kepala'];

    public static function makeKode(): string
    {
        do {
            $kode = strtoupper(Str::random(8));
        } while (AbsenKegiatan::query()->where('kode', $kode)->exists());

        return $kode;
    }

    public static function normalizeKode(?string $raw): string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return '';
        }

        $parts = parse_url($raw);

        if (is_array($parts) && isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if (! empty($query['kode']) && is_string($query['kode'])) {
                return strtoupper(trim($query['kode']));
            }
        }

        if (preg_match('/(?:^|[?&])kode=([A-Za-z0-9]+)/', $raw, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        return strtoupper($raw);
    }

    public static function peserta(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('role', self::PESERTA_ROLES)
            ->orderBy('name');
    }

    public static function findPeserta(int $userId): ?User
    {
        return self::peserta()->whereKey($userId)->first();
    }

    public static function alreadyAttended(int $kegiatanId, ?int $userId, ?string $nik): bool
    {
        $query = AbsenKehadiran::query()->where('kegiatan_id', $kegiatanId);

        if ($userId) {
            return (clone $query)->where('user_id', $userId)->exists();
        }

        if ($nik) {
            return (clone $query)->where('nik', $nik)->exists();
        }

        return false;
    }

    public static function filteredKegiatan(array $filters): Builder
    {
        return AbsenKegiatan::query()
            ->when($filters['tanggal_dari'] ?? null, fn (Builder $q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filters['tanggal_sampai'] ?? null, fn (Builder $q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filters['jenis'] ?? null, fn (Builder $q, $jenis) => $q->where('jenis', $jenis))
            ->when($filters['kegiatan_id'] ?? null, fn (Builder $q, $id) => $q->whereKey($id));
    }
}
