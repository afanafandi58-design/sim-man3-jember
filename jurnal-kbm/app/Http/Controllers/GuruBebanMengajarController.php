<?php

namespace App\Http\Controllers;

use App\Services\GuruBebanMengajarService;
use App\Services\GuruService;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GuruBebanMengajarController extends Controller
{
    public function __construct(
        private GuruBebanMengajarService $guruBebanMengajarService,
    ) {}

    public function index(Request $request): View
    {
        $tahunPelajaran = $request->get('tahun_pelajaran', GuruService::DEFAULT_TAHUN_PELAJARAN);
        $items = $this->guruBebanMengajarService->paginate(
            $request->only(['nama', 'nip', 'mapel', 'status']),
            $tahunPelajaran
        );
        $mapels = $this->guruBebanMengajarService->mapelOptions();

        return view('guru-beban-mengajar.index', compact('items', 'mapels', 'tahunPelajaran'));
    }
}
