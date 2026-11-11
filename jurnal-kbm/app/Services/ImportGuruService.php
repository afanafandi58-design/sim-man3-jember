<?php

namespace App\Services;

use App\DTO\ImportGuruResult;
use App\DTO\ImportPreviewResult;
use App\Models\Guru;
use App\Support\Import\ImportModule;
use App\Support\Import\ImportTemplateRegistry;
use App\Support\Import\NormalizesImportValues;
use App\Support\Import\ReadsImportWorkbook;
use App\Traits\MapsExcelColumns;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportGuruService
{
    use MapsExcelColumns;
    use NormalizesImportValues;
    use ReadsImportWorkbook;

    public function __construct(
        private GuruService $guruService,
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

    public function import(UploadedFile $file): ImportGuruResult
    {
        $analysis = $this->analyzeFile($file);

        $this->assertImportAllowed($analysis['headerErrors'], $analysis['invalid'] + $analysis['duplicate'], $analysis['total']);

        $successCount = 0;
        $accountsProvisioned = 0;
        $accountsUpdated = 0;
        $failedCount = $analysis['invalid'] + $analysis['duplicate'];

        if (! empty($analysis['validRows'])) {
            DB::transaction(function () use ($analysis, &$successCount, &$accountsProvisioned, &$accountsUpdated) {
                foreach ($analysis['validRows'] as $rowData) {
                    $hadAccount = $rowData['nip'] !== null
                        && Guru::query()->where('nip', $rowData['nip'])->whereHas('user')->exists();

                    try {
                        $guru = $this->guruService->upsertFromImport($rowData);
                    } catch (QueryException $e) {
                        if ($this->isDuplicateNipException($e)) {
                            throw ValidationException::withMessages([
                                'file' => $rowData['nip'] !== null
                                    ? "NIP {$rowData['nip']} sudah digunakan."
                                    : 'Data guru tanpa NIP tidak dapat disimpan karena bentrok dengan data yang sudah ada.',
                            ]);
                        }

                        if ($this->isNipNullConstraintException($e)) {
                            throw ValidationException::withMessages([
                                'file' => 'Guru tanpa NIP tidak dapat disimpan. Kolom nip pada database harus mengizinkan nilai NULL.',
                            ]);
                        }

                        throw $e;
                    }

                    $successCount++;

                    if ($guru->user && ! $hadAccount) {
                        $accountsProvisioned++;
                    } elseif ($guru->user) {
                        $accountsUpdated++;
                    }
                }
            });
        }

        $this->guruService->syncAllJumlahJamMengajar();

        return new ImportGuruResult(
            total: $analysis['total'],
            success: $successCount,
            failed: $failedCount,
            errors: $analysis['errors'],
            accountsProvisioned: $accountsProvisioned,
            accountsUpdated: $accountsUpdated,
        );
    }

    private function analyzeFile(UploadedFile $file): array
    {
        $definition = ImportTemplateRegistry::get(ImportModule::GURU);
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

        $existingNips = Guru::query()
            ->whereNotNull('nip')
            ->pluck('nip')
            ->flip();
        $errors = [];
        $previewRows = [];
        $validRows = [];
        $valid = 0;
        $invalid = 0;
        $duplicate = 0;
        $total = 0;
        $seenNipsInFile = collect();

        foreach ($parsed['rows'] as $entry) {
            $rowNumber = $entry['row_number'];
            $rowData = $this->normalizeRow($entry['data']);

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $total++;
            $validation = $this->validateRow($rowData, $rowNumber, $seenNipsInFile);

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

            if ($rowData['nip'] !== null) {
                $seenNipsInFile->put($rowData['nip'], true);
            }

            $valid++;
            $validRows[] = $rowData;
            $previewRows[] = array_merge($rowData, [
                'status' => $rowData['nip'] !== null && $existingNips->has($rowData['nip']) ? 'update' : 'valid',
                'row' => $rowNumber,
            ]);
        }

        if ($total === 0 && empty($headerErrors)) {
            throw ValidationException::withMessages([
                'file' => 'Sheet DATA tidak memiliki baris data untuk diimport.',
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
            'nip' => $this->normalizeOptionalNip($this->pickString($data, ['nip', 'no_nip', 'nomor_nip'])),
            'nama' => $this->normalizeRequiredString($this->pickString($data, ['nama', 'nama_guru', 'nama_lengkap'])),
        ];
    }

    private function isEmptyRow(array $row): bool
    {
        return $row['nip'] === null && $row['nama'] === '';
    }

    private function validateRow(array $row, int $rowNumber, Collection $seenNipsInFile): array
    {
        $errors = [];

        if ($row['nama'] === '') {
            $errors[] = "Baris {$rowNumber}: Kolom nama wajib diisi.";
        }

        if ($row['nip'] !== null && mb_strlen($row['nip']) > 50) {
            $errors[] = "Baris {$rowNumber}: NIP maksimal 50 karakter.";
        }

        if (! empty($errors)) {
            return ['errors' => $errors, 'status' => 'error', 'data' => null];
        }

        if ($row['nip'] !== null && $seenNipsInFile->has($row['nip'])) {
            return [
                'errors' => ["Baris {$rowNumber}: NIP {$row['nip']} sudah digunakan."],
                'status' => 'duplicate',
                'data' => null,
            ];
        }

        return ['errors' => [], 'status' => 'valid', 'data' => $row];
    }

    private function isDuplicateNipException(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'Duplicate entry')
            || str_contains($message, '1062')
            || str_contains($message, 'gurus_nip_unique');
    }

    private function isNipNullConstraintException(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, '1048')
            && str_contains($message, 'nip');
    }
}
