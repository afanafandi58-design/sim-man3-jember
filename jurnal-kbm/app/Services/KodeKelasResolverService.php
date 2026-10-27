<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\KodeKelas;

class KodeKelasResolverService
{
    public function resolveFromString(string $kelas): ?KodeKelas
    {
        $kelasJadwal = trim($kelas);

        if ($kelasJadwal === '') {
            return null;
        }

        $normalized = $this->normalize($kelasJadwal);

        $exact = KodeKelas::query()
            ->whereRaw('LOWER(kode_kelas) = ?', [$normalized])
            ->first();

        if ($exact) {
            return $exact;
        }

        return KodeKelas::query()
            ->get()
            ->first(function (KodeKelas $kodeKelas) use ($normalized) {
                $candidates = [
                    $this->normalize($kodeKelas->kode_kelas),
                    $this->normalize($kodeKelas->rombel),
                    $this->normalize($kodeKelas->kelas.' '.$kodeKelas->rombel),
                    $this->normalize($kodeKelas->kelas.'-'.$kodeKelas->rombel),
                    $this->normalize($kodeKelas->kelas.$kodeKelas->rombel),
                ];

                return in_array($normalized, $candidates, true);
            });
    }

    public function resolve(Jadwal $jadwal): ?KodeKelas
    {
        return $this->resolveFromString((string) $jadwal->kelas);
    }

    public function resolveMeta(Jadwal $jadwal): array
    {
        $kodeKelas = $this->resolve($jadwal);

        if ($kodeKelas) {
            return [
                'kode_kelas_id' => $kodeKelas->id,
                'kode_kelas' => $kodeKelas->kode_kelas,
                'kelas' => $kodeKelas->kelas,
                'rombel' => $kodeKelas->rombel,
            ];
        }

        return [
            'kode_kelas_id' => null,
            'kode_kelas' => $jadwal->kelas,
            'kelas' => $jadwal->kelas,
            'rombel' => $jadwal->kelas,
        ];
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }
}
