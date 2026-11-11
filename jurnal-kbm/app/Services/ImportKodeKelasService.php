<?php

namespace App\Services;

use App\DTO\ImportKodeKelasResult;
use App\DTO\ImportPreviewResult;
use App\Models\KodeKelas;
use App\Support\Import\ImportModule;
use App\Support\Import\ImportTemplateRegistry;
use App\Support\Import\NormalizesImportValues;
use App\Support\Import\ReadsImportWorkbook;
use App\Traits\MapsExcelColumns;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportKodeKelasService
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

    public function import(UploadedFile $file): ImportKodeKelasResult
    {
        $analysis = $this->analyzeFile($file);

        $this->assertImportAllowed($analysis['headerErrors'], $analysis['invalid'] + $analysis['duplicate'], $analysis['total']);

        $success = 0;

        DB::transaction(function () use ($analysis, &$success) {
            foreach ($analysis['validRows'] as $data) {
                KodeKelas::updateOrCreate(
                    ['kode_kelas' => $data['kode_kelas']],
                    [
                        'kelas' => $data['kelas'],
                        'rombel' => $data['rombel'],
                    ],
                );

                $success++;
            }
        });

        return new ImportKodeKelasResult(
            total: $analysis['total'],
            success: $success,
            failed: $analysis['invalid'] + $analysis['duplicate'],
            errors: $analysis['errors'],
        );
    }

    private function analyzeFile(UploadedFile $file): array
    {
        $definition = ImportTemplateRegistry::get(ImportModule::KELAS);
        $parsed = $this->readOfficialOrLegacyRows($file, $definition, fallbackFirstSheet: true);
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

        $errors = [];
        $previewRows = [];
        $validRows = [];
        $valid = 0;
        $invalid = 0;
        $duplicate = 0;
        $total = 0;
        $seenKode = collect();

        foreach ($parsed['rows'] as $entry) {
            $rowNumber = $entry['row_number'];
            $data = $this->normalizeRow($entry['data']);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $total++;

            if ($seenKode->has(strtolower($data['kode_kelas'])) && $data['kode_kelas'] !== '') {
                $duplicate++;
                $errors[] = "Baris {$rowNumber}: kode_kelas \"{$data['kode_kelas']}\" duplikat dalam file yang sama.";
                $previewRows[] = array_merge($data, ['status' => 'duplicate', 'row' => $rowNumber]);
                continue;
            }

            if ($data['kode_kelas'] === '' || $data['kelas'] === '' || $data['rombel'] === '') {
                $invalid++;
                $errors[] = "Baris {$rowNumber}: kode_kelas, kelas, dan rombel wajib diisi.";
                $previewRows[] = array_merge($data, ['status' => 'error', 'row' => $rowNumber]);
                continue;
            }

            $seenKode->put(strtolower($data['kode_kelas']), true);
            $valid++;
            $validRows[] = $data;
            $previewRows[] = array_merge($data, ['status' => 'valid', 'row' => $rowNumber]);
        }

        if ($total === 0 && empty($headerErrors)) {
            throw ValidationException::withMessages([
                'file' => 'File Excel tidak memiliki data kode kelas.',
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

    private function normalizeRow(array $row): array
    {
        $data = $this->mapExcelRow($row);

        return [
            'kode_kelas' => $this->pickString($data, ['kode_kelas', 'kode', 'kodekelas']),
            'kelas' => $this->pickString($data, ['kelas', 'tingkat']),
            'rombel' => $this->pickString($data, ['rombel', 'nama_rombel', 'nama_kelas']),
        ];
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => $value === '' || $value === null);
    }
}
