<?php

namespace App\Http\Controllers;

use App\Services\KepalaDashboardService;

class KepalaDashboardController extends Controller
{
    public function __construct(
        private KepalaDashboardService $kepalaDashboardService,
    ) {}

    public function index()
    {
        $summary = $this->kepalaDashboardService->getSummary();

        return view('kepala.dashboard', compact('summary'));
    }

    public function detail(string $type)
    {
        $detail = $this->kepalaDashboardService->getDetail($type);

        return view('kepala.detail', compact('detail'));
    }
}
