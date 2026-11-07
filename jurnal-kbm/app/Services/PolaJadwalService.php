<?php

namespace App\Services;

use App\Models\PolaJadwal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PolaJadwalService
{
    public function allAktif(): Collection
    {
        return PolaJadwal::query()->aktif()->orderBy('kode')->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        return PolaJadwal::query()->orderBy('kode')->paginate(20);
    }

    public function resolveKodeFromHari(string $hari): string
    {
        return $hari === 'Jumat' ? PolaJadwal::KODE_JUM : PolaJadwal::KODE_REG;
    }

    public function resolveFromHari(string $hari): PolaJadwal
    {
        $kode = $this->resolveKodeFromHari($hari);

        $pola = PolaJadwal::query()->aktif()->where('kode', $kode)->first();

        if (! $pola) {
            $pola = $this->getDefaultReg();
        }

        if (! $pola) {
            throw ValidationException::withMessages([
                'hari' => 'Master Pola Jadwal belum tersedia. Hubungi admin.',
            ]);
        }

        return $pola;
    }

    public function getDefaultReg(): ?PolaJadwal
    {
        return PolaJadwal::query()
            ->where('kode', PolaJadwal::KODE_REG)
            ->first();
    }

    public function polaHariMapForJs(): array
    {
        $map = [];
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($hariList as $hari) {
            $kode = $this->resolveKodeFromHari($hari);
            $pola = PolaJadwal::query()->aktif()->where('kode', $kode)->first();

            $map[$hari] = [
                'kode' => $kode,
                'nama' => $pola?->nama ?? ($kode === 'JUM' ? 'Jumat' : 'Reguler Senin–Kamis'),
                'pola_id' => $pola?->id,
            ];
        }

        return $map;
    }

    public function store(array $data): PolaJadwal
    {
        $this->assertUniqueKode($data['kode']);

        return PolaJadwal::create([
            'kode' => strtoupper(trim($data['kode'])),
            'nama' => trim($data['nama']),
            'status' => $data['status'] ?? PolaJadwal::STATUS_AKTIF,
        ]);
    }

    public function update(PolaJadwal $polaJadwal, array $data): PolaJadwal
    {
        $polaJadwal->update([
            'nama' => trim($data['nama']),
            'status' => $data['status'] ?? $polaJadwal->status,
        ]);

        return $polaJadwal->fresh();
    }

    private function assertUniqueKode(string $kode): void
    {
        if (PolaJadwal::query()->where('kode', strtoupper(trim($kode)))->exists()) {
            throw ValidationException::withMessages([
                'kode' => 'Kode pola jadwal sudah digunakan.',
            ]);
        }
    }
}
