<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait MapsExcelColumns
{
    protected function mapExcelRow(Collection|array $row): array
    {
        return collect($row)->mapWithKeys(function ($value, $key) {
            $normalized = strtolower(trim(preg_replace('/\s+/', '_', (string) $key)));

            return [$normalized => $value];
        })->toArray();
    }

    protected function pickColumn(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            $normalized = strtolower(trim(preg_replace('/\s+/', '_', $key)));

            if (array_key_exists($normalized, $data) && $data[$normalized] !== null && $data[$normalized] !== '') {
                return $data[$normalized];
            }
        }

        return null;
    }

    protected function pickString(array $data, array $keys): string
    {
        $value = $this->pickColumn($data, $keys);

        return trim((string) ($value ?? ''));
    }

    protected function normalizeNip(string $nip): string
    {
        if ($nip === '') {
            return '';
        }

        if (is_numeric($nip) && str_contains($nip, '.')) {
            return number_format((float) $nip, 0, '', '');
        }

        return trim($nip);
    }
}
