<?php

namespace App\DTO;

class ImportKodeKelasResult
{
    public function __construct(
        public int $total,
        public int $success,
        public int $failed,
        public array $errors = [],
    ) {}
}
