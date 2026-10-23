<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected Carbon $hariIni;

    protected string $hariSekarang;

    public function __construct()
    {
        $this->hariIni = Carbon::today();

        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $this->hariSekarang = $namaHari[$this->hariIni->format('l')];
    }

    public function getDashboardData(): array
    {
        return array_merge(
            $this->getKPI(),
            $this->getGuruKPI(),
            $this->getMonitoring(),
            $this->getGrafikGuru(),
            $this->getGrafikKelas(),
            $this->getGrafikMapel()
        );
    }

    private function getKPI(): array
    {
        $jumlahGuru = Guru::count();
        $jumlahJadwal = Jadwal::count();
        $jumlahJurnal = Jurnal::count();
        $jurnalHariIni = Jurnal::whereDate('tanggal', $this->hariIni)->count();
        $kbmSelesai = Jurnal::whereDate('tanggal', $this->hariIni)
            ->where('status_kbm', 'Selesai')
            ->count();
        $kbmBelum = Jadwal::whereRaw('LOWER(hari)=?', [strtolower($this->hariSekarang)])
            ->whereDoesntHave('jurnals', fn ($query) => $query->whereDate('tanggal', $this->hariIni))
            ->count();

        return [
            'jumlahGuru' => $jumlahGuru,
            'jumlahJadwal' => $jumlahJadwal,
            'jumlahJurnal' => $jumlahJurnal,
            'jurnalHariIni' => $jurnalHariIni,
            'kbmSelesai' => $kbmSelesai,
            'kbmBelum' => $kbmBelum,
        ];
    }

    private function getGuruKPI(): array
    {
        $guruAktif = User::query()
            ->where('role', 'guru')
            ->where('is_active', true)
            ->count();

        $guruBelumPunyaAkun = Guru::query()->whereDoesntHave('user')->count();

        $guruSudahLogin = User::query()
            ->where('role', 'guru')
            ->whereNotNull('last_login_at')
            ->count();

        $guruBelumLogin = User::query()
            ->where('role', 'guru')
            ->whereNull('last_login_at')
            ->count();

        return [
            'guruAktif' => $guruAktif,
            'guruBelumPunyaAkun' => $guruBelumPunyaAkun,
            'guruSudahLogin' => $guruSudahLogin,
            'guruBelumLogin' => $guruBelumLogin,
        ];
    }

    private function getMonitoring(): array
    {
        $belumIsiJurnal = Jadwal::with('guru')
            ->whereRaw('LOWER(hari)=?', [strtolower($this->hariSekarang)])
            ->whereDoesntHave('jurnals', fn ($query) => $query->whereDate('tanggal', $this->hariIni))
            ->orderBy('jam_mulai')
            ->get();

        return [
            'belumIsiJurnal' => $belumIsiJurnal,
        ];
    }

    private function getGrafikGuru(): array
    {
        $grafikGuru = Jurnal::select('gurus.nama', DB::raw('COUNT(jurnals.id) as total'))
            ->join('jadwals', 'jurnals.jadwal_id', '=', 'jadwals.id')
            ->join('gurus', 'jadwals.guru_id', '=', 'gurus.id')
            ->groupBy('gurus.nama')
            ->orderByDesc('total')
            ->get();

        return [
            'labelGuru' => $grafikGuru->pluck('nama'),
            'dataGuru' => $grafikGuru->pluck('total'),
        ];
    }

    private function getGrafikKelas(): array
    {
        return [
            'labelKelas' => collect(),
            'dataKelas' => collect(),
        ];
    }

    private function getGrafikMapel(): array
    {
        return [
            'labelMapel' => collect(),
            'dataMapel' => collect(),
        ];
    }
}
