<?php

namespace App\Http\Controllers\Lms\Concerns;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Collection;

trait InteractsWithLmsJadwal
{
    /** @return Collection<int, Jadwal> */
    protected function jadwalOptionsFor(User $user): Collection
    {
        if ($user->role === 'admin') {
            return Jadwal::query()->with('guru')->orderBy('kelas')->orderBy('mapel')->get();
        }

        if ($user->guru_id) {
            return Jadwal::query()
                ->where('guru_id', $user->guru_id)
                ->orderBy('kelas')
                ->orderBy('mapel')
                ->get();
        }

        return collect();
    }

    /** @param  array<string, mixed>  $data */
    protected function applyJadwalMeta(array &$data): void
    {
        if (empty($data['jadwal_id'])) {
            return;
        }

        $jadwal = Jadwal::query()->find($data['jadwal_id']);
        if (! $jadwal) {
            return;
        }

        $data['kelas'] = $jadwal->kelas;
        $data['mapel'] = $jadwal->mapel;
        $data['tahun_pelajaran'] = $jadwal->tahun_pelajaran;
    }
}
