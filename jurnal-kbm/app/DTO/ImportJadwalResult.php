<?php

namespace App\DTO;

class ImportJadwalResult
{
    public function __construct(
        public readonly int $total,
        public readonly int $success,
        public readonly int $failed,
        public readonly array $errors,
    ) {}

    public function progressPercent(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) round(($this->success / $this->total) * 100);
    }
}
