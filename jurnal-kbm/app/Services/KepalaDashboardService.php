<?php

namespace App\Services;

use App\Repositories\KepalaMonitoringRepository;
use Carbon\Carbon;

class KepalaDashboardService
{
    private Carbon $tanggal;

    private string $hariSekarang;

    public function __construct(
        private KepalaMonitoringRepository $repository,
    ) {
        $this->tanggal = Carbon::today();
        $this->hariSekarang = $this->resolveHariIndonesia($this->tanggal);
    }

    public function getSummary(): array
    {
        return [
            'tanggal' => $this->tanggal,
            'hari' => $this->hariSekarang,
            'jumlahGuru' => $this->repository->countGuru(),
            'guruMengajar' => $this->repository->countGuruMengajarHariIni($this->hariSekarang),
            'sudahMengisi' => $this->repository->countSudahMengisi($this->tanggal, $this->hariSekarang),
            'belumMengisi' => $this->repository->countBelumMengisi($this->tanggal, $this->hariSekarang),
        ];
    }

    public function getDetail(string $type): array
    {
        $titles = [
            'guru-all' => 'Semua Guru',
            'guru-mengajar' => 'Guru Mengajar Hari Ini',
            'sudah-mengisi' => 'Guru Sudah Mengisi Jurnal',
            'belum-mengisi' => 'Guru Belum Mengisi Jurnal',
        ];

        if (! array_key_exists($type, $titles)) {
            abort(404);
        }

        $data = match ($type) {
            'guru-all' => $this->repository->getAllGuru(),
            'guru-mengajar' => $this->repository->getGuruMengajarHariIni($this->hariSekarang),
            'sudah-mengisi' => $this->repository->getSudahMengisi($this->tanggal, $this->hariSekarang),
            'belum-mengisi' => $this->repository->getBelumMengisi($this->tanggal, $this->hariSekarang),
        };

        return [
            'type' => $type,
            'title' => $titles[$type],
            'hari' => $this->hariSekarang,
            'tanggal' => $this->tanggal,
            'items' => $data,
        ];
    }

    private function resolveHariIndonesia(Carbon $date): string
    {
        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $namaHari[$date->format('l')];
    }
}
