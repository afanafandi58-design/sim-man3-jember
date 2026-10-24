<?php

namespace App\Http\Controllers;

use App\Services\LaporanPdfService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LaporanPdfController extends Controller
{
    public function __construct(
        private LaporanPdfService $laporanPdfService,
    ) {}

    public function index()
    {
        $options = $this->laporanPdfService->getFilterOptions();

        return view('laporan.index', $options);
    }

    public function cetak(Request $request)
    {
        $filters = $request->validate([
            'periode' => 'required|in:harian,mingguan,bulanan,semester',
            'tanggal' => 'nullable|date',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'semester' => 'nullable|in:Ganjil,Genap',
            'tahun_pelajaran' => 'nullable|string|max:20',
            'guru_id' => 'nullable|exists:gurus,id',
            'mapel' => 'nullable|string|max:100',
            'kelas' => 'nullable|string|max:100',
        ]);

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);

            $reportData = $this->laporanPdfService->getReportData($filters);

            if ($reportData['jurnals']->isEmpty()) {
                return redirect()
                    ->route('laporan.index')
                    ->withInput()
                    ->withErrors([
                        'periode' => 'Tidak ada data jurnal untuk filter yang dipilih.',
                    ]);
            }

            $pdf = PDF::loadView('laporan.rekap', $reportData)
                ->setPaper('a4', 'landscape');

            return $pdf->stream($this->laporanPdfService->buildFilename($reportData));
        } catch (Throwable $e) {
            Log::error('Gagal cetak laporan jurnal PDF', [
                'message' => $e->getMessage(),
                'filters' => $filters,
            ]);

            return redirect()
                ->route('laporan.index')
                ->withInput()
                ->withErrors([
                    'periode' => 'Gagal membuat PDF laporan. Coba filter lebih sempit atau ulangi beberapa saat lagi.',
                ]);
        }
    }
}
