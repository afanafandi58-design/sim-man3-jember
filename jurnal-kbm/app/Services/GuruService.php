<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruService
{
    public const DEFAULT_TAHUN_PELAJARAN = '2026/2027';

    public function __construct(
        private GuruAccountService $guruAccountService,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $nama = trim((string) ($filters['nama'] ?? ''));
        $nip = trim((string) ($filters['nip'] ?? ''));
        $mapel = trim((string) ($filters['mapel'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return Guru::query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($builder) use ($keyword) {
                    $builder->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('nama_tampilan', 'like', "%{$keyword}%")
                        ->orWhere('nip', 'like', "%{$keyword}%")
                        ->orWhere('kode_guru', 'like', "%{$keyword}%")
                        ->orWhere('mapel', 'like', "%{$keyword}%");
                });
            })
            ->when($nama !== '', function ($query) use ($nama) {
                $query->where(function ($builder) use ($nama) {
                    $builder->where('nama', 'like', "%{$nama}%")
                        ->orWhere('nama_tampilan', 'like', "%{$nama}%");
                });
            })
            ->when($nip !== '', fn ($query) => $query->where('nip', 'like', "%{$nip}%"))
            ->when($mapel !== '', fn ($query) => $query->where('mapel', $mapel))
            ->when($status === 'belum', fn ($query) => $query->whereDoesntHave('user'))
            ->when($status === 'aktif', fn ($query) => $query->whereHas('user', fn ($user) => $user->where('is_active', true)))
            ->when($status === 'nonaktif', fn ($query) => $query->whereHas('user', fn ($user) => $user->where('is_active', false)))
            ->orderBy('kode_guru')
            ->paginate(10)
            ->withQueryString();
    }

    public function mapelOptions(): array
    {
        return Guru::query()
            ->distinct()
            ->orderBy('mapel')
            ->pluck('mapel')
            ->filter()
            ->values()
            ->all();
    }

    public function create(array $data, bool $provisionAccount = true): Guru
    {
        $data = $this->prepareGuruData($data);

        return DB::transaction(function () use ($data, $provisionAccount) {
            if ($data['nip'] !== null && $data['nip'] !== '') {
                $this->assertUniqueNip($data['nip']);
            }

            $guru = Guru::create($data);
            $this->syncJumlahJamMengajar($guru);

            if ($provisionAccount) {
                $this->guruAccountService->provisionFromImport($guru);
            }

            return $guru->refresh();
        });
    }

    public function update(Guru $guru, array $data, bool $syncAccount = true): Guru
    {
        $data = $this->normalizeData($data);

        return DB::transaction(function () use ($guru, $data, $syncAccount) {
            if ($data['nip'] !== $guru->nip) {
                $this->assertUniqueNip($data['nip'], $guru->id);
            }

            $guru->update($data);
            $this->syncJumlahJamMengajar($guru);

            if ($syncAccount) {
                $this->guruAccountService->syncAccountProfile($guru->refresh());
            }

            return $guru;
        });
    }

    public function upsertFromImport(array $data): Guru
    {
        $data = $this->normalizeData($data);

        if ($data['nip'] === null) {
            return $this->create($data, true);
        }

        $existing = Guru::query()->where('nip', $data['nip'])->first();

        if ($existing) {
            return DB::transaction(function () use ($existing, $data) {
                $updates = [
                    'nama' => $data['nama'],
                ];

                if (! $existing->kode_guru) {
                    $updates['kode_guru'] = $this->generateNextKode();
                }

                $existing->update($updates);

                $this->syncJumlahJamMengajar($existing);

                if (! $existing->user) {
                    $this->guruAccountService->provisionFromImport($existing->refresh());
                } else {
                    $this->guruAccountService->syncAccountProfile($existing->refresh());
                }

                return $existing->refresh();
            });
        }

        return $this->create($data, true);
    }

    public function delete(Guru $guru): void
    {
        DB::transaction(function () use ($guru) {
            User::query()->where('guru_id', $guru->id)->delete();
            $guru->delete();
        });
    }

    public function generateNextKode(): string
    {
        $maxNumber = Guru::query()
            ->whereNotNull('kode_guru')
            ->pluck('kode_guru')
            ->map(fn (string $kode) => (int) preg_replace('/\D/', '', $kode))
            ->max() ?? 0;

        return 'G'.str_pad((string) ($maxNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    public function syncJumlahJamMengajar(Guru $guru, ?string $tahunPelajaran = null): void
    {
        $tahunPelajaran ??= self::DEFAULT_TAHUN_PELAJARAN;

        $totalJam = Jadwal::query()
            ->where('guru_id', $guru->id)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->sum('jam_ke');

        if ($totalJam === 0) {
            $totalJam = Jadwal::query()
                ->where('guru_id', $guru->id)
                ->where('tahun_pelajaran', $tahunPelajaran)
                ->count();
        }

        $guru->update(['jumlah_jam_mengajar' => (int) $totalJam]);
    }

    public function syncAllJumlahJamMengajar(?string $tahunPelajaran = null): void
    {
        Guru::query()->each(fn (Guru $guru) => $this->syncJumlahJamMengajar($guru, $tahunPelajaran));
    }

    private function prepareGuruData(array $data): array
    {
        $data = $this->normalizeData($data);
        $duplicate = $this->guruAccountService->resolveDuplicateIdentity($data['nama']);

        return array_merge($data, [
            'kode_guru' => $this->generateNextKode(),
            'nama_tampilan' => $duplicate['nama_tampilan'],
            'username_suffix' => $duplicate['username_suffix'],
        ]);
    }

    private function normalizeData(array $data): array
    {
        return [
            'nama' => trim((string) ($data['nama'] ?? '')),
            'nip' => $this->normalizeNipValue(array_key_exists('nip', $data) ? $data['nip'] : null),
            'mapel' => trim((string) ($data['mapel'] ?? '')) ?: '-',
        ];
    }

    private function normalizeNipValue(mixed $nip): ?string
    {
        if ($nip === null) {
            return null;
        }

        $nip = trim((string) $nip);
        $nip = preg_replace('/\s+/u', '', $nip);

        if ($nip === '') {
            return null;
        }

        if (is_numeric($nip) && str_contains($nip, '.')) {
            return number_format((float) $nip, 0, '', '');
        }

        return $nip;
    }

    private function assertUniqueNip(?string $nip, ?int $ignoreId = null): void
    {
        if ($nip === null || $nip === '') {
            return;
        }
        $exists = Guru::query()
            ->where('nip', $nip)
            ->when($ignoreId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'nip' => "NIP \"{$nip}\" sudah terdaftar.",
            ]);
        }
    }
}
