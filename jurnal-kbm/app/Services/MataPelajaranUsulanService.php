<?php

namespace App\Services;

use App\Models\MataPelajaran;
use App\Models\MataPelajaranUsulan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MataPelajaranUsulanService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;

        return MataPelajaranUsulan::query()
            ->with(['guru', 'processor'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function submit(int $guruId, string $nama): MataPelajaranUsulan
    {
        $nama = trim($nama);

        if (MataPelajaran::query()->where('nama', $nama)->exists()) {
            throw ValidationException::withMessages([
                'mapel_baru' => 'Mata pelajaran sudah tersedia di master.',
            ]);
        }

        $pending = MataPelajaranUsulan::query()
            ->where('guru_id', $guruId)
            ->where('nama', $nama)
            ->where('status', MataPelajaranUsulan::STATUS_PENDING)
            ->exists();

        if ($pending) {
            throw ValidationException::withMessages([
                'mapel_baru' => 'Usulan mata pelajaran ini sudah diajukan dan menunggu persetujuan.',
            ]);
        }

        return MataPelajaranUsulan::create([
            'guru_id' => $guruId,
            'nama' => $nama,
            'status' => MataPelajaranUsulan::STATUS_PENDING,
        ]);
    }

    public function approve(MataPelajaranUsulan $usulan): MataPelajaranUsulan
    {
        $this->assertPending($usulan);

        if (MataPelajaran::query()->where('nama', $usulan->nama)->exists()) {
            $usulan->update([
                'status' => MataPelajaranUsulan::STATUS_APPROVED,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'admin_catatan' => 'Mapel sudah ada di master.',
            ]);

            return $usulan->fresh(['guru', 'processor']);
        }

        MataPelajaran::create([
            'nama' => $usulan->nama,
            'is_active' => true,
        ]);

        $usulan->update([
            'status' => MataPelajaranUsulan::STATUS_APPROVED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return $usulan->fresh(['guru', 'processor']);
    }

    public function reject(MataPelajaranUsulan $usulan, ?string $adminCatatan = null): MataPelajaranUsulan
    {
        $this->assertPending($usulan);

        $usulan->update([
            'status' => MataPelajaranUsulan::STATUS_REJECTED,
            'processed_by' => Auth::id(),
            'admin_catatan' => $adminCatatan,
            'processed_at' => now(),
        ]);

        return $usulan->fresh(['guru', 'processor']);
    }

    public function countPending(): int
    {
        return MataPelajaranUsulan::query()
            ->where('status', MataPelajaranUsulan::STATUS_PENDING)
            ->count();
    }

    private function assertPending(MataPelajaranUsulan $usulan): void
    {
        if (! $usulan->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Usulan ini sudah diproses.',
            ]);
        }
    }
}
