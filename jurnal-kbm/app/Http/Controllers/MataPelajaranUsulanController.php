<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaranUsulan;
use App\Services\MataPelajaranUsulanService;
use Illuminate\Http\Request;

class MataPelajaranUsulanController extends Controller
{
    public function __construct(
        private MataPelajaranUsulanService $mataPelajaranUsulanService,
    ) {}

    public function index(Request $request)
    {
        $usulans = $this->mataPelajaranUsulanService->paginate([
            'status' => $request->get('status'),
        ]);

        $pendingCount = $this->mataPelajaranUsulanService->countPending();
        $status = $request->get('status');

        return view('master.mata-pelajaran-usulan.index', compact('usulans', 'pendingCount', 'status'));
    }

    public function approve(MataPelajaranUsulan $mataPelajaranUsulan)
    {
        $this->mataPelajaranUsulanService->approve($mataPelajaranUsulan);

        return redirect()
            ->route('mata-pelajaran-usulan.index')
            ->with('success', 'Usulan disetujui. Mata pelajaran telah ditambahkan ke master.');
    }

    public function reject(Request $request, MataPelajaranUsulan $mataPelajaranUsulan)
    {
        $validated = $request->validate([
            'admin_catatan' => 'nullable|string|max:500',
        ]);

        $this->mataPelajaranUsulanService->reject($mataPelajaranUsulan, $validated['admin_catatan'] ?? null);

        return redirect()
            ->route('mata-pelajaran-usulan.index')
            ->with('success', 'Usulan mata pelajaran ditolak.');
    }
}
