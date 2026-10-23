<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\KodeKelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaAdminService
{
    public const DEFAULT_TAHUN_PELAJARAN = '2026/2027';

    public function __construct(
        private KodeKelasResolverService $kodeKelasResolverService,
        private SiswaAuditLogger $auditLogger,
    ) {}

    public function paginate(User $viewer, array $filters = []): LengthAwarePaginator
    {
        $keyword = trim((string) ($filters['keyword'] ?? $filters['q'] ?? ''));
        $kelas = trim((string) ($filters['kelas'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return $this->baseQueryForViewer($viewer)
            ->when($keyword !== '', function (Builder $query) use ($keyword) {
                $query->where(function (Builder $builder) use ($keyword) {
                    $builder->where('nis', 'like', "%{$keyword}%")
                        ->orWhere('nisn', 'like', "%{$keyword}%")
                        ->orWhere('nama', 'like', "%{$keyword}%");
                });
            })
            ->when($kelas !== '', fn (Builder $query) => $query->where('kode_kelas', $kelas))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Siswa>
     */
    public function exportCollection(User $viewer, array $filters = []): Collection
    {
        $keyword = trim((string) ($filters['keyword'] ?? $filters['q'] ?? ''));
        $kelas = trim((string) ($filters['kelas'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return $this->baseQueryForViewer($viewer)
            ->when($keyword !== '', function (Builder $query) use ($keyword) {
                $query->where(function (Builder $builder) use ($keyword) {
                    $builder->where('nis', 'like', "%{$keyword}%")
                        ->orWhere('nisn', 'like', "%{$keyword}%")
                        ->orWhere('nama', 'like', "%{$keyword}%");
                });
            })
            ->when($kelas !== '', fn (Builder $query) => $query->where('kode_kelas', $kelas))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('nama')
            ->get();
    }

    public function exportExcel(User $viewer, array $filters = []): StreamedResponse
    {
        $rows = $this->exportCollection($viewer, $filters);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $headers = ['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kelas', 'Kode Kelas', 'Tahun Pelajaran', 'Status'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $rowNumber = 2;
        foreach ($rows as $index => $siswa) {
            $sheet->setCellValueByColumnAndRow(1, $rowNumber, $index + 1);
            $sheet->setCellValueByColumnAndRow(2, $rowNumber, $siswa->nis);
            $sheet->setCellValueByColumnAndRow(3, $rowNumber, $siswa->nisn);
            $sheet->setCellValueByColumnAndRow(4, $rowNumber, $siswa->nama);
            $sheet->setCellValueByColumnAndRow(5, $rowNumber, $siswa->jenis_kelamin);
            $sheet->setCellValueByColumnAndRow(6, $rowNumber, $siswa->kelasDisplay());
            $sheet->setCellValueByColumnAndRow(7, $rowNumber, $siswa->kode_kelas);
            $sheet->setCellValueByColumnAndRow(8, $rowNumber, $siswa->tahun_pelajaran);
            $sheet->setCellValueByColumnAndRow(9, $rowNumber, $siswa->statusLabel());
            $rowNumber++;
        }

        $filename = 'Data_Siswa_'.date('Y-m-d_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function assertCanView(User $viewer, Siswa $siswa): void
    {
        if (in_array($viewer->role, ['admin', 'kepala'], true)) {
            return;
        }

        if ($viewer->role !== 'guru') {
            abort(403);
        }

        $allowed = $this->accessibleKodeKelasForGuru($viewer);

        if ($allowed === []) {
            abort(403, 'Anda tidak memiliki kelas mengajar.');
        }

        if (! $this->siswaMatchesKodeKelasList($siswa, $allowed)) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }
    }

    public function create(array $data, User $admin, ?string $ipAddress = null): Siswa
    {
        $this->assertAdmin($admin);
        $payload = $this->preparePayload($data);

        return DB::transaction(function () use ($payload, $admin, $ipAddress) {
            $this->assertUniqueNis($payload['nis']);

            $siswa = Siswa::create($payload);

            $this->auditLogger->log($admin, 'CREATE', $siswa, null, $siswa->auditSnapshot(), $ipAddress);

            return $siswa;
        });
    }

    public function update(Siswa $siswa, array $data, User $admin, ?string $ipAddress = null): Siswa
    {
        $this->assertAdmin($admin);
        $payload = $this->preparePayload($data, $siswa);
        $oldData = $siswa->auditSnapshot();

        return DB::transaction(function () use ($siswa, $payload, $oldData, $admin, $ipAddress) {
            if ($payload['nis'] !== $siswa->nis) {
                $this->assertUniqueNis($payload['nis'], $siswa->id);
            }

            $siswa->update($payload);
            $siswa->refresh();

            $this->auditLogger->log($admin, 'UPDATE', $siswa, $oldData, $siswa->auditSnapshot(), $ipAddress);

            return $siswa;
        });
    }

    public function deactivate(Siswa $siswa, User $admin, ?string $ipAddress = null): Siswa
    {
        $this->assertAdmin($admin);
        $oldData = $siswa->auditSnapshot();

        return DB::transaction(function () use ($siswa, $oldData, $admin, $ipAddress) {
            $siswa->update(['status' => Siswa::STATUS_NONAKTIF]);
            $siswa->refresh();

            $this->auditLogger->log($admin, 'DEACTIVATE', $siswa, $oldData, $siswa->auditSnapshot(), $ipAddress);

            return $siswa;
        });
    }

    /** @return list<string> */
    public function kelasOptions(?User $viewer = null): array
    {
        $query = KodeKelas::query()->orderBy('kelas')->orderBy('rombel');

        if ($viewer && $viewer->role === 'guru') {
            $allowed = $this->accessibleKodeKelasForGuru($viewer);

            if ($allowed === []) {
                return [];
            }

            $query->where(function (Builder $builder) use ($allowed) {
                foreach ($allowed as $kode) {
                    $builder->orWhereRaw('LOWER(kode_kelas) = ?', [strtolower($kode)]);
                }
            });
        }

        return $query->pluck('kode_kelas')->all();
    }

    /** @return list<string> */
    public function statusOptions(): array
    {
        return Siswa::STATUSES;
    }

    private function baseQueryForViewer(User $viewer): Builder
    {
        $query = Siswa::query();

        if ($viewer->role === 'guru') {
            $allowed = $this->accessibleKodeKelasForGuru($viewer);

            if ($allowed === []) {
                return $query->whereRaw('1 = 0');
            }

            $query->where(function (Builder $builder) use ($allowed) {
                foreach ($allowed as $kode) {
                    $normalized = strtolower(trim($kode));
                    $builder->orWhereRaw('LOWER(kode_kelas) = ?', [$normalized]);
                }
            });
        }

        return $query;
    }

    /** @return list<string> */
    private function accessibleKodeKelasForGuru(User $viewer): array
    {
        $viewer->loadMissing('guru');

        if (! $viewer->guru) {
            return [];
        }

        return Jadwal::query()
            ->where('guru_id', $viewer->guru->id)
            ->distinct()
            ->pluck('kelas')
            ->filter()
            ->values()
            ->all();
    }

    /** @param  list<string>  $allowed */
    private function siswaMatchesKodeKelasList(Siswa $siswa, array $allowed): bool
    {
        foreach ($allowed as $kode) {
            if (strtolower($siswa->kode_kelas) === strtolower($kode)) {
                return true;
            }

            $resolved = $this->kodeKelasResolverService->resolveFromString($kode);

            if ($resolved && strtolower($resolved->kode_kelas) === strtolower($siswa->kode_kelas)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?Siswa $existing = null): array
    {
        $kodeKelas = KodeKelas::query()
            ->where('kode_kelas', $data['kode_kelas'])
            ->first();

        if (! $kodeKelas) {
            throw ValidationException::withMessages([
                'kode_kelas' => 'Kode kelas tidak ditemukan di master kelas.',
            ]);
        }

        return [
            'nis' => trim((string) $data['nis']),
            'nisn' => ($data['nisn'] ?? '') !== '' ? trim((string) $data['nisn']) : null,
            'nama' => trim((string) $data['nama']),
            'jenis_kelamin' => in_array($data['jenis_kelamin'] ?? null, ['L', 'P'], true) ? $data['jenis_kelamin'] : null,
            'tempat_lahir' => ($data['tempat_lahir'] ?? '') !== '' ? trim((string) $data['tempat_lahir']) : null,
            'tanggal_lahir' => ($data['tanggal_lahir'] ?? '') !== '' ? $data['tanggal_lahir'] : null,
            'kelas' => $kodeKelas->kelas,
            'rombel' => $kodeKelas->rombel,
            'kode_kelas' => $kodeKelas->kode_kelas,
            'tahun_pelajaran' => trim((string) ($data['tahun_pelajaran'] ?? self::DEFAULT_TAHUN_PELAJARAN)),
            'status' => in_array($data['status'] ?? null, Siswa::STATUSES, true)
                ? $data['status']
                : ($existing?->status ?? Siswa::STATUS_AKTIF),
        ];
    }

    private function assertUniqueNis(string $nis, ?int $ignoreId = null): void
    {
        $exists = Siswa::query()
            ->where('nis', $nis)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nis' => 'NIS sudah digunakan.',
            ]);
        }
    }

    private function assertAdmin(User $user): void
    {
        if ($user->role !== 'admin') {
            abort(403);
        }
    }
}
