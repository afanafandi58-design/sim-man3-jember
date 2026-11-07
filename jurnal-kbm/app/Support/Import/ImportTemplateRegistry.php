<?php

namespace App\Support\Import;

use App\Models\Siswa;

final class ImportTemplateRegistry
{
    public static function get(string $module): array
    {
        return match ($module) {
            ImportModule::GURU => self::guru(),
            ImportModule::JADWAL => self::jadwal(),
            ImportModule::KELAS => self::kelas(),
            ImportModule::SISWA => self::siswa(),
            default => throw new \InvalidArgumentException("Modul import tidak dikenal: {$module}"),
        };
    }

    public static function filename(string $module): string
    {
        return match ($module) {
            ImportModule::GURU => 'Template_Import_Guru_SIM_MAN3.xlsx',
            ImportModule::JADWAL => 'Template_Import_Jadwal_SIM_MAN3.xlsx',
            ImportModule::KELAS => 'Template_Import_Kelas_SIM_MAN3.xlsx',
            ImportModule::SISWA => 'Template_Import_Siswa_SIM_MAN3.xlsx',
            default => 'Template_Import_SIM_MAN3.xlsx',
        };
    }

    private static function guru(): array
    {
        return [
            'module' => ImportModule::GURU,
            'title' => 'Import Data Guru',
            'legacy_sheets' => ['Guru'],
            'headers' => ['nip', 'nama'],
            'required' => ['nama'],
            'optional' => ['nip'],
            'example' => [
                'nip' => '198001012010011001',
                'nama' => 'Ahmad Fauzi, S.Pd',
            ],
            'instructions' => [
                'TEMPLATE RESMI — IMPORT DATA GURU | SIM MAN 3 JEMBER',
                '',
                '1. Jangan ubah nama sheet (PETUNJUK, DATA) dan header baris 1 pada sheet DATA.',
                '2. Isi data mulai baris 3. Baris 2 berisi contoh — hapus atau timpa saat mengisi.',
                '3. Kolom wajib: nama.',
                '4. Kolom opsional: nip (Opsional).',
                '5. Kode Guru di-generate otomatis sistem (G001, G002, dst.) — digunakan pada Import Jadwal.',
                '6. Mata pelajaran diatur melalui Import Jadwal, bukan di sini.',
                '7. Jika NIP diisi, harus unik. Duplikat NIP dalam file akan ditolak.',
                '8. Format NIP: teks/angka tanpa spasi.',
                '9. Simpan file sebagai .xlsx sebelum upload.',
            ],
            'validations' => [],
        ];
    }

    private static function jadwal(): array
    {
        return [
            'module' => ImportModule::JADWAL,
            'title' => 'Import Jadwal Mengajar',
            'legacy_sheets' => ['Jadwal'],
            'headers' => [
                'kode_guru', 'kelas', 'mapel', 'hari', 'jam_ke',
                'jam_mulai', 'jam_selesai', 'tahun_pelajaran', 'semester',
            ],
            'required' => [
                'kode_guru', 'kelas', 'mapel', 'hari', 'jam_ke',
                'jam_mulai', 'jam_selesai', 'tahun_pelajaran', 'semester',
            ],
            'optional' => [],
            'example' => [
                'kode_guru' => 'G001',
                'kelas' => 'X IPA 1',
                'mapel' => 'Matematika',
                'hari' => 'Senin',
                'jam_ke' => 1,
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '07:45:00',
                'tahun_pelajaran' => '2026/2027',
                'semester' => 'Ganjil',
            ],
            'instructions' => [
                'TEMPLATE RESMI — IMPORT JADWAL MENGAJAR | SIM MAN 3 JEMBER',
                '',
                '1. Jangan ubah nama sheet (PETUNJUK, DATA) dan header baris 1 pada sheet DATA.',
                '2. Isi data mulai baris 3. Baris 2 berisi contoh — hapus atau timpa saat mengisi.',
                '3. Semua kolom wajib diisi.',
                '4. kode_guru harus sudah terdaftar di master guru (gunakan dropdown).',
                '5. Satu guru dapat memiliki banyak baris jadwal dengan mapel berbeda.',
                '6. kelas harus sesuai master kode kelas (gunakan dropdown).',
                '7. hari: Senin–Sabtu. semester: Ganjil atau Genap.',
                '8. Format waktu: HH:MM:SS (contoh 07:00:00).',
                '9. tahun_pelajaran: YYYY/YYYY (contoh 2026/2027).',
                '10. Import guru dan kode kelas harus sudah dilakukan terlebih dahulu.',
            ],
            'validations' => [
                'kode_guru' => 'kode_guru_master',
                'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'semester' => ['Ganjil', 'Genap'],
                'kelas' => 'kode_kelas_master',
            ],
        ];
    }

    private static function kelas(): array
    {
        return [
            'module' => ImportModule::KELAS,
            'title' => 'Import Kode Kelas',
            'legacy_sheets' => [],
            'headers' => ['kode_kelas', 'kelas', 'rombel'],
            'required' => ['kode_kelas', 'kelas', 'rombel'],
            'optional' => [],
            'example' => [
                'kode_kelas' => 'X-IPA-1',
                'kelas' => 'X',
                'rombel' => 'X IPA 1',
            ],
            'instructions' => [
                'TEMPLATE RESMI — IMPORT KODE KELAS | SIM MAN 3 JEMBER',
                '',
                '1. Jangan ubah nama sheet (PETUNJUK, DATA) dan header baris 1 pada sheet DATA.',
                '2. Isi data mulai baris 3. Baris 2 berisi contoh — hapus atau timpa saat mengisi.',
                '3. Semua kolom wajib: kode_kelas, kelas, rombel.',
                '4. kode_kelas harus unik (identifier sistem).',
                '5. rombel ditampilkan pada jadwal dan absensi (contoh: X IPA 1).',
                '6. Import kelas wajib sebelum import siswa dan jadwal.',
            ],
            'validations' => [],
        ];
    }

    private static function siswa(): array
    {
        return [
            'module' => ImportModule::SISWA,
            'title' => 'Import Data Siswa',
            'legacy_sheets' => [],
            'headers' => [
                'nis', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir',
                'tanggal_lahir', 'kode_kelas', 'tahun_pelajaran', 'status',
            ],
            'required' => ['nis', 'nama', 'kode_kelas'],
            'optional' => ['nisn', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'tahun_pelajaran', 'status'],
            'example' => [
                'nis' => '1234567890',
                'nisn' => '0012345678',
                'nama' => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Jember',
                'tanggal_lahir' => '2010-05-15',
                'kode_kelas' => 'X-IPA-1',
                'tahun_pelajaran' => '2026/2027',
                'status' => Siswa::STATUS_AKTIF,
            ],
            'instructions' => [
                'TEMPLATE RESMI — IMPORT DATA SISWA | SIM MAN 3 JEMBER',
                '',
                '1. Jangan ubah nama sheet (PETUNJUK, DATA) dan header baris 1 pada sheet DATA.',
                '2. Isi data mulai baris 3. Baris 2 berisi contoh — hapus atau timpa saat mengisi.',
                '3. Kolom wajib: nis, nama, kode_kelas.',
                '4. Kolom opsional: nisn, jenis_kelamin, tempat_lahir (Opsional / Optional), tanggal_lahir (Opsional / Optional), tahun_pelajaran, status.',
                '5. kode_kelas harus sudah ada di master kelas (gunakan dropdown).',
                '6. jenis_kelamin: L atau P.',
                '7. status: aktif, lulus, atau pindah (default aktif jika kosong).',
                '8. Format tanggal_lahir (jika diisi): YYYY-MM-DD (contoh 2010-05-15).',
                '9. NIS harus unik. Duplikat NIS dalam file akan ditolak.',
                '10. Import kode kelas harus sudah dilakukan terlebih dahulu.',
            ],
            'validations' => [
                'jenis_kelamin' => ['L', 'P'],
                'status' => Siswa::STATUSES,
                'kode_kelas' => 'kode_kelas_master',
            ],
        ];
    }
}
