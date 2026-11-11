<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Services\GuruService;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function __construct(
        private GuruService $guruService,
    ) {}

    public function index(Request $request)
    {
        $gurus = $this->guruService->paginate($request->only(['keyword', 'nama', 'nip', 'mapel', 'status']));
        $mapels = $this->guruService->mapelOptions();

        return view('guru.index', compact('gurus', 'mapels'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50',
            'mapel' => 'nullable|string|max:255',
        ]);

        $this->guruService->create($validated);

        return redirect()
            ->route('gurus.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50',
            'mapel' => 'nullable|string|max:255',
        ]);

        $this->guruService->update($guru, $validated);

        return redirect()
            ->route('gurus.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $this->guruService->delete($guru);

        return redirect()
            ->route('gurus.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
