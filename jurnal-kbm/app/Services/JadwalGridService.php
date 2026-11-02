<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPerbaikanRequest;
use App\Models\PolaJadwal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JadwalGridService
{
    /** @var list<string> */
    public const HARI_COLUMNS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    public function __construct(
        private JadwalMasterDataService $jadwalMasterDataService,
        private JadwalScheduleService $jadwalScheduleService,
        private GuruService $guruService,
    ) {}

    /**
     * @return array{
     *     jamRows: Collection<int, \App\Models\JamPelajaran>,
     *     hariColumns: list<string>,
     *     cells: array<string, array{jadwal: ?Jadwal, css_classes: list<string>, display: string}>
     * }
     */
    public function buildGridData(int $guruId, string $tahunPelajaran, string $semester): array
    {
        $hariColumns = self::HARI_COLUMNS;
        $regPola = PolaJadwal::query()->aktif()->where('kode', PolaJadwal::KODE_REG)->first()
            ?? $this->jadwalMasterDataService->resolvePolaFromHari('Senin');

        $jamRows = $this->jadwalMasterDataService->jamOptions($regPola->id);

        $jadwals = Jadwal::query()
            ->with('pendingPerbaikanRequest')
            ->where('guru_id', $guruId)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->where('semester', $semester)
            ->whereIn('hari', $hariColumns)
            ->get();

        $cells = [];

        foreach ($hariColumns as $hari) {
            foreach ($jamRows as $jam) {
                $key = $this->cellKey($hari, (int) $jam->jam_ke);
                $jadwal = $jadwals->first(
                    fn (Jadwal $row) => $row->hari === $hari && (int) $row->jam_ke === (int) $jam->jam_ke,
                );

                $cells[$key] = [
                    'key' => $key,
                    'jadwal' => $jadwal,
                    'kode_kelas' => $jadwal
                        ? $this->jadwalMasterDataService->findKodeKelasValue($jadwal->kelas)
                        : null,
                    'css_classes' => $this->resolveCellCssClasses($jadwal),
                    'display' => $jadwal
                        ? trim($jadwal->kelas."\n".$jadwal->mapel)
                        : '',
                ];
            }
        }

        return [
            'jamRows' => $jamRows,
            'hariColumns' => $hariColumns,
            'cells' => $cells,
        ];
    }

    public function cellKey(string $hari, int $jamKe): string
    {
        return $hari.'|'.$jamKe;
    }

    /**
     * @return list<string>
     */
    public function resolveCellCssClasses(?Jadwal $jadwal): array
    {
        if (! $jadwal) {
            return ['jadwal-grid-cell--empty'];
        }

        $classes = ['jadwal-grid-cell--filled'];

        if (! $jadwal->is_locked || $this->hasPendingPerbaikan($jadwal)) {
            $classes[] = 'jadwal-grid-cell--warning';
        }

        if ($jadwal->updated_at && $jadwal->updated_at->gte(Carbon::now()->subDay())) {
            $classes[] = 'jadwal-grid-cell--recent';
        }

        return $classes;
    }

    public function upsertGridCell(array $data): Jadwal
    {
        $guruId = (int) $data['guru_id'];
        $hari = $data['hari'];
        $jamKe = (int) $data['jam_ke'];
        $tahunPelajaran = $data['tahun_pelajaran'];
        $semester = $data['semester'];

        $kelas = $this->jadwalMasterDataService->resolveKelas($data['kode_kelas']);
        $mapel = $this->jadwalMasterDataService->resolveMapel($data['mapel']);

        $rangeInput = array_merge($data, [
            'jam_ke_akhir' => $data['jam_ke_akhir'] ?? $jamKe,
        ]);
        $rangeData = $this->jadwalMasterDataService->mergeJamRangeIntoData($rangeInput);

        $existing = Jadwal::query()
            ->where('guru_id', $guruId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->where('semester', $semester)
            ->first();

        $excludeId = $existing?->id;

        if ($this->jadwalScheduleService->hasJamKeConflict($guruId, $hari, $jamKe, $excludeId)) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Guru sudah memiliki jadwal pada jam ke ini untuk hari yang sama.',
            ]);
        }

        if ($this->jadwalScheduleService->hasTimeConflict(
            $guruId,
            $rangeData['hari'],
            $rangeData['jam_mulai'],
            $rangeData['jam_selesai'],
            $excludeId,
        )) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Guru sudah memiliki jadwal yang bertabrakan pada waktu tersebut.',
            ]);
        }

        return DB::transaction(function () use (
            $existing,
            $guruId,
            $kelas,
            $mapel,
            $rangeData,
            $hari,
            $jamKe,
            $tahunPelajaran,
            $semester,
        ) {
            $payload = [
                'guru_id' => $guruId,
                'kelas' => $kelas,
                'mapel' => $mapel,
                'hari' => $rangeData['hari'],
                'pola_jadwal_id' => $rangeData['pola_jadwal_id'],
                'jam_ke' => $rangeData['jam_ke'],
                'jam_mulai' => $rangeData['jam_mulai'],
                'jam_selesai' => $rangeData['jam_selesai'],
                'tahun_pelajaran' => $tahunPelajaran,
                'semester' => $semester,
                'is_locked' => false,
            ];

            if ($existing) {
                $existing->update($payload);
                $jadwal = $existing->fresh();
            } else {
                $jadwal = Jadwal::create($payload);
            }

            $this->cancelPendingPerbaikanForJadwal($jadwal);
            $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($guruId));

            return $jadwal->fresh(['pendingPerbaikanRequest']);
        });
    }

    public function cancelPendingPerbaikanForJadwal(Jadwal $jadwal): void
    {
        JadwalPerbaikanRequest::query()
            ->where('jadwal_id', $jadwal->id)
            ->where('status', JadwalPerbaikanRequest::STATUS_PENDING)
            ->update([
                'status' => JadwalPerbaikanRequest::STATUS_REJECTED,
                'processed_by' => Auth::id(),
                'admin_catatan' => 'Dibatalkan otomatis saat admin menyimpan grid Master Jadwal.',
                'processed_at' => now(),
            ]);
    }

    private function hasPendingPerbaikan(Jadwal $jadwal): bool
    {
        if ($jadwal->relationLoaded('pendingPerbaikanRequest') && $jadwal->pendingPerbaikanRequest) {
            return true;
        }

        return $jadwal->pendingPerbaikanRequest()->exists();
    }
}
