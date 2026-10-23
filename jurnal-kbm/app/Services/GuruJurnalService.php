<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruJurnalService
{
    public function __construct(
        private AbsensiSiswaService $absensiSiswaService,
        private OperationalDateService $operationalDate,
    ) {}

    public function assertGuruAccount(): User
    {
        $user = Auth::user();

        if (! $user || ! $user->guru_id) {
            abort(403, 'Akun guru tidak valid.');
        }

        return $user;
    }

    public function assertJadwalOwnership(Jadwal $jadwal): void
    {
        $user = $this->assertGuruAccount();

        if ((int) $jadwal->guru_id !== (int) $user->guru_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }
    }

    public function assertJurnalOwnership(Jurnal $jurnal): void
    {
        $jurnal->loadMissing('jadwal');
        $this->assertJadwalOwnership($jurnal->jadwal);
    }

    public function create(array $data, ?array $absensiInput = null): Jurnal
    {
        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        $this->assertJadwalOwnership($jadwal);

        if ($absensiInput !== null) {
            $counts = $this->absensiSiswaService->countsFromInput($absensiInput, $jadwal);
            $data = array_merge($data, $counts);
        }

        return DB::transaction(function () use ($data, $jadwal, $absensiInput) {
            $exists = Jurnal::query()
                ->where('jadwal_id', $data['jadwal_id'])
                ->whereDate('tanggal', $data['tanggal'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'jadwal_id' => 'Jurnal untuk jadwal dan tanggal tersebut sudah ada.',
                ]);
            }

            $jurnal = Jurnal::create($data);

            if ($absensiInput !== null) {
                $this->absensiSiswaService->syncForJurnal($jurnal, $jadwal, $absensiInput);
            }

            return $jurnal;
        });
    }

    public function update(Jurnal $jurnal, array $data, ?array $absensiInput = null): Jurnal
    {
        $this->assertJurnalOwnership($jurnal);
        $jurnal->loadMissing('jadwal');

        if ($absensiInput !== null) {
            $counts = $this->absensiSiswaService->countsFromInput($absensiInput, $jurnal->jadwal);
            $data = array_merge($data, $counts);
        }

        return DB::transaction(function () use ($jurnal, $data, $absensiInput) {
            $jurnal->update($data);

            if ($absensiInput !== null) {
                $this->absensiSiswaService->syncForJurnal($jurnal, $jurnal->jadwal, $absensiInput);
            }

            return $jurnal->refresh();
        });
    }

    public function findTodayJurnalId(Jadwal $jadwal): ?int
    {
        return Jurnal::query()
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $this->operationalDate->today())
            ->value('id');
    }
}
