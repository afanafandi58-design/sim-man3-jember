<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPerbaikanRequest;
use App\Models\User;
use App\Support\JadwalPerbaikanSchema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruJadwalSayaService
{
    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private JadwalMasterDataService $jadwalMasterDataService,
        private MataPelajaranUsulanService $mataPelajaranUsulanService,
        private GuruService $guruService,
    ) {}

    public function assertGuruAccount(): User
    {
        $user = Auth::user();

        if (! $user || ! $user->guru_id) {
            abort(403, 'Akun guru tidak valid.');
        }

        return $user;
    }

    public function getGuru(): Guru
    {
        $user = $this->assertGuruAccount();

        return Guru::findOrFail($user->guru_id);
    }

    public function formData(): array
    {
        return [
            'kelasOptions' => $this->jadwalMasterDataService->kelasOptions(),
            'mapelOptions' => $this->jadwalMasterDataService->mapelOptions(),
            'polaHariMap' => $this->jadwalMasterDataService->polaHariMapForJs(),
            'jamMapByPola' => $this->jadwalMasterDataService->jamMapByPolaForJs(),
        ];
    }

    public function paginateForGuru(array $filters = []): LengthAwarePaginator
    {
        $user = $this->assertGuruAccount();
        $tahunPelajaran = $filters['tahun_pelajaran'] ?? GuruService::DEFAULT_TAHUN_PELAJARAN;
        $semester = $filters['semester'] ?? null;

        return Jadwal::query()
            ->with('pendingPerbaikanRequest')
            ->where('guru_id', $user->guru_id)
            ->when($tahunPelajaran, fn ($q) => $q->where('tahun_pelajaran', $tahunPelajaran))
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('jam_ke')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();
    }

    public function assertOwnership(Jadwal $jadwal): void
    {
        $user = $this->assertGuruAccount();

        if ((int) $jadwal->guru_id !== (int) $user->guru_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }
    }

    public function store(array $data): Jadwal
    {
        $user = $this->assertGuruAccount();
        $guruId = (int) $user->guru_id;

        if (($data['mapel'] ?? '') === '__lainnya__') {
            $this->mataPelajaranUsulanService->submit($guruId, $data['mapel_baru'] ?? '');

            throw ValidationException::withMessages([
                'mapel' => 'Usulan mata pelajaran telah dikirim ke admin. Tunggu persetujuan sebelum mapel dapat digunakan di jadwal.',
            ]);
        }

        $kelas = $this->jadwalMasterDataService->resolveKelas($data['kode_kelas']);
        $mapel = $this->jadwalMasterDataService->resolveMapel($data['mapel']);
        $rangeData = $this->jadwalMasterDataService->mergeJamRangeIntoData($data);

        if ($this->jadwalScheduleService->hasGuruScheduleDuplicate(
            $guruId,
            $rangeData['hari'],
            $rangeData['jam_ke'],
            $kelas,
            $rangeData['semester'],
            $rangeData['tahun_pelajaran'],
        )) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Jadwal dengan Guru, Hari, Jam Ke, Kelas, Semester, dan Tahun Pelajaran yang sama sudah ada.',
            ]);
        }

        if ($this->jadwalScheduleService->hasTimeConflict(
            $guruId,
            $rangeData['hari'],
            $rangeData['jam_mulai'],
            $rangeData['jam_selesai'],
        )) {
            throw ValidationException::withMessages([
                'jam_ke' => 'Guru sudah memiliki jadwal yang bertabrakan pada waktu tersebut.',
            ]);
        }

        $jadwal = Jadwal::create([
            'guru_id' => $guruId,
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

        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($guruId));

        return $jadwal;
    }

    /**
     * Data untuk form perbaikan (select kelas/hari/jam/mapel terisi nilai slot saat ini).
     */
    public function perbaikanFormData(Jadwal $jadwal): array
    {
        $jamSelection = app(JurnalJamSelectionService::class);

        return array_merge($this->formData(), [
            'selectedKodeKelas' => $this->jadwalMasterDataService->findKodeKelasValue($jadwal->kelas),
            'selectedMapel' => $jadwal->mapel,
            'selectedJamKe' => $jadwal->jam_ke,
            'selectedJamKeAkhir' => $jamSelection->jamAkhirKeForJadwal($jadwal),
            'selectedHari' => $jadwal->hari,
            'selectedJamMulai' => substr((string) $jadwal->jam_mulai, 0, 5),
            'selectedJamSelesai' => substr((string) $jadwal->jam_selesai, 0, 5),
            'showMapelLainnya' => false,
        ]);
    }

    /**
     * Guru menimpa slot jadwal yang sama (id tidak berubah → jurnal tetap terikat),
     * lalu menyimpan jejak permintaan berstatus sudah diterapkan.
     *
     * @param  array{kode_kelas:string,mapel:string,hari:string,jam_ke:int|string,jam_ke_akhir?:int|string|null,alasan:string}  $data
     */
    public function submitPerbaikan(Jadwal $jadwal, array $data): JadwalPerbaikanRequest
    {
        $this->assertOwnership($jadwal);
        JadwalPerbaikanSchema::ensure();

        if (! $jadwal->is_locked) {
            throw ValidationException::withMessages([
                'alasan' => 'Jadwal ini sedang dalam proses perbaikan oleh admin.',
            ]);
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            throw ValidationException::withMessages([
                'alasan' => 'Sudah ada permintaan perbaikan yang menunggu persetujuan admin.',
            ]);
        }

        $user = $this->assertGuruAccount();
        $kelas = $this->jadwalMasterDataService->resolveKelas($data['kode_kelas']);
        $mapel = $this->jadwalMasterDataService->resolveMapel($data['mapel']);

        $rangeInput = [
            'hari' => $data['hari'],
            'jam_ke' => $data['jam_ke'],
            'jam_ke_akhir' => $data['jam_ke_akhir'] ?? $data['jam_ke'],
            'tahun_pelajaran' => $jadwal->tahun_pelajaran,
            'semester' => $jadwal->semester,
        ];
        $rangeData = $this->jadwalMasterDataService->mergeJamRangeIntoData($rangeInput);

        if ($this->jadwalScheduleService->hasGuruScheduleDuplicate(
            (int) $jadwal->guru_id,
            $rangeData['hari'],
            (int) $rangeData['jam_ke'],
            $kelas,
            $jadwal->semester,
            $jadwal->tahun_pelajaran,
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

        $oldValues = $this->snapshotJadwal($jadwal);

        return DB::transaction(function () use ($jadwal, $kelas, $mapel, $rangeData, $data, $user, $oldValues) {
            $jadwal->update([
                'kelas' => $kelas,
                'mapel' => $mapel,
                'hari' => $rangeData['hari'],
                'pola_jadwal_id' => $rangeData['pola_jadwal_id'],
                'jam_ke' => $rangeData['jam_ke'],
                'jam_mulai' => $rangeData['jam_mulai'],
                'jam_selesai' => $rangeData['jam_selesai'],
                'is_locked' => true,
            ]);

            $jadwal->refresh();
            $newValues = $this->snapshotJadwal($jadwal);

            $request = JadwalPerbaikanRequest::create([
                'jadwal_id' => $jadwal->id,
                'guru_id' => $jadwal->guru_id,
                'alasan' => $data['alasan'],
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'status' => JadwalPerbaikanRequest::STATUS_APPLIED,
                'processed_by' => $user->id,
                'admin_catatan' => 'Diterapkan langsung oleh guru (timpa slot).',
                'processed_at' => now(),
            ]);

            $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($jadwal->guru_id));

            return $request;
        });
    }

    /** @return array<string, mixed> */
    private function snapshotJadwal(Jadwal $jadwal): array
    {
        return [
            'kelas' => $jadwal->kelas,
            'mapel' => $jadwal->mapel,
            'hari' => $jadwal->hari,
            'pola_jadwal_id' => $jadwal->pola_jadwal_id,
            'jam_ke' => $jadwal->jam_ke,
            'jam_mulai' => substr((string) $jadwal->jam_mulai, 0, 5),
            'jam_selesai' => substr((string) $jadwal->jam_selesai, 0, 5),
            'tahun_pelajaran' => $jadwal->tahun_pelajaran,
            'semester' => $jadwal->semester,
        ];
    }

    public function getStatusLabel(Jadwal $jadwal): string
    {
        if ($jadwal->relationLoaded('pendingPerbaikanRequest') && $jadwal->pendingPerbaikanRequest) {
            return 'MENUNGGU PERBAIKAN';
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            return 'MENUNGGU PERBAIKAN';
        }

        return $jadwal->is_locked ? 'TERKUNCI' : 'TERBUKA';
    }
}
