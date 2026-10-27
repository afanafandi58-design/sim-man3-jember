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
        return $this->buildQuery($filters, eager: false)->count('absensi_siswas.id');
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

    /**
     * Join-based filters (faster than nested whereHas) + slim eager loads for PDF.
     */
    private function buildQuery(array $filters, bool $eager = true): Builder
    {
        $query = AbsensiSiswa::query()
            ->select([
                'absensi_siswas.id',
                'absensi_siswas.jurnal_id',
                'absensi_siswas.jadwal_id',
                'absensi_siswas.guru_id',
                'absensi_siswas.siswa_id',
                'absensi_siswas.status',
                'absensi_siswas.keterangan',
                'absensi_siswas.created_at',
            ])
            ->join('jurnals', 'jurnals.id', '=', 'absensi_siswas.jurnal_id')
            ->join('jadwals', 'jadwals.id', '=', 'absensi_siswas.jadwal_id')
            ->join('siswas', 'siswas.id', '=', 'absensi_siswas.siswa_id');

        if ($eager) {
            $query->with([
                'jurnal:id,tanggal',
                'jadwal:id,guru_id,mapel',
                'jadwal.guru:id,nama',
                'siswa:id,nama,nis,kelas,rombel',
            ]);
        }

        return $query
            ->when($filters['tanggal'] ?? null, function (Builder $query, string $tanggal) {
                $query->whereDate('jurnals.tanggal', $tanggal);
            })
            ->when($filters['tanggal_mulai'] ?? null, function (Builder $query, string $tanggalMulai) {
                $query->whereDate('jurnals.tanggal', '>=', $tanggalMulai);
            })
            ->when($filters['tanggal_selesai'] ?? null, function (Builder $query, string $tanggalSelesai) {
                $query->whereDate('jurnals.tanggal', '<=', $tanggalSelesai);
            })
            ->when($filters['guru_id'] ?? null, fn (Builder $query, $guruId) => $query->where('absensi_siswas.guru_id', $guruId))
            ->when($filters['mapel'] ?? null, fn (Builder $query, string $mapel) => $query->where('jadwals.mapel', $mapel))
            ->when($filters['kelas'] ?? null, fn (Builder $query, string $kelas) => $query->where('siswas.kelas', $kelas))
            ->when($filters['rombel'] ?? null, fn (Builder $query, string $rombel) => $query->where('siswas.rombel', $rombel))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('absensi_siswas.status', $status))
            ->orderByDesc('jurnals.tanggal')
            ->orderByDesc('absensi_siswas.id');
    }
}
