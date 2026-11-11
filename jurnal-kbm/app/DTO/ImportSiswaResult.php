<?php

namespace App\DTO;

class ImportSiswaResult
{
    public function __construct(
        public int $total,
        public int $success,
        public int $failed,
        public array $errors = [],
    ) {}
}
