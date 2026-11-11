<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalSheetImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private Collection $rows = new Collection(),
    ) {}

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }
}
