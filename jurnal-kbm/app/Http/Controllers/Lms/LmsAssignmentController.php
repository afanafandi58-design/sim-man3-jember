<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLmsJadwal;
use App\Http\Requests\Lms\StoreLmsAssignmentRequest;
use App\Http\Requests\Lms\UpdateLmsAssignmentRequest;
use App\Models\LmsAssignment;
use App\Services\LmsGuruScopeService;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsAssignmentController extends Controller
{
    use InteractsWithLmsJadwal;

    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $query = LmsAssignment::query()->with('guru')->withCount('submissions')->latest();
        if ($gid = $this->scope->guruIdForQuery($user)) {
            $query->where('guru_id', $gid);
        }

        $assignments = $query->paginate(15);

        return view('lms.assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.assignments.create', compact('jadwals'));
    }

    public function store(StoreLmsAssignmentRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        if (! empty($data['drive_url'])) {
            $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        }
        $data['guru_id'] = $this->scope->resolveGuruIdForStore($user, $data['jadwal_id'] ?? null);
        $data['is_published'] = $request->boolean('is_published', true);

        LmsAssignment::query()->create($data);

        return redirect()->route('lms.assignments.index')->with('success', 'Tugas berhasil disimpan.');
    }

    public function edit(LmsAssignment $assignment): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($assignment, $user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.assignments.edit', compact('assignment', 'jadwals'));
    }

    public function update(UpdateLmsAssignmentRequest $request, LmsAssignment $assignment): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($assignment, $user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        if (isset($data['drive_url']) && $data['drive_url'] !== '') {
            $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        }
        $data['is_published'] = $request->boolean('is_published', $assignment->is_published);

        $assignment->update($data);

        return redirect()->route('lms.assignments.index')->with('success', 'Tugas diperbarui.');
    }

    public function destroy(LmsAssignment $assignment): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($assignment, $user);
        $assignment->delete();

        return redirect()->route('lms.assignments.index')->with('success', 'Tugas dihapus.');
    }
}
