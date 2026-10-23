<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OperationalDateService
{
    /** @var array<string, string> */
    private const HARI_INDONESIA = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    public function today(): Carbon
    {
        return Carbon::today();
    }

    public function hari(): string
    {
        if ($this->isSimulation()) {
            return (string) config('operational_date.uat.hari', 'Senin');
        }

        return $this->resolveHariIndonesia($this->today());
    }

    public function bulan(): string
    {
        return $this->today()->translatedFormat('F');
    }

    public function tahun(): int
    {
        return (int) $this->today()->year;
    }

    public function semester(): string
    {
        return (string) config('operational_date.default_semester', 'Ganjil');
    }

    public function tahunPelajaran(): string
    {
        return (string) config('operational_date.default_tahun_pelajaran', '2026/2027');
    }

    public function isSimulation(): bool
    {
        if (! config('operational_date.uat.enabled', false)) {
            return false;
        }

        $user = Auth::user();

        if (! $user || ! $user->guru_id) {
            return false;
        }

        return $this->isUatSubject($user);
    }

    public function showBadge(): bool
    {
        return $this->isSimulation();
    }

    public function tanggalSistemFormatted(): string
    {
        return $this->today()->translatedFormat('d F Y');
    }

    public function context(): array
    {
        return [
            'tanggalSistem' => $this->tanggalSistemFormatted(),
            'hariOperasional' => $this->hari(),
            'bulan' => $this->bulan(),
            'tahun' => $this->tahun(),
            'semester' => $this->semester(),
            'tahunPelajaran' => $this->tahunPelajaran(),
            'isSimulation' => $this->isSimulation(),
            'showBadge' => $this->showBadge(),
            'showWatermark' => $this->isSimulation(),
        ];
    }

    public function logUatLogin(User $user): void
    {
        if (! $this->isUatSubject($user)) {
            return;
        }

        $user->loadMissing('guru');
        $guru = $user->guru;

        $lines = [
            '[UAT]',
            'Guru : '.($guru?->nama ?? $user->name),
            'NIP : '.($guru?->nip ?? '-'),
            'Hari Operasional : '.$this->hari(),
            'Tanggal Sistem : '.$this->tanggalSistemFormatted(),
            'Jam Login : '.Carbon::now()->format('H.i').' WIB',
        ];

        Log::channel('uat')->info(implode(PHP_EOL, $lines));
    }

    public function isUatSubject(User $user): bool
    {
        if (! $user->guru_id) {
            return false;
        }

        $user->loadMissing('guru');
        $guru = $user->guru;

        if (! $guru) {
            return false;
        }

        $configNip = trim((string) config('operational_date.uat.guru_nip', ''));

        if ($configNip !== '') {
            return trim((string) $guru->nip) === $configNip;
        }

        $configNama = trim((string) config('operational_date.uat.guru_nama', ''));

        if ($configNama !== '') {
            return stripos($guru->nama, $configNama) !== false;
        }

        return false;
    }

    private function resolveHariIndonesia(Carbon $date): string
    {
        return self::HARI_INDONESIA[$date->format('l')] ?? $date->format('l');
    }
}
