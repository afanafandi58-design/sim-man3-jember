<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Services\MataPelajaranService;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function __construct(
        private MataPelajaranService $mataPelajaranService,
    ) {}

    public function index()
    {
        $mataPelajarans = $this->mataPelajaranService->paginate();

        return view('master.mata-pelajaran.index', compact('mataPelajarans'));
    }

    public function create()
    {
        return view('master.mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $this->mataPelajaranService->store($validated);

        return redirect()
            ->route('mata-pelajaran.index')
            ->with('success', 'Master mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('master.mata-pelajaran.edit', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $this->mataPelajaranService->update($mataPelajaran, $validated);

        return redirect()
            ->route('mata-pelajaran.index')
            ->with('success', 'Master mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $this->mataPelajaranService->delete($mataPelajaran);

        return redirect()
            ->route('mata-pelajaran.index')
            ->with('success', 'Master mata pelajaran dinonaktifkan.');
    }
}
