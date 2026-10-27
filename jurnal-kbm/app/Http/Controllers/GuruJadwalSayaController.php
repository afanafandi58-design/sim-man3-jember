<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Services\GuruJadwalSayaService;
use App\Services\GuruService;
use Illuminate\Http\Request;

class GuruJadwalSayaController extends Controller
{
    public function __construct(
        private GuruJadwalSayaService $guruJadwalSayaService,
    ) {}

    public function index(Request $request)
    {
        $guru = $this->guruJadwalSayaService->getGuru();
        $jadwals = $this->guruJadwalSayaService->paginateForGuru([
            'tahun_pelajaran' => $request->get('tahun_pelajaran', GuruService::DEFAULT_TAHUN_PELAJARAN),
            'semester' => $request->get('semester'),
        ]);

        $tahunPelajaran = $request->get('tahun_pelajaran', GuruService::DEFAULT_TAHUN_PELAJARAN);
        $semester = $request->get('semester');

        return view('guru.jadwal-saya.index', compact('guru', 'jadwals', 'tahunPelajaran', 'semester'));
    }

    public function create()
    {
        $guru = $this->guruJadwalSayaService->getGuru();
        $formData = $this->guruJadwalSayaService->formData();

        return view('guru.jadwal-saya.create', array_merge(compact('guru'), $formData));
    }

    public function store(Request $request)
    {
        $rules = [
            'kode_kelas' => 'required|string|max:50',
            'mapel' => 'required|string|max:100',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'tahun_pelajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
        ];

        if ($request->input('mapel') === '__lainnya__') {
            $rules['mapel_baru'] = 'required|string|min:2|max:100';
        }

        $validated = $request->validate($rules);

        $this->guruJadwalSayaService->store($validated);

        return redirect()
            ->route('guru.jadwal-saya.index')
            ->with('success', 'Jadwal berhasil disimpan dan status menjadi TERKUNCI.');
    }

    public function perbaikanForm(Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);

        if (! $jadwal->is_locked) {
            return redirect()
                ->route('guru.jadwal-saya.index')
                ->with('warning', 'Jadwal ini sedang dalam proses perbaikan oleh admin.');
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            return redirect()
                ->route('guru.jadwal-saya.index')
                ->with('warning', 'Permintaan perbaikan untuk jadwal ini sudah diajukan.');
        }

        $guru = $this->guruJadwalSayaService->getGuru();

        return view('guru.jadwal-saya.perbaikan', compact('jadwal', 'guru'));
    }

    public function perbaikanStore(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|min:10|max:1000',
        ]);

        $this->guruJadwalSayaService->submitPerbaikan($jadwal, $validated['alasan']);

        return redirect()
            ->route('guru.jadwal-saya.index')
            ->with('success', 'Permintaan perbaikan berhasil diajukan. Menunggu persetujuan admin.');
    }
}
