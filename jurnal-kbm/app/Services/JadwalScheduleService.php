<?php

namespace App\Services;

use App\Models\Jadwal;

class JadwalScheduleService
{
    public function hasTimeConflict(
        int $guruId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeId = null,
    ): bool {
        return Jadwal::query()
            ->where('guru_id', $guruId)
            ->where('hari', $hari)
            ->when($excludeId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->where(function ($query) use ($jamMulai, $jamSelesai) {
                $query->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);
            })
            ->exists();
    }

    public function buildScheduleFingerprint(array $data): string
    {
        return implode('|', [
            $data['guru_id'],
            $data['kelas'],
            $data['mapel'],
            $data['hari'],
            $data['jam_ke'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['tahun_pelajaran'],
            $data['semester'],
        ]);
    }

    public function isDuplicateSchedule(array $data, ?int $excludeId = null): bool
    {
        return Jadwal::query()
            ->where('guru_id', $data['guru_id'])
            ->where('kelas', $data['kelas'])
            ->where('mapel', $data['mapel'])
            ->where('hari', $data['hari'])
            ->where('jam_ke', $data['jam_ke'])
            ->where('jam_mulai', $data['jam_mulai'])
            ->where('jam_selesai', $data['jam_selesai'])
            ->where('tahun_pelajaran', $data['tahun_pelajaran'])
            ->where('semester', $data['semester'])
            ->when($excludeId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();
    }

    public function hasJamKeConflict(
        int $guruId,
        string $hari,
        int $jamKe,
        ?int $excludeId = null,
    ): bool {
        return Jadwal::query()
            ->where('guru_id', $guruId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe)
            ->when($excludeId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();
    }

    public function hasGuruScheduleDuplicate(
        int $guruId,
        string $hari,
        int $jamKe,
        string $kelas,
        string $semester,
        string $tahunPelajaran,
        ?int $excludeId = null,
    ): bool {
        return Jadwal::query()
            ->where('guru_id', $guruId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe)
            ->where('kelas', $kelas)
            ->where('semester', $semester)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->when($excludeId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();
    }
}
