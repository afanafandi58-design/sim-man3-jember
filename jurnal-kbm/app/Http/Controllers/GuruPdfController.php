<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Services\GuruJurnalService;
use App\Services\OperationalDateService;
use Barryvdh\DomPDF\Facade\PDF;

class GuruPdfController extends Controller
{
    public function __construct(
        private GuruJurnalService $guruJurnalService,
        private OperationalDateService $operationalDate,
    ) {}

    public function jurnal(Jurnal $jurnal)
    {
        $this->guruJurnalService->assertJurnalOwnership($jurnal);
        $jurnal->load(['jadwal.guru', 'attachments']);

        $showUatPdfFooter = $this->operationalDate->isSimulation();

        $pdf = PDF::loadView('guru.jurnal.pdf_jurnal', compact('jurnal', 'showUatPdfFooter'));

        $namaFile = 'Jurnal-'
            .str_replace(' ', '-', $jurnal->jadwal->guru->nama)
            .'-'
            .$jurnal->tanggal
            .'.pdf';

        if (request()->has('download')) {
            return $pdf->download($namaFile);
        }

        return $pdf->stream($namaFile);
    }
}
