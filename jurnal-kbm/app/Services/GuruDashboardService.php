<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Collection;

class GuruDashboardService
{
    public function __construct(
        private OperationalDateService $operationalDate,
    ) {}

    public function getDashboardData(User $user): array
    {
        $guru = $user->guru;

        if (! $guru) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $hariOperasional = $this->operationalDate->hari();
        $jadwals = $this->getJadwalHariIni($guru->id, $hariOperasional);
        $jurnalHariIni = $this->getJurnalHariIniMap($jadwals->pluck('id'));

        $jadwals = $jadwals->map(function (Jadwal $jadwal) use ($jurnalHariIni) {
            $jurnal = $jurnalHariIni->get($jadwal->id);
            $jadwal->sudah_diisi = $jurnal !== null;
            $jadwal->jurnal_id = $jurnal?->id;

            return $jadwal;
        });

        $riwayatJurnal = Jurnal::query()
            ->with('jadwal')
            ->whereHas('jadwal', fn ($query) => $query->where('guru_id', $guru->id))
            ->latest('tanggal')
            ->limit(5)
            ->get();

        return [
            'guru' => $guru,
            'hari' => $hariOperasional,
            'jadwals' => $jadwals,
            'jadwalHariIni' => $jadwals->count(),
            'sudahIsi' => $jadwals->where('sudah_diisi', true)->count(),
            'belumIsi' => $jadwals->where('sudah_diisi', false)->count(),
            'totalJurnal' => Jurnal::whereHas('jadwal', fn ($query) => $query->where('guru_id', $guru->id))->count(),
            'riwayatJurnal' => $riwayatJurnal,
            'operationalDate' => $this->operationalDate->context(),
        ];
    }

    private function getJadwalHariIni(int $guruId, string $hariOperasional): Collection
    {
        return Jadwal::query()
            ->where('guru_id', $guruId)
            ->whereRaw('LOWER(hari) = ?', [strtolower($hariOperasional)])
            ->orderBy('jam_mulai')
            ->get();
    }

    private function getJurnalHariIniMap(Collection $jadwalIds): Collection
    {
        if ($jadwalIds->isEmpty()) {
            return collect();
        }

        return Jurnal::query()
            ->whereIn('jadwal_id', $jadwalIds)
            ->whereDate('tanggal', $this->operationalDate->today())
            ->get()
            ->keyBy('jadwal_id');
    }
}
