<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsSubmissionRequest;
use App\Http\Requests\Lms\UpdateLmsSubmissionRequest;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\Siswa;
use App\Services\LmsGuruScopeService;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsSubmissionController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $query = LmsSubmission::query()
            ->with(['assignment', 'siswa', 'guru'])
            ->latest('submitted_at');

        if ($gid = $this->scope->guruIdForQuery($user)) {
            $query->whereHas('assignment', fn ($q) => $q->where('guru_id', $gid));
        }

        $submissions = $query->paginate(20);

        return view('lms.submissions.index', compact('submissions'));
    }

    public function create(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $assignments = LmsAssignment::query()
            ->when($user->guru_id, fn ($q) => $q->where('guru_id', $user->guru_id))
            ->orderBy('judul')
            ->get();

        $siswas = Siswa::query()->where('status', 'aktif')->orderBy('nama')->limit(500)->get();

        return view('lms.submissions.create', compact('assignments', 'siswas'));
    }

    public function store(StoreLmsSubmissionRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $assignment = LmsAssignment::query()->findOrFail($request->integer('lms_assignment_id'));
        $this->scope->assertOwnsOrStaff($assignment, $user);

        LmsSubmission::query()->create([
            'lms_assignment_id' => $assignment->id,
            'siswa_id' => $request->integer('siswa_id'),
            'drive_url' => LmsDriveUrl::normalize($request->string('drive_url')->toString()),
            'catatan' => $request->input('catatan'),
            'score' => $request->input('score'),
            'guru_id' => $user->guru_id,
            'submitted_at' => now(),
        ]);

        return redirect()->route('lms.submissions.index')->with('success', 'Pengumpulan tercatat.');
    }

    public function edit(LmsSubmission $submission): View
    {
        $user = $this->scope->currentUser();
        $submission->load('assignment');
        $this->scope->assertOwnsOrStaff($submission->assignment, $user);

        return view('lms.submissions.edit', compact('submission'));
    }

    public function update(UpdateLmsSubmissionRequest $request, LmsSubmission $submission): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $submission->load('assignment');
        $this->scope->assertOwnsOrStaff($submission->assignment, $user);

        $submission->update([
            'drive_url' => LmsDriveUrl::normalize($request->string('drive_url')->toString()),
            'catatan' => $request->input('catatan'),
            'score' => $request->input('score'),
            'guru_id' => $user->guru_id ?? $submission->guru_id,
        ]);

        return redirect()->route('lms.submissions.index')->with('success', 'Pengumpulan diperbarui.');
    }

    public function destroy(LmsSubmission $submission): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $submission->load('assignment');
        $this->scope->assertOwnsOrStaff($submission->assignment, $user);
        $submission->delete();

        return redirect()->route('lms.submissions.index')->with('success', 'Pengumpulan dihapus.');
    }
}
