<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class LaporanPdfService
{
    public function getFilterOptions(): array
    {
        return [
            'gurus' => Guru::query()->orderBy('nama')->get(['id', 'nama']),
            'mapels' => Jadwal::query()->distinct()->orderBy('mapel')->pluck('mapel'),
            'kelas' => Jadwal::query()->distinct()->orderBy('kelas')->pluck('kelas'),
            'periodes' => [
                'harian' => 'Harian',
                'mingguan' => 'Mingguan',
                'bulanan' => 'Bulanan',
                'semester' => 'Semester',
            ],
        ];
    }

    /** Opsi filter untuk portal guru (hanya mapel/kelas milik jadwal guru ini). */
    public function getGuruFilterOptions(int $guruId): array
    {
        $jadwalQuery = Jadwal::query()->where('guru_id', $guruId);

        return [
            'mapels' => (clone $jadwalQuery)->distinct()->orderBy('mapel')->pluck('mapel'),
            'kelas' => (clone $jadwalQuery)->distinct()->orderBy('kelas')->pluck('kelas'),
            'periodes' => [
                'mingguan' => 'Mingguan',
                'bulanan' => 'Bulanan',
                'semester' => 'Semester',
            ],
        ];
    }

    public function buildGuruTitle(array $filters, Carbon $startDate, Carbon $endDate, ?string $guruNama): string
    {
        $periode = ucfirst($filters['periode'] ?? 'mingguan');
        $nama = $guruNama ?: 'Guru';

        return "Rekap Jurnal KBM {$periode} — {$nama} ({$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')})";
    }

    public function buildGuruFilename(array $reportData): string
    {
        $slug = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) ($reportData['guru']->nama ?? 'guru'));

        return 'Rekap-Jurnal-Guru-'
            .trim($slug, '-')
            .'-'
            .$reportData['periode']
            .'-'
            .$reportData['startDate']->format('Ymd')
            .'-'
            .$reportData['endDate']->format('Ymd')
            .'.pdf';
    }

    public function getReportData(array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $query = Jurnal::query()
            ->with(['jadwal.guru'])
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($filters['guru_id'] ?? null, function ($query, $guruId) {
                $query->whereHas('jadwal', fn ($jadwal) => $jadwal->where('guru_id', $guruId));
            })
            ->when($filters['mapel'] ?? null, function ($query, $mapel) {
                $query->whereHas('jadwal', fn ($jadwal) => $jadwal->where('mapel', $mapel));
            })
            ->when($filters['kelas'] ?? null, function ($query, $kelas) {
                $query->whereHas('jadwal', fn ($jadwal) => $jadwal->where('kelas', $kelas));
            })
            ->when(($filters['periode'] ?? null) === 'semester', function ($query) use ($filters) {
                $query->whereHas('jadwal', function ($jadwal) use ($filters) {
                    if (! empty($filters['semester'])) {
                        $jadwal->where('semester', $filters['semester']);
                    }
                    if (! empty($filters['tahun_pelajaran'])) {
                        $jadwal->where('tahun_pelajaran', $filters['tahun_pelajaran']);
                    }
                });
            })
            ->orderBy('tanggal')
            ->orderBy('jam_ke');

        /** @var Collection<int, Jurnal> $jurnals */
        $jurnals = $query->get();

        return [
            'jurnals' => $jurnals,
            'periode' => Arr::get($filters, 'periode', 'harian'),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
            'title' => $this->buildTitle($filters, $startDate, $endDate),
        ];
    }

    public function buildFilename(array $reportData): string
    {
        return 'Laporan-Jurnal-'
            .$reportData['periode']
            .'-'
            .$reportData['startDate']->format('Ymd')
            .'-'
            .$reportData['endDate']->format('Ymd')
            .'.pdf';
    }

    private function buildTitle(array $filters, Carbon $startDate, Carbon $endDate): string
    {
        $periode = ucfirst($filters['periode'] ?? 'harian');

        return "Laporan Jurnal KBM {$periode} ({$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')})";
    }

    private function resolveDateRange(array $filters): array
    {
        $periode = $filters['periode'] ?? 'harian';
        $tanggal = Carbon::parse($filters['tanggal'] ?? today());

        return match ($periode) {
            'mingguan' => [$tanggal->copy()->startOfWeek(), $tanggal->copy()->endOfWeek()],
            'bulanan' => [
                Carbon::createFromDate($filters['tahun'] ?? $tanggal->year, $filters['bulan'] ?? $tanggal->month, 1)->startOfMonth(),
                Carbon::createFromDate($filters['tahun'] ?? $tanggal->year, $filters['bulan'] ?? $tanggal->month, 1)->endOfMonth(),
            ],
            'semester' => [
                Carbon::parse($filters['tanggal_mulai'] ?? $tanggal->copy()->startOfMonth()),
                Carbon::parse($filters['tanggal_selesai'] ?? $tanggal->copy()->endOfMonth()),
            ],
            default => [$tanggal->copy(), $tanggal->copy()],
        };
    }
}
