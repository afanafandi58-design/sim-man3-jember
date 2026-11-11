<?php

namespace App\Http\Controllers;

use App\Services\ImportDataService;
use Illuminate\View\View;

class ImportDataController extends Controller
{
    public function __construct(
        private ImportDataService $importDataService,
    ) {}

    public function index(): View
    {
        $workflow = $this->importDataService->getWorkflowStatus();

        return view('import-data.index', $workflow);
    }
}
