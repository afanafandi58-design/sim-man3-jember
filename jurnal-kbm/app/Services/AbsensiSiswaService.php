<?php

namespace App\Services;

use App\Models\AbsensiSiswa;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbsensiSiswaService
{
    public function __construct(
        private SiswaService $siswaService,
    ) {}

    public function syncForJurnal(Jurnal $jurnal, Jadwal $jadwal, array $absensiInput): void
    {
        $allowedStudents = $this->siswaService
            ->forJadwal($jadwal)
            ->keyBy('id');

        if ($allowedStudents->isEmpty()) {
            return;
        }

        $records = $this->normalizeInput($absensiInput, $allowedStudents);

        DB::transaction(function () use ($jurnal, $jadwal, $records) {
            AbsensiSiswa::query()
                ->where('jurnal_id', $jurnal->id)
                ->delete();

            foreach ($records as $record) {
                AbsensiSiswa::create([
                    'jurnal_id' => $jurnal->id,
                    'jadwal_id' => $jadwal->id,
                    'guru_id' => $jadwal->guru_id,
                    'siswa_id' => $record['siswa_id'],
                    'status' => $record['status'],
                    'keterangan' => $record['keterangan'],
                ]);
            }
        });
    }

    public function countsFromInput(array $absensiInput, Jadwal $jadwal): array
    {
        $allowedStudents = $this->siswaService->forJadwal($jadwal);

        if ($allowedStudents->isEmpty()) {
            return [
                'hadir' => (int) ($absensiInput['hadir'] ?? 0),
                'izin' => (int) ($absensiInput['izin'] ?? 0),
                'sakit' => (int) ($absensiInput['sakit'] ?? 0),
                'alpha' => (int) ($absensiInput['alpha'] ?? 0),
            ];
        }

        $records = $this->normalizeInput(
            $absensiInput,
            $allowedStudents->keyBy('id'),
        );

        return $this->countByStatus(collect($records));
    }

    public function countByStatus(Collection $records): array
    {
        $counts = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        foreach ($records as $record) {
            $key = strtolower($record['status']);

            if (array_key_exists($key, $counts)) {
                $counts[$key]++;
            }
        }

        return $counts;
    }

    public function groupedAbsentees(Jurnal $jurnal): array
    {
        $absensi = AbsensiSiswa::query()
            ->with('siswa')
            ->where('jurnal_id', $jurnal->id)
            ->where('status', '!=', AbsensiSiswa::STATUS_HADIR)
            ->get()
            ->sortBy(fn (AbsensiSiswa $row) => $row->siswa?->nama ?? '');

        $grouped = [
            AbsensiSiswa::STATUS_SAKIT => [],
            AbsensiSiswa::STATUS_IZIN => [],
            AbsensiSiswa::STATUS_ALPHA => [],
        ];

        foreach ($absensi as $row) {
            $grouped[$row->status][] = $row->siswa?->nama ?? '-';
        }

        $total = AbsensiSiswa::query()
            ->where('jurnal_id', $jurnal->id)
            ->count();

        $hadir = AbsensiSiswa::query()
            ->where('jurnal_id', $jurnal->id)
            ->where('status', AbsensiSiswa::STATUS_HADIR)
            ->count();

        return [
            'grouped' => $grouped,
            'counts' => [
                AbsensiSiswa::STATUS_SAKIT => count($grouped[AbsensiSiswa::STATUS_SAKIT]),
                AbsensiSiswa::STATUS_IZIN => count($grouped[AbsensiSiswa::STATUS_IZIN]),
                AbsensiSiswa::STATUS_ALPHA => count($grouped[AbsensiSiswa::STATUS_ALPHA]),
                AbsensiSiswa::STATUS_HADIR => $hadir,
            ],
            'total' => $total,
        ];
    }

    private function normalizeInput(array $absensiInput, Collection $allowedStudents): array
    {
        $records = [];
        $siswaInput = $absensiInput['siswa'] ?? [];
        $keteranganInput = $absensiInput['keterangan'] ?? [];

        if (is_array($siswaInput)) {
            foreach ($siswaInput as $siswaId => $status) {
                $siswaId = (int) $siswaId;

                if (! $allowedStudents->has($siswaId)) {
                    throw ValidationException::withMessages([
                        'absensi' => "Siswa dengan ID {$siswaId} tidak termasuk kelas jadwal ini.",
                    ]);
                }

                $status = $this->normalizeStatus((string) $status);
                $keterangan = trim((string) ($keteranganInput[$siswaId] ?? ''));

                $records[] = [
                    'siswa_id' => $siswaId,
                    'status' => $status,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                ];
            }
        }

        foreach ($allowedStudents as $siswa) {
            if (collect($records)->contains(fn ($row) => $row['siswa_id'] === $siswa->id)) {
                continue;
            }

            $records[] = [
                'siswa_id' => $siswa->id,
                'status' => AbsensiSiswa::STATUS_HADIR,
                'keterangan' => null,
            ];
        }

        return $records;
    }

    private function normalizeStatus(string $status): string
    {
        $status = ucfirst(strtolower(trim($status)));

        if (! in_array($status, AbsensiSiswa::STATUSES, true)) {
            throw ValidationException::withMessages([
                'absensi' => "Status absensi \"{$status}\" tidak valid.",
            ]);
        }

        return $status;
    }
}
