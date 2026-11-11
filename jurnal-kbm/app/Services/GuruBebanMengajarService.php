<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GuruBebanMengajarService
{
    public function __construct(
        private GuruService $guruService,
        private GuruAccountService $guruAccountService,
    ) {}

    public function paginate(array $filters = [], ?string $tahunPelajaran = null): LengthAwarePaginator
    {
        $tahunPelajaran ??= GuruService::DEFAULT_TAHUN_PELAJARAN;

        $nama = trim((string) ($filters['nama'] ?? ''));
        $nip = trim((string) ($filters['nip'] ?? ''));
        $mapel = trim((string) ($filters['mapel'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return Guru::query()
            ->with(['user', 'jadwals' => fn ($query) => $query->where('tahun_pelajaran', $tahunPelajaran)])
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
            ->withQueryString()
            ->through(fn (Guru $guru) => $this->mapRekapItem($guru, $tahunPelajaran));
    }

    public function getCollection(array $filters = [], ?string $tahunPelajaran = null): Collection
    {
        $tahunPelajaran ??= GuruService::DEFAULT_TAHUN_PELAJARAN;

        return Guru::query()
            ->with('user')
            ->orderBy('kode_guru')
            ->get()
            ->map(fn (Guru $guru) => $this->mapRekapItem($guru, $tahunPelajaran));
    }

    public function mapelOptions(): array
    {
        return $this->guruService->mapelOptions();
    }

    private function mapRekapItem(Guru $guru, string $tahunPelajaran): array
    {
        $jadwals = Jadwal::query()
            ->where('guru_id', $guru->id)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->get();

        $jumlahJam = (int) $jadwals->sum('jam_ke');

        if ($jumlahJam === 0) {
            $jumlahJam = $jadwals->count();
        }

        $jumlahKelas = $jadwals->pluck('kelas')->unique()->count();
        $jumlahJurnal = Jurnal::query()
            ->whereHas('jadwal', function ($query) use ($guru, $tahunPelajaran) {
                $query->where('guru_id', $guru->id)
                    ->where('tahun_pelajaran', $tahunPelajaran);
            })
            ->count();

        $targetJurnal = max($jadwals->count(), 1);
        $progressKbm = min(100, round(($jumlahJurnal / $targetJurnal) * 100, 1));

        return [
            'guru' => $guru,
            'kode_guru' => $guru->kode_guru,
            'nama' => $guru->displayName(),
            'nip' => $guru->nip,
            'mapel' => $guru->mapel,
            'jumlah_jam_mengajar' => $jumlahJam,
            'jumlah_kelas' => $jumlahKelas,
            'status' => $this->guruAccountService->accountStatusLabel($guru),
            'jumlah_jurnal' => $jumlahJurnal,
            'progress_kbm' => $progressKbm,
        ];
    }
}
