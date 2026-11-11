<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\KodeKelas;
use App\Models\Siswa;

class ImportDataService
{
    public function getWorkflowStatus(): array
    {
        $guruTotal = Guru::count();
        $guruWithAccount = Guru::whereHas('user')->count();
        $guruWithoutAccount = Guru::whereDoesntHave('user')->count();
        $kodeKelasTotal = KodeKelas::count();
        $siswaTotal = Siswa::count();
        $jadwalTotal = Jadwal::count();

        $steps = [
            [
                'number' => 1,
                'title' => 'Import Guru',
                'route' => 'import-guru.index',
                'icon' => 'fas fa-user-tie',
                'done' => $guruTotal > 0,
                'summary' => $guruTotal > 0 ? "{$guruTotal} guru terdaftar" : 'Belum ada data guru',
                'features' => ['Validasi', 'Preview', 'Import'],
            ],
            [
                'number' => 2,
                'title' => 'Generate Akun Guru',
                'route' => 'guru-accounts.index',
                'icon' => 'fas fa-users-cog',
                'done' => $guruTotal > 0 && $guruWithoutAccount === 0,
                'summary' => $guruTotal === 0
                    ? 'Import guru terlebih dahulu'
                    : ($guruWithoutAccount > 0
                        ? "{$guruWithoutAccount} guru belum punya akun"
                        : 'Semua guru sudah punya akun'),
                'features' => ['Username', 'Password'],
            ],
            [
                'number' => 3,
                'title' => 'Import Kelas',
                'route' => 'import-siswa.index',
                'icon' => 'fas fa-door-open',
                'done' => $kodeKelasTotal > 0,
                'summary' => $kodeKelasTotal > 0 ? "{$kodeKelasTotal} kode kelas" : 'Belum ada master kelas',
                'features' => ['Preview'],
            ],
            [
                'number' => 4,
                'title' => 'Import Siswa',
                'route' => 'import-siswa.index',
                'icon' => 'fas fa-user-graduate',
                'done' => $siswaTotal > 0,
                'summary' => $siswaTotal > 0 ? "{$siswaTotal} siswa terdaftar" : 'Belum ada data siswa',
                'features' => ['Preview', 'Deteksi Duplikat'],
            ],
            [
                'number' => 5,
                'title' => 'Import Jadwal',
                'route' => 'import-jadwal.index',
                'icon' => 'fas fa-calendar-alt',
                'done' => $jadwalTotal > 0,
                'summary' => $jadwalTotal > 0 ? "{$jadwalTotal} jadwal mengajar" : 'Belum ada jadwal',
                'features' => ['Validasi Guru', 'Validasi Kelas'],
            ],
        ];

        $verification = $this->getVerification();

        return [
            'steps' => $steps,
            'verification' => $verification,
            'counts' => [
                'guru' => $guruTotal,
                'guru_with_account' => $guruWithAccount,
                'kode_kelas' => $kodeKelasTotal,
                'siswa' => $siswaTotal,
                'jadwal' => $jadwalTotal,
            ],
            'completed_steps' => collect($steps)->where('done', true)->count(),
            'total_steps' => count($steps),
        ];
    }

    public function getVerification(): array
    {
        $guruTotal = Guru::count();
        $guruWithAccount = Guru::whereHas('user')->count();
        $kodeKelasTotal = KodeKelas::count();
        $siswaTotal = Siswa::count();
        $jadwalTotal = Jadwal::count();

        $guruOk = $guruTotal > 0 && $guruWithAccount === $guruTotal;
        $siswaOk = $siswaTotal > 0 && $kodeKelasTotal > 0;
        $jadwalOk = $jadwalTotal > 0;

        $guruWithoutAccount = Guru::whereDoesntHave('user')->count();
        $jadwalOrphanGuru = Jadwal::whereDoesntHave('guru')->count();

        $checks = [
            'guru' => [
                'label' => 'Guru',
                'ok' => $guruOk,
                'message' => $guruTotal === 0
                    ? 'Belum ada data guru'
                    : ($guruWithoutAccount > 0
                        ? "{$guruWithoutAccount} guru belum punya akun login"
                        : "{$guruTotal} guru siap ({$guruWithAccount} akun aktif)"),
            ],
            'siswa' => [
                'label' => 'Siswa',
                'ok' => $siswaOk,
                'message' => $kodeKelasTotal === 0
                    ? 'Master kode kelas belum diimport'
                    : ($siswaTotal === 0
                        ? 'Belum ada data siswa'
                        : "{$siswaTotal} siswa di {$kodeKelasTotal} kelas"),
            ],
            'jadwal' => [
                'label' => 'Jadwal',
                'ok' => $jadwalOk && $jadwalOrphanGuru === 0,
                'message' => $jadwalTotal === 0
                    ? 'Belum ada jadwal mengajar'
                    : ($jadwalOrphanGuru > 0
                        ? "{$jadwalOrphanGuru} jadwal tanpa guru valid"
                        : "{$jadwalTotal} jadwal terhubung ke guru"),
            ],
        ];

        $ready = collect($checks)->every(fn (array $check) => $check['ok']);

        return [
            'checks' => $checks,
            'ready' => $ready,
            'ready_message' => $ready
                ? 'Sistem siap digunakan — guru dapat login, mengisi jurnal, dan monitoring kepala madrasah aktif.'
                : 'Selesaikan langkah yang belum lengkap sebelum go-live.',
        ];
    }
}
