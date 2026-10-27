<?php

namespace App\Http\Controllers;

use App\Services\LaporanPdfService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Rekap jurnal milik guru login — Mingguan / Bulanan / Semester.
 * Alur: filter → pratinjau → cetak PDF.
 */
class GuruLaporanController extends Controller
{
    public function __construct(
        private LaporanPdfService $laporanPdfService,
    ) {}

    public function index(Request $request)
    {
        $user = $this->assertGuru($request);
        $guru = $user->guru;
        $options = $this->laporanPdfService->getGuruFilterOptions((int) $user->guru_id);

        return view('guru.laporan.index', array_merge($options, [
            'guru' => $guru,
            'defaultPeriode' => $request->query('periode', 'mingguan'),
        ]));
    }

    public function preview(Request $request)
    {
        $user = $this->assertGuru($request);
        $filters = $this->validatedFilters($request);
        $filters['guru_id'] = (int) $user->guru_id;

        $reportData = $this->laporanPdfService->getReportData($filters);
        $reportData['guru'] = $user->guru;
        $reportData['title'] = $this->laporanPdfService->buildGuruTitle(
            $filters,
            $reportData['startDate'],
            $reportData['endDate'],
            $user->guru?->nama
        );

        if ($reportData['jurnals']->isEmpty()) {
            return redirect()
                ->route('guru.laporan.index')
                ->withInput()
                ->withErrors([
                    'periode' => 'Tidak ada jurnal Anda pada filter periode yang dipilih.',
                ]);
        }

        return view('guru.laporan.preview', array_merge($reportData, [
            'filters' => $filters,
        ]));
    }

    public function cetak(Request $request)
    {
        $user = $this->assertGuru($request);
        $filters = $this->validatedFilters($request);
        $filters['guru_id'] = (int) $user->guru_id;

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);

            $reportData = $this->laporanPdfService->getReportData($filters);
            $reportData['guru'] = $user->guru;
            $reportData['title'] = $this->laporanPdfService->buildGuruTitle(
                $filters,
                $reportData['startDate'],
                $reportData['endDate'],
                $user->guru?->nama
            );

            if ($reportData['jurnals']->isEmpty()) {
                return redirect()
                    ->route('guru.laporan.index')
                    ->withInput()
                    ->withErrors([
                        'periode' => 'Tidak ada jurnal Anda pada filter periode yang dipilih.',
                    ]);
            }

            $pdf = PDF::loadView('guru.laporan.rekap', $reportData)
                ->setPaper('a4', 'landscape');

            $filename = $this->laporanPdfService->buildGuruFilename($reportData);

            return $pdf->stream($filename);
        } catch (Throwable $e) {
            Log::error('Gagal cetak rekap jurnal guru', [
                'message' => $e->getMessage(),
                'guru_id' => $user->guru_id,
                'filters' => $filters,
            ]);

            return redirect()
                ->route('guru.laporan.index')
                ->withInput()
                ->withErrors([
                    'periode' => 'Gagal membuat PDF rekap. Coba filter lebih sempit atau ulangi beberapa saat lagi.',
                ]);
        }
    }

    private function assertGuru(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'guru' && $user->guru_id, 403, 'Akun guru belum terhubung ke data guru.');

        return $user;
    }

    /** @return array<string, mixed> */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'periode' => 'required|in:mingguan,bulanan,semester',
            'tanggal' => 'nullable|date',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'semester' => 'nullable|in:Ganjil,Genap',
            'tahun_pelajaran' => 'nullable|string|max:20',
            'mapel' => 'nullable|string|max:100',
            'kelas' => 'nullable|string|max:100',
        ]);
    }
}
