<?php

namespace App\DTO;

class ImportPreviewResult
{
    public function __construct(
        public readonly int $total,
        public readonly int $valid,
        public readonly int $invalid,
        public readonly array $rows,
        public readonly array $errors,
        public readonly int $duplicate = 0,
        public readonly array $headerErrors = [],
        public readonly bool $sheetValid = true,
        public readonly bool $canImport = false,
    ) {}

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'valid' => $this->valid,
            'invalid' => $this->invalid,
            'duplicate' => $this->duplicate,
            'rows' => $this->rows,
            'errors' => $this->errors,
            'header_errors' => $this->headerErrors,
            'sheet_valid' => $this->sheetValid,
            'can_import' => $this->canImport,
        ];
    }
}
