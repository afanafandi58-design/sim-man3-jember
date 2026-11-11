<?php

namespace App\Http\Controllers;

use App\Services\GuruDashboardService;

class GuruDashboardController extends Controller
{
    public function __construct(
        private GuruDashboardService $guruDashboardService,
    ) {}

    public function index()
    {
        return view('guru.dashboard', $this->guruDashboardService->getDashboardData(auth()->user()));
    }
}
