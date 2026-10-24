<?php

namespace App\Support\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

trait ReadsImportWorkbook
{
    protected function getWorkbookSheetNames(UploadedFile $file): array
    {
        return IOFactory::load($file->getRealPath())->getSheetNames();
    }

    protected function usesOfficialTemplate(array $sheetNames): bool
    {
        return in_array(ImportModule::SHEET_DATA, $sheetNames, true);
    }

    protected function resolveDataSheetName(array $sheetNames, array $legacySheets = [], bool $fallbackFirstSheet = false): ?string
    {
        if (in_array(ImportModule::SHEET_DATA, $sheetNames, true)) {
            return ImportModule::SHEET_DATA;
        }

        foreach ($legacySheets as $legacySheet) {
            if (in_array($legacySheet, $sheetNames, true)) {
                return $legacySheet;
            }
        }

        if ($fallbackFirstSheet) {
            foreach ($sheetNames as $sheetName) {
                if (! in_array($sheetName, [ImportModule::SHEET_PETUNJUK, '_LIST'], true)) {
                    return $sheetName;
                }
            }
        }

        return null;
    }

    protected function assertDataSheetExists(
        UploadedFile $file,
        array $legacySheets,
        string $moduleLabel,
        bool $fallbackFirstSheet = false,
    ): string {
        $sheetNames = $this->getWorkbookSheetNames($file);
        $dataSheet = $this->resolveDataSheetName($sheetNames, $legacySheets, $fallbackFirstSheet);

        if ($dataSheet === null) {
            $legacyHint = $legacySheets !== []
                ? ' atau sheet legacy: '.implode(', ', $legacySheets)
                : ($fallbackFirstSheet ? ' atau sheet data pertama' : '');

            throw ValidationException::withMessages([
                'file' => "File harus memiliki sheet \"DATA\" (template resmi){$legacyHint}.",
            ]);
        }

        return $dataSheet;
    }

    protected function normalizeHeader(mixed $header): string
    {
        return strtolower(trim(preg_replace('/\s+/', '_', (string) $header)));
    }

    protected function extractHeaders(array $firstRow): array
    {
        return array_map(fn ($header) => $this->normalizeHeader($header), $firstRow);
    }

    protected function validateOfficialHeaders(array $actualRow, array $officialHeaders): array
    {
        $errors = [];
        $actual = array_values(array_filter(
            array_map(fn ($value) => trim((string) $value), $actualRow),
            fn ($value) => $value !== '',
        ));

        if (count($actual) < count($officialHeaders)) {
            $errors[] = 'Header sheet DATA tidak lengkap. Gunakan template resmi sistem.';
        }

        foreach ($officialHeaders as $index => $expected) {
            $found = $this->normalizeHeader($actual[$index] ?? '');

            if ($found !== $this->normalizeHeader($expected)) {
                $errors[] = 'Header kolom '.($index + 1)." harus \"{$expected}\"".
                    (($actual[$index] ?? '') !== '' ? ", ditemukan \"{$actual[$index]}\"" : ', kolom kosong/tidak ada.');
            }
        }

        return $errors;
    }

    protected function assertImportAllowed(array $headerErrors, int $invalid, int $total): void
    {
        if (! empty($headerErrors)) {
            throw ValidationException::withMessages([
                'file' => 'Header template tidak valid. Unduh template resmi dan jangan ubah nama kolom.',
            ]);
        }

        if ($total === 0) {
            throw ValidationException::withMessages([
                'file' => 'Tidak ada data untuk diimport.',
            ]);
        }

        if ($invalid > 0) {
            throw ValidationException::withMessages([
                'file' => 'Terdapat baris error. Perbaiki file lalu upload ulang. Gunakan Preview sebelum import.',
            ]);
        }
    }

    protected function buildPreviewResult(
        int $total,
        int $valid,
        int $invalid,
        int $duplicate,
        array $rows,
        array $errors,
        array $headerErrors = [],
        bool $sheetValid = true,
    ): \App\DTO\ImportPreviewResult {
        return new \App\DTO\ImportPreviewResult(
            total: $total,
            valid: $valid,
            invalid: $invalid,
            duplicate: $duplicate,
            rows: array_slice($rows, 0, 50),
            errors: $errors,
            headerErrors: $headerErrors,
            sheetValid: $sheetValid,
            canImport: $sheetValid && empty($headerErrors) && $invalid === 0 && $duplicate === 0 && $total > 0,
        );
    }

    protected function readOfficialOrLegacyRows(UploadedFile $file, array $definition, bool $fallbackFirstSheet = false): array
    {
        $sheetNames = $this->getWorkbookSheetNames($file);
        $dataSheetName = $this->assertDataSheetExists(
            $file,
            $definition['legacy_sheets'],
            $definition['title'],
            $fallbackFirstSheet,
        );
        $workbook = IOFactory::load($file->getRealPath());
        $sheet = $workbook->getSheetByName($dataSheetName);
        $allRows = $sheet->toArray();
        $headerRow = $allRows[0] ?? [];
        $headerErrors = [];

        if ($this->usesOfficialTemplate($sheetNames)) {
            $headerErrors = $this->validateOfficialHeaders($headerRow, $definition['headers']);
        }

        $headers = $this->extractHeaders($headerRow);
        $dataRows = collect();
        $startIndex = $this->usesOfficialTemplate($sheetNames) ? 2 : 1;

        for ($i = $startIndex; $i < count($allRows); $i++) {
            $mapped = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $mapped[$header] = $allRows[$i][$index] ?? null;
                }
            }

            $dataRows->push([
                'row_number' => $i + 1,
                'data' => $mapped,
            ]);
        }

        return [
            'header_errors' => $headerErrors,
            'rows' => $dataRows,
            'official' => $this->usesOfficialTemplate($sheetNames),
        ];
    }
}
