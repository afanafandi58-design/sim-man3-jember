<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Sekolah;
use App\Models\Siswa;

class DashboardController
{
    public function index(): void
    {
        require_login();
        view('layout/main', [
            'title' => 'Dasbor',
            'content' => 'dashboard',
            'totalSiswa' => Siswa::count(),
            'tingkatList' => Siswa::tingkatList(),
            'sekolah' => Sekolah::get(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }
}
