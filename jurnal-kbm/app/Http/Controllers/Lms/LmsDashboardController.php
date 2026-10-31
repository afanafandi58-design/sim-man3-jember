<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\LmsAnnouncement;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\LmsSubmission;
use App\Services\LmsGuruScopeService;
use Illuminate\View\View;

class LmsDashboardController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $guruId = $this->scope->guruIdForQuery($user);

        $materialsQuery = LmsMaterial::query();
        $assignmentsQuery = LmsAssignment::query();
        $quizzesQuery = LmsQuiz::query();
        $submissionsQuery = LmsSubmission::query()->whereHas('assignment');

        if ($guruId) {
            $materialsQuery->where('guru_id', $guruId);
            $assignmentsQuery->where('guru_id', $guruId);
            $quizzesQuery->where('guru_id', $guruId);
            $submissionsQuery->whereHas('assignment', fn ($q) => $q->where('guru_id', $guruId));
        }

        $stats = [
            'materials' => (clone $materialsQuery)->count(),
            'assignments' => (clone $assignmentsQuery)->count(),
            'quizzes' => (clone $quizzesQuery)->count(),
            'submissions' => (clone $submissionsQuery)->count(),
        ];

        $announcements = LmsAnnouncement::query()
            ->where('is_active', true)
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('lms.dashboard', compact('user', 'stats', 'announcements'));
    }
}
