<?php

namespace App\Http\Controllers;

use App\Services\ImportTemplateService;
use App\Support\Import\ImportModule;

class ImportTemplateController extends Controller
{
    public function __construct(
        private ImportTemplateService $importTemplateService,
    ) {}

    public function guru()
    {
        return $this->importTemplateService->download(ImportModule::GURU);
    }

    public function jadwal()
    {
        return $this->importTemplateService->download(ImportModule::JADWAL);
    }

    public function kelas()
    {
        return $this->importTemplateService->download(ImportModule::KELAS);
    }

    public function siswa()
    {
        return $this->importTemplateService->download(ImportModule::SISWA);
    }
}
