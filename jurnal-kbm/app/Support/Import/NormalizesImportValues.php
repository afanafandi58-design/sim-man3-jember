<?php

namespace App\Support\Import;

trait NormalizesImportValues
{
    /**
     * Trim and convert empty strings to null.
     */
    protected function emptyToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    /**
     * Trim required string fields. Empty remains empty string for mandatory validation.
     */
    protected function normalizeRequiredString(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    /**
     * Optional unique identifier: trim, strip whitespace, empty to null.
     */
    protected function normalizeOptionalNip(mixed $value): ?string
    {
        $nip = $this->emptyToNull($value);

        if ($nip === null) {
            return null;
        }

        $nip = preg_replace('/\s+/u', '', $nip);

        if ($nip === '') {
            return null;
        }

        if (is_numeric($nip) && str_contains($nip, '.')) {
            return number_format((float) $nip, 0, '', '');
        }

        return $nip;
    }

    /**
     * Optional string: trim, empty to null.
     */
    protected function normalizeOptionalString(mixed $value): ?string
    {
        return $this->emptyToNull($value);
    }

    /**
     * Kode guru / kode referensi: trim, uppercase, empty to null.
     */
    protected function normalizeKodeGuru(mixed $value): ?string
    {
        $kode = $this->emptyToNull($value);

        return $kode === null ? null : strtoupper($kode);
    }
}
