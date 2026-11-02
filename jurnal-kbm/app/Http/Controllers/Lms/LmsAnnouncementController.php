<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsAnnouncementRequest;
use App\Http\Requests\Lms\UpdateLmsAnnouncementRequest;
use App\Models\LmsAnnouncement;
use App\Services\LmsGuruScopeService;
use App\Support\LmsDriveUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsAnnouncementController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        $query = LmsAnnouncement::query()->with('guru')->latest('published_at');

        if ($this->scope->isGuru($user) && ! $this->scope->isAdmin($user)) {
            $query->where(function ($q) use ($user) {
                $q->where('guru_id', $user->guru_id)
                    ->orWhere('scope', 'all');
            });
        }

        $announcements = $query->paginate(15);

        return view('lms.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        return view('lms.announcements.create');
    }

    public function store(StoreLmsAnnouncementRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->scope->assertGuru($user);

        $data = $request->validated();
        if (! empty($data['drive_url'])) {
            $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        }
        $data['guru_id'] = $this->scope->isAdmin($user) && $request->boolean('as_admin')
            ? null
            : $user->guru_id;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['published_at'] = now();

        LmsAnnouncement::query()->create($data);

        return redirect()->route('lms.announcements.index')->with('success', 'Pengumuman dipublikasikan.');
    }

    public function edit(LmsAnnouncement $announcement): View
    {
        $user = $this->scope->currentUser();
        $this->assertAnnouncementAccess($announcement, $user);

        return view('lms.announcements.edit', compact('announcement'));
    }

    public function update(UpdateLmsAnnouncementRequest $request, LmsAnnouncement $announcement): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->assertAnnouncementAccess($announcement, $user);

        $data = $request->validated();
        if (! empty($data['drive_url'])) {
            $data['drive_url'] = LmsDriveUrl::normalize($data['drive_url']);
        }
        $data['is_active'] = $request->boolean('is_active', $announcement->is_active);

        $announcement->update($data);

        return redirect()->route('lms.announcements.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(LmsAnnouncement $announcement): RedirectResponse
    {
        $user = $this->scope->currentUser();
        $this->assertAnnouncementAccess($announcement, $user);
        $announcement->delete();

        return redirect()->route('lms.announcements.index')->with('success', 'Pengumuman dihapus.');
    }

    private function assertAnnouncementAccess(LmsAnnouncement $announcement, $user): void
    {
        if ($this->scope->isAdmin($user)) {
            return;
        }
        if ($announcement->guru_id && (int) $announcement->guru_id === (int) $user->guru_id) {
            return;
        }
        abort(403);
    }
}
