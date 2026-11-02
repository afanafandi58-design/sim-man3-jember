<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPerbaikanRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GuruJadwalSayaService
{
    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private JadwalMasterDataService $jadwalMasterDataService,
        private MataPelajaranUsulanService $mataPelajaranUsulanService,
        private GuruService $guruService,
        private AdminJadwalService $adminJadwalService,
        private JadwalChangeLogger $changeLogger,
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

    public function formDataForEdit(Jadwal $jadwal): array
    {
        $jamSelection = app(JurnalJamSelectionService::class);

        return array_merge($this->formData(), [
            'selectedKodeKelas' => $this->jadwalMasterDataService->findKodeKelasValue($jadwal->kelas),
            'selectedMapel' => $jadwal->mapel,
            'selectedJamKe' => $jadwal->jam_ke,
            'selectedJamKeAkhir' => $jamSelection->jamAkhirKeForJadwal($jadwal),
            'selectedHari' => $jadwal->hari,
            'showMapelLainnya' => false,
        ]);
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
            'is_locked' => false,
        ]);

        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($guruId));

        $this->changeLogger->log(
            'guru_create',
            $user,
            $jadwal,
            $guruId,
            null,
            $this->changeLogger->snapshot($jadwal),
            request()->ip(),
        );

        return $jadwal;
    }

    public function update(Jadwal $jadwal, array $data, ?string $ipAddress = null): Jadwal
    {
        $this->assertOwnership($jadwal);
        $user = $this->assertGuruAccount();
        $guruId = (int) $user->guru_id;

        if (($data['mapel'] ?? '') === '__lainnya__') {
            throw ValidationException::withMessages([
                'mapel' => 'Pilih mapel dari daftar. Usulan mapel baru lewat menu usulan admin.',
            ]);
        }

        $before = $this->changeLogger->snapshot($jadwal);

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
            $jadwal->id,
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
            'is_locked' => false,
        ]);

        $jadwal->refresh();
        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($guruId));

        $this->changeLogger->log(
            'guru_update',
            $user,
            $jadwal,
            $guruId,
            $before,
            $this->changeLogger->snapshot($jadwal),
            $ipAddress,
        );

        return $jadwal;
    }

    public function deleteOwned(Jadwal $jadwal, ?string $ipAddress = null): void
    {
        $this->assertOwnership($jadwal);
        $user = $this->assertGuruAccount();

        $canDelete = $this->adminJadwalService->canDelete($jadwal);
        if (! $canDelete['allowed']) {
            throw ValidationException::withMessages([
                'jadwal' => $canDelete['reason'],
            ]);
        }

        $before = $this->changeLogger->snapshot($jadwal);
        $guruId = (int) $jadwal->guru_id;
        $guru = $jadwal->guru;

        $this->changeLogger->log(
            'guru_delete',
            $user,
            $jadwal,
            $guruId,
            $before,
            null,
            $ipAddress,
        );

        $jadwal->delete();

        if ($guru) {
            $this->guruService->syncJumlahJamMengajar($guru);
        }
    }

    public function submitPerbaikan(Jadwal $jadwal, string $alasan): JadwalPerbaikanRequest
    {
        $this->assertOwnership($jadwal);

        if (! $jadwal->is_locked) {
            throw ValidationException::withMessages([
                'alasan' => 'Jadwal ini sedang dalam proses perbaikan.',
            ]);
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            throw ValidationException::withMessages([
                'alasan' => 'Sudah ada permintaan perbaikan yang menunggu persetujuan admin.',
            ]);
        }

        return JadwalPerbaikanRequest::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $jadwal->guru_id,
            'alasan' => $alasan,
            'status' => JadwalPerbaikanRequest::STATUS_PENDING,
        ]);
    }

    public function getStatusLabel(Jadwal $jadwal): string
    {
        if ($jadwal->relationLoaded('pendingPerbaikanRequest') && $jadwal->pendingPerbaikanRequest) {
            return 'MENUNGGU PERBAIKAN';
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            return 'MENUNGGU PERBAIKAN';
        }

        return 'TERBUKA';
    }
}
