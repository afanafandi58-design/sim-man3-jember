<?php

namespace App\Services;

use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapAbsensiService
{
    public function getFilterOptions(): array
    {
        return [
            'gurus' => Guru::query()->orderBy('nama')->get(['id', 'nama']),
            'mapels' => Jadwal::query()->distinct()->orderBy('mapel')->pluck('mapel'),
            'kelas' => Siswa::query()->distinct()->orderBy('kelas')->pluck('kelas'),
            'rombels' => Siswa::query()->distinct()->orderBy('rombel')->pluck('rombel'),
            'statuses' => AbsensiSiswa::STATUSES,
        ];
    }

    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->buildQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getRekap(array $filters, ?int $limit = null): Collection
    {
        $query = $this->buildQuery($filters);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function count(array $filters): int
    {
        return $this->buildQuery($filters)->count();
    }

    public function summarize(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'hadir' => $rows->where('status', AbsensiSiswa::STATUS_HADIR)->count(),
            'sakit' => $rows->where('status', AbsensiSiswa::STATUS_SAKIT)->count(),
            'izin' => $rows->where('status', AbsensiSiswa::STATUS_IZIN)->count(),
            'alpha' => $rows->where('status', AbsensiSiswa::STATUS_ALPHA)->count(),
        ];
    }

    private function buildQuery(array $filters): Builder
    {
        return AbsensiSiswa::query()
            ->with(['jurnal', 'jadwal.guru', 'siswa'])
            ->when($filters['tanggal'] ?? null, function (Builder $query, string $tanggal) {
                $query->whereHas('jurnal', fn (Builder $jurnal) => $jurnal->whereDate('tanggal', $tanggal));
            })
            ->when($filters['tanggal_mulai'] ?? null, function (Builder $query, string $tanggalMulai) {
                $query->whereHas('jurnal', fn (Builder $jurnal) => $jurnal->whereDate('tanggal', '>=', $tanggalMulai));
            })
            ->when($filters['tanggal_selesai'] ?? null, function (Builder $query, string $tanggalSelesai) {
                $query->whereHas('jurnal', fn (Builder $jurnal) => $jurnal->whereDate('tanggal', '<=', $tanggalSelesai));
            })
            ->when($filters['guru_id'] ?? null, fn (Builder $query, $guruId) => $query->where('guru_id', $guruId))
            ->when($filters['mapel'] ?? null, function (Builder $query, string $mapel) {
                $query->whereHas('jadwal', fn (Builder $jadwal) => $jadwal->where('mapel', $mapel));
            })
            ->when($filters['kelas'] ?? null, function (Builder $query, string $kelas) {
                $query->whereHas('siswa', fn (Builder $siswa) => $siswa->where('kelas', $kelas));
            })
            ->when($filters['rombel'] ?? null, function (Builder $query, string $rombel) {
                $query->whereHas('siswa', fn (Builder $siswa) => $siswa->where('rombel', $rombel));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at');
    }
}
