<?php

namespace App\Http\Controllers;

use App\Models\JadwalPerbaikanRequest;
use App\Services\JadwalPerbaikanService;
use Illuminate\Http\Request;

class JadwalPerbaikanController extends Controller
{
    public function __construct(
        private JadwalPerbaikanService $jadwalPerbaikanService,
    ) {}

    public function index(Request $request)
    {
        $requests = $this->jadwalPerbaikanService->paginate([
            'status' => $request->get('status'),
        ]);

        $pendingCount = $this->jadwalPerbaikanService->countPending();
        $needEditCount = $this->jadwalPerbaikanService->countApprovedNeedingEdit();
        $status = $request->get('status');

        return view('jadwal-perbaikan.index', compact('requests', 'pendingCount', 'needEditCount', 'status'));
    }

    public function approve(JadwalPerbaikanRequest $jadwalPerbaikan)
    {
        $this->jadwalPerbaikanService->approve($jadwalPerbaikan);

        return redirect()
            ->route('jadwal-perbaikan.edit', $jadwalPerbaikan)
            ->with('success', 'Permintaan disetujui. Silakan ubah data jadwal. Setelah disimpan, jadwal akan terkunci kembali.');
    }

    public function reject(Request $request, JadwalPerbaikanRequest $jadwalPerbaikan)
    {
        $validated = $request->validate([
            'admin_catatan' => 'nullable|string|max:500',
        ]);

        $this->jadwalPerbaikanService->reject($jadwalPerbaikan, $validated['admin_catatan'] ?? null);

        return redirect()
            ->route('jadwal-perbaikan.index')
            ->with('success', 'Permintaan perbaikan ditolak.');
    }

    public function edit(JadwalPerbaikanRequest $jadwalPerbaikan)
    {
        if ($jadwalPerbaikan->status !== JadwalPerbaikanRequest::STATUS_APPROVED) {
            return redirect()
                ->route('jadwal-perbaikan.index')
                ->with('warning', 'Setujui permintaan terlebih dahulu sebelum mengubah jadwal.');
        }

        $jadwalPerbaikan->load(['jadwal.guru', 'guru']);
        $formData = $this->jadwalPerbaikanService->formData($jadwalPerbaikan->jadwal);

        return view('jadwal-perbaikan.edit', array_merge(compact('jadwalPerbaikan'), $formData));
    }

    public function update(Request $request, JadwalPerbaikanRequest $jadwalPerbaikan)
    {
        $validated = $request->validate([
            'kode_kelas' => 'required|string|max:50',
            'mapel' => 'required|string|max:100',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'tahun_pelajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        $this->jadwalPerbaikanService->updateJadwal($jadwalPerbaikan, $validated);

        return redirect()
            ->route('jadwal-perbaikan.index')
            ->with('success', 'Jadwal berhasil diperbarui dan dikunci kembali.');
    }
}
