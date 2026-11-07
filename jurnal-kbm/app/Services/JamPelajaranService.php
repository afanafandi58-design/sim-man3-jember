<?php

namespace App\Services;

use App\Models\JamPelajaran;
use App\Models\PolaJadwal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class JamPelajaranService
{
    public function __construct(
        private PolaJadwalService $polaJadwalService,
    ) {}

    public function all(): Collection
    {
        return JamPelajaran::query()->with('polaJadwal')->orderBy('pola_jadwal_id')->orderBy('jam_ke')->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        return JamPelajaran::query()
            ->with('polaJadwal')
            ->orderBy('pola_jadwal_id')
            ->orderBy('jam_ke')
            ->paginate(20);
    }

    public function store(array $data): JamPelajaran
    {
        $this->assertValidJamKe((int) $data['jam_ke']);
        $polaId = (int) $data['pola_jadwal_id'];
        $this->assertNoJamKeConflict($polaId, (int) $data['jam_ke']);

        return JamPelajaran::create([
            'pola_jadwal_id' => $polaId,
            'jam_ke' => $data['jam_ke'],
            'nama_sesi' => $data['nama_sesi'] ?? null,
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'status' => $data['status'] ?? JamPelajaran::STATUS_AKTIF,
        ]);
    }

    public function update(JamPelajaran $jamPelajaran, array $data): JamPelajaran
    {
        $this->assertValidJamKe((int) $data['jam_ke']);
        $polaId = (int) $data['pola_jadwal_id'];

        if ($polaId !== (int) $jamPelajaran->pola_jadwal_id || (int) $data['jam_ke'] !== (int) $jamPelajaran->jam_ke) {
            $this->assertNoJamKeConflict($polaId, (int) $data['jam_ke'], $jamPelajaran->id);
        }

        $jamPelajaran->update([
            'pola_jadwal_id' => $polaId,
            'jam_ke' => $data['jam_ke'],
            'nama_sesi' => $data['nama_sesi'] ?? null,
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'status' => $data['status'] ?? $jamPelajaran->status,
        ]);

        return $jamPelajaran->fresh(['polaJadwal']);
    }

    public function delete(JamPelajaran $jamPelajaran): void
    {
        $jamPelajaran->update(['status' => JamPelajaran::STATUS_NONAKTIF]);
    }

    public function polaOptions(): Collection
    {
        return $this->polaJadwalService->allAktif();
    }

    private function assertValidJamKe(int $jamKe): void
    {
        if ($jamKe < 0) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Jam ke minimal adalah 0.',
            ]);
        }
    }

    private function assertNoJamKeConflict(int $polaJadwalId, int $jamKe, ?int $excludeId = null): void
    {
        $exists = JamPelajaran::query()
            ->where('pola_jadwal_id', $polaJadwalId)
            ->where('jam_ke', $jamKe)
            ->when($excludeId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Jam ke sudah terdaftar untuk pola jadwal ini.',
            ]);
        }
    }
}
