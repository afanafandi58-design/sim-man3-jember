<?php

namespace App\Repositories;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class KepalaMonitoringRepository
{
    public function countGuru(): int
    {
        return Guru::count();
    }

    public function countGuruMengajarHariIni(string $hari): int
    {
        return Guru::query()
            ->whereHas('jadwals', fn ($query) => $query->whereRaw('LOWER(hari) = ?', [strtolower($hari)]))
            ->count();
    }

    public function countSudahMengisi(Carbon $tanggal, string $hari): int
    {
        return Jurnal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwal', fn ($query) => $query->whereRaw('LOWER(hari) = ?', [strtolower($hari)]))
            ->count();
    }

    public function countBelumMengisi(Carbon $tanggal, string $hari): int
    {
        return Jadwal::query()
            ->whereRaw('LOWER(hari) = ?', [strtolower($hari)])
            ->whereDoesntHave('jurnals', fn ($query) => $query->whereDate('tanggal', $tanggal))
            ->count();
    }

    public function getAllGuru(): Collection
    {
        return Guru::query()->orderBy('nama')->get();
    }

    public function getGuruMengajarHariIni(string $hari): Collection
    {
        return Guru::query()
            ->whereHas('jadwals', fn ($query) => $query->whereRaw('LOWER(hari) = ?', [strtolower($hari)]))
            ->with(['jadwals' => fn ($query) => $query->whereRaw('LOWER(hari) = ?', [strtolower($hari)])->orderBy('jam_mulai')])
            ->orderBy('nama')
            ->get();
    }

    public function getSudahMengisi(Carbon $tanggal, string $hari): Collection
    {
        return Jurnal::query()
            ->with(['jadwal.guru'])
            ->withCount('attachments')
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwal', fn ($query) => $query->whereRaw('LOWER(hari) = ?', [strtolower($hari)]))
            ->orderBy('created_at')
            ->get();
    }

    public function getBelumMengisi(Carbon $tanggal, string $hari): Collection
    {
        return Jadwal::query()
            ->with('guru')
            ->whereRaw('LOWER(hari) = ?', [strtolower($hari)])
            ->whereDoesntHave('jurnals', fn ($query) => $query->whereDate('tanggal', $tanggal))
            ->orderBy('jam_mulai')
            ->get();
    }
}
