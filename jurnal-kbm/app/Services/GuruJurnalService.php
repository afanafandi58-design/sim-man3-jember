<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\User;
use App\Support\TitikESchema;
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

    /** Pengampu jadwal atau guru lain (mode pengganti) boleh mengakses form isi. */
    public function assertJadwalAccess(Jadwal $jadwal): void
    {
        $this->assertGuruAccount();
        TitikESchema::ensure();
    }

    public function isJadwalOwner(Jadwal $jadwal): bool
    {
        $user = $this->assertGuruAccount();

        return (int) $jadwal->guru_id === (int) $user->guru_id;
    }

    public function assertJurnalAccess(Jurnal $jurnal): void
    {
        $jurnal->loadMissing('jadwal.guru');
        $user = $this->assertGuruAccount();
        TitikESchema::ensure();

        $isOwner = (int) $jurnal->jadwal->guru_id === (int) $user->guru_id;
        $isPengganti = filled($jurnal->guru_pengganti)
            && $user->guru
            && strcasecmp(trim((string) $jurnal->guru_pengganti), trim((string) $user->guru->nama)) === 0;

        if (! $isOwner && ! $isPengganti) {
            abort(403, 'Anda tidak memiliki akses ke jurnal ini.');
        }
    }

    /** @deprecated gunakan assertJadwalAccess */
    public function assertJadwalOwnership(Jadwal $jadwal): void
    {
        $this->assertJadwalAccess($jadwal);
    }

    /** @deprecated gunakan assertJurnalAccess */
    public function assertJurnalOwnership(Jurnal $jurnal): void
    {
        $this->assertJurnalAccess($jurnal);
    }

    public function create(array $data, ?array $absensiInput = null): Jurnal
    {
        TitikESchema::ensure();

        $jadwal = Jadwal::with('guru')->findOrFail($data['jadwal_id']);
        $this->assertJadwalAccess($jadwal);
        $user = $this->assertGuruAccount();
        $user->loadMissing('guru');

        $this->operationalDate->assertFillableDate((string) $data['tanggal']);

        $isOwner = $this->isJadwalOwner($jadwal);
        $guruPengganti = trim((string) ($data['guru_pengganti'] ?? ''));

        if (! $isOwner) {
            // Pengisi bukan pengampu → wajib tercatat sebagai guru pengganti; pengampu tetap di jadwal.
            $data['guru_pengganti'] = $guruPengganti !== ''
                ? $guruPengganti
                : (string) ($user->guru?->nama ?? $user->name);
            if (($data['status_kbm'] ?? '') === 'Selesai') {
                $data['status_kbm'] = 'Guru Pengganti';
            }
        } else {
            $data['guru_pengganti'] = $guruPengganti !== '' ? $guruPengganti : null;
            if (($data['status_kbm'] ?? '') === 'Guru Pengganti' && empty($data['guru_pengganti'])) {
                throw ValidationException::withMessages([
                    'guru_pengganti' => 'Isi nama guru pengganti bila status KBM adalah Guru Pengganti.',
                ]);
            }
        }

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
        TitikESchema::ensure();
        $this->assertJurnalAccess($jurnal);
        $jurnal->loadMissing('jadwal.guru');

        if ($jurnal->isLocked()) {
            throw ValidationException::withMessages([
                'tanggal' => 'Jurnal di luar toleransi H/H-1 tidak dapat diubah.',
            ]);
        }

        if (array_key_exists('guru_pengganti', $data)) {
            $data['guru_pengganti'] = trim((string) $data['guru_pengganti']) !== ''
                ? trim((string) $data['guru_pengganti'])
                : null;
        }

        if (($data['status_kbm'] ?? $jurnal->status_kbm) === 'Guru Pengganti'
            && empty($data['guru_pengganti'] ?? $jurnal->guru_pengganti)) {
            throw ValidationException::withMessages([
                'guru_pengganti' => 'Isi nama guru pengganti bila status KBM adalah Guru Pengganti.',
            ]);
        }

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
        return $this->findJurnalIdForDate($jadwal, $this->operationalDate->today()->toDateString());
    }

    public function findJurnalIdForDate(Jadwal $jadwal, string $date): ?int
    {
        return Jurnal::query()
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $date)
            ->value('id');
    }
}
