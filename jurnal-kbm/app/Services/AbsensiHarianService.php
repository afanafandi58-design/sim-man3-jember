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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbsensiHarianService
{
    public function __construct(
        private OperationalDateService $operationalDate,
        private KodeKelasResolverService $kodeKelasResolver,
    ) {}

    public function ensureSchema(): void
    {
        TitikFSchema::ensure();
    }

    public function canInput(User $user): bool
    {
        return in_array($user->role, ['guru', 'staff', 'admin', 'kepala'], true);
    }

    /**
     * Siapa boleh melihat file bukti absensi (cegah IDOR antar guru).
     */
    public function canViewBukti(User $user, AbsensiHarian $row): bool
    {
        if (in_array($user->role, ['admin', 'kepala', 'staff'], true)) {
            return true;
        }

        if ($row->reported_by && (int) $row->reported_by === (int) $user->id) {
            return true;
        }

        $row->loadMissing('siswa');
        if (! $user->guru_id || ! $row->siswa) {
            return false;
        }

        $this->ensureSchema();

        if ($this->userCanAccessKelas($user, $row->siswa->kelas, $row->siswa->rombel, $row->siswa->kode_kelas)) {
            return true;
        }

        return false;
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
                $this->assertSafeImageUpload($bukti);
                if ($row->bukti_path) {
                    Storage::disk('public')->delete($row->bukti_path);
                }
                $ext = strtolower($bukti->getClientOriginalExtension() ?: 'jpg');
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $ext = 'jpg';
                }
                $dir = 'absensi-bukti/'.Carbon::parse($tanggal)->format('Y/m');
                $filename = Str::uuid()->toString().'.'.$ext;
                $path = $bukti->storeAs($dir, $filename, 'public');
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

    /**
     * Slot kelas yang boleh dilihat/difilter user.
     * Guru: jadwal aktif (kode jadwal → kelas/rombel siswa via kode_kelas) + assignment wali.
     *
     * @return Collection<int, array{key:string,label:string,kelas:string,rombel:?string,sumber:string}>
     */
    public function allowedKelasForUser(User $user): Collection
    {
        $this->ensureSchema();

        if (in_array($user->role, ['admin', 'kepala', 'staff'], true)) {
            return Siswa::query()
                ->where('status', Siswa::STATUS_AKTIF)
                ->select('kelas', 'rombel')
                ->distinct()
                ->orderBy('kelas')
                ->orderBy('rombel')
                ->get()
                ->map(function ($row) {
                    $kelas = (string) $row->kelas;
                    $rombel = $row->rombel ? (string) $row->rombel : null;

                    return [
                        'key' => $rombel ? $kelas.'|'.$rombel : $kelas,
                        'label' => $rombel ? $kelas.'/'.$rombel : $kelas,
                        'kelas' => $kelas,
                        'rombel' => $rombel,
                        'sumber' => 'semua',
                    ];
                })
                ->values();
        }

        if (! $user->guru_id) {
            return collect();
        }

        $fromJadwal = Jadwal::query()
            ->active()
            ->where('guru_id', $user->guru_id)
            ->select('kelas')
            ->distinct()
            ->pluck('kelas')
            ->filter()
            ->map(function ($kode) {
                $resolved = $this->resolveKelasSlot((string) $kode, null, 'mengajar');

                return $resolved;
            })
            ->filter();

        $fromWali = WaliKelas::query()
            ->where('guru_id', $user->guru_id)
            ->get(['kelas', 'rombel'])
            ->map(function (WaliKelas $row) {
                return $this->resolveKelasSlot(
                    (string) $row->kelas,
                    $row->rombel ? (string) $row->rombel : null,
                    'wali'
                );
            })
            ->filter();

        return $fromJadwal
            ->concat($fromWali)
            ->unique(fn (array $row) => $row['key'])
            ->sortBy([
                fn (array $row) => $row['kelas'],
                fn (array $row) => $row['rombel'] ?? '',
            ])
            ->values();
    }

    /** @return Collection<int, string> */
    public function allowedKelasNamesForUser(User $user): Collection
    {
        return $this->allowedKelasForUser($user)
            ->pluck('kelas')
            ->unique()
            ->sort()
            ->values();
    }

    public function findAllowedSlot(User $user, ?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        return $this->allowedKelasForUser($user)->firstWhere('key', $key);
    }

    public function userCanAccessKelas(User $user, ?string $kelas, ?string $rombel = null, ?string $kodeKelas = null): bool
    {
        if (in_array($user->role, ['admin', 'kepala', 'staff'], true)) {
            return true;
        }

        if (($kelas === null || $kelas === '') && ($kodeKelas === null || $kodeKelas === '')) {
            return false;
        }

        return $this->allowedKelasForUser($user)->contains(function (array $row) use ($kelas, $rombel, $kodeKelas) {
            if ($kodeKelas && ($row['key'] === $kodeKelas || str_starts_with($row['key'], $kodeKelas))) {
                return true;
            }
            if ($kelas && $row['kelas'] === $kelas) {
                if ($row['rombel'] === null || $row['rombel'] === '') {
                    return true;
                }

                return $rombel !== null && $rombel !== '' && $row['rombel'] === $rombel;
            }

            return false;
        });
    }

    /** Siswa tidak masuk pada tanggal (opsional filter kelas/rombel), diurutkan per kelas. */
    public function tidakMasuk(string $tanggal, ?string $kelas = null, ?string $rombel = null, ?array $slots = null): Collection
    {
        $this->ensureSchema();

        return AbsensiHarian::query()
            ->with(['siswa', 'reporter'])
            ->whereDate('tanggal', $tanggal)
            ->whereIn('status', AbsensiHarian::TIDAK_MASUK)
            ->whereHas('siswa', function ($sq) use ($kelas, $rombel, $slots) {
                if ($kelas) {
                    $sq->where('kelas', $kelas);
                    if ($rombel) {
                        $sq->where('rombel', $rombel);
                    }
                } elseif ($slots !== null) {
                    if ($slots === []) {
                        $sq->whereRaw('0 = 1');
                    } else {
                        $sq->where(function ($outer) use ($slots) {
                            foreach ($slots as $slot) {
                                $outer->orWhere(function ($inner) use ($slot) {
                                    $inner->where('kelas', $slot['kelas']);
                                    if (! empty($slot['rombel'])) {
                                        $inner->where('rombel', $slot['rombel']);
                                    }
                                });
                            }
                        });
                    }
                }
            })
            ->get()
            ->sortBy([
                fn ($r) => $r->siswa?->kelas ?? '',
                fn ($r) => $r->siswa?->rombel ?? '',
                fn ($r) => $r->siswa?->nama ?? '',
            ])
            ->values();
    }

    /** Daftar tidak masuk yang relevan untuk akun (guru dibatasi kelas ajar + wali). */
    public function tidakMasukForUser(User $user, string $tanggal, ?string $kelasKey = null, ?string $rombel = null): Collection
    {
        if (in_array($user->role, ['admin', 'kepala', 'staff'], true)) {
            // Admin filter: key may be "XII|XII SOSHUM 2" or plain kelas.
            if ($kelasKey && str_contains($kelasKey, '|')) {
                [$kelas, $rombelKey] = array_pad(explode('|', $kelasKey, 2), 2, null);

                return $this->tidakMasuk($tanggal, $kelas, $rombel ?: $rombelKey);
            }

            return $this->tidakMasuk($tanggal, $kelasKey, $rombel);
        }

        $allowed = $this->allowedKelasForUser($user);
        if ($kelasKey) {
            $slot = $allowed->firstWhere('key', $kelasKey);
            if (! $slot) {
                return collect();
            }

            return $this->tidakMasuk($tanggal, $slot['kelas'], $slot['rombel']);
        }

        return $this->tidakMasuk($tanggal, null, null, $allowed->all());
    }

    /** Guru pengajar pada hari efektif (nama hari Indonesia). */
    public function jadwalGuruHariIni(int $guruId, ?string $hari = null): Collection
    {
        $hari = $hari ?? $this->operationalDate->hari();

        return Jadwal::query()
            ->active()
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

    public function siswaOptions(?string $kelas = null, ?string $rombel = null, ?array $slots = null): Collection
    {
        return Siswa::query()
            ->where('status', Siswa::STATUS_AKTIF)
            ->when($kelas, fn ($q) => $q->where('kelas', $kelas))
            ->when($rombel, fn ($q) => $q->where('rombel', $rombel))
            ->when($slots !== null, function ($q) use ($slots) {
                if ($slots === []) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->where(function ($outer) use ($slots) {
                        foreach ($slots as $slot) {
                            $outer->orWhere(function ($inner) use ($slot) {
                                $inner->where('kelas', $slot['kelas']);
                                if (! empty($slot['rombel'])) {
                                    $inner->where('rombel', $slot['rombel']);
                                }
                            });
                        }
                    });
                }
            })
            ->orderBy('kelas')
            ->orderBy('rombel')
            ->orderBy('nama')
            ->limit(500)
            ->get(['id', 'nis', 'nama', 'kelas', 'rombel']);
    }

    public function siswaOptionsForUser(User $user, ?string $kelasKey = null, ?string $rombel = null): Collection
    {
        if (in_array($user->role, ['admin', 'kepala', 'staff'], true)) {
            if ($kelasKey && str_contains($kelasKey, '|')) {
                [$kelas, $rombelKey] = array_pad(explode('|', $kelasKey, 2), 2, null);

                return $this->siswaOptions($kelas, $rombel ?: $rombelKey);
            }

            return $this->siswaOptions($kelasKey, $rombel);
        }

        $allowed = $this->allowedKelasForUser($user);
        if ($kelasKey) {
            $slot = $allowed->firstWhere('key', $kelasKey);
            if (! $slot) {
                return collect();
            }

            return $this->siswaOptions($slot['kelas'], $slot['rombel']);
        }

        return $this->siswaOptions(null, null, $allowed->all());
    }

    /**
     * Petakan kode jadwal / assignment wali ke kelas+rombel siswa.
     *
     * @return array{key:string,label:string,kelas:string,rombel:?string,sumber:string}|null
     */
    private function resolveKelasSlot(string $kelasOrKode, ?string $rombel, string $sumber): ?array
    {
        $kelasOrKode = trim($kelasOrKode);
        $rombel = $rombel !== null ? trim($rombel) : null;
        if ($kelasOrKode === '') {
            return null;
        }

        // 1) kode jadwal exact (X-E → X / X E REG)
        $resolved = $this->kodeKelasResolver->resolveFromString($kelasOrKode);
        if ($resolved) {
            return [
                'key' => (string) $resolved->kode_kelas,
                'label' => $resolved->kode_kelas.' ('.$resolved->kelas.'/'.$resolved->rombel.')',
                'kelas' => (string) $resolved->kelas,
                'rombel' => (string) $resolved->rombel,
                'sumber' => $sumber,
            ];
        }

        // 2) coba gabungan kelas+rombel assignment wali
        if ($rombel) {
            $resolved = $this->kodeKelasResolver->resolveFromString($kelasOrKode.' '.$rombel)
                ?: $this->kodeKelasResolver->resolveFromString($rombel);
            if ($resolved) {
                return [
                    'key' => (string) $resolved->kode_kelas,
                    'label' => $resolved->kode_kelas.' ('.$resolved->kelas.'/'.$resolved->rombel.')',
                    'kelas' => (string) $resolved->kelas,
                    'rombel' => (string) $resolved->rombel,
                    'sumber' => $sumber,
                ];
            }

            // 3) cocokkan rombel siswa apa adanya
            $exists = Siswa::query()
                ->where('status', Siswa::STATUS_AKTIF)
                ->where('kelas', $kelasOrKode)
                ->where('rombel', $rombel)
                ->exists();
            if ($exists) {
                return [
                    'key' => $kelasOrKode.'|'.$rombel,
                    'label' => $kelasOrKode.'/'.$rombel,
                    'kelas' => $kelasOrKode,
                    'rombel' => $rombel,
                    'sumber' => $sumber,
                ];
            }

            // 4) fallback fuzzy: angka+huruf rombel vs rombel siswa (mis. "12 SOS 2" ≈ "XII SOSHUM 2")
            $fuzzy = $this->fuzzyMatchRombel($kelasOrKode, $rombel);
            if ($fuzzy) {
                return $fuzzy + ['sumber' => $sumber];
            }
        }

        // 5) seluruh tingkat kelas (tanpa rombel) — untuk wali dengan rombel usang
        return [
            'key' => $kelasOrKode,
            'label' => $kelasOrKode.' (semua rombel)',
            'kelas' => $kelasOrKode,
            'rombel' => null,
            'sumber' => $sumber,
        ];
    }

    /** @return array{key:string,label:string,kelas:string,rombel:string}|null */
    private function fuzzyMatchRombel(string $kelas, string $rombel): ?array
    {
        $aliases = [
            preg_replace('/\b12\b/i', 'XII', $rombel) ?? $rombel,
            preg_replace('/\b11\b/i', 'XI', $rombel) ?? $rombel,
            preg_replace('/\b10\b/i', 'X', $rombel) ?? $rombel,
        ];
        $expanded = [];
        foreach ($aliases as $alias) {
            $expanded[] = $alias;
            $expanded[] = preg_replace('/\bSOS\b/i', 'SOSHUM', $alias) ?? $alias;
            $expanded[] = preg_replace('/\bSAI\b/i', 'SAINS', $alias) ?? $alias;
        }

        foreach (array_unique($expanded) as $candidateAlias) {
            $resolved = $this->kodeKelasResolver->resolveFromString(trim($kelas.' '.$candidateAlias))
                ?: $this->kodeKelasResolver->resolveFromString(trim($candidateAlias));
            if ($resolved && strcasecmp((string) $resolved->kelas, $kelas) === 0) {
                return [
                    'key' => (string) $resolved->kode_kelas,
                    'label' => $resolved->kode_kelas.' ('.$resolved->kelas.'/'.$resolved->rombel.')',
                    'kelas' => (string) $resolved->kelas,
                    'rombel' => (string) $resolved->rombel,
                ];
            }

            $exists = Siswa::query()
                ->where('status', Siswa::STATUS_AKTIF)
                ->where('kelas', $kelas)
                ->where('rombel', $candidateAlias)
                ->exists();
            if ($exists) {
                return [
                    'key' => $kelas.'|'.$candidateAlias,
                    'label' => $kelas.'/'.$candidateAlias,
                    'kelas' => $kelas,
                    'rombel' => $candidateAlias,
                ];
            }
        }

        $needle = strtolower(preg_replace('/[^a-z0-9]+/i', '', $rombel) ?? '');
        if ($needle === '') {
            return null;
        }

        $candidates = Siswa::query()
            ->where('status', Siswa::STATUS_AKTIF)
            ->where('kelas', $kelas)
            ->distinct()
            ->orderBy('rombel')
            ->pluck('rombel')
            ->filter();

        foreach ($candidates as $candidate) {
            $hay = strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $candidate) ?? '');
            if ($hay === $needle || str_contains($hay, $needle) || str_contains($needle, $hay)) {
                $resolved = $this->kodeKelasResolver->resolveFromString($kelas.' '.$candidate);

                return [
                    'key' => $resolved?->kode_kelas ?: ($kelas.'|'.$candidate),
                    'label' => ($resolved?->kode_kelas ? $resolved->kode_kelas.' ' : '').'('.$kelas.'/'.$candidate.')',
                    'kelas' => $kelas,
                    'rombel' => (string) $candidate,
                ];
            }
        }

        return null;
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

    private function assertSafeImageUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'bukti' => 'Upload bukti gagal. Coba lagi dengan file gambar lain.',
            ]);
        }

        $mime = (string) $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'bukti' => 'Bukti harus berupa gambar JPG, PNG, atau WEBP.',
            ]);
        }

        $info = @getimagesize($file->getRealPath());
        if ($info === false) {
            throw ValidationException::withMessages([
                'bukti' => 'File bukti bukan gambar yang valid.',
            ]);
        }
    }
}
