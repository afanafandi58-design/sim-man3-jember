<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminJurnalService
{
    public function __construct(
        private JurnalAttachmentService $attachmentService,
    ) {}

    public function delete(Jurnal $jurnal, User $admin, ?string $ipAddress = null): void
    {
        if ($admin->role !== 'admin') {
            abort(403);
        }

        DB::transaction(function () use ($jurnal, $admin, $ipAddress) {
            $jurnal->load(['attachments', 'jadwal.guru']);

            $auditContext = $this->buildAuditContext($jurnal, $admin, $ipAddress);

            foreach ($jurnal->attachments as $attachment) {
                $this->attachmentService->purgeStorageFiles($attachment);
                $attachment->delete();
            }

            $jurnal->delete();

            $this->logDelete($auditContext);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAuditContext(Jurnal $jurnal, User $admin, ?string $ipAddress): array
    {
        $jadwal = $jurnal->jadwal;
        $now = Carbon::now();

        return [
            'tanggal' => $now->format('Y-m-d'),
            'jam' => $now->format('H:i:s'),
            'nama_admin' => $admin->name,
            'role' => $admin->role,
            'ip_address' => $ipAddress ?? '-',
            'jurnal_id' => $jurnal->id,
            'guru' => $jadwal?->guru?->nama ?? '-',
            'mapel' => $jadwal?->mapel ?? '-',
            'kelas' => $jadwal?->kelas ?? '-',
            'tanggal_kbm' => $jurnal->tanggal,
            'action' => 'DELETE',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logDelete(array $context): void
    {
        $lines = [
            '[JURNAL AUDIT]',
            'Tanggal     : '.$context['tanggal'],
            'Jam         : '.$context['jam'],
            'Nama Admin  : '.$context['nama_admin'],
            'Role        : '.$context['role'],
            'IP Address  : '.$context['ip_address'],
            'Jurnal ID   : '.$context['jurnal_id'],
            'Guru        : '.$context['guru'],
            'Mapel       : '.$context['mapel'],
            'Kelas       : '.$context['kelas'],
            'Tanggal KBM : '.$context['tanggal_kbm'],
            'Action      : '.$context['action'],
        ];

        Log::channel('jurnal_audit')->info(implode(PHP_EOL, $lines), $context);
    }
}
