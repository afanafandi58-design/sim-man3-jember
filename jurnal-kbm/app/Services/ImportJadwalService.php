<?php

namespace App\Services;

use App\DTO\ImportJadwalResult;
use App\DTO\ImportPreviewResult;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Support\Import\ImportModule;
use App\Support\Import\ImportTemplateRegistry;
use App\Support\Import\NormalizesImportValues;
use App\Support\Import\ReadsImportWorkbook;
use App\Traits\MapsExcelColumns;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportJadwalService
{
    use MapsExcelColumns;
    use NormalizesImportValues;
    use ReadsImportWorkbook;

    private const VALID_HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const VALID_SEMESTER = ['Ganjil', 'Genap'];

    private array $importFingerprints = [];

    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private GuruService $guruService,
        private KodeKelasResolverService $kodeKelasResolverService,
    ) {}

    public function preview(UploadedFile $file): ImportPreviewResult
    {
        $analysis = $this->analyzeFile($file);

        return $this->buildPreviewResult(
            total: $analysis['total'],
            valid: $analysis['valid'],
            invalid: $analysis['invalid'],
            duplicate: $analysis['duplicate'],
            rows: $analysis['previewRows'],
            errors: $analysis['errors'],
            headerErrors: $analysis['headerErrors'],
        );
    }

    public function import(UploadedFile $file): ImportJadwalResult
    {
        $analysis = $this->analyzeFile($file);

        $this->assertImportAllowed($analysis['headerErrors'], $analysis['invalid'] + $analysis['duplicate'], $analysis['total']);

        $successCount = 0;

        if (! empty($analysis['validRows'])) {
            DB::transaction(function () use ($analysis, &$successCount) {
                foreach ($analysis['validRows'] as $rowData) {
                    Jadwal::create([
                        'guru_id' => $rowData['guru_id'],
                        'kelas' => $rowData['kelas'],
                        'mapel' => $rowData['mapel'],
                        'hari' => $rowData['hari'],
                        'jam_ke' => $rowData['jam_ke'],
                        'jam_mulai' => $rowData['jam_mulai'],
                        'jam_selesai' => $rowData['jam_selesai'],
                        'tahun_pelajaran' => $rowData['tahun_pelajaran'],
                        'semester' => $rowData['semester'],
                    ]);

                    $successCount++;
                }
            });

            $this->syncJamMengajar(collect($analysis['validRows']));
        }

        return new ImportJadwalResult(
            total: $analysis['total'],
            success: $successCount,
            failed: $analysis['invalid'] + $analysis['duplicate'],
            errors: $analysis['errors'],
        );
    }

    private function analyzeFile(UploadedFile $file): array
    {
        $this->importFingerprints = [];
        $definition = ImportTemplateRegistry::get(ImportModule::JADWAL);
        $parsed = $this->readOfficialOrLegacyRows($file, $definition);
        $headerErrors = $parsed['header_errors'];

        if (! empty($headerErrors)) {
            return [
                'headerErrors' => $headerErrors,
                'total' => 0,
                'valid' => 0,
                'invalid' => 0,
                'duplicate' => 0,
                'errors' => $headerErrors,
                'previewRows' => [],
                'validRows' => [],
            ];
        }

        $guruMap = $this->buildGuruMapByKode();
        $legacyNipMap = $this->buildLegacyNipMap();
        $errors = [];
        $previewRows = [];
        $validRows = [];
        $valid = 0;
        $invalid = 0;
        $duplicate = 0;
        $total = 0;

        foreach ($parsed['rows'] as $entry) {
            $rowNumber = $entry['row_number'];
            $rowData = $this->normalizeRow($entry['data']);

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $total++;
            $validation = $this->validateRow($rowData, $rowNumber, $guruMap, $legacyNipMap);

            if ($validation['status'] === 'duplicate') {
                $duplicate++;
                $errors = array_merge($errors, $validation['errors']);
                $previewRows[] = array_merge($rowData, ['status' => 'duplicate', 'row' => $rowNumber]);
                continue;
            }

            if (! empty($validation['errors'])) {
                $invalid++;
                $errors = array_merge($errors, $validation['errors']);
                $previewRows[] = array_merge($rowData, ['status' => 'error', 'row' => $rowNumber]);
                continue;
            }

            $valid++;
            $validRows[] = $validation['data'];
            $this->importFingerprints[] = $this->jadwalScheduleService->buildScheduleFingerprint($validation['data']);
            $previewRows[] = array_merge($rowData, ['status' => 'valid', 'row' => $rowNumber]);
        }

        if ($total === 0 && empty($headerErrors)) {
            throw ValidationException::withMessages([
                'file' => 'Sheet DATA tidak memiliki baris data jadwal.',
            ]);
        }

        return compact(
            'headerErrors',
            'total',
            'valid',
            'invalid',
            'duplicate',
            'errors',
            'previewRows',
            'validRows',
        );
    }

    private function syncJamMengajar(Collection $validRows): void
    {
        $guruIds = collect($validRows)->pluck('guru_id')->unique();

        Guru::query()->whereIn('id', $guruIds)->each(
            fn (Guru $guru) => $this->guruService->syncJumlahJamMengajar($guru)
        );
    }

    private function normalizeRow(array $row): array
    {
        $data = $this->mapExcelRow($row);

        return [
            'kode_guru' => $this->normalizeKodeGuru($this->pickString($data, ['kode_guru', 'kode', 'kodeguru'])) ?? '',
            'nip_legacy' => $this->normalizeOptionalNip($this->pickString($data, ['nip', 'no_nip', 'nomor_nip', 'nip_guru'])),
            'kelas' => $this->pickString($data, ['kelas', 'nama_kelas', 'rombel']),
            'mapel' => $this->pickString($data, ['mapel', 'mata_pelajaran', 'matpel', 'nama_mapel']),
            'hari' => $this->normalizeHari($this->pickString($data, ['hari', 'nama_hari'])),
            'jam_ke' => $this->pickColumn($data, ['jam_ke', 'jam', 'jp', 'jam_pelajaran', 'ke']),
            'jam_mulai' => $this->normalizeTime($this->pickColumn($data, ['jam_mulai', 'mulai', 'waktu_mulai', 'jam_awal'])),
            'jam_selesai' => $this->normalizeTime($this->pickColumn($data, ['jam_selesai', 'selesai', 'waktu_selesai', 'jam_akhir'])),
            'tahun_pelajaran' => $this->pickString($data, ['tahun_pelajaran', 'tp', 'tahun_ajaran', 'ta']),
            'semester' => $this->normalizeSemester($this->pickString($data, ['semester', 'sem'])),
        ];
    }

    private function normalizeHari(string $hari): string
    {
        return ucfirst(strtolower(trim($hari)));
    }

    private function normalizeSemester(string $semester): string
    {
        $semester = trim($semester);

        return match ($semester) {
            '1', 'I' => 'Ganjil',
            '2', 'II' => 'Genap',
            default => ucfirst(strtolower($semester)),
        };
    }

    private function buildGuruMapByKode(): Collection
    {
        return Guru::query()
            ->whereNotNull('kode_guru')
            ->get()
            ->mapWithKeys(fn (Guru $guru) => [strtoupper($guru->kode_guru) => $guru->id]);
    }

    private function buildLegacyNipMap(): Collection
    {
        return Guru::query()
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->pluck('id', 'nip');
    }

    private function resolveGuruId(array $row, Collection $guruMap, Collection $legacyNipMap): ?int
    {
        if ($row['kode_guru'] !== '' && $guruMap->has($row['kode_guru'])) {
            return (int) $guruMap->get($row['kode_guru']);
        }

        if ($row['kode_guru'] === '' && $row['nip_legacy'] !== null && $legacyNipMap->has($row['nip_legacy'])) {
            return (int) $legacyNipMap->get($row['nip_legacy']);
        }

        return null;
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)
            ->except(['jam_ke', 'nip_legacy'])
            ->every(fn ($value) => $value === '' || $value === null);
    }

    private function validateRow(array $row, int $rowNumber, Collection $guruMap, Collection $legacyNipMap): array
    {
        $errors = [];
        $required = ImportTemplateRegistry::get(ImportModule::JADWAL)['required'];

        foreach ($required as $column) {
            if ($column === 'jam_ke') {
                if ($row['jam_ke'] === '' || $row['jam_ke'] === null) {
                    $errors[] = "Baris {$rowNumber}: Kolom jam_ke wajib diisi.";
                }

                continue;
            }

            if ($row[$column] === '' || $row[$column] === null) {
                $errors[] = "Baris {$rowNumber}: Kolom {$column} wajib diisi.";
            }
        }

        if (! empty($errors)) {
            return ['errors' => $errors, 'status' => 'error', 'data' => null];
        }

        $guruId = $this->resolveGuruId($row, $guruMap, $legacyNipMap);

        if ($guruId === null) {
            return [
                'errors' => ["Baris {$rowNumber}: Kode Guru {$row['kode_guru']} tidak ditemukan."],
                'status' => 'error',
                'data' => null,
            ];
        }

        if (! $this->kodeKelasResolverService->resolveFromString($row['kelas'])) {
            return [
                'errors' => ["Baris {$rowNumber}: Kelas \"{$row['kelas']}\" tidak ditemukan di master kode kelas."],
                'status' => 'error',
                'data' => null,
            ];
        }

        if (! in_array($row['hari'], self::VALID_HARI, true)) {
            $errors[] = "Baris {$rowNumber}: Hari \"{$row['hari']}\" tidak valid. Gunakan: ".implode(', ', self::VALID_HARI).'.';
        }

        if (! in_array($row['semester'], self::VALID_SEMESTER, true)) {
            $errors[] = "Baris {$rowNumber}: Semester \"{$row['semester']}\" tidak valid. Gunakan: Ganjil atau Genap.";
        }

        if (! is_numeric($row['jam_ke']) || (int) $row['jam_ke'] < 1) {
            $errors[] = "Baris {$rowNumber}: jam_ke harus berupa angka positif.";
        }

        if ($row['jam_mulai'] === null) {
            $errors[] = "Baris {$rowNumber}: Format jam_mulai tidak valid.";
        }

        if ($row['jam_selesai'] === null) {
            $errors[] = "Baris {$rowNumber}: Format jam_selesai tidak valid.";
        }

        if ($row['jam_mulai'] !== null && $row['jam_selesai'] !== null && $row['jam_mulai'] >= $row['jam_selesai']) {
            $errors[] = "Baris {$rowNumber}: jam_selesai harus lebih besar dari jam_mulai.";
        }

        if (! empty($errors)) {
            return ['errors' => $errors, 'status' => 'error', 'data' => null];
        }

        $row['guru_id'] = $guruId;
        unset($row['nip_legacy']);
        $row['jam_ke'] = (int) $row['jam_ke'];

        $fingerprint = $this->jadwalScheduleService->buildScheduleFingerprint($row);

        if (in_array($fingerprint, $this->importFingerprints, true)) {
            return [
                'errors' => ["Baris {$rowNumber}: Jadwal duplikat dalam file yang sama."],
                'status' => 'duplicate',
                'data' => null,
            ];
        }

        if ($this->jadwalScheduleService->isDuplicateSchedule($row)) {
            return [
                'errors' => ["Baris {$rowNumber}: Jadwal sudah ada di database."],
                'status' => 'duplicate',
                'data' => null,
            ];
        }

        if ($this->jadwalScheduleService->hasTimeConflict(
            $row['guru_id'],
            $row['hari'],
            $row['jam_mulai'],
            $row['jam_selesai'],
        )) {
            return [
                'errors' => ["Baris {$rowNumber}: Guru sudah memiliki jadwal bentrok pada waktu tersebut."],
                'status' => 'error',
                'data' => null,
            ];
        }

        return ['errors' => [], 'status' => 'valid', 'data' => $row];
    }

    private function normalizeTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (float) $value * 86400;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = floor($seconds % 60);

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        $time = trim((string) $value);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return strlen($time) === 5 ? "{$time}:00" : $time;
        }

        $timestamp = strtotime($time);

        return $timestamp !== false ? date('H:i:s', $timestamp) : null;
    }
}
