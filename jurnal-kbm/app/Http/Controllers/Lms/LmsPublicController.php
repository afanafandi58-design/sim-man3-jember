<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\PublicSubmitAssignmentRequest;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\LmsSubmission;
use App\Models\Siswa;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsPublicController extends Controller
{
    public function showMaterial(string $token): View
    {
        $material = LmsMaterial::query()
            ->where('share_token', $token)
            ->where('is_published', true)
            ->firstOrFail();

        return view('lms.public.material', compact('material'));
    }

    public function showAssignment(string $token): View
    {
        $assignment = LmsAssignment::query()
            ->where('share_token', $token)
            ->where('is_published', true)
            ->firstOrFail();

        return view('lms.public.assignment', compact('assignment'));
    }

    public function showQuiz(string $token): View
    {
        $quiz = LmsQuiz::query()
            ->where('share_token', $token)
            ->where('is_published', true)
            ->firstOrFail();

        return view('lms.public.quiz', compact('quiz'));
    }

    public function submitAssignmentForm(string $token): View
    {
        $assignment = LmsAssignment::query()
            ->where('share_token', $token)
            ->where('is_published', true)
            ->firstOrFail();

        return view('lms.public.submit-assignment', compact('assignment'));
    }

    public function submitAssignmentStore(PublicSubmitAssignmentRequest $request, string $token): RedirectResponse
    {
        $assignment = LmsAssignment::query()
            ->where('share_token', $token)
            ->where('is_published', true)
            ->firstOrFail();

        $siswa = Siswa::query()
            ->where('nis', $request->string('nis')->trim()->toString())
            ->where('status', 'aktif')
            ->firstOrFail();

        LmsSubmission::query()->updateOrCreate(
            [
                'lms_assignment_id' => $assignment->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'drive_url' => LmsDriveUrl::normalize($request->string('drive_url')->toString()),
                'catatan' => $request->input('catatan'),
                'submitted_at' => now(),
            ],
        );

        return redirect()
            ->route('lms.public.assignment', $token)
            ->with('success', 'Link tugas berhasil dikirim. Terima kasih.');
    }
}
