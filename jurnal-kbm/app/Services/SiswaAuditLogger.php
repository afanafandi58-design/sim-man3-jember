<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SiswaAuditLogger
{
    /**
     * @param  array<string, mixed>|null  $oldData
     * @param  array<string, mixed>|null  $newData
     */
    public function log(User $admin, string $action, ?Siswa $siswa = null, ?array $oldData = null, ?array $newData = null, ?string $ipAddress = null): void
    {
        if ($admin->role !== 'admin') {
            return;
        }

        $now = Carbon::now();

        $lines = [
            '[SISWA AUDIT]',
            'Tanggal    : '.$now->format('Y-m-d'),
            'Jam        : '.$now->format('H:i:s'),
            'Admin      : '.$admin->name,
            'Role       : '.$admin->role,
            'IP Address : '.($ipAddress ?? '-'),
            'Action     : '.$action,
            'Siswa ID   : '.($siswa?->id ?? '-'),
            'NIS        : '.($siswa?->nis ?? ($newData['nis'] ?? '-')),
            'Data Lama  : '.($oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : '-'),
            'Data Baru  : '.($newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : '-'),
        ];

        Log::channel('siswa_audit')->info(implode(PHP_EOL, $lines), [
            'action' => $action,
            'siswa_id' => $siswa?->id,
            'old' => $oldData,
            'new' => $newData,
        ]);
    }
}
