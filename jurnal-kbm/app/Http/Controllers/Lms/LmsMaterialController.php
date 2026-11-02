<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLmsJadwal;
use App\Http\Requests\Lms\StoreLmsMaterialRequest;
use App\Http\Requests\Lms\UpdateLmsMaterialRequest;
use App\Models\LmsMaterial;
use App\Services\LmsGuruScopeService;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsMaterialController extends Controller
{
    use InteractsWithLmsJadwal;

    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $query = LmsMaterial::query()->with('guru')->latest();
        if ($gid = $this->scope->guruIdForQuery($user)) {
            $query->where('guru_id', $gid);
        }

        $materials = $query->paginate(15);

        return view('lms.materials.index', compact('materials'));
    }

    public function create(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.materials.create', compact('jadwals'));
    }

    public function store(StoreLmsMaterialRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        $data['guru_id'] = $this->scope->resolveGuruIdForStore($user, $data['jadwal_id'] ?? null);
        $data['is_published'] = $request->boolean('is_published', true);

        LmsMaterial::query()->create($data);

        return redirect()->route('lms.materials.index')->with('success', 'Materi berhasil disimpan.');
    }

    public function edit(LmsMaterial $material): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($material, $user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.materials.edit', compact('material', 'jadwals'));
    }

    public function update(UpdateLmsMaterialRequest $request, LmsMaterial $material): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($material, $user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        if (isset($data['drive_url'])) {
            $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        }
        $data['is_published'] = $request->boolean('is_published', $material->is_published);

        $material->update($data);

        return redirect()->route('lms.materials.index')->with('success', 'Materi diperbarui.');
    }

    public function destroy(LmsMaterial $material): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($material, $user);
        $material->delete();

        return redirect()->route('lms.materials.index')->with('success', 'Materi dihapus.');
    }
}
