<?php

namespace App\Services;

use App\Models\AbsensiHarian;
use App\Models\AbsensiSiswa;
use App\Models\Jadwal;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use App\Support\TitikFSchema;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiHarianService
{
    public function __construct(
        private OperationalDateService $operationalDate,
    ) {}

    public function ensureSchema(): void
    {
        TitikFSchema::ensure();
    }

    public function canInput(User $user): bool
    {
        return in_array($user->role, ['guru', 'staff', 'admin', 'kepala'], true);
    }

    public function isWaliKelas(User $user): bool
    {
        if (! $user->guru_id) {
            return false;
        }
        $this->ensureSchema();

        return WaliKelas::query()->where('guru_id', $user->guru_id)->exists();
    }

    /** @return Collection<int, WaliKelas> */
    public function waliAssignments(User $user): Collection
    {
        if (! $user->guru_id) {
            return collect();
        }
        $this->ensureSchema();

        return WaliKelas::query()->where('guru_id', $user->guru_id)->get();
    }

    public function upsertLaporanOnline(
        string $tanggal,
        int $siswaId,
        string $status,
        ?string $keterangan,
        User $reporter,
        ?UploadedFile $bukti = null,
        ?string $buktiCaption = null,
    ): AbsensiHarian {
        $this->ensureSchema();
        $this->operationalDate->assertFillableDate($tanggal);

        if (! in_array($status, AbsensiHarian::STATUSES, true)) {
            $status = AbsensiHarian::STATUS_ALPHA;
        }

        return DB::transaction(function () use ($tanggal, $siswaId, $status, $keterangan, $reporter, $bukti, $buktiCaption) {
            $row = AbsensiHarian::query()->firstOrNew([
                'tanggal' => $tanggal,
                'siswa_id' => $siswaId,
            ]);

            $row->status = $status;
            $row->keterangan = $keterangan;
            $row->sumber = 'online';
            $row->reported_by = $reporter->id;

            if ($bukti) {
                if ($row->bukti_path) {
                    Storage::disk('public')->delete($row->bukti_path);
                }
                $path = $bukti->store('absensi-bukti/'.Carbon::parse($tanggal)->format('Y/m'), 'public');
                $row->bukti_path = $path;
                $row->bukti_caption = $buktiCaption;
            } elseif ($buktiCaption !== null) {
                $row->bukti_caption = $buktiCaption;
            }

            $row->save();

            return $row->load(['siswa', 'reporter']);
        });
    }

    /** Prefill map siswa_id => status dari absensi harian (untuk form jurnal). */
    public function prefillMapForKelas(string $tanggal, string $kelas, ?string $rombel = null): array
    {
        $this->ensureSchema();

        $q = AbsensiHarian::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('siswa', function ($sq) use ($kelas, $rombel) {
                $sq->where('kelas', $kelas);
                if ($rombel) {
                    $sq->where('rombel', $rombel);
                }
            });

        $statusMap = [];
        $ketMap = [];
        foreach ($q->get() as $row) {
            $statusMap[$row->siswa_id] = $row->status;
            $ketMap[$row->siswa_id] = $row->keterangan;
        }

        return ['status' => $statusMap, 'keterangan' => $ketMap];
    }

    /** Siswa tidak masuk pada tanggal (opsional filter kelas/rombel). */
    public function tidakMasuk(string $tanggal, ?string $kelas = null, ?string $rombel = null): Collection
    {
        $this->ensureSchema();

        return AbsensiHarian::query()
            ->with(['siswa', 'reporter'])
            ->whereDate('tanggal', $tanggal)
            ->whereIn('status', AbsensiHarian::TIDAK_MASUK)
            ->when($kelas, fn ($q) => $q->whereHas('siswa', function ($sq) use ($kelas, $rombel) {
                $sq->where('kelas', $kelas);
                if ($rombel) {
                    $sq->where('rombel', $rombel);
                }
            }))
            ->orderBy('status')
            ->get()
            ->sortBy(fn ($r) => $r->siswa?->nama ?? '');
    }

    /** Guru pengajar pada hari efektif (nama hari Indonesia). */
    public function jadwalGuruHariIni(int $guruId, ?string $hari = null): Collection
    {
        $hari = $hari ?? $this->operationalDate->hari();

        return Jadwal::query()
            ->where('guru_id', $guruId)
            ->whereRaw('LOWER(hari) = ?', [strtolower($hari)])
            ->orderBy('jam_mulai')
            ->get();
    }

    public function guruMengajarHariIni(User $user): bool
    {
        if (! $user->guru_id) {
            return false;
        }

        return $this->jadwalGuruHariIni((int) $user->guru_id)->isNotEmpty();
    }

    /**
     * Laporan agregat.
     *
     * @return array{rows: Collection, summary: array, periode_label: string, start: string, end: string}
     */
    public function laporan(string $periode, array $filters = []): array
    {
        $this->ensureSchema();
        [$start, $end, $label] = $this->resolvePeriode($periode, $filters);

        $rows = AbsensiHarian::query()
            ->with('siswa')
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->when($filters['kelas'] ?? null, fn ($q, $kelas) => $q->whereHas('siswa', fn ($sq) => $sq->where('kelas', $kelas)))
            ->when($filters['rombel'] ?? null, fn ($q, $rombel) => $q->whereHas('siswa', fn ($sq) => $sq->where('rombel', $rombel)))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('tanggal')
            ->orderBy('siswa_id')
            ->limit(5000)
            ->get();

        $summary = [
            'total' => $rows->count(),
            'hadir' => $rows->where('status', AbsensiHarian::STATUS_HADIR)->count(),
            'sakit' => $rows->where('status', AbsensiHarian::STATUS_SAKIT)->count(),
            'izin' => $rows->where('status', AbsensiHarian::STATUS_IZIN)->count(),
            'alpha' => $rows->where('status', AbsensiHarian::STATUS_ALPHA)->count(),
            'dengan_bukti' => $rows->filter->hasBukti()->count(),
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
            'periode_label' => $label,
            'start' => $start,
            'end' => $end,
        ];
    }

    /** Sync status dari jurnal absensi ke absensi harian (tidak menimpa laporan online dengan bukti). */
    public function syncFromJurnalAbsensi(int $jurnalId, string $tanggal, Collection $absensiSiswaRows): void
    {
        $this->ensureSchema();

        foreach ($absensiSiswaRows as $row) {
            /** @var AbsensiSiswa $row */
            $existing = AbsensiHarian::query()
                ->whereDate('tanggal', $tanggal)
                ->where('siswa_id', $row->siswa_id)
                ->first();

            if ($existing && $existing->sumber === 'online' && $existing->hasBukti()) {
                $existing->jurnal_id = $jurnalId;
                $existing->save();
                continue;
            }

            AbsensiHarian::query()->updateOrCreate(
                ['tanggal' => $tanggal, 'siswa_id' => $row->siswa_id],
                [
                    'status' => $row->status,
                    'keterangan' => $row->keterangan,
                    'sumber' => $existing?->sumber === 'online' ? 'online' : 'jurnal',
                    'jurnal_id' => $jurnalId,
                    'reported_by' => $existing?->reported_by,
                    'bukti_path' => $existing?->bukti_path,
                    'bukti_caption' => $existing?->bukti_caption,
                ]
            );
        }
    }

    public function siswaOptions(?string $kelas = null, ?string $rombel = null): Collection
    {
        return Siswa::query()
            ->where('status', Siswa::STATUS_AKTIF)
            ->when($kelas, fn ($q) => $q->where('kelas', $kelas))
            ->when($rombel, fn ($q) => $q->where('rombel', $rombel))
            ->orderBy('nama')
            ->limit(500)
            ->get(['id', 'nis', 'nama', 'kelas', 'rombel']);
    }

    /** @return array{0:string,1:string,2:string} */
    private function resolvePeriode(string $periode, array $filters): array
    {
        $today = $this->operationalDate->today();

        return match ($periode) {
            'mingguan' => [
                ($filters['tanggal'] ?? null)
                    ? Carbon::parse($filters['tanggal'])->startOfWeek()->toDateString()
                    : $today->copy()->startOfWeek()->toDateString(),
                ($filters['tanggal'] ?? null)
                    ? Carbon::parse($filters['tanggal'])->endOfWeek()->toDateString()
                    : $today->copy()->endOfWeek()->toDateString(),
                'Mingguan',
            ],
            'bulanan' => [
                Carbon::create((int) ($filters['tahun'] ?? $today->year), (int) ($filters['bulan'] ?? $today->month), 1)->startOfMonth()->toDateString(),
                Carbon::create((int) ($filters['tahun'] ?? $today->year), (int) ($filters['bulan'] ?? $today->month), 1)->endOfMonth()->toDateString(),
                'Bulanan',
            ],
            'semester' => $this->semesterRange((string) ($filters['semester'] ?? 'Ganjil'), (string) ($filters['tahun_pelajaran'] ?? $this->operationalDate->tahunPelajaran())),
            default => [
                (string) ($filters['tanggal'] ?? $today->toDateString()),
                (string) ($filters['tanggal'] ?? $today->toDateString()),
                'Harian',
            ],
        };
    }

    /** @return array{0:string,1:string,2:string} */
    private function semesterRange(string $semester, string $tahunPelajaran): array
    {
        [$y1, $y2] = array_pad(explode('/', $tahunPelajaran), 2, (string) date('Y'));
        if (strcasecmp($semester, 'Genap') === 0) {
            return ["{$y2}-01-01", "{$y2}-06-30", "Semester Genap {$tahunPelajaran}"];
        }

        return ["{$y1}-07-01", "{$y1}-12-31", "Semester Ganjil {$tahunPelajaran}"];
    }
}
