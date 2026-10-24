<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPerbaikanRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JadwalPerbaikanService
{
    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private JadwalMasterDataService $jadwalMasterDataService,
        private GuruService $guruService,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;

        return JadwalPerbaikanRequest::query()
            ->with(['jadwal', 'guru', 'processor'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function formData(Jadwal $jadwal): array
    {
        $jamSelection = app(JurnalJamSelectionService::class);

        return [
            'kelasOptions' => $this->jadwalMasterDataService->kelasOptions(),
            'mapelOptions' => $this->jadwalMasterDataService->mapelOptions(),
            'polaHariMap' => $this->jadwalMasterDataService->polaHariMapForJs(),
            'jamMapByPola' => $this->jadwalMasterDataService->jamMapByPolaForJs(),
            'selectedKodeKelas' => $this->jadwalMasterDataService->findKodeKelasValue($jadwal->kelas),
            'selectedMapel' => $jadwal->mapel,
            'selectedJamKe' => $jadwal->jam_ke,
            'selectedJamKeAkhir' => $jamSelection->jamAkhirKeForJadwal($jadwal),
            'selectedHari' => $jadwal->hari,
        ];
    }

    public function approve(JadwalPerbaikanRequest $request): JadwalPerbaikanRequest
    {
        $this->assertPending($request);

        $request->update([
            'status' => JadwalPerbaikanRequest::STATUS_APPROVED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $request->jadwal->update(['is_locked' => false]);

        return $request->fresh(['jadwal', 'guru']);
    }

    public function reject(JadwalPerbaikanRequest $request, ?string $adminCatatan = null): JadwalPerbaikanRequest
    {
        $this->assertPending($request);

        $request->update([
            'status' => JadwalPerbaikanRequest::STATUS_REJECTED,
            'processed_by' => Auth::id(),
            'admin_catatan' => $adminCatatan,
            'processed_at' => now(),
        ]);

        return $request->fresh(['jadwal', 'guru']);
    }

    public function updateJadwal(JadwalPerbaikanRequest $request, array $data): Jadwal
    {
        if ($request->status !== JadwalPerbaikanRequest::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'jadwal' => 'Permintaan perbaikan belum disetujui.',
            ]);
        }

        $jadwal = $request->jadwal;

        if ($jadwal->is_locked) {
            throw ValidationException::withMessages([
                'jadwal' => 'Jadwal masih terkunci. Setujui permintaan terlebih dahulu.',
            ]);
        }

        $kelas = $this->jadwalMasterDataService->resolveKelas($data['kode_kelas']);
        $mapel = $this->jadwalMasterDataService->resolveMapel($data['mapel']);
        $rangeData = $this->jadwalMasterDataService->mergeJamRangeIntoData($data);

        if ($this->jadwalScheduleService->hasGuruScheduleDuplicate(
            (int) $jadwal->guru_id,
            $rangeData['hari'],
            $rangeData['jam_ke'],
            $kelas,
            $rangeData['semester'],
            $rangeData['tahun_pelajaran'],
            $jadwal->id,
        )) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Jadwal dengan Guru, Hari, Jam Ke, Kelas, Semester, dan Tahun Pelajaran yang sama sudah ada.',
            ]);
        }

        if ($this->jadwalScheduleService->hasTimeConflict(
            (int) $jadwal->guru_id,
            $rangeData['hari'],
            $rangeData['jam_mulai'],
            $rangeData['jam_selesai'],
            $jadwal->id,
        )) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Guru sudah memiliki jadwal yang bertabrakan pada waktu tersebut.',
            ]);
        }

        $jadwal->update([
            'kelas' => $kelas,
            'mapel' => $mapel,
            'hari' => $rangeData['hari'],
            'pola_jadwal_id' => $rangeData['pola_jadwal_id'],
            'jam_ke' => $rangeData['jam_ke'],
            'jam_mulai' => $rangeData['jam_mulai'],
            'jam_selesai' => $rangeData['jam_selesai'],
            'tahun_pelajaran' => $rangeData['tahun_pelajaran'],
            'semester' => $rangeData['semester'],
            'is_locked' => true,
        ]);

        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($jadwal->guru_id));

        return $jadwal->fresh();
    }

    public function countPending(): int
    {
        return JadwalPerbaikanRequest::query()
            ->where('status', JadwalPerbaikanRequest::STATUS_PENDING)
            ->count();
    }

    private function assertPending(JadwalPerbaikanRequest $request): void
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Permintaan ini sudah diproses.',
            ]);
        }
    }
}
