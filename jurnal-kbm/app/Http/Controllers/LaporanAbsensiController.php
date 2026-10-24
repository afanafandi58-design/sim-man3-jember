<?php

namespace App\Http\Controllers;

use App\Services\RekapAbsensiService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LaporanAbsensiController extends Controller
{
    /** Max calendar days for one cetak request. */
    public const MAX_RANGE_DAYS = 14;

    /** Soft cap to avoid PDF/memory exhaustion. */
    public const MAX_ROWS = 1200;

    /** Without guru/kelas/rombel/mapel, keep ranges short. */
    public const MAX_UNFILTERED_DAYS = 3;

    public function __construct(
        private RekapAbsensiService $rekapAbsensiService,
    ) {}

    public function index()
    {
        return view('laporan.absensi.index', $this->rekapAbsensiService->getFilterOptions());
    }

    public function cetak(Request $request)
    {
        $filters = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'guru_id' => 'nullable|exists:gurus,id',
            'mapel' => 'nullable|string|max:100',
            'kelas' => 'nullable|string|max:100',
            'rombel' => 'nullable|string|max:100',
        ]);

        $start = Carbon::parse($filters['tanggal_mulai'])->startOfDay();
        $end = Carbon::parse($filters['tanggal_selesai'])->startOfDay();
        $rangeDays = $start->diffInDays($end) + 1;

        if ($rangeDays > self::MAX_RANGE_DAYS) {
            return redirect()
                ->route('laporan.absensi.index')
                ->withInput()
                ->withErrors([
                    'tanggal_selesai' => 'Rentang tanggal maksimal '.self::MAX_RANGE_DAYS.' hari. Persempit periode atau bagi menjadi beberapa laporan.',
                ]);
        }

        $hasNarrow = filled($filters['guru_id'] ?? null)
            || filled($filters['mapel'] ?? null)
            || filled($filters['kelas'] ?? null)
            || filled($filters['rombel'] ?? null);

        if ($rangeDays > self::MAX_UNFILTERED_DAYS && ! $hasNarrow) {
            return redirect()
                ->route('laporan.absensi.index')
                ->withInput()
                ->withErrors([
                    'kelas' => 'Untuk rentang lebih dari '.self::MAX_UNFILTERED_DAYS.' hari, pilih minimal satu filter: guru, mapel, kelas, atau rombel.',
                ]);
        }

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);

            // Join-optimized query; probe with limit+1 (no separate COUNT(*)).
            $probe = $this->rekapAbsensiService->getRekap($filters, self::MAX_ROWS + 1);
            if ($probe->isEmpty()) {
                return redirect()
                    ->route('laporan.absensi.index')
                    ->withInput()
                    ->withErrors([
                        'tanggal_mulai' => 'Tidak ada data absensi untuk filter yang dipilih.',
                    ]);
            }

            if ($probe->count() > self::MAX_ROWS) {
                return redirect()
                    ->route('laporan.absensi.index')
                    ->withInput()
                    ->withErrors([
                        'tanggal_selesai' => 'Data terlalu besar (lebih dari '.self::MAX_ROWS.' baris). Tambahkan filter guru/kelas/rombel atau persempit tanggal.',
                    ]);
            }

            $rows = $probe;
            $summary = $this->rekapAbsensiService->summarize($rows);

            $pdf = PDF::loadView('laporan.absensi.rekap', [
                'rows' => $rows,
                'summary' => $summary,
                'filters' => $filters,
            ])->setPaper('a4', 'landscape');

            $filename = 'Rekap-Absensi-Siswa-'
                .$filters['tanggal_mulai']
                .'-'
                .$filters['tanggal_selesai']
                .'.pdf';

            return $pdf->stream($filename);
        } catch (Throwable $e) {
            Log::error('Gagal cetak rekap absensi', [
                'message' => $e->getMessage(),
                'filters' => $filters,
            ]);

            return redirect()
                ->route('laporan.absensi.index')
                ->withInput()
                ->withErrors([
                    'tanggal_mulai' => 'Gagal membuat PDF rekap absensi. Coba rentang lebih pendek, tambah filter kelas/rombel, atau ulangi beberapa saat lagi.',
                ]);
        }
    }
}
