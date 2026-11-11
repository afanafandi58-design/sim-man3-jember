<?php

namespace App\Services;

use App\DTO\ImportPreviewResult;
use App\DTO\ImportSiswaResult;
use App\Models\KodeKelas;
use App\Models\Siswa;
use App\Support\Import\ImportModule;
use App\Support\Import\ImportTemplateRegistry;
use App\Support\Import\NormalizesImportValues;
use App\Support\Import\ReadsImportWorkbook;
use App\Traits\MapsExcelColumns;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSiswaService
{
    use MapsExcelColumns;
    use NormalizesImportValues;
    use ReadsImportWorkbook;

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

    public function import(UploadedFile $file): ImportSiswaResult
    {
        $analysis = $this->analyzeFile($file);

        $this->assertImportAllowed($analysis['headerErrors'], $analysis['invalid'] + $analysis['duplicate'], $analysis['total']);

        $success = 0;

        DB::transaction(function () use ($analysis, &$success) {
            foreach ($analysis['validRows'] as $item) {
                $data = $item['data'];
                $kodeKelas = $item['kode_kelas'];

                Siswa::updateOrCreate(
                    ['nis' => $data['nis']],
                    [
                        'nisn' => $data['nisn'] ?: null,
                        'nama' => $data['nama'],
                        'jenis_kelamin' => $data['jenis_kelamin'] ?: null,
                        'tempat_lahir' => $data['tempat_lahir'] !== '' ? $data['tempat_lahir'] : null,
                        'tanggal_lahir' => $data['tanggal_lahir'] ?: null,
                        'kelas' => $kodeKelas->kelas,
                        'rombel' => $kodeKelas->rombel,
                        'kode_kelas' => $kodeKelas->kode_kelas,
                        'tahun_pelajaran' => $data['tahun_pelajaran'] ?: date('Y').'/'.(date('Y') + 1),
                        'status' => $data['status'] ?: Siswa::STATUS_AKTIF,
                    ],
                );

                $success++;
            }
        });

        return new ImportSiswaResult(
            total: $analysis['total'],
            success: $success,
            failed: $analysis['invalid'] + $analysis['duplicate'],
            errors: $analysis['errors'],
        );
    }

    private function analyzeFile(UploadedFile $file): array
    {
        if ($this->usesOfficialTemplate($this->getWorkbookSheetNames($file))) {
            return $this->analyzeOfficialFile($file);
        }

        return $this->analyzeLegacyFile($file);
    }

    private function analyzeOfficialFile(UploadedFile $file): array
    {
        $definition = ImportTemplateRegistry::get(ImportModule::SISWA);
        $parsed = $this->readOfficialOrLegacyRows($file, $definition);
        $headerErrors = $parsed['header_errors'];

        if (! empty($headerErrors)) {
            return $this->emptyAnalysis($headerErrors);
        }

        return $this->processRows($parsed['rows']);
    }

    private function analyzeLegacyFile(UploadedFile $file): array
    {
        $workbook = IOFactory::load($file->getRealPath());
        $entries = collect();

        foreach ($workbook->getAllSheets() as $sheet) {
            if (in_array($sheet->getTitle(), [ImportModule::SHEET_PETUNJUK, '_LIST'], true)) {
                continue;
            }

            $sheetRows = $sheet->toArray();

            if (empty($sheetRows)) {
                continue;
            }

            $headers = $this->extractHeaders($sheetRows[0] ?? []);

            if (! $this->sheetHasStudentColumns($headers)) {
                continue;
            }

            for ($i = 1; $i < count($sheetRows); $i++) {
                $mapped = [];

                foreach ($headers as $index => $header) {
                    if ($header !== '') {
                        $mapped[$header] = $sheetRows[$i][$index] ?? null;
                    }
                }

                $entries->push([
                    'row_number' => $i + 1,
                    'data' => $mapped,
                    'label' => "Sheet {$sheet->getTitle()} baris ".($i + 1),
                ]);
            }
        }

        if ($entries->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'Tidak ditemukan data siswa. Gunakan template resmi atau pastikan kolom nis dan nama tersedia.',
            ]);
        }

        return $this->processRows($entries);
    }

    private function processRows(Collection $rows): array
    {
        $kodeKelasMap = KodeKelas::query()
            ->get()
            ->keyBy(fn (KodeKelas $item) => strtolower($item->kode_kelas));

        $errors = [];
        $previewRows = [];
        $validRows = [];
        $valid = 0;
        $invalid = 0;
        $duplicate = 0;
        $total = 0;
        $seenNisInFile = collect();

        foreach ($rows as $entry) {
            $rowNumber = $entry['row_number'];
            $label = $entry['label'] ?? "Baris {$rowNumber}";
            $data = $this->normalizeRow($entry['data']);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $total++;
            $rowResult = $this->validateRow($data, $label, $kodeKelasMap, $seenNisInFile);

            if ($rowResult['status'] === 'duplicate') {
                $duplicate++;
                $errors = array_merge($errors, $rowResult['errors']);
                $previewRows[] = array_merge($data, ['status' => 'duplicate', 'row' => $rowNumber]);
                continue;
            }

            if (! empty($rowResult['errors'])) {
                $invalid++;
                $errors = array_merge($errors, $rowResult['errors']);
                $previewRows[] = array_merge($data, ['status' => 'error', 'row' => $rowNumber]);
                continue;
            }

            $seenNisInFile->put($data['nis'], true);
            $valid++;
            $validRows[] = [
                'data' => $data,
                'kode_kelas' => $rowResult['kode_kelas'],
            ];
            $previewRows[] = array_merge($data, ['status' => 'valid', 'row' => $rowNumber]);
        }

        if ($total === 0) {
            throw ValidationException::withMessages([
                'file' => 'Tidak ditemukan data siswa untuk diimport.',
            ]);
        }

        return [
            'headerErrors' => [],
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicate' => $duplicate,
            'errors' => $errors,
            'previewRows' => $previewRows,
            'validRows' => $validRows,
        ];
    }

    private function emptyAnalysis(array $headerErrors): array
    {
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

    private function validateRow(
        array $data,
        string $label,
        Collection $kodeKelasMap,
        Collection $seenNisInFile,
    ): array {
        $errors = [];

        if ($data['nis'] === '' || $data['nama'] === '') {
            return [
                'errors' => ["{$label}: nis dan nama wajib diisi."],
                'status' => 'error',
                'kode_kelas' => null,
            ];
        }

        if ($seenNisInFile->has($data['nis'])) {
            return [
                'errors' => ["{$label}: NIS \"{$data['nis']}\" duplikat dalam file yang sama."],
                'status' => 'duplicate',
                'kode_kelas' => null,
            ];
        }

        if ($data['jenis_kelamin'] !== null && ! in_array($data['jenis_kelamin'], ['L', 'P'], true)) {
            $errors[] = "{$label}: jenis_kelamin harus L atau P.";
        }

        if ($data['status'] !== null && ! in_array($data['status'], Siswa::STATUSES, true)) {
            $errors[] = "{$label}: status harus ".implode(', ', Siswa::STATUSES).'.';
        }

        if ($data['tempat_lahir'] !== '' && mb_strlen($data['tempat_lahir']) > 255) {
            $errors[] = "{$label}: tempat_lahir maksimal 255 karakter.";
        }

        if ($data['tanggal_lahir'] === false) {
            $errors[] = "{$label}: format tanggal_lahir tidak valid (gunakan YYYY-MM-DD).";
        }

        $kodeKelas = $this->resolveKodeKelas($data, $kodeKelasMap);

        if (! $kodeKelas) {
            $errors[] = "{$label}: kode_kelas \"{$data['kode_kelas']}\" tidak ditemukan di master kelas.";
        }

        if (! empty($errors)) {
            return ['errors' => $errors, 'status' => 'error', 'kode_kelas' => null];
        }

        return ['errors' => [], 'status' => 'valid', 'kode_kelas' => $kodeKelas];
    }

    private function sheetHasStudentColumns(array $headers): bool
    {
        $hasNis = collect($headers)->contains(fn ($header) => in_array($header, ['nis', 'no_induk', 'nomor_induk'], true));
        $hasNama = collect($headers)->contains(fn ($header) => in_array($header, ['nama', 'nama_siswa', 'nama_lengkap'], true));

        return $hasNis && $hasNama;
    }

    private function normalizeRow(array $data): array
    {
        $mapped = $this->mapExcelRow($data);
        $jk = strtoupper($this->pickString($mapped, ['jenis_kelamin', 'jk', 'kelamin']));
        $status = strtolower($this->pickString($mapped, ['status']));
        $rawDate = $this->pickColumn($mapped, ['tanggal_lahir', 'tgl_lahir', 'tanggal']);
        $tanggalLahir = $this->normalizeDate($rawDate);

        if ($rawDate !== null && $rawDate !== '' && $tanggalLahir === false) {
            $tanggalLahir = false;
        }

        return [
            'nis' => $this->normalizeNip($this->pickString($mapped, ['nis', 'no_induk', 'nomor_induk'])),
            'nisn' => $this->normalizeNip($this->pickString($mapped, ['nisn', 'no_nisn', 'nomor_nisn'])),
            'nama' => $this->pickString($mapped, ['nama', 'nama_siswa', 'nama_lengkap']),
            'jenis_kelamin' => in_array($jk, ['L', 'P'], true) ? $jk : ($jk === '' ? null : $jk),
            'tempat_lahir' => $this->pickString($mapped, ['tempat_lahir', 'tmp_lahir', 'tempat']),
            'tanggal_lahir' => $tanggalLahir === false ? false : $tanggalLahir,
            'kode_kelas' => $this->pickString($mapped, ['kode_kelas', 'kode', 'kodekelas']),
            'kelas' => $this->pickString($mapped, ['kelas', 'tingkat']),
            'rombel' => $this->pickString($mapped, ['rombel', 'nama_rombel', 'nama_kelas']),
            'tahun_pelajaran' => $this->pickString($mapped, ['tahun_pelajaran', 'tp', 'tahun_ajaran', 'ta']),
            'status' => in_array($status, Siswa::STATUSES, true) ? $status : ($status === '' ? null : $status),
        ];
    }

    private function normalizeDate(mixed $value): string|false|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);

            return $date->format('Y-m-d');
        }

        $string = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $string)) {
            return $string;
        }

        $timestamp = strtotime($string);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : false;
    }

    private function resolveKodeKelas(array $data, Collection $kodeKelasMap): ?KodeKelas
    {
        if ($data['kode_kelas'] !== '') {
            return $kodeKelasMap->get(strtolower($data['kode_kelas']));
        }

        if ($data['kelas'] !== '' && $data['rombel'] !== '') {
            return KodeKelas::query()
                ->where('kelas', $data['kelas'])
                ->where('rombel', $data['rombel'])
                ->first();
        }

        return null;
    }

    private function isEmptyRow(array $row): bool
    {
        return $row['nis'] === '' && $row['nama'] === '';
    }
}
