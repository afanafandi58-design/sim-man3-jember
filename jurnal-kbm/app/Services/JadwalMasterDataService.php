<?php

namespace App\Services;

use App\Models\JamPelajaran;
use App\Models\KodeKelas;
use App\Models\MataPelajaran;
use App\Models\PolaJadwal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class JadwalMasterDataService
{
    public function __construct(
        private PolaJadwalService $polaJadwalService,
    ) {}

    public function kelasOptions(): Collection
    {
        return KodeKelas::query()
            ->orderBy('kelas')
            ->orderBy('rombel')
            ->get();
    }

    public function mapelOptions(): Collection
    {
        return MataPelajaran::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();
    }

    public function jamOptions(?int $polaJadwalId = null): Collection
    {
        return JamPelajaran::query()
            ->with('polaJadwal')
            ->aktif()
            ->when($polaJadwalId, fn ($q) => $q->where('pola_jadwal_id', $polaJadwalId))
            ->orderBy('jam_ke')
            ->get();
    }

    public function jamMapByPolaForJs(): array
    {
        $map = [];

        JamPelajaran::query()
            ->with('polaJadwal')
            ->aktif()
            ->orderBy('pola_jadwal_id')
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('pola_jadwal_id')
            ->each(function ($jams, $polaId) use (&$map) {
                $map[(string) $polaId] = $jams->mapWithKeys(fn (JamPelajaran $jam) => [
                    (string) $jam->jam_ke => [
                        'jam_mulai' => $this->formatTime($jam->jam_mulai),
                        'jam_selesai' => $this->formatTime($jam->jam_selesai),
                        'nama_sesi' => $jam->nama_sesi,
                        'label' => $jam->label,
                    ],
                ])->all();
            });

        return $map;
    }

    public function polaHariMapForJs(): array
    {
        return $this->polaJadwalService->polaHariMapForJs();
    }

    public function resolvePolaFromHari(string $hari): PolaJadwal
    {
        return $this->polaJadwalService->resolveFromHari($hari);
    }

    public function resolveKelas(string $kodeKelas): string
    {
        $record = KodeKelas::query()
            ->where('kode_kelas', $kodeKelas)
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'kode_kelas' => 'Kelas yang dipilih tidak valid.',
            ]);
        }

        return $record->kode_kelas;
    }

    public function resolveMapel(string $mapel, ?string $mapelBaru = null): string
    {
        if ($mapel === '__lainnya__') {
            throw ValidationException::withMessages([
                'mapel' => 'Mapel baru harus diajukan melalui usulan dan disetujui admin terlebih dahulu.',
            ]);
        }

        $master = MataPelajaran::query()
            ->where('is_active', true)
            ->where('nama', $mapel)
            ->first();

        if (! $master) {
            throw ValidationException::withMessages([
                'mapel' => 'Mata pelajaran yang dipilih tidak valid.',
            ]);
        }

        return $master->nama;
    }

    public function resolveJam(int $jamKe, int $polaJadwalId): array
    {
        $jam = JamPelajaran::query()
            ->aktif()
            ->where('pola_jadwal_id', $polaJadwalId)
            ->where('jam_ke', $jamKe)
            ->first();

        if (! $jam) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Jam pelajaran yang dipilih tidak valid untuk pola jadwal ini.',
            ]);
        }

        return [
            'jam_ke' => $jam->jam_ke,
            'jam_mulai' => $this->formatTime($jam->jam_mulai),
            'jam_selesai' => $this->formatTime($jam->jam_selesai),
            'pola_jadwal_id' => $polaJadwalId,
        ];
    }

    /**
     * @return array{jam_ke: int, jam_mulai: string, jam_selesai: string, pola_jadwal_id: int}
     */
    public function resolveJamRange(int $jamAwal, int $jamAkhir, int $polaJadwalId): array
    {
        if ($jamAkhir < $jamAwal) {
            throw ValidationException::withMessages([
                'jam_ke_akhir' => 'Jam Akhir tidak boleh lebih kecil dari Jam Awal.',
            ]);
        }

        for ($ke = $jamAwal; $ke <= $jamAkhir; $ke++) {
            $this->resolveJam($ke, $polaJadwalId);
        }

        $first = $this->resolveJam($jamAwal, $polaJadwalId);
        $last = $this->resolveJam($jamAkhir, $polaJadwalId);

        return [
            'jam_ke' => (int) $first['jam_ke'],
            'jam_mulai' => $first['jam_mulai'],
            'jam_selesai' => $last['jam_selesai'],
            'pola_jadwal_id' => $polaJadwalId,
        ];
    }

    public function jamRangeFormData(): array
    {
        return [
            'polaHariMap' => $this->polaHariMapForJs(),
            'jamMapByPola' => $this->jamMapByPolaForJs(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mergeJamRangeIntoData(array $data): array
    {
        $pola = $this->resolvePolaFromHari($data['hari']);
        $jamAwal = (int) $data['jam_ke'];
        $jamAkhir = (int) ($data['jam_ke_akhir'] ?? $jamAwal);
        $range = $this->resolveJamRange($jamAwal, $jamAkhir, $pola->id);

        return array_merge($data, [
            'pola_jadwal_id' => $range['pola_jadwal_id'],
            'jam_ke' => $range['jam_ke'],
            'jam_mulai' => $range['jam_mulai'],
            'jam_selesai' => $range['jam_selesai'],
        ]);
    }

    public function findKodeKelasValue(?string $kelasStored): ?string
    {
        if (! $kelasStored) {
            return null;
        }

        $exact = KodeKelas::query()
            ->where('kode_kelas', $kelasStored)
            ->value('kode_kelas');

        if ($exact) {
            return $exact;
        }

        $resolver = app(KodeKelasResolverService::class);
        $resolved = $resolver->resolveFromString($kelasStored);

        return $resolved?->kode_kelas;
    }

    private function formatTime(string $time): string
    {
        return substr($time, 0, 5);
    }
}
