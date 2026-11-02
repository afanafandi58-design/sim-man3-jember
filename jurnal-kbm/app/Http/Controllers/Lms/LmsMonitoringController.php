<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\LmsSubmission;
use App\Services\LmsGuruScopeService;
use Illuminate\View\View;

class LmsMonitoringController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        if (! $this->scope->isAdmin($user) && ! $this->scope->isKepala($user)) {
            abort(403);
        }

        $byGuru = Guru::query()
            ->withCount(['jadwals'])
            ->get()
            ->map(function (Guru $guru) {
                $gid = $guru->id;

                return [
                    'guru' => $guru,
                    'materials' => LmsMaterial::query()->where('guru_id', $gid)->count(),
                    'assignments' => LmsAssignment::query()->where('guru_id', $gid)->count(),
                    'quizzes' => LmsQuiz::query()->where('guru_id', $gid)->count(),
                    'submissions' => LmsSubmission::query()
                        ->whereHas('assignment', fn ($q) => $q->where('guru_id', $gid))
                        ->count(),
                ];
            })
            ->sortByDesc(fn ($row) => $row['materials'] + $row['assignments']);

        return view('lms.monitoring.index', compact('byGuru'));
    }
}
