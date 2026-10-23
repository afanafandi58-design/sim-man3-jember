<?php

namespace App\Http\Controllers;

use App\Models\JamPelajaran;
use App\Services\JamPelajaranService;
use Illuminate\Http\Request;

class JamPelajaranController extends Controller
{
    public function __construct(
        private JamPelajaranService $jamPelajaranService,
    ) {}

    public function index()
    {
        $jamPelajarans = $this->jamPelajaranService->paginate();

        return view('master.jam-pelajaran.index', compact('jamPelajarans'));
    }

    public function create()
    {
        $polaOptions = $this->jamPelajaranService->polaOptions();

        return view('master.jam-pelajaran.create', compact('polaOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pola_jadwal_id' => 'required|exists:pola_jadwals,id',
            'jam_ke' => 'required|integer|min:0|max:30',
            'nama_sesi' => 'nullable|string|max:100',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $this->jamPelajaranService->store($validated);

        return redirect()
            ->route('jam-pelajaran.index')
            ->with('success', 'Master jam pelajaran berhasil ditambahkan.');
    }

    public function edit(JamPelajaran $jamPelajaran)
    {
        $polaOptions = $this->jamPelajaranService->polaOptions();

        return view('master.jam-pelajaran.edit', compact('jamPelajaran', 'polaOptions'));
    }

    public function update(Request $request, JamPelajaran $jamPelajaran)
    {
        $validated = $request->validate([
            'pola_jadwal_id' => 'required|exists:pola_jadwals,id',
            'jam_ke' => 'required|integer|min:0|max:30',
            'nama_sesi' => 'nullable|string|max:100',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $this->jamPelajaranService->update($jamPelajaran, $validated);

        return redirect()
            ->route('jam-pelajaran.index')
            ->with('success', 'Master jam pelajaran berhasil diperbarui.');
    }

    public function destroy(JamPelajaran $jamPelajaran)
    {
        $this->jamPelajaranService->delete($jamPelajaran);

        return redirect()
            ->route('jam-pelajaran.index')
            ->with('success', 'Master jam pelajaran dinonaktifkan.');
    }
}
