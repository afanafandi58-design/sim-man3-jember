<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\UpdateLmsSettingsRequest;
use App\Models\LmsSetting;
use App\Services\LmsGuruScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LmsSettingController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function edit(): View
    {
        $user = $this->scope->currentUser();
        if (! $this->scope->isAdmin($user)) {
            abort(403);
        }

        $settings = [
            'drive_folder_url' => LmsSetting::getValue('drive_folder_url', ''),
            'share_instructions' => LmsSetting::getValue('share_instructions', 'Bagikan link Google Drive dengan siswa. Siswa tidak perlu login SIM.'),
            'manual_links_only' => LmsSetting::getValue('manual_links_only', '1'),
        ];

        $oauthConfigured = filled(env('GOOGLE_CLIENT_ID')) && filled(env('GOOGLE_CLIENT_SECRET'));

        return view('lms.settings.edit', compact('settings', 'oauthConfigured'));
    }

    public function update(UpdateLmsSettingsRequest $request): RedirectResponse
    {
        $user = $this->scope->currentUser();
        if (! $this->scope->isAdmin($user)) {
            abort(403);
        }

        $uid = (int) $user->id;
        LmsSetting::setValue('drive_folder_url', $request->input('drive_folder_url'), $uid);
        LmsSetting::setValue('share_instructions', $request->input('share_instructions'), $uid);
        LmsSetting::setValue('manual_links_only', $request->boolean('manual_links_only') ? '1' : '0', $uid);

        return redirect()->route('lms.settings.edit')->with('success', 'Pengaturan LMS disimpan.');
    }
}
