<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;

class SiswaService
{
    public function __construct(
        private KodeKelasResolverService $kodeKelasResolverService,
    ) {}

    /**
     * @return Collection<int, Siswa>
     */
    public function forJadwal(Jadwal $jadwal): Collection
    {
        $meta = $this->kodeKelasResolverService->resolveMeta($jadwal);

        $query = Siswa::query()
            ->aktif()
            ->orderBy('nama');

        if ($meta['kode_kelas'] && $meta['kode_kelas'] !== $jadwal->kelas) {
            $byKodeKelas = (clone $query)->where('kode_kelas', $meta['kode_kelas'])->get();

            if ($byKodeKelas->isNotEmpty()) {
                return $byKodeKelas;
            }
        }

        return $query
            ->where(function ($builder) use ($meta, $jadwal) {
                $builder
                    ->where('kode_kelas', $jadwal->kelas)
                    ->orWhere(function ($nested) use ($meta) {
                        $nested
                            ->where('kelas', $meta['kelas'])
                            ->where('rombel', $meta['rombel']);
                    });
            })
            ->get();
    }
}
