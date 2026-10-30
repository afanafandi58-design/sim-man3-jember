<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Services\LmsGuruScopeService;
use Illuminate\View\View;

class LmsShareLinksController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $gid = $this->scope->guruIdForQuery($user);

        $materials = LmsMaterial::query()
            ->when($gid, fn ($q) => $q->where('guru_id', $gid))
            ->where('is_published', true)
            ->latest()
            ->limit(50)
            ->get();

        $assignments = LmsAssignment::query()
            ->when($gid, fn ($q) => $q->where('guru_id', $gid))
            ->where('is_published', true)
            ->latest()
            ->limit(50)
            ->get();

        $quizzes = LmsQuiz::query()
            ->when($gid, fn ($q) => $q->where('guru_id', $gid))
            ->where('is_published', true)
            ->latest()
            ->limit(50)
            ->get();

        return view('lms.share.index', compact('materials', 'assignments', 'quizzes'));
    }
}
