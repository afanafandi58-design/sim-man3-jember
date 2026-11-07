<?php

namespace App\Http\Controllers;

use App\Services\RekapAbsensiService;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function __construct(
        private RekapAbsensiService $rekapAbsensiService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'tanggal' => 'nullable|date',
            'guru_id' => 'nullable|exists:gurus,id',
            'mapel' => 'nullable|string|max:100',
            'kelas' => 'nullable|string|max:100',
            'rombel' => 'nullable|string|max:100',
            'status' => 'nullable|in:Hadir,Sakit,Izin,Alpha',
        ]);

        $options = $this->rekapAbsensiService->getFilterOptions();
        $absensi = $this->rekapAbsensiService->paginate($filters);

        return view('absensi-siswa.index', array_merge($options, [
            'absensi' => $absensi,
            'filters' => $filters,
        ]));
    }
}
