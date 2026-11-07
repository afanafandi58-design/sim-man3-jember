<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use App\Support\TitikESchema;
use Illuminate\Support\Collection;

class GuruDashboardService
{
    public function __construct(
        private OperationalDateService $operationalDate,
    ) {}

    public function getDashboardData(User $user): array
    {
        TitikESchema::ensure();

        $guru = $user->guru;

        if (! $guru) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $hariOperasional = $this->operationalDate->hari();
        $today = $this->operationalDate->today()->toDateString();
        $yesterday = $this->operationalDate->earliestFillableDate()->toDateString();

        // Tidak dikunci ke hari master: tampilkan semua jadwal milik guru + sorot yang hari-nya cocok.
        $semuaJadwal = Jadwal::query()
            ->with('guru')
            ->where('guru_id', $guru->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $jadwalsHariIni = $semuaJadwal
            ->filter(fn (Jadwal $j) => strcasecmp((string) $j->hari, $hariOperasional) === 0)
            ->values();

        $jurnalTodayMap = $this->getJurnalMap($semuaJadwal->pluck('id'), $today);
        $jurnalYesterdayMap = $this->getJurnalMap($semuaJadwal->pluck('id'), $yesterday);

        $annotate = function (Collection $items) use ($jurnalTodayMap, $jurnalYesterdayMap, $today, $yesterday) {
            return $items->map(function (Jadwal $jadwal) use ($jurnalTodayMap, $jurnalYesterdayMap, $today, $yesterday) {
                $jurnalToday = $jurnalTodayMap->get($jadwal->id);
                $jurnalYesterday = $jurnalYesterdayMap->get($jadwal->id);
                $jadwal->sudah_diisi = $jurnalToday !== null;
                $jadwal->jurnal_id = $jurnalToday?->id;
                $jadwal->jurnal_kemarin_id = $jurnalYesterday?->id;
                $jadwal->bisa_isi_hari_ini = $jurnalToday === null;
                $jadwal->bisa_isi_kemarin = $jurnalYesterday === null;
                $jadwal->default_tanggal = $today;

                return $jadwal;
            });
        };

        $jadwals = $annotate($jadwalsHariIni);
        $jadwalLain = $annotate(
            $semuaJadwal->reject(fn (Jadwal $j) => strcasecmp((string) $j->hari, $hariOperasional) === 0)->values()
        );

        // Mode pengganti: jadwal guru lain (hari operasional + kemarin) yang belum diisi hari ini.
        $hariKemarinId = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ][$this->operationalDate->earliestFillableDate()->format('l')] ?? $hariOperasional;

        $jadwalPengganti = Jadwal::query()
            ->with('guru')
            ->where('guru_id', '!=', $guru->id)
            ->where(function ($q) use ($hariOperasional, $hariKemarinId) {
                $q->whereRaw('LOWER(hari) = ?', [strtolower($hariOperasional)])
                    ->orWhereRaw('LOWER(hari) = ?', [strtolower($hariKemarinId)]);
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->limit(40)
            ->get()
            ->map(function (Jadwal $jadwal) use ($jurnalTodayMap, $today) {
                // map only for own ids above — reload for these
                $exists = Jurnal::query()
                    ->where('jadwal_id', $jadwal->id)
                    ->whereDate('tanggal', $today)
                    ->exists();
                $jadwal->sudah_diisi = $exists;
                $jadwal->bisa_isi_hari_ini = ! $exists;
                $jadwal->default_tanggal = $today;

                return $jadwal;
            })
            ->filter(fn (Jadwal $j) => $j->bisa_isi_hari_ini)
            ->values();

        $riwayatJurnal = Jurnal::query()
            ->with('jadwal.guru')
            ->where(function ($q) use ($guru) {
                $q->whereHas('jadwal', fn ($jq) => $jq->where('guru_id', $guru->id))
                    ->orWhere('guru_pengganti', $guru->nama);
            })
            ->latest('tanggal')
            ->limit(8)
            ->get();

        return [
            'guru' => $guru,
            'hari' => $hariOperasional,
            'jadwals' => $jadwals,
            'jadwalLain' => $jadwalLain,
            'jadwalPengganti' => $jadwalPengganti,
            'jadwalHariIni' => $jadwals->count(),
            'sudahIsi' => $jadwals->where('sudah_diisi', true)->count(),
            'belumIsi' => $jadwals->where('sudah_diisi', false)->count(),
            'totalJurnal' => Jurnal::whereHas('jadwal', fn ($query) => $query->where('guru_id', $guru->id))->count(),
            'riwayatJurnal' => $riwayatJurnal,
            'operationalDate' => $this->operationalDate->context(),
            'jurnalRulesNotice' => $this->operationalDate->jurnalRulesNotice(),
            'fillableDates' => $this->operationalDate->fillableDateStrings(),
            'tanggalHariIni' => $today,
            'tanggalKemarin' => $yesterday,
        ];
    }

    private function getJurnalMap(Collection $jadwalIds, string $date): Collection
    {
        if ($jadwalIds->isEmpty()) {
            return collect();
        }

        return Jurnal::query()
            ->whereIn('jadwal_id', $jadwalIds)
            ->whereDate('tanggal', $date)
            ->get()
            ->keyBy('jadwal_id');
    }
}
