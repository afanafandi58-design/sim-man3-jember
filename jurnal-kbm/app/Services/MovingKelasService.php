<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JadwalMovingSiswa;
use App\Models\Siswa;
use App\Models\User;
use App\Support\JadwalMovingSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovingKelasService
{
    public function ensureSchema(): void
    {
        JadwalMovingSchema::ensure();
    }

    public function assertCanManage(Jadwal $jadwal): User
    {
        $this->ensureSchema();
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Anda harus login.');
        }

        if (($user->role ?? null) === 'admin') {
            return $user;
        }

        if ($user->guru_id && (int) $user->guru_id === (int) $jadwal->guru_id) {
            return $user;
        }

        abort(403, 'Hanya pemilik jadwal atau admin yang dapat mengatur kelas moving ini.');
    }

    public function setMovingFlag(Jadwal $jadwal, bool $enabled): Jadwal
    {
        $this->assertCanManage($jadwal);
        $this->ensureSchema();

        $jadwal->is_moving_class = $enabled;
        if (! $enabled) {
            $jadwal->moving_kelas_nama = null;
        }
        $jadwal->save();

        if (! $enabled) {
            JadwalMovingSiswa::query()->where('jadwal_id', $jadwal->id)->delete();
        }

        return $jadwal->refresh();
    }

    /**
     * @param  list<int|string>  $siswaIds
     */
    public function saveRoster(Jadwal $jadwal, string $namaKelas, array $siswaIds): Jadwal
    {
        $this->assertCanManage($jadwal);
        $this->ensureSchema();

        if (! $jadwal->is_moving_class) {
            throw ValidationException::withMessages([
                'is_moving_class' => 'Slot ini belum ditandai sebagai moving class.',
            ]);
        }

        $nama = trim($namaKelas);
        if ($nama === '') {
            throw ValidationException::withMessages([
                'moving_kelas_nama' => 'Nama kelas moving wajib diisi.',
            ]);
        }

        $ids = collect($siswaIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Pilih minimal satu siswa dari rombel yang ada.',
            ]);
        }

        $validCount = Siswa::query()
            ->aktif()
            ->whereIn('id', $ids->all())
            ->count();

        if ($validCount !== $ids->count()) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Beberapa siswa tidak valid atau tidak aktif. Hanya siswa dari rombel existing yang boleh dipilih.',
            ]);
        }

        return DB::transaction(function () use ($jadwal, $nama, $ids) {
            $jadwal->moving_kelas_nama = $nama;
            $jadwal->save();

            JadwalMovingSiswa::query()->where('jadwal_id', $jadwal->id)->delete();

            $now = now();
            $rows = $ids->map(fn (int $siswaId) => [
                'jadwal_id' => $jadwal->id,
                'siswa_id' => $siswaId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            JadwalMovingSiswa::query()->insert($rows);

            return $jadwal->refresh();
        });
    }

    /** @return Collection<string, Collection<int, Siswa>> */
    public function candidateSiswaGrouped(): Collection
    {
        $this->ensureSchema();

        return Siswa::query()
            ->aktif()
            ->orderBy('kode_kelas')
            ->orderBy('nama')
            ->get()
            ->groupBy(function (Siswa $siswa) {
                $kode = trim((string) ($siswa->kode_kelas ?: ''));
                if ($kode !== '') {
                    return $kode;
                }

                $kelas = trim((string) ($siswa->kelas ?: '-'));
                $rombel = trim((string) ($siswa->rombel ?: ''));

                return $rombel !== '' ? $kelas.' / '.$rombel : $kelas;
            });
    }

    /** @return list<int> */
    public function selectedSiswaIds(Jadwal $jadwal): array
    {
        $this->ensureSchema();

        return JadwalMovingSiswa::query()
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('siswa_id')
            ->pluck('siswa_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function displayKelasLabel(Jadwal $jadwal): string
    {
        $this->ensureSchema();

        if ($jadwal->is_moving_class && filled($jadwal->moving_kelas_nama)) {
            return (string) $jadwal->moving_kelas_nama;
        }

        return (string) $jadwal->kelas;
    }
}
