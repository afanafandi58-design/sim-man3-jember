<?php

namespace App\Http\Controllers;

use App\Models\PolaJadwal;
use App\Services\PolaJadwalService;
use Illuminate\Http\Request;

class PolaJadwalController extends Controller
{
    public function __construct(
        private PolaJadwalService $polaJadwalService,
    ) {}

    public function index()
    {
        $polaJadwals = $this->polaJadwalService->paginate();

        return view('master.pola-jadwal.index', compact('polaJadwals'));
    }

    public function create()
    {
        return view('master.pola-jadwal.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10',
            'nama' => 'required|string|max:150',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $this->polaJadwalService->store($validated);

        return redirect()
            ->route('pola-jadwal.index')
            ->with('success', 'Master Pola Jadwal berhasil ditambahkan.');
    }

    public function edit(PolaJadwal $polaJadwal)
    {
        return view('master.pola-jadwal.edit', compact('polaJadwal'));
    }

    public function update(Request $request, PolaJadwal $polaJadwal)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $this->polaJadwalService->update($polaJadwal, $validated);

        return redirect()
            ->route('pola-jadwal.index')
            ->with('success', 'Master Pola Jadwal berhasil diperbarui.');
    }
}
