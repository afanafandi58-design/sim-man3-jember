<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLmsJadwal;
use App\Http\Requests\Lms\StoreLmsQuizRequest;
use App\Http\Requests\Lms\UpdateLmsQuizRequest;
use App\Models\LmsQuiz;
use App\Services\LmsGuruScopeService;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsQuizController extends Controller
{
    use InteractsWithLmsJadwal;

    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $query = LmsQuiz::query()->with('guru')->latest();
        if ($gid = $this->scope->guruIdForQuery($user)) {
            $query->where('guru_id', $gid);
        }

        $quizzes = $query->paginate(15);

        return view('lms.quizzes.index', compact('quizzes'));
    }

    public function create(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.quizzes.create', compact('jadwals'));
    }

    public function store(StoreLmsQuizRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        $data['guru_id'] = $this->scope->resolveGuruIdForStore($user, $data['jadwal_id'] ?? null);
        $data['is_published'] = $request->boolean('is_published', true);

        LmsQuiz::query()->create($data);

        return redirect()->route('lms.quizzes.index')->with('success', 'Kuis berhasil disimpan.');
    }

    public function edit(LmsQuiz $quiz): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($quiz, $user);
        $jadwals = $this->jadwalOptionsFor($user);

        return view('lms.quizzes.edit', compact('quiz', 'jadwals'));
    }

    public function update(UpdateLmsQuizRequest $request, LmsQuiz $quiz): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($quiz, $user);

        $data = $request->validated();
        $this->applyJadwalMeta($data);
        $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        $data['is_published'] = $request->boolean('is_published', $quiz->is_published);

        $quiz->update($data);

        return redirect()->route('lms.quizzes.index')->with('success', 'Kuis diperbarui.');
    }

    public function destroy(LmsQuiz $quiz): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertOwnsOrStaff($quiz, $user);
        $quiz->delete();

        return redirect()->route('lms.quizzes.index')->with('success', 'Kuis dihapus.');
    }
}
