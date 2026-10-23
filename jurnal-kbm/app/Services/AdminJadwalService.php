<?php

namespace App\Services;

use App\Models\AbsensiSiswa;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminJadwalService
{
    public function __construct(
        private GuruService $guruService,
    ) {}

    /**
     * @return array{allowed: bool, reason: string, checks: array<string, bool>}
     */
    public function canDelete(Jadwal $jadwal): array
    {
        $jadwal->loadMissing('guru');

        $hasJurnal = $jadwal->jurnals()->exists();
        $hasLampiran = $jadwal->jurnals()->whereHas('attachments')->exists();
        $hasAbsensi = AbsensiSiswa::query()->where('jadwal_id', $jadwal->id)->exists();
        $hasRiwayatPerbaikan = $jadwal->perbaikanRequests()->exists();

        $checks = [
            'jurnal' => $hasJurnal,
            'lampiran' => $hasLampiran,
            'monitoring' => $hasJurnal,
            'pdf' => $hasJurnal,
            'riwayat' => $hasRiwayatPerbaikan,
            'absensi' => $hasAbsensi,
        ];

        $blocked = collect($checks)->contains(true);

        return [
            'allowed' => ! $blocked,
            'reason' => $blocked
                ? 'Jadwal tidak dapat dihapus karena masih digunakan.'
                : '',
            'checks' => $checks,
        ];
    }

    public function delete(Jadwal $jadwal, User $admin, ?string $ipAddress = null, ?string $alasan = null): void
    {
        if ($admin->role !== 'admin') {
            abort(403);
        }

        $canDelete = $this->canDelete($jadwal);

        if (! $canDelete['allowed']) {
            throw ValidationException::withMessages([
                'jadwal' => $canDelete['reason'],
            ]);
        }

        DB::transaction(function () use ($jadwal, $admin, $ipAddress, $alasan) {
            $jadwal->loadMissing('guru');

            $archiveContext = $this->buildArchiveContext($jadwal, $admin, $ipAddress, $alasan);
            $this->logArchive($archiveContext);

            $guru = $jadwal->guru;
            $jadwal->delete();

            if ($guru) {
                $this->guruService->syncJumlahJamMengajar($guru);
            }

            $this->logDelete($archiveContext);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildArchiveContext(Jadwal $jadwal, User $admin, ?string $ipAddress, ?string $alasan): array
    {
        $now = Carbon::now();

        return [
            'tanggal' => $now->format('Y-m-d'),
            'jam' => $now->format('H:i:s'),
            'nama_admin' => $admin->name,
            'role' => $admin->role,
            'ip_address' => $ipAddress ?? '-',
            'jadwal_id' => $jadwal->id,
            'guru' => $jadwal->guru?->nama ?? '-',
            'kelas' => $jadwal->kelas,
            'mapel' => $jadwal->mapel,
            'hari' => $jadwal->hari,
            'jam_ke' => $jadwal->jam_ke,
            'jam_mulai' => $jadwal->jam_mulai,
            'jam_selesai' => $jadwal->jam_selesai,
            'tahun_pelajaran' => $jadwal->tahun_pelajaran,
            'semester' => $jadwal->semester,
            'alasan' => trim((string) $alasan) !== '' ? trim((string) $alasan) : '-',
            'snapshot' => $jadwal->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logArchive(array $context): void
    {
        $lines = [
            '[JADWAL ADMIN]',
            'Action      : ARCHIVE',
            'Tanggal     : '.$context['tanggal'],
            'Jam         : '.$context['jam'],
            'Nama Admin  : '.$context['nama_admin'],
            'Role        : '.$context['role'],
            'IP Address  : '.$context['ip_address'],
            'Jadwal ID   : '.$context['jadwal_id'],
            'Guru        : '.$context['guru'],
            'Kelas       : '.$context['kelas'],
            'Mapel       : '.$context['mapel'],
            'Hari        : '.$context['hari'],
            'Jam Ke      : '.$context['jam_ke'],
            'Jam Mulai   : '.$context['jam_mulai'],
            'Jam Selesai : '.$context['jam_selesai'],
            'TP          : '.$context['tahun_pelajaran'],
            'Semester    : '.$context['semester'],
            'Alasan      : '.$context['alasan'],
            'Snapshot    : '.json_encode($context['snapshot'], JSON_UNESCAPED_UNICODE),
        ];

        Log::channel('jurnal_audit')->info(implode(PHP_EOL, $lines), $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logDelete(array $context): void
    {
        $lines = [
            '[JADWAL ADMIN]',
            'Action      : DELETE',
            'Tanggal     : '.$context['tanggal'],
            'Jam         : '.$context['jam'],
            'Nama Admin  : '.$context['nama_admin'],
            'Role        : '.$context['role'],
            'IP Address  : '.$context['ip_address'],
            'Jadwal ID   : '.$context['jadwal_id'],
            'Guru        : '.$context['guru'],
            'Kelas       : '.$context['kelas'],
            'Mapel       : '.$context['mapel'],
            'Alasan      : '.$context['alasan'],
        ];

        Log::channel('jurnal_audit')->info(implode(PHP_EOL, $lines), $context);
    }
}
