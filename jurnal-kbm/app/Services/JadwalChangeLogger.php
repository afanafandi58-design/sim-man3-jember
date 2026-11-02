<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JadwalChangeLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class JadwalChangeLogger
{
    public function snapshot(Jadwal $jadwal): array
    {
        return [
            'id' => $jadwal->id,
            'guru_id' => $jadwal->guru_id,
            'kelas' => $jadwal->kelas,
            'mapel' => $jadwal->mapel,
            'hari' => $jadwal->hari,
            'jam_ke' => $jadwal->jam_ke,
            'jam_mulai' => substr((string) $jadwal->jam_mulai, 0, 8),
            'jam_selesai' => substr((string) $jadwal->jam_selesai, 0, 8),
            'tahun_pelajaran' => $jadwal->tahun_pelajaran,
            'semester' => $jadwal->semester,
        ];
    }

    public function log(
        string $action,
        User $actor,
        ?Jadwal $jadwal,
        int $guruId,
        ?array $before,
        ?array $after,
        ?string $ipAddress = null,
    ): JadwalChangeLog {
        $entry = JadwalChangeLog::create([
            'jadwal_id' => $jadwal?->id,
            'guru_id' => $guruId,
            'user_id' => $actor->id,
            'action' => $action,
            'snapshot_before' => $before,
            'snapshot_after' => $after,
            'ip_address' => $ipAddress,
        ]);

        Log::channel('jurnal_audit')->info('[JADWAL CHANGE] '.$action, [
            'log_id' => $entry->id,
            'jadwal_id' => $jadwal?->id,
            'guru_id' => $guruId,
            'user_id' => $actor->id,
            'before' => $before,
            'after' => $after,
        ]);

        return $entry;
    }
}
