<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class JurnalJamSelectionService
{
    public function __construct(
        private JadwalMasterDataService $jadwalMasterDataService,
    ) {}

    /**
     * @param  list<int|string>  $selectedJp
     * @return array{jam_ke: int, jam_mulai: string, jam_selesai: string, jp_count: int, jp_label: string}
     */
    public function resolveFromSelection(array $selectedJp, Jadwal $jadwal): array
    {
        $normalized = $this->normalizeSelection($selectedJp);
        $this->assertConsecutive($normalized);

        $polaId = (int) ($jadwal->pola_jadwal_id ?? 0);
        $first = (int) $normalized[0];
        $last = (int) $normalized[array_key_last($normalized)];

        // Fleksibel: bila pola tidak ada / slot tidak ketemu, pakai jam di master jadwal.
        if ($polaId <= 0) {
            return [
                'jam_ke' => $first > 0 ? $first : (int) ($jadwal->jam_ke ?? 1),
                'jam_mulai' => $this->normalizeTime($jadwal->jam_mulai ?? '07:00').':00',
                'jam_selesai' => $this->normalizeTime($jadwal->jam_selesai ?? '07:45').':00',
                'jp_count' => count($normalized),
                'jp_label' => $this->formatJpRangeLabel($normalized),
            ];
        }

        try {
            $firstJam = $this->jadwalMasterDataService->resolveJam($first, $polaId);
            $lastJam = $this->jadwalMasterDataService->resolveJam($last, $polaId);
        } catch (\Throwable) {
            return [
                'jam_ke' => $first,
                'jam_mulai' => $this->normalizeTime($jadwal->jam_mulai ?? '07:00').':00',
                'jam_selesai' => $this->normalizeTime($jadwal->jam_selesai ?? '07:45').':00',
                'jp_count' => count($normalized),
                'jp_label' => $this->formatJpRangeLabel($normalized),
            ];
        }

        return [
            'jam_ke' => $first,
            'jam_mulai' => $firstJam['jam_mulai'].':00',
            'jam_selesai' => $lastJam['jam_selesai'].':00',
            'jp_count' => count($normalized),
            'jp_label' => $this->formatJpRangeLabel($normalized),
        ];
    }

    /**
     * @param  list<int|string>  $selectedJp
     * @return list<int>
     */
    public function normalizeSelection(array $selectedJp): array
    {
        $values = collect($selectedJp)
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->filter(fn ($value) => $value !== null)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($values === []) {
            throw ValidationException::withMessages([
                'jp_selected' => 'Pilih minimal satu Jam Pelajaran.',
            ]);
        }

        return $values;
    }

    /**
     * @param  list<int>  $selectedJp
     */
    public function assertConsecutive(array $selectedJp): void
    {
        if (count($selectedJp) <= 1) {
            return;
        }

        for ($i = 1; $i < count($selectedJp); $i++) {
            if ($selectedJp[$i] !== $selectedJp[$i - 1] + 1) {
                throw ValidationException::withMessages([
                    'jp_selected' => 'Jam Pelajaran harus berurutan.',
                ]);
            }
        }
    }

    /**
     * @return list<int>
     */
    public function selectedJpForJurnal(Jurnal $jurnal, Jadwal $jadwal): array
    {
        $jurnal->loadMissing('jadwal');
        $polaId = (int) ($jadwal->pola_jadwal_id ?? 0);

        if ($polaId <= 0) {
            return [(int) $jurnal->jam_ke];
        }

        $slots = $this->jamSlotsForPola($polaId);

        if ($jurnal->jam_mulai && $jurnal->jam_selesai) {
            $mulai = $this->normalizeTime($jurnal->jam_mulai);
            $selesai = $this->normalizeTime($jurnal->jam_selesai);

            $selected = $slots
                ->filter(function (JamPelajaran $jam) use ($mulai, $selesai) {
                    $jamMulai = $this->normalizeTime($jam->jam_mulai);
                    $jamSelesai = $this->normalizeTime($jam->jam_selesai);

                    return $jamMulai >= $mulai && $jamSelesai <= $selesai;
                })
                ->pluck('jam_ke')
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all();

            if ($selected !== []) {
                return $selected;
            }
        }

        return [(int) $jurnal->jam_ke];
    }

    /**
     * @return array<int, array{jam_ke: int, jam_mulai: string, jam_selesai: string, label: string}>
     */
    public function jpOptionsForJadwal(Jadwal $jadwal): array
    {
        $polaId = (int) ($jadwal->pola_jadwal_id ?? 0);

        if ($polaId > 0) {
            $fromPola = $this->jamSlotsForPola($polaId)
                ->mapWithKeys(fn (JamPelajaran $jam) => [
                    (string) $jam->jam_ke => [
                        'jam_ke' => (int) $jam->jam_ke,
                        'jam_mulai' => $this->normalizeTime($jam->jam_mulai),
                        'jam_selesai' => $this->normalizeTime($jam->jam_selesai),
                        'label' => 'JP '.$jam->jam_ke,
                    ],
                ])
                ->all();

            if ($fromPola !== []) {
                return $fromPola;
            }
        }

        // Fallback fleksibel: opsi JP 1–10 + slot jadwal saat ini
        $jp = max(1, (int) ($jadwal->jam_ke ?? 1));
        $options = [];
        for ($i = 1; $i <= 10; $i++) {
            $options[(string) $i] = [
                'jam_ke' => $i,
                'jam_mulai' => $i === $jp ? $this->normalizeTime($jadwal->jam_mulai ?? '07:00') : '',
                'jam_selesai' => $i === $jp ? $this->normalizeTime($jadwal->jam_selesai ?? '07:45') : '',
                'label' => 'JP '.$i,
            ];
        }

        return $options;
    }

    public function preselectedJp(Jadwal $jadwal): array
    {
        $selected = $this->selectedJpForJadwal($jadwal);

        return $selected !== [] ? $selected : [];
    }

    /**
     * @return list<int>
     */
    public function selectedJpForJadwal(Jadwal $jadwal): array
    {
        $polaId = (int) ($jadwal->pola_jadwal_id ?? 0);

        if ($polaId <= 0) {
            $jp = (int) ($jadwal->jam_ke ?? 0);

            return $jp >= 0 ? [$jp] : [];
        }

        if ($jadwal->jam_mulai && $jadwal->jam_selesai) {
            $mulai = $this->normalizeTime($jadwal->jam_mulai);
            $selesai = $this->normalizeTime($jadwal->jam_selesai);

            $selected = $this->jamSlotsForPola($polaId)
                ->filter(function (JamPelajaran $jam) use ($mulai, $selesai) {
                    $jamMulai = $this->normalizeTime($jam->jam_mulai);
                    $jamSelesai = $this->normalizeTime($jam->jam_selesai);

                    return $jamMulai >= $mulai && $jamSelesai <= $selesai;
                })
                ->pluck('jam_ke')
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all();

            if ($selected !== []) {
                return $selected;
            }
        }

        $jp = (int) ($jadwal->jam_ke ?? 0);

        return $jp >= 0 ? [$jp] : [];
    }

    public function jamAkhirKeForJadwal(Jadwal $jadwal): int
    {
        $selected = $this->selectedJpForJadwal($jadwal);

        return (int) ($selected[array_key_last($selected)] ?? $jadwal->jam_ke ?? 0);
    }

    public function jamRangeLabelForJadwal(Jadwal $jadwal): string
    {
        return $this->formatJamRangeLabel($this->selectedJpForJadwal($jadwal));
    }

    /**
     * @param  list<int>  $selectedJp
     */
    public function formatJamRangeLabel(array $selectedJp): string
    {
        if ($selectedJp === []) {
            return '-';
        }

        sort($selectedJp);
        $first = (int) $selectedJp[0];
        $last = (int) $selectedJp[array_key_last($selectedJp)];

        if ($first === $last) {
            return 'Jam '.$first;
        }

        return 'Jam '.$first.'–'.$last;
    }

    public function jamKeLabel(Jurnal $jurnal): string
    {
        $jurnal->loadMissing('jadwal');
        $selected = $this->selectedJpForJurnal($jurnal, $jurnal->jadwal);

        return $this->formatJpRangeLabel($selected);
    }

    public function jpCount(Jurnal $jurnal): int
    {
        $jurnal->loadMissing('jadwal');

        return count($this->selectedJpForJurnal($jurnal, $jurnal->jadwal));
    }

    public function effectiveJamMulai(Jurnal $jurnal): string
    {
        if ($jurnal->jam_mulai) {
            return $this->normalizeTime($jurnal->jam_mulai);
        }

        $jurnal->loadMissing('jadwal');

        return $this->normalizeTime($jurnal->jadwal->jam_mulai ?? '00:00');
    }

    public function effectiveJamSelesai(Jurnal $jurnal): string
    {
        if ($jurnal->jam_selesai) {
            return $this->normalizeTime($jurnal->jam_selesai);
        }

        $jurnal->loadMissing('jadwal');

        return $this->normalizeTime($jurnal->jadwal->jam_selesai ?? '00:00');
    }

    /**
     * @param  list<int>  $selectedJp
     */
    public function formatJpRangeLabel(array $selectedJp): string
    {
        if ($selectedJp === []) {
            return '-';
        }

        sort($selectedJp);
        $first = (int) $selectedJp[0];
        $last = (int) $selectedJp[array_key_last($selectedJp)];

        if ($first === $last) {
            return 'JP '.$first;
        }

        return 'JP '.$first.' - JP '.$last;
    }

    /**
     * @return Collection<int, JamPelajaran>
     */
    private function jamSlotsForPola(int $polaId): Collection
    {
        return JamPelajaran::query()
            ->aktif()
            ->where('pola_jadwal_id', $polaId)
            ->orderBy('jam_ke')
            ->get();
    }

    private function normalizeTime(mixed $time): string
    {
        return substr((string) $time, 0, 5);
    }
}
